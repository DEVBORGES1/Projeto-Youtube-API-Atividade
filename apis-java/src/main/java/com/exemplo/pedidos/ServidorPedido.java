package com.exemplo.pedidos;

import jakarta.xml.ws.Endpoint;

public class ServidorPedido {
    public static void main(String[] args) {
        String endereco = "http://127.0.0.1:8081/soap/pedidos";

        Endpoint endpoint = Endpoint.publish(
                endereco,
                new PedidoPortTypeImpl()
        );

        System.out.println("Servidor SOAP Java ativo em:");
        System.out.println(endereco);
        System.out.println("Pressione Ctrl+C para encerrar.");

        Runtime.getRuntime().addShutdownHook(new Thread(() -> {
            System.out.println("\nEncerrando o servidor SOAP Java...");
            endpoint.stop();
        }));
    }
}
