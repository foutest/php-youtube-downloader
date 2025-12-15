<?php
// public/stream.php

// 1. Validar o arquivo
$file = $_GET['file'] ?? ''; // Recebe o nome do arquivo via query string (?file=nome_do_arquivo)
$file = basename($file); // Usa basename() para garantir que o arquivo não tenha caminhos perigosos como "../"
// Ex: "../arquivo" será convertido para "arquivo"
$path = __DIR__ . '/../storage/downloads/' . $file; // Caminho completo do arquivo

// Verifica se o arquivo existe
if (!file_exists($path)) {
    http_response_code(404); // Se não existir, retorna erro 404
    die("Arquivo não encontrado.");
}

// 2. Preparar variáveis
$size = filesize($path); // Tamanho do arquivo em bytes
$fp = fopen($path, 'rb'); // Abre o arquivo em modo binário de leitura (read binary)
$start = 0; // Início do arquivo (default)
$end = $size - 1; // Fim do arquivo (tamanho total - 1)

// 3. Definir Cabeçalhos (Headers)
// Define o tipo de conteúdo como vídeo MP4
header('Content-type: video/mp4'); 
// Define que o servidor aceita "range" de bytes (permite pausar e retomar o vídeo)
header("Accept-Ranges: bytes");

// 4. Lógica de "Seek" (Pular o vídeo)
if (isset($_SERVER['HTTP_RANGE'])) { 
    // Se o navegador enviar um cabeçalho "Range", significa que ele quer um pedaço do arquivo (ex: streaming)
    $c_start = $start; 
    $c_end = $end;
    
    // O navegador envia algo tipo "bytes=1024-" (Significa: comece a partir do byte 1024)
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2); // Extrai o valor após o sinal de "="
    
    // Se a string "range" contiver vírgulas, significa que a solicitação é inválida
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable'); // Retorna erro 416
        header("Content-Range: bytes $start-$end/$size"); // Cabeçalho com intervalo de bytes
        exit;
    }
    
    // Se a solicitação for algo como "bytes=-1024", significa que o cliente quer os últimos 1024 bytes
    if ($range == '-') {
        $c_start = $size - substr($range, 1); // Ajusta o início para o tamanho total menos os bytes
    } else {
        // Se for um intervalo específico, divide em início e fim
        $range = explode('-', $range);
        $c_start = $range[0]; // Início do intervalo
        // Se o final não for especificado, usa o valor máximo
        $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
    }
    
    // Garante que o final do intervalo não ultrapasse o tamanho do arquivo
    $c_end = ($c_end > $end) ? $end : $c_end;
    
    // Atualiza as variáveis de início e fim para a parte solicitada
    $start = $c_start;
    $end = $c_end;
    $length = $end - $start + 1; // Tamanho do pedaço solicitado
    
    // Move o ponteiro do arquivo para o início da parte solicitada
    fseek($fp, $start); 
    // Responde com o código HTTP 206 (Partial Content) e os cabeçalhos adequados
    header('HTTP/1.1 206 Partial Content');
    header("Content-Length: ".$length); // Define o tamanho do conteúdo que será retornado
    header("Content-Range: bytes $start-$end/$size"); // Define o intervalo de bytes solicitado
} else {
    // Se não for um pedido de intervalo, envia o tamanho total
    header("Content-Length: ".$size);
}

// 5. Entregar o arquivo em pedaços (Buffer)
// O PHP não carrega o arquivo inteiro na memória, lê o arquivo em pedaços (chunks) de 8KB
$buffer = 1024 * 8; // Tamanho do buffer em bytes (8KB)
while(!feof($fp) && ($p = ftell($fp)) <= $end) {
    // Se o próximo pedaço for maior que o fim do intervalo, ajusta o tamanho do buffer
    if ($p + $buffer > $end) {
        $buffer = $end - $p + 1;
    }
    
    set_time_limit(0); // Evita o tempo de execução do PHP expirar durante o streaming
    echo fread($fp, $buffer); // Lê e envia o próximo pedaço do arquivo
    flush(); // Força o envio do conteúdo para o navegador
}
fclose($fp); // Fecha o arquivo após a leitura
