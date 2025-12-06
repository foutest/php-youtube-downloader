<?php
require __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido');
    }

    $file = $_POST['file'] ?? '';
    
    // SEGURANÇA: Previne que o usuário mande "../../../etc/passwd"
    $filename = basename($file);
    
    if (empty($filename)) {
        throw new Exception('Nome do arquivo inválido');
    }

    $filePath = __DIR__ . '/../../storage/downloads/' . $filename;

    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo json_encode(['status' => 'success']);
        } else {
            throw new Exception('Erro de permissão ao tentar excluir.');
        }
    } else {
        throw new Exception('Arquivo não encontrado.');
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}