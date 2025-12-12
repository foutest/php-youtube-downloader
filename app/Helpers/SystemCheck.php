<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// system_check.php

// Se você estiver usando Symfony Process, certifique-se que o Composer autoload esteja incluído
require __DIR__ . '/../../vendor/autoload.php';


use Symfony\Component\Process\Process;

// --- CLASSE SYSTEMCHECK ---
class SystemCheck
{
    public static function checkEnvironment()
    {
        $results = [];

        // 1. Verifica versão do PHP
        $results['php'] = [
            'label' => 'Versão do PHP',
            'status' => phpversion(),
            'ok' => version_compare(phpversion(), '8.0.0', '>=')
        ];

        // 2. Verifica se a pasta storage tem permissão de escrita
        $storagePath = realpath(__DIR__ . '/storage');
        $isWritable = $storagePath && is_writable($storagePath);
        $results['storage'] = [
            'label' => 'Permissão de Escrita (Storage)',
            'status' => $storagePath ? ($isWritable ? 'Permitido' : 'Sem permissão') : 'Pasta não encontrada',
            'ok' => $isWritable
        ];

        // 3. Verifica se o yt-dlp está instalado
        try {
            $process = new Process(['yt-dlp', '--version']);
            $process->run();

            if ($process->isSuccessful()) {
                $results['ytdlp'] = [
                    'label' => 'yt-dlp (Core)',
                    'status' => 'Instalado v' . trim($process->getOutput()),
                    'ok' => true
                ];
            } else {
                throw new \Exception('Erro ao executar yt-dlp');
            }
        } catch (\Exception $e) {
            $results['ytdlp'] = [
                'label' => 'yt-dlp (Core)',
                'status' => 'Não encontrado ou erro: ' . $e->getMessage(),
                'ok' => false
            ];
        }

        return $results;
    }
}

// --- CHAMA A VERIFICAÇÃO ---
$results = SystemCheck::checkEnvironment();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Verificação do Ambiente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            padding: 30px;
        }
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        .status-fail {
            color: #dc3545;
            font-weight: bold;
        }
        .result-item {
            border: 1px solid #333;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .result-item:hover {
            background-color: #1e1e1e;
        }
        h1 {
            margin-bottom: 30px;
        }
        .icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>

    <h1>Verificação do Ambiente</h1>

    <?php foreach ($results as $key => $result): ?>
        <div class="result-item">
            <h5>
                <?php 
                    $icon = $result['ok'] ? '✅' : '❌';
                    echo "<span class='icon'>{$icon}</span>";
                    echo htmlspecialchars($result['label']); 
                ?>
            </h5>
            <p>Status: <?php echo htmlspecialchars($result['status']); ?></p>
            <p>OK: 
                <span class="<?php echo $result['ok'] ? 'status-ok' : 'status-fail'; ?>">
                    <?php echo $result['ok'] ? 'Sim' : 'Não'; ?>
                </span>
            </p>
        </div>
    <?php endforeach; ?>

</body>
</html>
