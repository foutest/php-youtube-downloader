<?php
// public/stream.php

// 1. Validar o arquivo
$file = $_GET['file'] ?? '';
$file = basename($file); // Segurança contra ../../
$path = __DIR__ . '/../storage/downloads/' . $file;

if (!file_exists($path)) {
    http_response_code(404);
    die("Arquivo não encontrado.");
}

// 2. Preparar variáveis
$size = filesize($path);
$fp = fopen($path, 'rb'); // Abre o arquivo em modo binário de leitura
$start = 0;
$end = $size - 1;

// 3. Definir Cabeçalhos (Headers)
// Diz ao navegador que isso é um vídeo e que aceitamos "pular" partes (bytes)
header('Content-type: video/mp4'); 
header("Accept-Ranges: bytes");

// 4. Lógica de "Seek" (Pular o vídeo)
if (isset($_SERVER['HTTP_RANGE'])) {
    $c_start = $start;
    $c_end = $end;
    
    // O navegador manda algo tipo "bytes=1024-" (Me dê do byte 1024 pra frente)
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        exit;
    }
    
    if ($range == '-') {
        $c_start = $size - substr($range, 1);
    } else {
        $range = explode('-', $range);
        $c_start = $range[0];
        $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
    }
    
    $c_end = ($c_end > $end) ? $end : $c_end;
    
    // Atualiza o ponteiro de leitura
    $start = $c_start;
    $end = $c_end;
    $length = $end - $start + 1;
    
    fseek($fp, $start); // Pula para a parte que o usuário pediu
    header('HTTP/1.1 206 Partial Content');
    header("Content-Length: ".$length);
    header("Content-Range: bytes $start-$end/$size");
} else {
    // Se não pediu pra pular, manda o tamanho total
    header("Content-Length: ".$size);
}

// 5. Entregar o arquivo em pedaços (Buffer)
// Não tentamos carregar 1GB na memória RAM de uma vez. Lemos 8KB por vez.
$buffer = 1024 * 8;
while(!feof($fp) && ($p = ftell($fp)) <= $end) {
    if ($p + $buffer > $end) {
        $buffer = $end - $p + 1;
    }
    set_time_limit(0); // Evita timeout do PHP
    echo fread($fp, $buffer);
    flush(); // Força o envio para o navegador
}
fclose($fp);