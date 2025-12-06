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
        // 1. Gera um ID único para este download
        $downloadId = uniqid('dl_');
        
        // 2. Define onde salvar o vídeo e onde salvar o log de progresso
        // %(title)s.%(ext)s é a sintaxe do yt-dlp para nomear o arquivo automaticamente
        $outputTemplate = $this->storagePath . '/%(title)s.%(ext)s';
        $progressFile = $this->tempPath . '/' . $downloadId . '.log';

        // 3. Monta o comando
        // -f: formato
        // -o: onde salvar
        // >: redireciona a saída (texto) para o arquivo de log
        // 2>&1: redireciona erros também para o log
        // &: (E comercial no final) é o segredo do Linux para rodar em BACKGROUND
        $command = sprintf(
            'nohup yt-dlp -f %s -o "%s" %s > "%s" 2>&1 & echo $!',
            escapeshellarg($formatId),
            $outputTemplate,
            escapeshellarg($url),
            $progressFile
        );

        // 4. Executa o comando no terminal
        exec($command, $output);

        // 5. Retorna o ID para o Frontend monitorar
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