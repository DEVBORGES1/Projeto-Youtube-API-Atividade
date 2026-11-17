package com.exemplo.pedidos.grpc;

import io.grpc.ManagedChannel;
import io.grpc.ManagedChannelBuilder;
import io.grpc.StatusRuntimeException;

import java.util.concurrent.TimeUnit;

public class ClientePedidoGrpc {
    public static void main(String[] args) throws InterruptedException {
        String numeroPedido = args.length > 0
                ? args[0]
                : "PED-1001";

        ManagedChannel canal = ManagedChannelBuilder
                .forAddress("127.0.0.1", 50051)
                .usePlaintext()
                .build();

        try {
            PedidoServiceGrpc.PedidoServiceBlockingStub cliente =
                    PedidoServiceGrpc.newBlockingStub(canal);

            ConsultarPedidoRequest requisicao =
                    ConsultarPedidoRequest.newBuilder()
                            .setNumeroPedido(numeroPedido)
                            .build();

            Pedido pedido = cliente.consultarPedido(requisicao);

            System.out.println("Pedido recebido pelo gRPC:");
            System.out.println("Número: " + pedido.getNumero());
            System.out.println("Cliente: " + pedido.getCliente());
            System.out.println("Status: " + pedido.getStatus());
            System.out.printf("Valor total: R$ %.2f%n", pedido.getValorTotal());
        } catch (StatusRuntimeException erro) {
            System.err.println("Falha na chamada gRPC:");
            System.err.println("Código: " + erro.getStatus().getCode());
            System.err.println("Detalhe: " + erro.getStatus().getDescription());
        } finally {
            canal.shutdownNow();
            canal.awaitTermination(5, TimeUnit.SECONDS);
        }
    }
}
