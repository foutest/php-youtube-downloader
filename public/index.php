<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YT Downloader Pro</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* --- Tema Dark Personalizado --- */
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        .navbar {
            background-color: #1e1e1e !important;
            border-bottom: 1px solid #333;
        }

        .brand-icon {
            color: #ff0000;
            font-size: 1.5rem;
            margin-right: 8px;
        }

        .card-custom {
            background-color: #1e1e1e;
            border: 1px solid #333;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }

        .form-control-dark {
            background-color: #121212;
            border: 1px solid #333;
            color: #fff;
            padding: 12px;
        }
        
        .form-control-dark:focus {
            background-color: #000;
            border-color: #ff0000;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(255, 0, 0, 0.25);
        }

        .btn-yt {
            background-color: #cc0000;
            color: white;
            font-weight: 600;
            border: none;
            padding: 10px 25px;
            transition: all 0.3s;
        }

        .btn-yt:hover {
            background-color: #ff0000;
            transform: translateY(-2px);
            color: white;
        }

        /* Thumbail Preview */
        .thumb-container {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
        
        .thumb-img {
            width: 100%;
            height: auto;
            display: block;
        }

        .duration-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        /* Tabela de Downloads */
        .table-dark-custom {
            background-color: transparent;
        }
        .table-dark-custom td, .table-dark-custom th {
            background-color: transparent;
            border-bottom: 1px solid #333;
            vertical-align: middle;
        }
        
        /* Utilitários */
        .text-yt { color: #ff0000; }
        .hidden { display: none; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark mb-5">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fa-brands fa-youtube brand-icon"></i>
                <strong>Downloader<span class="text-white fw-light">Pro</span></strong>
            </a>
        </div>
    </nav>

    <div class="container">
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">Baixe vídeos com qualidade</h2>
                    <p class="text-muted">Cole o link do YouTube abaixo para começar</p>
                </div>

                <div class="card-custom p-4">
                    <form id="search-form">
                        <div class="input-group">
                            <input type="text" id="url-input" class="form-control form-control-dark rounded-start" placeholder="https://www.youtube.com/watch?v=..." required>
                            <button class="btn btn-yt rounded-end" type="submit" id="btn-search">
                                <i class="fa-solid fa-magnifying-glass me-2"></i> Buscar
                            </button>
                        </div>
                    </form>
                    <div id="loading-spinner" class="text-center mt-3 hidden">
                        <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="text-muted small mt-2">Analisando link...</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="preview-area" class="row justify-content-center hidden">
            <div class="col-lg-8">
                <div class="card-custom p-0 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-5 bg-black d-flex align-items-center justify-content-center p-3">
                            <div class="thumb-container">
                                <img src="" id="video-thumb" class="thumb-img" alt="Thumbnail">
                                <span class="duration-badge" id="video-duration">00:00</span>
                            </div>
                        </div>
                        <div class="col-md-7 p-4">
                            <h5 class="card-title fw-bold mb-3" id="video-title">Título do Vídeo</h5>
                            
                            <div class="mb-4">
                                <label class="text-muted small mb-1">Escolha o formato:</label>
                                <select class="form-select form-select-sm bg-dark text-white border-secondary" id="format-select">
                                    </select>
                            </div>

                            <button id="btn-download" class="btn btn-yt w-100">
                                <i class="fa-solid fa-download me-2"></i> Baixar Agora
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="progress-area" class="row justify-content-center mt-3 hidden">
            <div class="col-lg-8">
                <div class="card-custom p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold"><i class="fa-solid fa-gear fa-spin me-2"></i>Processando download...</span>
                        <span id="progress-percent">0%</span>
                    </div>
                    <div class="progress" style="height: 10px; background-color: #333;">
                        <div id="progress-bar" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="mt-3 text-center">
                        <small class="text-muted">Isso pode levar alguns instantes dependendo do tamanho do vídeo.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-lg-8">
                <h5 class="mb-3 text-muted"><i class="fa-solid fa-folder-open me-2"></i>Downloads Recentes</h5>
                <div class="card-custom p-0">
                    <div class="table-responsive">
                        <table class="table table-dark-custom mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Vídeo</th>
                                    <th>Tamanho</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="downloads-list">
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        Nenhum download recente.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Variáveis globais
            let checkInterval = null;

            // 1. Carregar lista inicial
            loadDownloads();

            // 2. Evento: BUSCAR VÍDEO
            $('#search-form').on('submit', function(e) {
                e.preventDefault();
                
                let url = $('#url-input').val();
                
                // Interface
                $('#loading-spinner').removeClass('hidden');
                $('#preview-area').addClass('hidden');
                $('#progress-area').addClass('hidden');
                $('#btn-search').prop('disabled', true);

                // AJAX POST
                $.post('ajax/getInfo.php', { url: url }, function(response) {
                    $('#loading-spinner').addClass('hidden');
                    $('#btn-search').prop('disabled', false);

                    if (response.status === 'success') {
                        let data = response.data;
                        
                        // Preenche os dados
                        $('#video-thumb').attr('src', data.thumbnail);
                        $('#video-title').text(data.title);
                        $('#video-duration').text(data.duration);
                        
                        // Limpa e preenche o Select
                        let $select = $('#format-select');
                        $select.empty();
                        
                        data.formats.forEach(function(fmt) {
                            $select.append(`<option value="${fmt.format_id}">${fmt.label} - ${fmt.size}</option>`);
                        });

                        // Mostra a área
                        $('#preview-area').removeClass('hidden').hide().fadeIn();
                    } else {
                        alert('Erro: ' + response.message);
                    }
                }, 'json').fail(function() {
                    $('#loading-spinner').addClass('hidden');
                    $('#btn-search').prop('disabled', false);
                    alert('Erro de conexão com o servidor.');
                });
            });

            // 3. Evento: INICIAR DOWNLOAD
            $('#btn-download').on('click', function() {
                let url = $('#url-input').val();
                let format = $('#format-select').val();

                if(!url) return;

                // Interface
                $('#btn-download').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Iniciando...');
                
                $.post('ajax/start.php', { url: url, format: format }, function(response) {
                    if (response.status === 'success') {
                        startProgressCheck(response.id);
                    } else {
                        alert('Erro ao iniciar: ' + response.message);
                        resetDownloadButton();
                    }
                }, 'json');
            });

            // 4. Função: MONITORAR PROGRESSO
            function startProgressCheck(id) {
                $('#preview-area').addClass('hidden');
                $('#progress-area').removeClass('hidden');
                $('#progress-bar').css('width', '0%').addClass('bg-danger').removeClass('bg-success');

                // Limpa intervalo anterior se existir
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
                }, 1000); // Checa a cada 1 segundo
            }

            // 5. Função: FINALIZAR
            function finishDownload() {
                $('#progress-bar').removeClass('bg-danger').addClass('bg-success');
                $('#progress-percent').text('Concluído!');
                
                // Atualiza a lista lá embaixo
                loadDownloads();

                // Reseta a interface após 2 segundos
                setTimeout(function() {
                    $('#progress-area').fadeOut();
                    resetDownloadButton();
                    $('#url-input').val(''); // Limpa campo
                }, 3000);
            }

            function resetDownloadButton() {
                $('#btn-download').prop('disabled', false).html('<i class="fa-solid fa-download me-2"></i> Baixar Agora');
            }

            // 6. Função: CARREGAR LISTA
            function loadDownloads() {
                $.get('ajax/list.php', function(files) {
                    let html = '';
                    
                    if (files.length === 0) {
                        html = '<tr><td colspan="3" class="text-center text-muted py-4">Nenhum download recente.</td></tr>';
                    } else {
                        files.forEach(function(f) {
                            html += `
                                <tr>
                                    <td class="ps-4 text-white">${f.name}</td>
                                    <td class="text-white-50">${f.size}</td>
                                    <td class="text-end pe-4">
                                        <a href="${f.link}" class="btn btn-sm btn-outline-light" target="_blank">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#downloads-list').html(html);
                });
            }
        });
    </script>
</body>
</html>