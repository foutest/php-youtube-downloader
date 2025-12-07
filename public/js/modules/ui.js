// public/js/modules/ui.js

export const Ui = {
    // Notificações
    showToast: (message, type = 'info') => {
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
        $toast.on('hidden.bs.toast', function () { $(this).remove(); });
    },

    // Controle de estado de carregamento
    toggleLoading: (isLoading) => {
        if (isLoading) {
            $('#loading-spinner').removeClass('hidden');
            $('#preview-area, #progress-area, #featured-section').addClass('hidden');
            $('#btn-search').prop('disabled', true);
        } else {
            $('#loading-spinner').addClass('hidden');
            $('#btn-search').prop('disabled', false);
        }
    },

    // Exibir informações do vídeo e preencher select
    showPreview: (data) => {
        $('#video-thumb').attr('src', data.thumbnail);
        $('#video-title').text(data.title);
        $('#video-duration').text(data.duration);
        
        const $select = $('#format-select');
        $select.empty();

        if (data.formats.video && data.formats.video.length > 0) {
            let groupVideo = $('<optgroup label="Vídeo (MP4)">');
            data.formats.video.forEach(fmt => {
                groupVideo.append(`<option value="${fmt.id}">${fmt.label}</option>`);
            });
            $select.append(groupVideo);
        }

        if (data.formats.audio && data.formats.audio.length > 0) {
            let groupAudio = $('<optgroup label="Apenas Áudio">');
            data.formats.audio.forEach(fmt => {
                groupAudio.append(`<option value="${fmt.id}">${fmt.label}</option>`);
            });
            $select.append(groupAudio);
        }

        $('#preview-area').removeClass('hidden').hide().fadeIn();
    },

    // Botão de Download
    setDownloadState: (isDownloading) => {
        if (isDownloading) {
            $('#btn-download').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Iniciando...');
        } else {
            $('#btn-download').prop('disabled', false).html('<i class="fa-solid fa-download me-2"></i> Baixar Agora');
        }
    },

    // Barra de Progresso
    startProgressBar: () => {
        $('#preview-area').addClass('hidden');
        $('#progress-area').removeClass('hidden');
        $('#progress-bar').css('width', '0%').addClass('bg-danger').removeClass('bg-success');
    },

    updateProgress: (percent) => {
        $('#progress-bar').css('width', percent + '%');
        $('#progress-percent').text(percent + '%');
    },

    finishProgressBar: () => {
        $('#progress-bar').removeClass('bg-danger').addClass('bg-success');
        $('#progress-percent').text('Concluído!');
        setTimeout(() => {
            $('#progress-area').fadeOut();
            $('#url-input').val('');
        }, 3000);
    },

    // Renderizar Lista de Downloads (Com destaque e mobile support)
    renderDownloads: (files, highlightFirst = false) => {
        $('#downloads-list').html('');
        $('#featured-section').addClass('hidden');

        if (files.length === 0) {
            $('#downloads-list').html('<tr><td colspan="3" class="text-center text-muted py-4">Nenhum download recente.</td></tr>');
            return;
        }

        // Destaque (Featured)
        if (highlightFirst && files.length > 0) {
            const newest = files[0];
            const featuredHtml = `
                <div class="row align-items-center">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <h5 class="fw-bold mb-1 text-truncate">${newest.title || newest.name}</h5>
                        <span class="badge bg-dark border border-secondary">${newest.size}</span>
                    </div>
                    <div class="col-md-4 d-flex justify-content-md-end gap-2">
                        <button onclick="window.playFile('${newest.name}')" class="btn btn-info text-white fw-bold">
                            <i class="fa-solid fa-play me-2"></i> Assistir
                        </button>
                        <a href="${newest.link}" class="btn btn-outline-light">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>
                </div>
            `;
            $('#featured-content').html(featuredHtml);
            $('#featured-section').removeClass('hidden').hide().fadeIn();
        }

        // Tabela
        const tableFiles = highlightFirst ? files.slice(1) : files;
        
        if (tableFiles.length > 0) {
            let html = '';
            tableFiles.forEach(f => {
                const displayName = f.title || f.name;
                html += `
                    <tr>
                        <td class="ps-4 text-white align-middle name-col">
                            <i class="fa-solid fa-video me-2 text-danger d-md-none"></i>
                            ${displayName}
                        </td>
                        <td class="text-white-50 align-middle">${f.size}</td>
                        <td class="text-end pe-4 align-middle action-col">
                            <div class="d-flex gap-2">
                                <button onclick="window.playFile('${f.name}')" class="btn btn-sm btn-outline-info" title="Assistir">
                                    <i class="fa-solid fa-play"></i>
                                </button>
                                <a href="${f.link}" class="btn btn-sm btn-outline-light" title="Baixar">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                <button onclick="window.deleteFile('${f.name}')" class="btn btn-sm btn-outline-danger" title="Excluir">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            $('#downloads-list').html(html);
        }
    },

    // Modal Player
    openPlayer: (filename) => {
        const streamUrl = 'stream.php?file=' + encodeURIComponent(filename);
        const ext = filename.split('.').pop().toLowerCase();
        let playerHtml = '';

        if (['mp3', 'm4a', 'wav'].includes(ext)) {
            playerHtml = `
                <div class="p-5">
                    <i class="fa-solid fa-music fa-4x text-info mb-4"></i>
                    <audio controls autoplay class="w-100">
                        <source src="${streamUrl}" type="audio/mp4">
                        Navegador não suporta áudio.
                    </audio>
                </div>
            `;
        } else {
            playerHtml = `
                <video controls autoplay class="w-100" style="max-height: 70vh;">
                    <source src="${streamUrl}" type="video/mp4">
                    Navegador não suporta vídeo.
                </video>
            `;
        }

        $('#player-container').html(playerHtml);
        $('#playerTitle').text(filename);
        const myModal = new bootstrap.Modal(document.getElementById('playerModal'));
        myModal.show();
        $('#playerModal').on('hidden.bs.modal', function () {
            $('#player-container').html('');
        });
    }
};