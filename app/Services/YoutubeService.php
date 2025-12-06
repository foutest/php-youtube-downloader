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
     * Filtra os milhares de formatos do YouTube para apenas os úteis.
     */
    private function filterFormats(array $formats): array
    {
        $cleanFormats = [];

        foreach ($formats as $f) {
            // Ignora formatos sem protocolo HTTP (m3u8, rtmp, etc são ruins para download direto)
            if (strpos($f['protocol'] ?? '', 'http') !== 0) continue;

            // Formata o tamanho do arquivo (se existir)
            $filesize = isset($f['filesize']) ? round($f['filesize'] / 1024 / 1024, 2) . ' MB' : 'N/A';
            
            // Estrutura simplificada
            $isVideo = $f['vcodec'] !== 'none';
            $isAudio = $f['acodec'] !== 'none';

            // Queremos:
            // 1. Vídeo com áudio (best)
            // 2. Áudio puro (mp3/m4a)
            // 3. Ignoramos vídeo mudo (video only) para simplificar este tutorial
            
            if ($isVideo && $isAudio) {
                $cleanFormats[] = [
                    'format_id' => $f['format_id'],
                    'label' => "Vídeo " . ($f['height'] ?? '??') . "p (" . $f['ext'] . ")",
                    'type' => 'video',
                    'ext' => $f['ext'],
                    'size' => $filesize
                ];
            } elseif (!$isVideo && $isAudio) {
                $cleanFormats[] = [
                    'format_id' => $f['format_id'],
                    'label' => "Áudio Apenas (" . $f['ext'] . ")",
                    'type' => 'audio',
                    'ext' => $f['ext'],
                    'size' => $filesize
                ];
            }
        }
        
        // Reordena: Melhor qualidade primeiro (opcional)
        return array_reverse($cleanFormats);
    }

    /**
     * Converte segundos (ex: 125) para string (02:05)
     */
    private function formatDuration(int $seconds): string
    {
        return gmdate(($seconds > 3600 ? "H:i:s" : "i:s"), $seconds);
    }
}