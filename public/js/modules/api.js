// public/js/modules/api.js

// O objeto Api contém vários métodos para interagir com a API do servidor via AJAX.
export const Api = {
    // Método para pegar informações sobre o vídeo, usando uma requisição POST.
    getVideoInfo: (url) => {
        // Envia a URL do vídeo para o servidor e retorna a resposta no formato JSON.
        return $.post('ajax/getInfo.php', { url }, null, 'json');
    },
    
    // Método para iniciar o download de um vídeo, passando a URL e o formato desejado.
    startDownload: (url, format) => {
        // Envia a URL do vídeo e o formato para o servidor, e recebe a resposta em formato JSON.
        return $.post('ajax/start.php', { url, format }, null, 'json');
    },
    
    // Método para verificar o progresso do download com base no ID do download.
    checkProgress: (id) => {
        // Realiza uma requisição GET para checar o progresso do download e retorna a resposta JSON.
        return $.get('ajax/progress.php', { id }, null, 'json');
    },
    
    // Método para listar todos os downloads disponíveis.
    getDownloads: () => {
        // Envia uma requisição GET para o servidor para obter a lista de downloads em formato JSON.
        return $.get('ajax/list.php', null, null, 'json');
    },
    
    // Método para excluir um arquivo do servidor.
    deleteFile: (filename) => {
        // Envia uma requisição POST para excluir o arquivo e retorna a resposta JSON.
        return $.post('ajax/delete.php', { file: filename }, null, 'json');
    }
};
