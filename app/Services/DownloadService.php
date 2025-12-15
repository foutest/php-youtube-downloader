<?php

namespace App\Services;

class DownloadService
{
    // Diretórios de armazenamento, staging (preparação) e temporários
    private $storagePath;
    private $stagingPath;
    private $tempPath;

    // Construtor da classe: inicializa os caminhos e cria diretórios se necessário
    public function __construct()
    {
        // Define os diretórios para download, staging e temporários
        $this->storagePath = __DIR__ . '/../../storage/downloads';
        $this->stagingPath = __DIR__ . '/../../storage/staging';
        $this->tempPath = __DIR__ . '/../../storage/temp';

        // Cria os diretórios se não existirem
        if (!is_dir($this->stagingPath)) mkdir($this->stagingPath, 0777, true);
        if (!is_dir($this->storagePath)) mkdir($this->storagePath, 0777, true);
        if (!is_dir($this->tempPath)) mkdir($this->tempPath, 0777, true);
    }

    /**
     * Inicia o download do arquivo a partir de uma URL com o formato especificado
     * 
     * @param string $url URL do vídeo ou áudio
     * @param string $formatId ID do formato desejado
     * @return string ID único de download
     */
    public function startDownload(string $url, string $formatId): string
    {
        // Gera um ID único para o download
        $downloadId = uniqid('dl_');
        
        // Caminhos para onde os arquivos de saída e logs serão salvos
        $outputTemplate = $this->stagingPath . '/%(title)s.%(ext)s'; // Padrão de nome do arquivo
        $progressFile = $this->tempPath . '/' . $downloadId . '.log'; // Caminho para o arquivo de log
        $finalDir = $this->storagePath . '/'; // Diretório final para o arquivo baixado

        // Define se o arquivo precisa ser mesclado
        $isMerge = strpos($formatId, '+') !== false ? '1' : '0';
        
        // Marca o horário de início do download
        $startTime = time();

        // Comando para executar o yt-dlp com os parâmetros necessários
        $ytDlpCmdParts = [
            'yt-dlp', 
            '-f', escapeshellarg($formatId), // Formato a ser baixado
            '--merge-output-format', 'mp4', // Mesclar para MP4
            '-o', escapeshellarg($outputTemplate), // Modelo de nome do arquivo de saída
            '--exec', escapeshellarg("mv -f {} $finalDir"), // Move o arquivo final para o diretório de destino
            escapeshellarg($url) // URL do vídeo ou áudio
        ];
        $ytDlpCmd = implode(' ', $ytDlpCmdParts);

        // Comando completo com os dados do progresso sendo registrado no arquivo de log
        $fullCommand = sprintf(
            '(echo "[METADATA_MERGE]: %s" && echo "[METADATA_START]: %s" && %s && echo "[PROCESS_COMPLETED]") > %s 2>&1 &',
            $isMerge,
            $startTime, // Hora de início (injetada no comando)
            $ytDlpCmd,
            escapeshellarg($progressFile)
        );

        // Executa o comando em segundo plano
        exec($fullCommand);

        // Retorna o ID único do download
        return $downloadId;
    }

    /**
     * Obtém o progresso do download em andamento
     * 
     * @param string $downloadId ID único do download
     * @return array Status e informações do progresso do download
     */
    public function getProgress(string $downloadId): array
    {
        // Caminho para o arquivo de log associado ao download
        $logFile = $this->tempPath . '/' . $downloadId . '.log';

        // Se o arquivo de log não existir, o download ainda está começando
        if (!file_exists($logFile)) {
            return ['status' => 'starting', 'percent' => 0, 'eta' => '--:--', 'elapsed' => '00:00'];
        }

        // Lê o conteúdo do arquivo de log
        $content = file_get_contents($logFile);
        
        // --- Tempo decorrido ---
        $elapsed = 0;
        if (preg_match('/\[METADATA_START\]: (\d+)/', $content, $matches)) {
            $startTime = (int)$matches[1];
            $elapsed = time() - $startTime;
        } else {
            $elapsed = time() - filemtime($logFile);
        }

        // --- Verifica se o download foi concluído ---
        if (strpos($content, '[PROCESS_COMPLETED]') !== false) {
            return [
                'status' => 'completed', // Status é 'completed' se o processo terminou
                'percent' => 100, // Percentual é 100%
                'eta' => '00:00', // ETA (tempo restante) é 0
                'elapsed' => gmdate("i:s", $elapsed) // Tempo decorrido formatado
            ];
        }

        // --- Percentual do download ---
        $percent = 0;
        $isMerge = (strpos($content, '[METADATA_MERGE]: 1') !== false);
        if (preg_match_all('/(\d{1,3}\.\d)%/', $content, $matches)) {
            $rawPercent = (float)end($matches[1]);
            if (!$isMerge) {
                $percent = $rawPercent;
            } else {
                // Ajusta o percentual para mesclagem de vídeos/áudios
                $filesCount = substr_count($content, '[download] Destination:');
                if ($filesCount <= 1) {
                    $percent = $rawPercent * 0.85; // Primeiros 85% para o primeiro arquivo
                } else {
                    $percent = 85 + ($rawPercent * 0.10); // Para os arquivos subsequentes
                }
                if ($percent >= 95 || strpos($content, '[Merger]') !== false) {
                    $percent = 99; // Marca como 99% se o processo de mesclagem estiver ocorrendo
                }
            }
        }

        // Arredonda o percentual
        $percent = round($percent, 1);

        // --- Calcula ETA baseado no percentual e tempo decorrido ---
        if ($percent > 0 && $percent < 100) {
            $etaSeconds = round($elapsed * (100 - $percent) / $percent);
            $eta = gmdate("i:s", $etaSeconds); // Calcula o tempo estimado restante
        } else {
            $eta = '--:--'; // Se o download estiver completo ou não iniciado
        }

        // --- Tempo decorrido formatado ---
        $elapsedFormatted = gmdate("i:s", $elapsed); // Formata o tempo decorrido como mm:ss

        return [
            'status' => 'downloading', // O download está em andamento
            'percent' => $percent, // Percentual do download
            'eta' => $eta, // Tempo restante estimado
            'elapsed' => $elapsedFormatted // Tempo decorrido formatado
        ];
    }
}
