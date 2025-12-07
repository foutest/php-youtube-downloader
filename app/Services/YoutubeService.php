<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class YoutubeService
{
    private $organizer;

    public function __construct()
    {
        // AQUI ESTÁ A CHAVE: Carregamos a nova classe
        $this->organizer = new FormatOrganizerService();
    }

    public function getVideoInfo(string $url): array
    {
        $command = ['yt-dlp', '--dump-json', '--no-playlist', $url];

        $process = new Process($command);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception('Erro ao processar vídeo: ' . $process->getErrorOutput());
        }

        $data = json_decode($process->getOutput(), true);

        if (!$data) {
            throw new \Exception('Erro ao decodificar JSON');
        }

        // Delegamos a organização para o novo serviço
        $cleanFormats = $this->organizer->organize($data['formats'] ?? []);

        return [
            'id' => $data['id'],
            'title' => $data['title'],
            'thumbnail' => $data['thumbnail'],
            'duration' => gmdate(($data['duration'] > 3600 ? "H:i:s" : "i:s"), $data['duration']),
            'formats' => $cleanFormats
        ];
    }
}