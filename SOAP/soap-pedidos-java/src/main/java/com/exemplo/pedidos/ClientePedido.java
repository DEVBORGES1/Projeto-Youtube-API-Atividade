package com.exemplo.pedidos;

import jakarta.xml.ws.BindingProvider;
import java.net.URL;

public class ClientePedido {
    public static void main(String[] args) throws Exception {
        URL wsdl = ClientePedido.class.getResource("/wsdl/pedido.wsdl");

        if (wsdl == null) {
            throw new IllegalStateException("Não foi possível encontrar pedido.wsdl");
        }

        PedidoService servico = new PedidoService(wsdl);
        PedidoPortType porta = servico.getPedidoPort();

        String endereco = "http://127.0.0.1:8000/servidor.php";

        ((BindingProvider) porta)
                .getRequestContext()
                .put(BindingProvider.ENDPOINT_ADDRESS_PROPERTY, endereco);

        Pedido pedido = porta.consultarPedido("PED-1001");

        System.out.println("Número: " + pedido.getNumero());
        System.out.println("Cliente: " + pedido.getCliente());
        System.out.println("Status: " + pedido.getStatus());
        System.out.println("Valor total: R$ "
                + pedido.getValorTotal().toPlainString());
    }
}
