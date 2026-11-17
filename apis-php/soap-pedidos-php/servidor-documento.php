<?php

class ServidorPedidosDocumento
{
    private array $pedidos = [
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

    public function consultarPedido($parametros)
    {
        if (is_object($parametros)) {
            $numeroPedido = $parametros->numeroPedido ?? '';
        } elseif (is_array($parametros)) {
            $numeroPedido = $parametros['numeroPedido'] ?? '';
        } else {
            $numeroPedido = '';
        }

        $numeroPedido = (string) $numeroPedido;

        // Registra a consulta no console/log, sem acrescentar texto à resposta SOAP.
        error_log("Consulta document/literal recebida: {$numeroPedido}");

        if (!isset($this->pedidos[$numeroPedido])) {
            throw new SoapFault(
                'Client',
                "Pedido {$numeroPedido} não encontrado."
            );
        }

        // No document/literal wrapped, a resposta é o elemento consultarPedidoResponse,
        // que contém o pedido dentro de "return" (correção: o PDF retorna o pedido direto,
        // e assim os campos chegam vazios no cliente).
        return ['return' => (object) $this->pedidos[$numeroPedido]];
    }
}

$arquivoWsdl = __DIR__ . '/pedido-documento.wsdl';

$servidor = new SoapServer($arquivoWsdl, [
    'soap_version' => SOAP_1_1,
    'cache_wsdl' => WSDL_CACHE_NONE
]);

$servidor->setObject(new ServidorPedidosDocumento());
$servidor->handle();
