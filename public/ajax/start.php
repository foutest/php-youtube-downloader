<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Services\DownloadService;

header('Content-Type: application/json');

try {
    // 1. Pega os dados enviados via POST
    $url = $_POST['url'] ?? '';

    $format = $_POST['format'] ?? 'best';

    // 2. Validação: Verifica se a URL foi informada
    if (empty($url)) throw new Exception('URL não informada'); 
    
    // 3. Cria uma instância do serviço de download
    $service = new DownloadService();

    // 4. Inicia o download e gera um ID único para este processo
    $id = $service->startDownload($url, $format);

    // 5. Retorna a resposta JSON de sucesso com o ID do download
    echo json_encode(['status' => 'success', 'id' => $id]);

} catch (Exception $e) {
    // 6. Caso ocorra algum erro, retorna um JSON com status 'error' e a mensagem de erro
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
