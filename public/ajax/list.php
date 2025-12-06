<?php
header('Content-Type: application/json');

$downloadPath = __DIR__ . '/../../storage/downloads';
$files = [];

if (is_dir($downloadPath)) {
    // Escaneia a pasta, removendo . e ..
    $scanned = array_diff(scandir($downloadPath), ['.', '..', '.gitkeep']);
    
    foreach ($scanned as $file) {
        $fullPath = $downloadPath . '/' . $file;
        $size = filesize($fullPath);
        
        // Formata tamanho
        $sizeStr = ($size > 1048576) 
            ? round($size / 1048576, 2) . ' MB' 
            : round($size / 1024, 2) . ' KB';

        $files[] = [
            'name' => $file,
            'size' => $sizeStr,
            // Link para o script de download que faremos a seguir
            'link' => 'download.php?file=' . urlencode($file)
        ];
    }
}

// Reverte para mostrar os mais recentes primeiro (se o OS ordenar por nome)
echo json_encode(array_values($files));