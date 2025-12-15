<?php

// Carrega as dependências do Composer
require __DIR__ . '/../../vendor/autoload.php';

// Importa a classe YoutubeService
use App\Services\YoutubeService;

// Define o cabeçalho de resposta como JSON
header('Content-Type: application/json');

try {
    // 1. Verifica se a requisição foi feita via POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido'); // Lança um erro se não for POST
    }

    // 2. Obtém a URL do vídeo a partir do corpo da requisição
    $url = $_POST['url'] ?? ''; // Utiliza a URL fornecida no corpo do POST, ou uma string vazia se não for fornecida

    // 3. Valida se a URL foi informada
    if (empty($url)) {
        throw new Exception('URL é obrigatória'); // Lança um erro se a URL não for fornecida
    }

    // 4. Cria uma instância do serviço YoutubeService
    $service = new YoutubeService();
    
    // 5. Obtém as informações do vídeo
    $info = $service->getVideoInfo($url); 

    // 6. Responde com um JSON contendo o status e as informações do vídeo
    echo json_encode([
        'status' => 'success',
        'data' => $info
    ]);

} catch (Exception $e) {
    // 7. Se ocorrer um erro, captura a exceção e retorna um JSON com o status 'error' e a mensagem do erro
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
