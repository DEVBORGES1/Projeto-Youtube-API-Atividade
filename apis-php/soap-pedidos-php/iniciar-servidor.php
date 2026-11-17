<?php

$host = '127.0.0.1';
$porta = 8081;

if (!extension_loaded('sockets')) {
    fwrite(STDERR, "A extensão sockets não está habilitada neste PHP.\n");
    exit(1);
}

if (!defined('SO_EXCLUSIVEADDRUSE')) {
    fwrite(
        STDERR,
        "A opção SO_EXCLUSIVEADDRUSE não está disponível neste PHP.\n"
    );
    exit(1);
}

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if ($socket === false) {
    fwrite(STDERR, "Não foi possível criar o socket.\n");
    exit(1);
}

if (!socket_set_option(
    $socket,
    SOL_SOCKET,
    SO_EXCLUSIVEADDRUSE,
    true
)) {
    $erro = socket_last_error($socket);
    fwrite(
        STDERR,
        "Não foi possível configurar o socket: "
        . socket_strerror($erro) . PHP_EOL
    );
    socket_close($socket);
    exit(1);
}

if (!socket_bind($socket, $host, $porta)) {
    $erro = socket_last_error($socket);
    fwrite(
        STDERR,
        "A porta {$porta} já está ocupada ou indisponível: "
        . socket_strerror($erro) . PHP_EOL
    );
    socket_close($socket);
    exit(1);
}

if (!socket_listen($socket, 1)) {
    $erro = socket_last_error($socket);
    fwrite(
        STDERR,
        "Não foi possível escutar na porta {$porta}: "
        . socket_strerror($erro) . PHP_EOL
    );
    socket_close($socket);
    exit(1);
}

// Libera o socket de verificação antes de iniciar o servidor PHP.
socket_close($socket);

echo "Porta {$porta} disponível. Iniciando o servidor SOAP...\n";

$comando = escapeshellarg(PHP_BINARY)
    . ' -S ' . escapeshellarg("{$host}:{$porta}")
    . ' -t ' . escapeshellarg(__DIR__)
    . ' ' . escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . 'servidor.php');
    
passthru($comando, $codigoSaida);
exit($codigoSaida);
