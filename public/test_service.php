<?php
// Carrega o Autoload
require __DIR__ . '/../vendor/autoload.php';

use App\Services\YoutubeService;

header('Content-Type: application/json'); // Vamos ver o resultado em JSON no navegador

try {
    // URL de teste (Use um vídeo curto para ser rápido)
    $url = $_GET['url'] ?? 'https://www.youtube.com/watch?v=BaW_jenozKc'; // Ex: "Hello World"

    $service = new YoutubeService();
    $info = $service->getVideoInfo($url);

    echo json_encode([
        'status' => 'success',
        'data' => $info
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}