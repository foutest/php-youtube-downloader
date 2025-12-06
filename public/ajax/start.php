<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Services\DownloadService;

header('Content-Type: application/json');

try {
    // Pega os dados enviados via POST
    $url = $_POST['url'] ?? '';
    $format = $_POST['format'] ?? 'best';

    if (empty($url)) throw new Exception('URL não informada');

    $service = new DownloadService();
    $id = $service->startDownload($url, $format);

    echo json_encode(['status' => 'success', 'id' => $id]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}