<?php

$endpoint = 'http://127.0.0.1:8082/graphql';
$numeroPedido = 'PED-1001';

/**
 * Envia uma consulta GraphQL para o servidor.
 */
function consultarPedidoGraphQL(string $endpoint, string $numeroPedido, array $campos): array {
    $camposPermitidos = [ 'numero', 'cliente', 'status', 'valorTotal' ];
    $camposInvalidos = array_diff($campos, $camposPermitidos);

    if ($camposInvalidos !== []) {
        throw new InvalidArgumentException('Campos inválidos: ' . implode(', ', $camposInvalidos));
    }

    $campos = array_values(array_unique($campos));

    if ($campos === []) {
        throw new InvalidArgumentException('Informe pelo menos um campo para consultar.');
    }

    $selecao = implode("\n        ", $campos);

    $consulta = <<<GRAPHQL
    query ConsultarPedido(\$numeroPedido: String!) {
        pedido(numeroPedido: \$numeroPedido) {
            {$selecao}
        }
    }
    GRAPHQL;

    $corpo = json_encode(
        [
            'query' => $consulta,
            'variables' => [ 'numeroPedido' => $numeroPedido ]
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

    $respostaJson = file_get_contents($endpoint, false, $contexto);

    if ($respostaJson === false) {
        throw new RuntimeException('Não foi possível acessar o servidor GraphQL.');
    }

    $resposta = json_decode($respostaJson, true, 512, JSON_THROW_ON_ERROR);

    if (isset($resposta['errors'])) {
        $mensagens = array_map(
            static fn(array $erro): string => $erro['message'] ?? 'Erro GraphQL sem mensagem.',
            $resposta['errors']
        );

        throw new RuntimeException(implode(PHP_EOL, $mensagens));
    }

    return $resposta;
}

$consultas = [
    'Consulta completa' => [
        'numero',
        'cliente',
        'status',
        'valorTotal'
    ],

    'Consulta resumida' => [
        'numero',
        'status'
    ]
];

try {
    foreach ($consultas as $descricao => $campos) {
        echo PHP_EOL . "=== {$descricao} ===" . PHP_EOL;

        $resposta = consultarPedidoGraphQL($endpoint, $numeroPedido, $campos);
        $pedido = $resposta['data']['pedido'] ?? null;

        echo json_encode(
                $pedido,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ) . PHP_EOL;
    }
} catch (Throwable $erro) {
    fwrite(STDERR, 'Erro: ' . $erro->getMessage() . PHP_EOL);
    exit(1);
}
