<?php

namespace App\Services;

class FormatOrganizerService
{
    public function organize(array $formats): array
    {
        $videoFormats = [];
        $audioFormats = [];

        foreach ($formats as $f) {
            if (strpos($f['protocol'] ?? '', 'http') !== 0) continue;

            $size = $this->formatSize($f['filesize'] ?? $f['filesize_approx'] ?? 0);
            
            // Lógica Áudio
            if (($f['vcodec'] ?? 'none') === 'none' && ($f['acodec'] ?? 'none') !== 'none') {
                $audioFormats[] = [
                    'id' => $f['format_id'],
                    'label' => strtoupper($f['ext']) . " • " . round($f['abr'] ?? 0) . "kbps",
                    'bitrate' => round($f['abr'] ?? 0),
                    'ext' => $f['ext'],
                    'size' => $size
                ];
            }
            // Lógica Vídeo
            elseif (($f['vcodec'] ?? 'none') !== 'none') {
                if (!in_array($f['ext'], ['mp4', 'webm'])) continue;

                $height = $f['height'] ?? 0;
                $audioStatus = ($f['acodec'] ?? 'none') !== 'none' ? '🔊' : '🔇';
                
                $videoFormats[] = [
                    'id' => $f['format_id'],
                    'label' => "{$height}p ({$f['ext']})",
                    'resolution' => $height . 'p',
                    'height' => $height,
                    'fps' => $f['fps'] ?? 0,
                    'ext' => $f['ext'],
                    'size' => $size,
                    'acodec' => $f['acodec'] ?? 'none'
                ];
            }
        }

        usort($videoFormats, fn($a, $b) => $b['height'] <=> $a['height']);
        usort($audioFormats, fn($a, $b) => $b['bitrate'] <=> $a['bitrate']);

        return [
            'video' => $videoFormats,
            'audio' => $audioFormats
        ];
    }

    private function formatSize($bytes)
    {
        if ($bytes <= 0) return 'N/A';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 1) . ' ' . $units[$pow];
    }
}