// public/js/modules/api.js

export const Api = {
    getVideoInfo: (url) => {
        return $.post('ajax/getInfo.php', { url }, null, 'json');
    },
    
    startDownload: (url, format) => {
        return $.post('ajax/start.php', { url, format }, null, 'json');
    },
    
    checkProgress: (id) => {
        return $.get('ajax/progress.php', { id }, null, 'json');
    },
    
    getDownloads: () => {
        return $.get('ajax/list.php', null, null, 'json');
    },
    
    deleteFile: (filename) => {
        return $.post('ajax/delete.php', { file: filename }, null, 'json');
    }
};