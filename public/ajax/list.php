<?php
header('Content-Type: application/json');

$downloadPath = __DIR__ . '/../../storage/downloads';
$files = [];

if (is_dir($downloadPath)) {
    // 1. Pega todos os arquivos
    $scanned = array_diff(scandir($downloadPath), ['.', '..', '.gitkeep']);
    
    // 2. Prepara o array com data de modificação
    foreach ($scanned as $file) {
        $fullPath = $downloadPath . '/' . $file;
        $files[] = [
            'name' => $file,
            'path' => $fullPath,
            'time' => filemtime($fullPath) // Pega a hora exata da criação
        ];
    }

    // 3. ORDENAÇÃO: O mais novo (maior time) fica em primeiro
    usort($files, function($a, $b) {
        return $b['time'] - $a['time'];
    });

    // 4. Formata para o JSON final
    $finalList = [];
    foreach ($files as $f) {
        $size = filesize($f['path']);
        $sizeStr = ($size > 1048576) 
            ? round($size / 1048576, 2) . ' MB' 
            : round($size / 1024, 2) . ' KB';

        $finalList[] = [
            'name' => $f['name'],
            'title' => $f['name'], 
            'size' => $sizeStr,
            'link' => 'download.php?file=' . urlencode($f['name'])
        ];
    }
    
    echo json_encode($finalList);
} else {
    echo json_encode([]);
}