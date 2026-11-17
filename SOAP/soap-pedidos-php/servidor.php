<?php

class PedidoDTO
{
    public $numero;
    public $cliente;
    public $status;
    public $valorTotal;
}

class PedidoSoapService
{
    private $pedidos = [
        'PED-1001' => [
            'numero' => 'PED-1001',
            'cliente' => 'Ana Souza',
            'status' => 'Em separação',
            'valorTotal' => '159.90'
        ],
        'PED-1002' => [
            'numero' => 'PED-1002',
            'cliente' => 'Carlos Lima',
            'status' => 'Enviado',
            'valorTotal' => '89.50'
        ]
    ];

    public function consultarPedido($numeroPedido)
    {
        if (!isset($this->pedidos[$numeroPedido])) {
            throw new SoapFault('Client', "Pedido {$numeroPedido} não encontrado.");
        }

        $dados = $this->pedidos[$numeroPedido];

        $pedido = new PedidoDTO();
        $pedido->numero = $dados['numero'];
        $pedido->cliente = $dados['cliente'];
        $pedido->status = $dados['status'];
        $pedido->valorTotal = $dados['valorTotal'];

        return $pedido;
    }
}

$servidor = new SoapServer(
    __DIR__ . '/pedido.wsdl',
    [
        'classmap' => [ 'Pedido' => 'PedidoDTO' ],
        'cache_wsdl' => WSDL_CACHE_NONE
    ]
);

$servidor->setClass(PedidoSoapService::class);
$servidor->handle();
