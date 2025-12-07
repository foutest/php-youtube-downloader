<?php

namespace App\Services;

class DownloadService
{
    private $storagePath;
    private $tempPath;

    public function __construct()
    {
        $this->storagePath = __DIR__ . '/../../storage/downloads';
        $this->tempPath = __DIR__ . '/../../storage/temp';
    }

    public function startDownload(string $url, string $formatId): string
    {
        // 1. Gera um ID único
        $downloadId = uniqid('dl_');
        
        // 2. Define caminhos
        $outputTemplate = $this->storagePath . '/%(title)s.%(ext)s';
        $progressFile = $this->tempPath . '/' . $downloadId . '.log';

        // 3. Monta o comando (COM A CORREÇÃO DE ÁUDIO)
        // Adicionamos '--merge-output-format mp4' para garantir que a fusão
        // de vídeo+áudio resulte sempre num arquivo .mp4 compatível.
        $command = sprintf(
            'nohup yt-dlp -f %s --merge-output-format mp4 -o "%s" %s > "%s" 2>&1 & echo $!',
            escapeshellarg($formatId),
            $outputTemplate,
            escapeshellarg($url),
            $progressFile
        );

        // 4. Executa
        exec($command, $output);

        // 5. Retorna ID
        return $downloadId;
    }

    public function getProgress(string $downloadId): array
    {
        $logFile = $this->tempPath . '/' . $downloadId . '.log';

        if (!file_exists($logFile)) {
            return ['status' => 'starting', 'percent' => 0];
        }

        // Lê as últimas linhas do arquivo de log para achar a porcentagem
        // O yt-dlp escreve linhas como: "[download]  23.5% of 10.00MiB at 2.50MiB/s..."
        $content = file_get_contents($logFile);
        
        // Lógica simples de Regex para pegar a última porcentagem
        if (preg_match_all('/(\d{1,3}\.\d)%/', $content, $matches)) {
            $lastPercent = end($matches[1]);
            
            if ($lastPercent >= 100) {
                return ['status' => 'completed', 'percent' => 100];
            }
            return ['status' => 'downloading', 'percent' => (float)$lastPercent];
        }

        return ['status' => 'downloading', 'percent' => 0];
    }
}