package com.exemplo.pedidos.documento;

import jakarta.xml.ws.Endpoint;

public class ServidorPedidoDocumento {
    public static void main(String[] args) {
        String endereco = "http://127.0.0.1:8082/soap/pedidos-documento";

        Endpoint endpoint = Endpoint.publish(
                endereco,
                new PedidoPortTypeDocumentoImpl()
        );

        System.out.println("Servidor SOAP document/literal ativo em:");
        System.out.println(endereco);
        System.out.println("Pressione Ctrl+C para encerrar.");

        Runtime.getRuntime().addShutdownHook(new Thread(() -> {
            System.out.println("\nEncerrando o servidor document/literal...");
            endpoint.stop();
        }));
    }
}
