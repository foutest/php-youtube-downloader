<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YT Downloader Pro</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

    <div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container" style="z-index: 1055;"></div>

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
                        <span class="fw-bold"><i class="fa-solid fa-gear fa-spin me-2"></i> Processando...</span>
                        <span id="progress-percent">0%</span>
                    </div>
                    <div class="progress" style="height: 10px; background-color: #333;">
                        <div id="progress-bar" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="playerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="playerTitle">Tocando...</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-black text-center d-flex align-items-center justify-content-center" style="min-height: 300px;">
                    <div id="player-container" class="w-100"></div>
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
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="js/app.js"></script>
</body>
</html>