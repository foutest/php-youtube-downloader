<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Services\DownloadService;

header('Content-Type: application/json');

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit;
}

$service = new DownloadService();
$progress = $service->getProgress($id);

echo json_encode($progress);