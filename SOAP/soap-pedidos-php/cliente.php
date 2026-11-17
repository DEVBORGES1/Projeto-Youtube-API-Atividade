<?php

function formatarXml(?string $xml): string
{
    if ($xml === null || $xml === '') {
        return '(XML não disponível)';
    }

    $documento = new DOMDocument('1.0', 'UTF-8');
    $documento->preserveWhiteSpace = false;
    $documento->formatOutput = true;

    if (!$documento->loadXML($xml, LIBXML_NONET)) {
        return $xml;
    }

    return $documento->saveXML();
}

$cliente = new SoapClient(
    __DIR__ . '/pedido.wsdl',
    [
        'location' => 'http://127.0.0.1:8000/servidor.php',
        'trace' => true,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE
    ]
);

// Número do pedido pode ser passado na linha de comando: php cliente.php PED-9999
$numeroPedido = $argv[1] ?? 'PED-1001';

try {
    $pedido = $cliente->__soapCall('consultarPedido', [$numeroPedido]);

    echo "Número: {$pedido->numero}" . PHP_EOL;
    echo "Cliente: {$pedido->cliente}" . PHP_EOL;
    echo "Status: {$pedido->status}" . PHP_EOL;
    echo "Valor total: R$ {$pedido->valorTotal}" . PHP_EOL;

    echo PHP_EOL . "--- Requisição SOAP enviada ---" . PHP_EOL;
    echo formatarXml($cliente->__getLastRequest());

    echo PHP_EOL . "--- Resposta SOAP recebida ---" . PHP_EOL;
    echo formatarXml($cliente->__getLastResponse());
} catch (SoapFault $erro) {
    echo "Falha SOAP: {$erro->getMessage()}" . PHP_EOL;
    echo "Código SOAP: {$erro->faultcode}" . PHP_EOL;
    echo "Mensagem: {$erro->faultstring}" . PHP_EOL;
    echo "Resposta recebida:" . PHP_EOL;

    $resposta = $cliente->__getLastResponse();

    if ($resposta !== null && $resposta !== '') {
        echo PHP_EOL . "--- Resposta SOAP de falha ---" . PHP_EOL;
        echo formatarXml($resposta);
    }
}
