<?php
// Define que a resposta será no formato JSON
header('Content-Type: application/json');

// Define o caminho da pasta onde os arquivos estão armazenados
$downloadPath = __DIR__ . '/../../storage/downloads';
// Inicializa um array vazio para armazenar as informações dos arquivos
$files = [];

// Verifica se o caminho especificado é um diretório
if (is_dir($downloadPath)) {
    // 1. Pega todos os arquivos dentro do diretório, exceto os "." e ".." (diretórios especiais)
    //    e o arquivo ".gitkeep" que geralmente é usado para manter diretórios vazios no Git
    $scanned = array_diff(scandir($downloadPath), ['.', '..', '.gitkeep']);
    
    // 2. Prepara o array com informações sobre cada arquivo
    foreach ($scanned as $file) {
        $fullPath = $downloadPath . '/' . $file;  // Caminho completo do arquivo
        $files[] = [
            'name' => $file,                          // Nome do arquivo
            'path' => $fullPath,                      // Caminho completo do arquivo
            'time' => filemtime($fullPath)            // Data e hora da última modificação do arquivo
        ];
    }

    // 3. ORDENAÇÃO: Ordena os arquivos pela data de modificação, do mais recente para o mais antigo
    usort($files, function($a, $b) {
        return $b['time'] - $a['time'];  // Ordena pelo timestamp (maior para menor)
    });

    // 4. Formata os dados para gerar o JSON final
    $finalList = [];
    foreach ($files as $f) {
        // Obtém o tamanho do arquivo
        $size = filesize($f['path']);
        
        // Converte o tamanho do arquivo para uma string legível (MB ou KB)
        $sizeStr = ($size > 1048576) 
            ? round($size / 1048576, 2) . ' MB'   // Se maior que 1MB, exibe em MB
            : round($size / 1024, 2) . ' KB';     // Caso contrário, exibe em KB

        // Adiciona as informações do arquivo ao array final
        $finalList[] = [
            'name' => $f['name'],                          // Nome do arquivo
            'title' => $f['name'],                         // Título do arquivo (igual ao nome)
            'size' => $sizeStr,                            // Tamanho do arquivo formatado
            'link' => 'download.php?file=' . urlencode($f['name'])  // Gera o link para download
        ];
    }
    
    // Retorna a lista de arquivos como JSON
    echo json_encode($finalList);
} else {
    // Se o diretório não existir, retorna um array vazio
    echo json_encode([]);
}
