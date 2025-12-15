<?php
// Carrega as dependências do Composer
require __DIR__ . '/../../vendor/autoload.php';

// Importa a classe DownloadService, que gerencia o download de arquivos
use App\Services\DownloadService;

// Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json');

try {
    // 1. Pega os dados enviados via POST
    // Captura a URL do vídeo que será baixado, ou define um valor padrão vazio
    $url = $_POST['url'] ?? '';
    // Captura o formato do vídeo, ou define 'best' (melhor qualidade) como valor padrão
    $format = $_POST['format'] ?? 'best';

    // 2. Validação: Verifica se a URL foi informada
    if (empty($url)) throw new Exception('URL não informada'); // Se não, lança uma exceção

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
