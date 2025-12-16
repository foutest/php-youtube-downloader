<?php

namespace App\Services;

// Serviço responsável por organizar formatos de áudio e vídeo
class FormatOrganizerService
{
    //Organiza os formatos recebidos separando em vídeo e áudio
    public function organize(array $formats): array
    {
        // Arrays que armazenarão os formatos finais
        $videoFormats = [];
        $audioFormats = [];

        // Percorre todos os formatos recebidos
        foreach ($formats as $f) {

            //Verifica se o protocolo começa com "http"
            if (strpos($f['protocol'] ?? '', 'http') !== 0) continue;

            //Define o tamanho do arquivo:
            $size = $this->formatSize(
                $f['filesize'] ?? $f['filesize_approx'] ?? 0
            );

            //Identificndo Audio
            if (($f['vcodec'] ?? 'none') === 'none'
                && ($f['acodec'] ?? 'none') !== 'none') {

                $audioFormats[] = [
                    'id' => $f['format_id'],
                    // Exemplo: MP3 • 128kbps
                    'label' => strtoupper($f['ext']) . " • " . round($f['abr'] ?? 0) . "kbps",
                    'bitrate' => round($f['abr'] ?? 0),
                    'ext' => $f['ext'],
                    'size' => $size
                ];
            }

            //Identificando video
            elseif (($f['vcodec'] ?? 'none') !== 'none') {

                // Aceita apenas vídeos mp4 ou webm
                if (!in_array($f['ext'], ['mp4', 'webm'])) continue;

                // Altura do vídeo (ex: 720, 1080)
                $height = $f['height'] ?? 0;

                // Verifica se o vídeo possui áudio
                $audioStatus = ($f['acodec'] ?? 'none') !== 'none' ? '🔊' : '🔇';

                $videoFormats[] = [
                    'id' => $f['format_id'],
                    // Exemplo: 1080p (mp4)
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

        //Ordena os vídeos pela resolução (do maior para o menor)
        usort($videoFormats, fn($a, $b) => $b['height'] <=> $a['height']);

        //rdena os áudios pelo bitrate (do maior para o menor)
        usort($audioFormats, fn($a, $b) => $b['bitrate'] <=> $a['bitrate']);

        // Retorna os formatos organizados
        return [
            'video' => $videoFormats,
            'audio' => $audioFormats
        ];
    }

    //Converte bytes para um formato legível (KB, MB, GB...)
    private function formatSize($bytes)
    {
        // Se não houver tamanho válido
        if ($bytes <= 0) return 'N/A';

        // Unidades de medida
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        // Garante que o valor seja positivo
        $bytes = max($bytes, 0);

        // Calcula o "nível" da unidade (KB, MB, etc)
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Converte o valor
        $bytes /= pow(1024, $pow);

        // Retorna valor formatado (ex: 12.5 MB)
        return round($bytes, 1) . ' ' . $units[$pow];
    }
}
