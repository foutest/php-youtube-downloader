// public/js/app.js

$(document).ready(function() {
    let checkInterval = null;

    // Inicialização
    loadDownloads();

    // --- FUNÇÕES UTILITÁRIAS ---
    
    // Mostra notificação bonita (Toast)
    function showToast(message, type = 'info') {
        const bgClass = type === 'error' ? 'bg-danger' : 'bg-success';
        const toastHtml = `
            <div class="toast align-items-center text-white ${bgClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        const $toast = $(toastHtml);
        $('#toast-container').append($toast);
        const toast = new bootstrap.Toast($toast[0]);
        toast.show();
        
        // Remove do DOM depois de fechar
        $toast.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }

    // --- LÓGICA DE DOWNLOAD ---

    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        let url = $('#url-input').val();
        
        // UI Updates
        $('#loading-spinner').removeClass('hidden');
        $('#preview-area, #progress-area').addClass('hidden');
        $('#btn-search').prop('disabled', true);

        $.post('ajax/getInfo.php', { url: url }, function(response) {
            $('#loading-spinner').addClass('hidden');
            $('#btn-search').prop('disabled', false);

            if (response.status === 'success') {
                let data = response.data;
                $('#video-thumb').attr('src', data.thumbnail);
                $('#video-title').text(data.title);
                $('#video-duration').text(data.duration);
                
                let $select = $('#format-select');
                $select.empty();
                data.formats.forEach(fmt => {
                    $select.append(`<option value="${fmt.format_id}">${fmt.label} - ${fmt.size}</option>`);
                });

                $('#preview-area').removeClass('hidden').hide().fadeIn();
            } else {
                showToast('Erro: ' + response.message, 'error');
            }
        }, 'json').fail(() => {
            $('#loading-spinner').addClass('hidden');
            $('#btn-search').prop('disabled', false);
            showToast('Erro de conexão.', 'error');
        });
    });

    $('#btn-download').on('click', function() {
        let url = $('#url-input').val();
        let format = $('#format-select').val();

        if(!url) return;

        $('#btn-download').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Iniciando...');
        
        $.post('ajax/start.php', { url: url, format: format }, function(response) {
            if (response.status === 'success') {
                startProgressCheck(response.id);
                showToast('Download iniciado!', 'success');
            } else {
                showToast('Erro: ' + response.message, 'error');
                resetDownloadButton();
            }
        }, 'json');
    });

    function startProgressCheck(id) {
        $('#preview-area').addClass('hidden');
        $('#progress-area').removeClass('hidden');
        $('#progress-bar').css('width', '0%').addClass('bg-danger').removeClass('bg-success');

        if (checkInterval) clearInterval(checkInterval);

        checkInterval = setInterval(function() {
            $.get('ajax/progress.php', { id: id }, function(res) {
                let percent = res.percent;
                $('#progress-bar').css('width', percent + '%');
                $('#progress-percent').text(percent + '%');

                if (res.status === 'completed' || percent >= 100) {
                    clearInterval(checkInterval);
                    finishDownload();
                }
            }, 'json');
        }, 1000);
    }

    function finishDownload() {
        $('#progress-bar').removeClass('bg-danger').addClass('bg-success');
        $('#progress-percent').text('Concluído!');
        loadDownloads();
        showToast('Download concluído com sucesso!', 'success');

        setTimeout(function() {
            $('#progress-area').fadeOut();
            resetDownloadButton();
            $('#url-input').val('');
        }, 3000);
    }

    function resetDownloadButton() {
        $('#btn-download').prop('disabled', false).html('<i class="fa-solid fa-download me-2"></i> Baixar Agora');
    }

    function loadDownloads() {
        $.get('ajax/list.php', function(files) {
            let html = '';
            if (files.length === 0) {
                html = '<tr><td colspan="3" class="text-center text-muted py-4">Nenhum download recente.</td></tr>';
            } else {
                files.forEach(function(f) {
                    html += `
                        <tr>
                            <td class="ps-4 text-white align-middle">
                                <i class="fa-solid fa-video me-2 text-danger d-md-none"></i>
                                ${f.name}
                            </td>
                            <td class="text-white-50 align-middle">${f.size}</td>
                            <td class="text-end pe-4">
                                <a href="${f.link}" class="btn btn-sm btn-outline-light me-2" title="Baixar">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                <button onclick="window.deleteFile('${f.name}')" class="btn btn-sm btn-outline-danger" title="Excluir">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#downloads-list').html(html);
        });
    }

    // Torna global para o onclick funcionar
    window.deleteFile = function(filename) {
        if(!confirm(`Excluir "${filename}"?`)) return;

        $.post('ajax/delete.php', { file: filename }, function(res) {
            if(res.status === 'success') {
                loadDownloads();
                showToast('Arquivo excluído.', 'success');
            } else {
                showToast('Erro ao excluir.', 'error');
            }
        }, 'json');
    };
});