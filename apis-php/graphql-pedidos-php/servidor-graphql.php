<?php

require __DIR__ . '/vendor/autoload.php';

use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Schema;

$caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($caminho !== '/graphql') {
    http_response_code(404);
    exit('Endpoint GraphQL não encontrado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use POST para enviar uma operação GraphQL.');
}

$arquivoPedidos = __DIR__ . '/pedidos.json';

$pedidosIniciais = [
    'PED-1001' => [
        'numero' => 'PED-1001',
        'cliente' => 'Ana Souza',
        'status' => 'Em separação',
        'valorTotal' => 159.90
    ],
    'PED-1002' => [
        'numero' => 'PED-1002',
        'cliente' => 'Carlos Lima',
        'status' => 'Enviado',
        'valorTotal' => 89.50
    ]
];

function salvarPedidos(string $arquivo, array $pedidos): void
{
    $json = json_encode(
        $pedidos,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_THROW_ON_ERROR
    );

    if (file_put_contents($arquivo, $json . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException('Não foi possível salvar os pedidos.');
    }
}

function carregarPedidos(string $arquivo): array
{
    $conteudo = file_get_contents($arquivo);

    if ($conteudo === false) {
        throw new RuntimeException('Não foi possível ler o arquivo de pedidos.');
    }

    $pedidos = json_decode($conteudo, true, 512, JSON_THROW_ON_ERROR);

    if (!is_array($pedidos)) {
        throw new RuntimeException('O arquivo de pedidos possui formato inválido.');
    }

    return $pedidos;
}

// Na primeira execução, cria o arquivo com os pedidos iniciais.
if (!file_exists($arquivoPedidos)) {
    salvarPedidos($arquivoPedidos, $pedidosIniciais);
}

// Em cada requisição, carrega os dados persistidos.
$pedidos = carregarPedidos($arquivoPedidos);

$pedidoType = new ObjectType([
    'name' => 'Pedido',
    'fields' => [
        'numero' => Type::nonNull(Type::string()),
        'cliente' => Type::nonNull(Type::string()),
        'status' => Type::nonNull(Type::string()),
        'valorTotal' => Type::nonNull(Type::float())
    ]
]);

$pedidoInputType = new InputObjectType([
    'name' => 'PedidoInput',
    'fields' => [
        'numero' => Type::nonNull(Type::string()),
        'cliente' => Type::nonNull(Type::string()),
        'status' => Type::nonNull(Type::string()),
        'valorTotal' => Type::nonNull(Type::float())
    ]
]);

$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'pedido' => [
            'type' => $pedidoType,
            'args' => [
                'numeroPedido' => Type::nonNull(Type::string())
            ],
            'resolve' => static function ($raiz, array $argumentos) use ($pedidos) {
                return $pedidos[$argumentos['numeroPedido']] ?? null;
            }
        ]
    ]
]);

$mutationType = new ObjectType([
    'name' => 'Mutation',
    'fields' => [
        'atualizarStatusPedido' => [
            'type' => $pedidoType,
            'args' => [
                'numeroPedido' => Type::nonNull(Type::string()),
                'novoStatus' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($raiz, array $argumentos) use (&$pedidos, $arquivoPedidos) {
                $numeroPedido = $argumentos['numeroPedido'];

                if (!isset($pedidos[$numeroPedido])) {
                    return null;
                }

                $pedidos[$numeroPedido]['status'] = $argumentos['novoStatus'];

                salvarPedidos($arquivoPedidos, $pedidos);

                return $pedidos[$numeroPedido];
            }
        ],
        'criarPedido' => [
            'type' => $pedidoType,
            'args' => [
                'pedido' => Type::nonNull($pedidoInputType)
            ],
            'resolve' => function ($raiz, array $argumentos) use (&$pedidos, $arquivoPedidos) {
                $novoPedido = $argumentos['pedido'];
                $numeroPedido = $novoPedido['numero'];

                if (isset($pedidos[$numeroPedido])) {
                    return null;
                }

                $pedidos[$numeroPedido] = $novoPedido;

                salvarPedidos($arquivoPedidos, $pedidos);

                return $pedidos[$numeroPedido];
            }
        ]
    ]
]);

$schema = new Schema([
    'query' => $queryType,
    'mutation' => $mutationType
]);

header('Content-Type: application/json; charset=utf-8');

try {
    $entrada = json_decode(
        file_get_contents('php://input'),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!is_array($entrada) || !isset($entrada['query'])) {
        http_response_code(400);
        $saida = [
            'errors' => [
                ['message' => 'Envie um JSON com a propriedade "query".']
            ]
        ];
    } else {
        $resultado = GraphQL::executeQuery(
            $schema,
            $entrada['query'],
            null,
            null,
            $entrada['variables'] ?? null
        );

        $saida = $resultado->toArray();
    }
} catch (JsonException $erro) {
    http_response_code(400);
    $saida = [
        'errors' => [
            ['message' => 'O corpo da requisição não contém um JSON válido.']
        ]
    ];
} catch (Throwable $erro) {
    http_response_code(500);
    $saida = [
        'errors' => [
            ['message' => $erro->getMessage()]
        ]
    ];
}

echo json_encode(
    $saida,
    JSON_PRETTY_PRINT |
    JSON_UNESCAPED_UNICODE |
    JSON_THROW_ON_ERROR
);
