<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class YoutubeService
{
    private $organizer; // Atributo para armazenar a instância de FormatOrganizerService

    public function __construct()
    {
        // Instanciamos a classe FormatOrganizerService para organizar os formatos do vídeo
        $this->organizer = new FormatOrganizerService();
    }

    /**
     * Obtém informações sobre o vídeo do YouTube.
     * 
     * @param string $url URL do vídeo do YouTube.
     * @return array Dados organizados do vídeo.
     * @throws \Exception Se ocorrer algum erro no processamento do vídeo.
     */
    public function getVideoInfo(string $url): array
    {
        // Comando para o yt-dlp obter informações sobre o vídeo em formato JSON, sem playlist
        $command = ['yt-dlp', '--dump-json', '--no-playlist', $url];

        // Cria o objeto Process para executar o comando do yt-dlp
        $process = new Process($command);
        $process->setTimeout(60); // Define um tempo limite de 60 segundos para a execução do comando
        $process->run(); // Executa o comando

        // Verifica se o processo foi bem-sucedido
        if (!$process->isSuccessful()) {
            // Se não foi bem-sucedido, lança uma exceção com a mensagem de erro
            throw new \Exception('Erro ao processar vídeo: ' . $process->getErrorOutput());
        }

        // Decodifica a saída JSON do comando yt-dlp para um array PHP
        $data = json_decode($process->getOutput(), true);

        // Verifica se a decodificação do JSON foi bem-sucedida
        if (!$data) {
            // Se houver falha na decodificação, lança uma exceção
            throw new \Exception('Erro ao decodificar JSON');
        }

        // Chama o serviço FormatOrganizerService para organizar os formatos de vídeo extraídos
        // O yt-dlp retorna vários formatos de vídeo e áudio que precisam ser organizados
        $cleanFormats = $this->organizer->organize($data['formats'] ?? []);

        // Organiza e retorna as informações do vídeo
        return [
            'id' => $data['id'], // ID do vídeo no YouTube
            'title' => $data['title'], // Título do vídeo
            'thumbnail' => $data['thumbnail'], // URL da miniatura do vídeo
            'duration' => gmdate(($data['duration'] > 3600 ? "H:i:s" : "i:s"), $data['duration']), // Duração formatada (ex: 02:15:45 ou 15:45)
            'formats' => $cleanFormats // Formatos de vídeo organizados
        ];
    }
}
