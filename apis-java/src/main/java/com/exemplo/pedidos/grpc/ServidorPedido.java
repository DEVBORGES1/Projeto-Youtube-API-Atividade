package com.exemplo.pedidos.grpc;

import io.grpc.Server;
import io.grpc.ServerBuilder;
import io.grpc.Status;
import io.grpc.stub.StreamObserver;

import java.io.IOException;
import java.util.Map;

public class ServidorPedido {
    public static void main(String[] args) throws IOException, InterruptedException {
        Server servidor = ServerBuilder
                .forPort(50051)
                .addService(new ServicoPedidos())
                .build()
                .start();

        System.out.println("Servidor gRPC ativo na porta 50051.");
        servidor.awaitTermination();
    }

    private static class ServicoPedidos extends PedidoServiceGrpc.PedidoServiceImplBase {
        private final Map<String, Pedido> pedidos = Map.of(
                "PED-1001",
                Pedido.newBuilder()
                        .setNumero("PED-1001")
                        .setCliente("Ana Souza")
                        .setStatus("Em separação")
                        .setValorTotal(159.90)
                        .build(),

                "PED-1002",
                Pedido.newBuilder()
                        .setNumero("PED-1002")
                        .setCliente("Carlos Lima")
                        .setStatus("Enviado")
                        .setValorTotal(89.50)
                        .build()
        );

        @Override
        public void consultarPedido(ConsultarPedidoRequest requisicao, StreamObserver<Pedido> resposta) {
            String numeroPedido = requisicao.getNumeroPedido();
            Pedido pedido = pedidos.get(numeroPedido);

            if (pedido == null) {
                resposta.onError(
                        Status.NOT_FOUND
                                .withDescription("Pedido " + numeroPedido + " não encontrado.")
                                .asRuntimeException()
                );
                return;
            }

            resposta.onNext(pedido);
            resposta.onCompleted();
        }
    }
}
