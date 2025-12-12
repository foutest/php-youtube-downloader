// public/js/modules/ui.js

export const Ui = {
    // ... (As funções showToast e toggleLoading continuam iguais) ...
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

    toggleLoading: (isLoading) => {
        if (isLoading) {
            $('#loading-spinner').removeClass('hidden');
            $('#btn-search').prop('disabled', true);
        } else {
            $('#loading-spinner').addClass('hidden');
            $('#btn-search').prop('disabled', false);
        }
    },

    // --- AQUI ESTÁ A MUDANÇA VISUAL ---
    showPreview: (data) => {
        $('#video-thumb').attr('src', data.thumbnail);
        $('#video-title').text(data.title);
        $('#video-duration').text(data.duration);
        
        const $select = $('#format-select');
        $select.empty();

        // 1. Grupo de VÍDEO (Com ícones e detalhes)
        if (data.formats.video && data.formats.video.length > 0) {
            let groupVideo = $('<optgroup label="🎥 Vídeo (MP4/WebM)">');
            
            data.formats.video.forEach(fmt => {
                // Lógica de Fusão Inteligente
                let isMuted = fmt.acodec === 'none';
                let audioIcon = isMuted ? '🔇' : '🔊';
                
                // TRUQUE: Se for mudo, o valor enviado será "ID+bestaudio"
                // Se tiver áudio, manda só o "ID"
                let valueToSend = isMuted ? `${fmt.id}+bestaudio` : fmt.id;

                // Agora que garantimos o áudio, podemos mostrar um ícone positivo
                // ou avisar que será processado. Vamos deixar o ícone de som normal!
                // (Opcional: Se quiser ser transparente, use um ícone de "ferramenta" 🛠️)
                let displayIcon = '🔊'; 
                
                let hdStatus = (fmt.height >= 720) ? 'ᴴᴰ' : '';
                
                let parts = [
                    `${fmt.resolution} (${fmt.ext})`,
                    hdStatus,
                    (fmt.fps > 30 ? `${fmt.fps}fps` : ''),
                    displayIcon, // Mostramos som porque vamos corrigir!
                    fmt.size
                ];
                
                let label = parts.filter(p => p !== '').join(' • ');
                
                groupVideo.append(`<option value="${valueToSend}">${label}</option>`);
            });
            $select.append(groupVideo);
        }

        // 2. Grupo de ÁUDIO
        if (data.formats.audio && data.formats.audio.length > 0) {
            let groupAudio = $('<optgroup label="🎧 Áudio Puro">');
            
            data.formats.audio.forEach(fmt => {
                let label = `${fmt.ext.toUpperCase()} • ${fmt.bitrate}kbps • ${fmt.size}`;
                groupAudio.append(`<option value="${fmt.id}">${label}</option>`);
            });
            $select.append(groupAudio);
        }

        $('#preview-area').removeClass('hidden').hide().fadeIn();
    },

    // ... (O resto do arquivo: setDownloadState, startProgressBar, etc. continua igual) ...
    setDownloadState: (isDownloading) => {
        if (isDownloading) {
            $('#btn-download').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Download em andamento...');
        } else {
            $('#btn-download').prop('disabled', false).html('<i class="fa-solid fa-download me-2"></i> Baixar Agora');
        }
    },

    // ...
    
    startProgressBar: () => {
        $('#progress-area').removeClass('hidden').fadeIn();
        $('#featured-section').addClass('hidden');
        $('#progress-bar').css('width', '0%').addClass('bg-danger').removeClass('bg-success');
        
        // Limpa ou cria a área de info de tempo
        if ($('#progress-info').length === 0) {
             $('.progress').after('<div id="progress-info" class="d-flex justify-content-between text-muted small mt-1"><span>Tempo: 00:00</span><span>Faltam: --:--</span></div>');
        } else {
             $('#progress-info').html('<span>Tempo: 00:00</span><span>Faltam: --:--</span>');
        }
    },

    updateProgress: (percent, eta, elapsed) => {
        $('#progress-bar').css('width', percent + '%');
        $('#progress-percent').text(Math.round(percent) + '%');
        
        // Atualiza os tempos
        // Se eta ou elapsed vierem vazios, usamos placeholders
        const etaText = eta || '--:--';
        const elapsedText = elapsed || '00:00';
        
        $('#progress-info').html(`
            <span><i class="fa-regular fa-clock me-1"></i> Decorrido: ${elapsedText}</span>
            <span><i class="fa-solid fa-hourglass-half me-1"></i> Restante: ${etaText}</span>
        `);
    },

    finishProgressBar: () => {
        $('#progress-bar').removeClass('bg-danger').addClass('bg-success');
        $('#progress-percent').text('Concluído!');
        $('#progress-info').html('<span class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i> Finalizado com sucesso!</span>');
        
        setTimeout(() => {
            $('#progress-area').fadeOut();
        }, 1000);
    },
    
    // ...

    renderDownloads: (files, highlightFirst = false) => {
        $('#downloads-list').html('');
        $('#featured-section').addClass('hidden');

        if (files.length === 0) {
            $('#downloads-list').html('<tr><td colspan="3" class="text-center text-muted py-4">Nenhum download recente.</td></tr>');
            return;
        }

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