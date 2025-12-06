<?php
// Exibir erros apenas em ambiente de desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carrega o Autoload do Composer (Obrigatório)
require __DIR__ . '/../vendor/autoload.php';

use App\Helpers\SystemCheck;

// Executa nossa verificação
$checks = SystemCheck::checkEnvironment();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Youtube Downloader - Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="container py-5">

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-4">
                <h1>🎬 Youtube Downloader</h1>
                <p class="text-muted">Ambiente de Desenvolvimento</p>
            </div>

            <div class="card">
                <div class="card-header bg-dark text-white">
                    Status do Sistema
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($checks as $key => $check): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><?php echo $check['label']; ?></strong>
                                <span>
                                    <?php echo $check['status']; ?>
                                    <?php if ($check['ok']): ?>
                                        <span class="badge bg-success rounded-pill">OK</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill">ERRO</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-footer text-center">
                    <?php if ($checks['ytdlp']['ok'] && $checks['storage']['ok']): ?>
                        <div class="alert alert-success m-0">
                            ✅ Tudo pronto! Podemos começar a codar os Services.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning m-0">
                            ⚠️ Corrija os erros acima antes de continuar.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</body>
</html>