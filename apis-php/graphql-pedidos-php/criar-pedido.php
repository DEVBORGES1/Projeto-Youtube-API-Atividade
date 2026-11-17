<?php

$endpoint = 'http://127.0.0.1:8082/graphql';

$consulta = <<<'GRAPHQL'
mutation CriarPedido($pedido: PedidoInput!) {
    criarPedido(pedido: $pedido) {
        numero
        cliente
        status
        valorTotal
    }
}
GRAPHQL;

$variaveis = [
    'pedido' => [
        'numero' => 'PED-1003',
        'cliente' => 'Marina Alves',
        'status' => 'Recebido',
        'valorTotal' => 215.75
    ]
];

try {
    $corpo = json_encode(
        [
            'query' => $consulta,
            'variables' => $variaveis
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

    $respostaJson = @file_get_contents($endpoint, false, $contexto);

    if ($respostaJson === false) {
        throw new RuntimeException(
            'Não foi possível acessar o servidor GraphQL. ' .
            'Verifique se ele está ativo e se o endpoint está correto.'
        );
    }

    $resposta = json_decode($respostaJson, true, 512, JSON_THROW_ON_ERROR);

    if (!is_array($resposta)) {
        throw new RuntimeException('O servidor retornou uma resposta em formato inesperado.');
    }

    if (isset($resposta['errors'])) {
        $mensagens = array_map(
            static fn(array $erro): string => $erro['message'] ?? 'Erro GraphQL sem mensagem.',
            $resposta['errors']
        );

        throw new RuntimeException(implode(PHP_EOL, $mensagens));
    }

    $pedidoCriado = $resposta['data']['criarPedido'] ?? null;

    if ($pedidoCriado === null) {
        echo "Pedido não criado. Verifique se esse número já está cadastrado." . PHP_EOL;
    } else {
        echo "Pedido criado:" . PHP_EOL;
        echo json_encode(
                $pedidoCriado,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ) . PHP_EOL;
    }
} catch (Throwable $erro) {
    echo 'Erro: ' . $erro->getMessage() . PHP_EOL;
    exit(1);
}
