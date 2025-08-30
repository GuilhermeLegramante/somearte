<?php
$jsonFile = __DIR__ . '/musicas.json';

// Pastas de upload
$audioDir = __DIR__ . '/uploads/audio/';
$pdfDir   = __DIR__ . '/uploads/pdf/';

// Cria pastas se não existirem
if (!is_dir($audioDir)) mkdir($audioDir, 0777, true);
if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);

// Carrega músicas existentes
$musicas = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

// Função para salvar arquivos com validação
function salvarArquivo($campo, $dir, $tiposPermitidos = [])
{
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $nomeOriginal = $_FILES[$campo]['name'];
    $tmpName = $_FILES[$campo]['tmp_name'];
    $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

    // Verifica extensão permitida
    if ($tiposPermitidos && !in_array($ext, $tiposPermitidos)) {
        echo "Arquivo '$nomeOriginal' não permitido.<br>";
        return null;
    }

    $nomeArquivo = time() . '-' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', $nomeOriginal);
    $destino = $dir . $nomeArquivo;

    if (move_uploaded_file($tmpName, $destino)) {
        return str_replace(__DIR__ . '/', '', $destino); // caminho relativo
    } else {
        echo "Erro ao mover o arquivo '$nomeOriginal'.<br>";
        return null;
    }
}

// Salva arquivos
$audioPath = salvarArquivo('audio', $audioDir, ['mp3','wav','mp4']); // adicionamos mp4
$pdfPath   = salvarArquivo('pdf', $pdfDir, ['pdf']);

// Nova música
$novaMusica = [
    'title'    => $_POST['title'] ?? 'Sem título',
    'category' => $_POST['category'] ?? 'outros',
    'video'    => $_POST['video'] ?? '',
    'audio'    => $audioPath,
    'pdf'      => $pdfPath,
];

// Adiciona ao array e salva no JSON
$musicas[] = $novaMusica;

if (file_put_contents($jsonFile, json_encode($musicas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    // Sucesso
    header('Location: index.html');
    exit;
} else {
    echo "Erro ao salvar no JSON.";
}
