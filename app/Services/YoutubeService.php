<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class YoutubeService
{
    /**
     * Obtém informações do vídeo sem baixar.
     * * @param string $url A URL do YouTube
     * @return array Dados estruturados do vídeo
     * @throws \Exception Se falhar ao obter dados
     */
    public function getVideoInfo(string $url): array
    {
        // 1. Monta o comando
        // --dump-json: Retorna tudo em JSON (mais fácil pro PHP ler)
        // --no-playlist: Garante que só pegamos um vídeo, não a lista toda
        $command = ['yt-dlp', '--dump-json', '--no-playlist', $url];

        // 2. Executa usando Symfony Process
        $process = new Process($command);
        $process->setTimeout(60); // Timeout de 60s para conexões lentas
        $process->run();

        // 3. Verifica erros
        if (!$process->isSuccessful()) {
            throw new \Exception('Erro ao processar vídeo: ' . $process->getErrorOutput());
        }

        // 4. Decodifica o JSON retornado pelo yt-dlp
        $output = $process->getOutput();
        $data = json_decode($output, true);

        if (!$data) {
            throw new \Exception('Erro ao decodificar JSON do yt-dlp');
        }

        // 5. Retorna apenas os dados limpos que usaremos no Front
        return [
            'id' => $data['id'],
            'title' => $data['title'],
            'thumbnail' => $data['thumbnail'],
            'duration' => $this->formatDuration($data['duration'] ?? 0),
            'formats' => $this->filterFormats($data['formats'] ?? [])
        ];
    }

    /**
     * Filtra e organiza os formatos em categorias (Vídeo e Áudio)
     */
    private function filterFormats(array $formats): array
    {
        $videoFormats = [];
        $audioFormats = [];

        foreach ($formats as $f) {
            if (strpos($f['protocol'] ?? '', 'http') !== 0) continue;
            
            $filesize = isset($f['filesize']) ? round($f['filesize'] / 1024 / 1024, 1) . ' MB' : 'N/A';
            $ext = $f['ext'];
            
            $isAudioOnly = ($f['vcodec'] === 'none' && $f['acodec'] !== 'none');
            $isVideo = ($f['vcodec'] !== 'none');

            if ($isAudioOnly) {
                $audioFormats[] = [
                    'id' => $f['format_id'],
                    'label' => "Áudio ({$ext}) - {$filesize}",
                    'type' => 'audio'
                ];
            } 
            elseif ($isVideo) {
                if ($ext !== 'mp4') continue; 

                $height = $f['height'] ?? 0;
                
                $videoFormats[] = [
                    'id' => $f['format_id'],
                    'label' => "{$height}p ({$ext}) - {$filesize}",
                    'type' => 'video',
                    'height' => $height
                ];
            }
        }

        usort($videoFormats, fn($a, $b) => $b['height'] <=> $a['height']);

        return [
            'video' => array_values($videoFormats),
            'audio' => array_values($audioFormats)
        ];
    }

    /**
     * Converte segundos (ex: 125) para string (02:05)
     */
    private function formatDuration(int $seconds): string
    {
        return gmdate(($seconds > 3600 ? "H:i:s" : "i:s"), $seconds);
    }
}