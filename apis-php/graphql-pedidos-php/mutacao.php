<?php

$endpoint = 'http://127.0.0.1:8082/graphql';

$consulta = <<<'GRAPHQL'
mutation AtualizarStatusPedido($numeroPedido: String!, $novoStatus: String!) {
    atualizarStatusPedido(numeroPedido: $numeroPedido, novoStatus: $novoStatus) {
        numero
        cliente
        status
        valorTotal
    }
}
GRAPHQL;

$corpo = json_encode(
    [
        'query' => $consulta,
        'variables' => [
            'numeroPedido' => 'PED-1001',
            'novoStatus' => 'Em transporte'
        ]
    ],
    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);

$contexto = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' =>
            "Content-Type: application/json\r\n" .
            "Accept: application/json\r\n",
        'content' => $corpo,
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

try {
    $respostaJson = file_get_contents($endpoint, false, $contexto);

    if ($respostaJson === false) {
        throw new RuntimeException('Não foi possível acessar o servidor GraphQL.');
    }

    $resposta = json_decode($respostaJson, true, 512, JSON_THROW_ON_ERROR);

    if (isset($resposta['errors'])) {
        foreach ($resposta['errors'] as $erro) {
            echo 'Erro GraphQL: ' . ($erro['message'] ?? 'Erro sem mensagem.') . PHP_EOL;
        }

        exit(1);
    }

    echo json_encode(
            $resposta['data']['atualizarStatusPedido'] ?? null,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ) . PHP_EOL;
} catch (Throwable $erro) {
    echo 'Erro: ' . $erro->getMessage() . PHP_EOL;
    exit(1);
}
