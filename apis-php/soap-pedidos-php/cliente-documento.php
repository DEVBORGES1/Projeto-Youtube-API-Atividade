<?php

// Troque para 'PED-9999' para ver a falha SOAP (ou passe o número: php cliente-documento.php PED-9999).
$numeroPedido = $argv[1] ?? 'PED-1001';
$clienteSoap = null;

function formatarXml(?string $xml): string
{
    if ($xml === null || $xml === '') {
        return '(XML indisponível)';
    }

    $documento = new DOMDocument('1.0', 'UTF-8');
    $documento->preserveWhiteSpace = false;
    $documento->formatOutput = true;

    if (!$documento->loadXML($xml)) {
        return $xml;
    }

    return $documento->saveXML();
}

try {
    $clienteSoap = new SoapClient(
        __DIR__ . '/pedido-documento.wsdl',
        [
            'trace' => true,
            'exceptions' => true,
            'location' => 'http://127.0.0.1:8082/soap/pedidos-documento'
        ]
    );

    $resultado = $clienteSoap->consultarPedido([
        'numeroPedido' => $numeroPedido
    ]);

    // O retorno pode vir diretamente ou dentro do elemento "return".
    if (is_object($resultado) && isset($resultado->return)) {
        $pedido = $resultado->return;
    } else {
        $pedido = $resultado;
    }

    if (!$pedido) {
        echo "Pedido não encontrado." . PHP_EOL;
    } else {
        echo "Número: " . ($pedido->numero ?? '') . PHP_EOL;
        echo "Cliente: " . ($pedido->cliente ?? '') . PHP_EOL;
        echo "Status: " . ($pedido->status ?? '') . PHP_EOL;
        echo "Valor total: R$ " . number_format((float) ($pedido->valorTotal ?? 0), 2, '.', '') . PHP_EOL;
    }
} catch (SoapFault $erro) {
    $codigo = isset($erro->faultcode) ? (string) $erro->faultcode : '';
    $mensagem = isset($erro->faultstring) ? (string) $erro->faultstring : $erro->getMessage();

    if (strtoupper($codigo) === 'HTTP') {
        echo "Erro HTTP ao acessar o endpoint." . PHP_EOL;
        echo "Verifique se o servidor está ativo e se o endereço, a porta e a rota estão corretos." . PHP_EOL;
        echo "Detalhe: " . $mensagem . PHP_EOL;
    } else {
        echo "Falha SOAP: " . $mensagem . PHP_EOL;

        if ($codigo !== '') {
            echo "Código SOAP: " . $codigo . PHP_EOL;
        }
    }
}

if ($clienteSoap !== null) {
    $requisicao = $clienteSoap->__getLastRequest();
    $resposta = $clienteSoap->__getLastResponse();

    if ($requisicao) {
        echo PHP_EOL . "--- Requisição SOAP enviada ---" . PHP_EOL;
        echo formatarXml($requisicao) . PHP_EOL;
    }

    if ($resposta) {
        echo PHP_EOL . "--- Resposta SOAP recebida ---" . PHP_EOL;
        echo formatarXml($resposta) . PHP_EOL;
    }
}
