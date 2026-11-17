package com.exemplo.pedidos.documento;

import jakarta.xml.ws.BindingProvider;
import jakarta.xml.ws.soap.SOAPFaultException;

public class ClientePedidoDocumento {
    public static void main(String[] args) {
        String numeroPedido = "PED-1001";

        PedidoServiceDocumento servico = new PedidoServiceDocumento();
        PedidoPortTypeDocumento porta = servico.getPedidoPortDocumento();

        BindingProvider bindingProvider = (BindingProvider) porta;
        bindingProvider.getRequestContext().put(
                BindingProvider.ENDPOINT_ADDRESS_PROPERTY,
                "http://127.0.0.1:8082/soap/pedidos-documento"
        );

        try {
            Pedido pedido = porta.consultarPedido(numeroPedido);

            if (pedido == null) {
                System.out.println("Pedido não encontrado.");
                return;
            }

            System.out.println("Número: " + pedido.getNumero());
            System.out.println("Cliente: " + pedido.getCliente());
            System.out.println("Status: " + pedido.getStatus());
            System.out.println("Valor total: R$ " + pedido.getValorTotal().toPlainString());
        } catch (SOAPFaultException erro) {
            System.out.println("Falha SOAP: " + erro.getFault().getFaultString());
        } catch (jakarta.xml.ws.WebServiceException erro) {
            System.out.println("Não foi possível acessar o endpoint. Verifique servidor, porta e rota.");
            System.out.println("Detalhe: " + erro.getMessage());
        }
    }
}
