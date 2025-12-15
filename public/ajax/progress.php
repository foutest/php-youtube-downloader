<?php
// Carrega as dependências do Composer
require __DIR__ . '/../../vendor/autoload.php';

// Importa a classe DownloadService
use App\Services\DownloadService;

// Define o cabeçalho de resposta como JSON
header('Content-Type: application/json');

// 1. Obtém o ID do download a partir dos parâmetros da URL
$id = $_GET['id'] ?? ''; // Utiliza o ID fornecido na URL ou uma string vazia se não for fornecido

// 2. Verifica se o ID foi fornecido
if (empty($id)) {
    // Caso o ID seja vazio, retorna um erro JSON e sai da execução
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit; // Interrompe a execução do script
}

// 3. Cria uma instância do serviço DownloadService
$service = new DownloadService();

// 4. Obtém o progresso do download a partir do ID
$progress = $service->getProgress($id);

// 5. Retorna o progresso do download em formato JSON
echo json_encode($progress);
