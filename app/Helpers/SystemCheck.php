<?php

namespace App\Helpers;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SystemCheck
{
    public static function checkEnvironment()
    {
        $results = [];

        // 1. Verifica versão do PHP
        $results['php'] = [
            'label' => 'Versão do PHP',
            'status' => phpversion(),
            'ok' => version_compare(phpversion(), '8.0.0', '>=')
        ];

        // 2. Verifica se a pasta storage tem permissão de escrita
        $storagePath = __DIR__ . '/../../storage';
        $isWritable = is_writable($storagePath);
        $results['storage'] = [
            'label' => 'Permissão de Escrita (Storage)',
            'status' => $isWritable ? 'Permitido' : 'Sem permissão',
            'ok' => $isWritable
        ];

        // 3. Verifica se o yt-dlp está instalado e rodando
        // Aqui usamos o Symfony Process que instalamos via Composer
        try {
            $process = new Process(['yt-dlp', '--version']);
            $process->run();

            if ($process->isSuccessful()) {
                $results['ytdlp'] = [
                    'label' => 'yt-dlp (Core)',
                    'status' => 'Instalado v' . trim($process->getOutput()),
                    'ok' => true
                ];
            } else {
                throw new \Exception('Erro ao rodar');
            }
        } catch (\Exception $e) {
            $results['ytdlp'] = [
                'label' => 'yt-dlp (Core)',
                'status' => 'Não encontrado ou erro',
                'ok' => false
            ];
        }

        return $results;
    }
}