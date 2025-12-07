# Documentação do Processo de Desenvolvimento: YT Downloader Pro

Este documento detalha o ciclo de vida de desenvolvimento do sistema **YT Downloader Pro**, desde a conceção da arquitetura até à implementação de funcionalidades avançadas como streaming e processamento assíncrono.

---

## 1. Visão Geral e Objetivos

O objetivo principal era criar uma aplicação web que servisse como interface gráfica (GUI) para a ferramenta de linha de comando `yt-dlp`.

### Requisitos Técnicos Definidos:
1.  **Backend:** PHP 8+ (para gestão de processos).
2.  **Frontend:** HTML5/JS/jQuery (para interatividade sem refresh).
3.  **Infraestrutura:** Linux (para execução de comandos em background).
4.  **UX:** Feedback em tempo real e design responsivo.

---

## 2. Arquitetura do Sistema

Para garantir performance e não bloquear o navegador do utilizador durante downloads pesados, adotou-se uma arquitetura assíncrona baseada em arquivos.

### Fluxo de Dados:
1.  **Cliente (JS)** solicita o download.
2.  **Servidor (PHP)** inicia o processo no Linux e retorna um ID imediatamente.
3.  **Linux** executa o download em background e escreve o progresso num arquivo `.log`.
4.  **Cliente (JS)** consulta o servidor a cada 1 segundo (Polling) para ler o `.log` e atualizar a barra de progresso.

---

## 3. Implementação Passo a Passo

### Fase 1: Configuração do Ambiente
Utilizámos o **Composer** para gerir dependências profissionais e estruturar o projeto no padrão **PSR-4** (Autoloading).

* **Dependências:** `symfony/process` (para executar comandos seguros) e `vlucas/phpdotenv`.
* **Permissões:** Configuração de `chmod 777` nas pastas `storage/` e `logs/` para permitir escrita pelo servidor web.

### Fase 2: O Motor de Download (Backend)
Criámos o `DownloadService.php` para encapsular a complexidade do `yt-dlp`.

* **Desafio:** O PHP tem timeout de 30 segundos.
* **Solução:** Utilização do comando `nohup ... &` do Linux.
    * *Código:* `nohup yt-dlp ... > progress.log 2>&1 & echo $!`
    * Isto cria um processo "zumbi" que continua vivo mesmo após o script PHP terminar.

### Fase 3: Extração de Metadados
Criámos o `YoutubeService.php` para obter informações do vídeo antes de baixar.

* **Funcionalidade:** Extrai título, thumbnail e duração.
* **Filtragem Inteligente:** O sistema ignora formatos de vídeo "mudos" (sem áudio) e separa automaticamente as opções em dois grupos: "Vídeo (MP4)" e "Apenas Áudio".

### Fase 4: Interface e Interatividade (Frontend)
Desenvolvemos uma Single Page Application (SPA) simulada usando **jQuery**.

* **Design:** Utilização do Bootstrap 5 com um tema "Dark Mode" personalizado.
* **Responsividade:**
    * Desktop: Tabela detalhada.
    * Mobile: A tabela transforma-se em "Cards" com botões grandes e centralizados para facilitar o toque.
* **Feedback:** Implementação de "Toasts" (notificações flutuantes) para substituir os `alert()` nativos.

### Fase 5: Funcionalidades Avançadas

#### A. Streaming Seguro (`stream.php`)
Para permitir que o utilizador assista ao vídeo sem baixar, implementámos um script que suporta **Byte-Range Requests**.
* *Como funciona:* O navegador pede "bytes=0-1024". O PHP lê apenas esse pedaço do arquivo e entrega. Isso permite avançar/recuar o vídeo instantaneamente.

#### B. Gestão de Arquivos (`list.php`)
O sistema de listagem foi aprimorado para ordenar ficheiros pela **Data de Modificação**.
* *Benefício:* O utilizador vê sempre o download mais recente no topo da lista.

#### C. Destaque Automático
Criámos uma lógica visual que, ao terminar um download, exibe uma secção verde de "Sucesso" no topo da página com o vídeo pronto a assistir.

---

## 4. Estrutura Final de Pastas

```text
/project
├── app/
│   ├── Services/           # Lógica de Negócio (YoutubeService, DownloadService)
│   └── Helpers/            # Utilitários (SystemCheck)
├── public/
│   ├── ajax/               # API Endpoints (start, progress, list, delete)
│   ├── css/                # Estilos personalizados (style.css)
│   ├── js/                 # Lógica Frontend (app.js)
│   ├── index.php           # Interface Principal
│   └── stream.php          # Player de Vídeo
├── storage/                # Arquivos Gerados (Downloads e Logs)
└── composer.json           # Definição de Dependências


```

## 5. Decisões de Segurança

1.  **Isolamento:** A pasta `storage` está fora da pasta `public`. Ninguém consegue acessar os arquivos diretamente pela URL. O acesso é feito apenas via `download.php` ou `stream.php`.
2.  **Sanitização:** Todos os inputs de arquivos passam pela função `basename()` para evitar ataques de *Directory Traversal* (ex: tentar acessar `../../etc/passwd`).

## 6. Conclusão

O **YT Downloader Pro** demonstra como integrar tecnologias web modernas com comandos de sistema de baixo nível, resultando numa ferramenta rápida, segura e com experiência de utilizador fluida.
