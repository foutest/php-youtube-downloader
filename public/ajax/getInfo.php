<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Services\YoutubeService;

header('Content-Type: application/json');

try {
    // Verifica se veio via POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido');
    }

    $url = $_POST['url'] ?? '';
    
    if (empty($url)) {
        throw new Exception('URL é obrigatória');
    }

    $service = new YoutubeService();
    $info = $service->getVideoInfo($url);

    echo json_encode(['status' => 'success', 'data' => $info]);

} catch (Exception $e) {
    // Retorna erro 400 ou 500 dependendo do caso, mas aqui vamos simplificar
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}