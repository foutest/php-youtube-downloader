<?php
// Script para servir arquivos protegidos
$file = $_GET['file'] ?? '';

// SEGURANÇA BÁSICA: Remove caracteres que permitam navegar para outras pastas (Directory Traversal)
$file = basename($file); 

$path = __DIR__ . '/../storage/downloads/' . $file;

if (!empty($file) && file_exists($path)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($path));
    readfile($path); // Lê o arquivo e joga pro navegador
    exit;
} else {
    die("Arquivo não encontrado.");
}