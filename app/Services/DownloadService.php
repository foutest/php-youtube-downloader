<?php

namespace App\Services;

class DownloadService
{
    private $storagePath;
    private $stagingPath;
    private $tempPath;

    public function __construct()
    {
        $this->storagePath = __DIR__ . '/../../storage/downloads';
        $this->stagingPath = __DIR__ . '/../../storage/staging';
        $this->tempPath = __DIR__ . '/../../storage/temp';

        if (!is_dir($this->stagingPath)) mkdir($this->stagingPath, 0777, true);
        if (!is_dir($this->storagePath)) mkdir($this->storagePath, 0777, true);
        if (!is_dir($this->tempPath)) mkdir($this->tempPath, 0777, true);
    }

    public function startDownload(string $url, string $formatId): string
    {
        $downloadId = uniqid('dl_');
        
        $outputTemplate = $this->stagingPath . '/%(title)s.%(ext)s';
        $progressFile = $this->tempPath . '/' . $downloadId . '.log';
        $finalDir = $this->storagePath . '/';

        $isMerge = strpos($formatId, '+') !== false ? '1' : '0';
        // Pegamos a hora exata de AGORA
        $startTime = time();

        $ytDlpCmdParts = [
            'yt-dlp', 
            '-f', escapeshellarg($formatId),
            '--merge-output-format', 'mp4',
            '-o', escapeshellarg($outputTemplate),
            '--exec', escapeshellarg("mv -f {} $finalDir"),
            escapeshellarg($url)
        ];
        $ytDlpCmd = implode(' ', $ytDlpCmdParts);

        // MUDANÇA 1: Gravamos o [METADATA_START] no cabeçalho do log
        $fullCommand = sprintf(
            '(echo "[METADATA_MERGE]: %s" && echo "[METADATA_START]: %s" && %s && echo "[PROCESS_COMPLETED]") > %s 2>&1 &',
            $isMerge,
            $startTime, // <--- Injetamos o tempo aqui
            $ytDlpCmd,
            escapeshellarg($progressFile)
        );

        exec($fullCommand);

        return $downloadId;
    }

    public function getProgress(string $downloadId): array
    {
        $logFile = $this->tempPath . '/' . $downloadId . '.log';

        if (!file_exists($logFile)) {
            return ['status' => 'starting', 'percent' => 0, 'eta' => '--:--', 'elapsed' => '00:00'];
        }

        $content = file_get_contents($logFile);
        
        // MUDANÇA 2: Lemos o tempo fixo de dentro do arquivo
        $elapsed = 0;
        if (preg_match('/\[METADATA_START\]: (\d+)/', $content, $matches)) {
            $startTime = (int)$matches[1];
            $elapsed = time() - $startTime;
        } else {
            // Fallback caso não ache a tag (usa o tempo de modificação, que reseta, mas evita erro)
            $elapsed = time() - filemtime($logFile);
        }
        
        if (strpos($content, '[PROCESS_COMPLETED]') !== false) {
            return [
                'status' => 'completed', 
                'percent' => 100, 
                'eta' => '00:00', 
                'elapsed' => gmdate("i:s", $elapsed)
            ];
        }

        $eta = '--:--';
        if (preg_match_all('/ETA\s+([\d:]{2,8})/', $content, $matches)) {
            $eta = end($matches[1]);
        }

        $percent = 0;
        $isMerge = (strpos($content, '[METADATA_MERGE]: 1') !== false);

        if (preg_match_all('/(\d{1,3}\.\d)%/', $content, $matches)) {
            $rawPercent = (float)end($matches[1]);
            
            if (!$isMerge) {
                $percent = $rawPercent;
            } else {
                $filesCount = substr_count($content, '[download] Destination:');
                
                if ($filesCount <= 1) {
                    $percent = $rawPercent * 0.85;
                } else {
                    $percent = 85 + ($rawPercent * 0.10);
                }

                if ($percent >= 95 || strpos($content, '[Merger]') !== false) {
                    $percent = 99;
                }
            }
        }

        return [
            'status' => 'downloading', 
            'percent' => round($percent, 1),
            'eta' => $eta,
            'elapsed' => gmdate("i:s", $elapsed)
        ];
    }
}