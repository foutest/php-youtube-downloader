<?php

$file = $_GET['file'] ?? '';

// basename() remove qualquer caminho e deixa apenas o nome do arquivo
$file = basename($file);  

// Define o caminho completo para o arquivo a ser servido
$path = __DIR__ . '/../storage/downloads/' . $file;

// Verifica se o nome do arquivo não está vazio e se o arquivo realmente existe no servidor
if (!empty($file) && file_exists($path)) {
    
    // Define cabeçalhos HTTP que instruem o navegador a tratar a resposta como um download de arquivo

    // Cabeçalho que descreve que é uma transferência de arquivo
    header('Content-Description: File Transfer');
    
    // Define o tipo de conteúdo como genérico, forçando o download
    header('Content-Type: application/octet-stream');
    
    // Instrução para o navegador baixar o arquivo com o nome correto
    header('Content-Disposition: attachment; filename="' . $file . '"');
    
    // Garante que o arquivo não será armazenado em cache
    header('Expires: 0');
    header('Cache-Control: must-revalidate');  // Requer validação para garantir que o arquivo não fique no cache
    header('Pragma: public'); // Garante que o arquivo pode ser transferido para o cliente

    // Define o tamanho do arquivo para o navegador
    header('Content-Length: ' . filesize($path));

    // Lê o arquivo e o envia para o navegador
    readfile($path);

    // Finaliza a execução do script para evitar que mais conteúdo seja enviado
    exit;
} else {
    // Se o arquivo não for encontrado ou não for fornecido, exibe uma mensagem de erro
    die("Arquivo não encontrado.");
}
