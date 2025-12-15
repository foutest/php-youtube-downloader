// public/js/app.js
import { Api } from './modules/api.js';  // Importa o módulo de API que contém as funções de comunicação com o backend
import { Ui } from './modules/ui.js';    // Importa o módulo de UI que contém as funções de interação com a interface do usuário

$(document).ready(function() {
    let checkInterval = null;  // Variável que armazena o intervalo de monitoramento do progresso de download

    // 1. Carrega a lista inicial de downloads
    refreshDownloads();

    // --- EVENTOS DE FORMULÁRIO ---

    // Evento ao submeter o formulário de busca do vídeo
    $('#search-form').on('submit', function(e) {
        e.preventDefault();  // Previne o comportamento padrão do formulário (recarregar a página)
        const url = $('#url-input').val();  // Pega a URL do campo de input
        
        // Exibe um carregamento enquanto a requisição é processada
        Ui.toggleLoading(true);

        // Chama a API para obter informações sobre o vídeo
        Api.getVideoInfo(url)
            .done(function(response) {  // Quando a resposta é recebida
                Ui.toggleLoading(false);  // Remove o indicador de carregamento
                if (response.status === 'success') {
                    Ui.showPreview(response.data);  // Exibe a visualização do vídeo
                } else {
                    Ui.showToast('Erro: 1' + response.message, 'error');  // Exibe mensagem de erro
                }
            })
            .fail(function() {  // Se houver erro na requisição
                Ui.toggleLoading(false);
                Ui.showToast('Erro de conexão.', 'error');  // Exibe erro de conexão
            });
    });

    // Evento de clique no botão "Limpar"
    $('#btn-clear').on('click', function () {
        // Limpa os campos e elementos da interface
        $('#url-input').val('');
        $('#preview-area').addClass('hidden').hide();  // Esconde a área de pré-visualização
        $('#progress-area').addClass('hidden').hide();  // Esconde a área de progresso
        $('#btn-clear').addClass('hidden');  // Esconde o botão de limpar

        // Reseta o botão de download e a barra de progresso
        Ui.setDownloadState(false);
        $('#progress-bar').css('width', '0%');
        $('#progress-percent').text('');
        $('#progress-info').html('');

        // Foca no campo de input
        $('#url-input').focus();
    });

    // Evento de clique no botão "Download"
    $('#btn-download').on('click', function() {
        const url = $('#url-input').val();  // Pega a URL inserida
        const format = $('#format-select').val();  // Pega o formato selecionado

        if(!url) return;  // Se não houver URL, não faz nada

        Ui.setDownloadState(true);  // Ativa o estado de "download em andamento"
        
        // Chama a API para iniciar o download
        Api.startDownload(url, format)
            .done(function(response) {
                if (response.status === 'success') {
                    Ui.startProgressBar();  // Inicia a barra de progresso
                    Ui.showToast('Download iniciado!', 'success');  // Exibe mensagem de sucesso
                    monitorProgress(response.id);  // Inicia o monitoramento do progresso do download
                } else {
                    Ui.showToast('Erro: ' + response.message, 'error');  // Exibe erro caso o download não tenha sido iniciado
                    Ui.setDownloadState(false);  // Reseta o estado do botão de download
                }
            });
    });

    // --- FUNÇÕES DE CONTROLE ---

    // Função que monitora o progresso do download
    function monitorProgress(id) {
        if (checkInterval) clearInterval(checkInterval);  // Limpa qualquer intervalo anterior

        checkInterval = setInterval(function() {
            Api.checkProgress(id).done(function(res) {
                const percent = res.percent;  // Percentual do progresso

                // Atualiza a barra de progresso com os dados recebidos
                Ui.updateProgress(percent, res.eta, res.elapsed);

                // Se o download estiver completo, finaliza o monitoramento
                if (res.status === 'completed' || percent >= 100) {
                    clearInterval(checkInterval);  // Interrompe o monitoramento
                    Ui.finishProgressBar();  // Finaliza a barra de progresso
                    Ui.setDownloadState(false);  // Desativa o estado de "download em andamento"
                    Ui.showToast('Download concluído!', 'success');  // Exibe mensagem de sucesso
                    refreshDownloads(true);  // Atualiza a lista de downloads
                }
            });
        }, 1000);  // Checa o progresso a cada 1 segundo
    }

    // Função que atualiza a lista de downloads
    function refreshDownloads(highlightFirst = false) {
        Api.getDownloads().done(function(files) {
            Ui.renderDownloads(files, highlightFirst);  // Renderiza a lista de downloads na interface
        });
    }

    // --- EXPOR FUNÇÕES PARA O HTML (ONCLICK) ---
    // Como agora usamos módulos, as funções não são globais por padrão.
    // Precisamos atrelá-las ao 'window' para que o HTML consiga vê-las.
    
    // Função para excluir um arquivo
    window.deleteFile = function(filename) {
        if(!confirm(`Excluir "${filename}"?`)) return;  // Confirma a exclusão com o usuário
        
        // Chama a API para excluir o arquivo
        Api.deleteFile(filename).done(function(res) {
            if(res.status === 'success') {
                Ui.showToast('Arquivo excluído.', 'success');  // Exibe mensagem de sucesso
                refreshDownloads();  // Atualiza a lista de downloads
            } else {
                Ui.showToast('Erro ao excluir.', 'error');  // Exibe erro caso a exclusão falhe
            }
        });
    };

    // Função para reproduzir o arquivo
    window.playFile = function(filename) {
        Ui.openPlayer(filename);  // Abre o player de vídeo para o arquivo
    };
});
