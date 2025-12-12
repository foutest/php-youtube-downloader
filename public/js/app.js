// public/js/app.js
import { Api } from './modules/api.js';
import { Ui } from './modules/ui.js';

$(document).ready(function() {
    let checkInterval = null;

    // 1. Carrega a lista inicial
    refreshDownloads();

    // --- EVENTOS DE FORMULÁRIO ---

    // Buscar Vídeo
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        const url = $('#url-input').val();
        
        Ui.toggleLoading(true);

        Api.getVideoInfo(url)
            .done(function(response) {
                Ui.toggleLoading(false);
                if (response.status === 'success') {
                    Ui.showPreview(response.data);
                } else {
                    Ui.showToast('Erro: 1' + response.message, 'error');
                }
            })
            .fail(function() {
                Ui.toggleLoading(false);
                Ui.showToast('Erro die conexão.', 'error');
            });
    });

    //Botao limpar
    $('#btn-clear').on('click', function () {
        $('#url-input').val('');
        $('#preview-area').addClass('hidden').hide();
        $('#progress-area').addClass('hidden').hide();
        $('#btn-clear').addClass('hidden');
    
        // Reseta botão de download
        Ui.setDownloadState(false);
    
        // Opcional: limpa barra de progresso
        $('#progress-bar').css('width', '0%');
        $('#progress-percent').text('');
        $('#progress-info').html('');
    
        // Foca no input
        $('#url-input').focus();
    });
    

    // Iniciar Download
    $('#btn-download').on('click', function() {
        const url = $('#url-input').val();
        const format = $('#format-select').val();

        if(!url) return;

        Ui.setDownloadState(true);
        
        Api.startDownload(url, format)
            .done(function(response) {
                if (response.status === 'success') {
                    Ui.startProgressBar();
                    Ui.showToast('Download iniciado!', 'success');
                    monitorProgress(response.id);
                } else {
                    Ui.showToast('Erro: ' + response.message, 'error');
                    Ui.setDownloadState(false);
                }
            });
    });

    // --- FUNÇÕES DE CONTROLE ---

    function monitorProgress(id) {
        if (checkInterval) clearInterval(checkInterval);

        checkInterval = setInterval(function() {
            Api.checkProgress(id).done(function(res) {
                const percent = res.percent;
                
                // PASSAMOS OS NOVOS DADOS AQUI:
                Ui.updateProgress(percent, res.eta, res.elapsed);

                if (res.status === 'completed' || percent >= 100) {
                    clearInterval(checkInterval);
                    Ui.finishProgressBar();
                    Ui.setDownloadState(false);
                    Ui.showToast('Download concluído!', 'success');
                    refreshDownloads(true);
                }
            });
        }, 1000);
    }

    function refreshDownloads(highlightFirst = false) {
        Api.getDownloads().done(function(files) {
            Ui.renderDownloads(files, highlightFirst);
        });
    }

    // --- EXPOR FUNÇÕES PARA O HTML (ONCLICK) ---
    // Como agora usamos módulos, as funções não são globais por padrão.
    // Precisamos atrelá-las ao 'window' para que o HTML consiga vê-las.
    
    window.deleteFile = function(filename) {
        if(!confirm(`Excluir "${filename}"?`)) return;
        
        Api.deleteFile(filename).done(function(res) {
            if(res.status === 'success') {
                Ui.showToast('Arquivo excluído.', 'success');
                refreshDownloads();
            } else {
                Ui.showToast('Erro ao excluir.', 'error');
            }
        });
    };

    window.playFile = function(filename) {
        Ui.openPlayer(filename);
    };
});