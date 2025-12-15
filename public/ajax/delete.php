<?php
// Carrega as dependências do Composer (caso haja alguma necessária)
require __DIR__ . '/../../vendor/autoload.php';

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

try {
    // Verifica se a requisição é do tipo POST, caso contrário lança uma exceção
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido'); // Lança uma exceção se não for POST
    }

    // Obtém o nome do arquivo do POST, caso não exista, atribui uma string vazia
    $file = $_POST['file'] ?? '';
    
    // SEGURANÇA: Impede que o usuário envie caminhos como "../../../etc/passwd"
    // basename() elimina qualquer caminho adicional, retornando apenas o nome do arquivo
    $filename = basename($file);
    
    // Se o nome do arquivo estiver vazio, lança uma exceção
    if (empty($filename)) {
        throw new Exception('Nome do arquivo inválido'); // Lança exceção se o nome do arquivo for vazio
    }

    // Define o caminho completo para o arquivo dentro da pasta de downloads
    $filePath = __DIR__ . '/../../storage/downloads/' . $filename;

    // Verifica se o arquivo existe no caminho especificado
    if (file_exists($filePath)) {
        // Tenta excluir o arquivo
        if (unlink($filePath)) {
            // Se a exclusão for bem-sucedida, retorna um JSON com status de sucesso
            echo json_encode(['status' => 'success']);
        } else {
            // Se não for possível excluir o arquivo devido a permissão ou outro erro
            throw new Exception('Erro de permissão ao tentar excluir.');
        }
    } else {
        // Se o arquivo não existir no caminho informado
        throw new Exception('Arquivo não encontrado.');
    }

} catch (Exception $e) {
    // Caso ocorra algum erro ou exceção, retorna um JSON com o status de erro e a mensagem do erro
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
