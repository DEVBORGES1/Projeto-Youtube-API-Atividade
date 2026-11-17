package com.exemplo.pedidos.graphql;

import java.io.IOException;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.nio.charset.StandardCharsets;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Set;

public class ClienteGraphQL {
    private static final String ENDPOINT = "http://127.0.0.1:8082/graphql";
    private static final Set<String> CAMPOS_PERMITIDOS = Set.of("numero", "cliente", "status", "valorTotal");
    private static final HttpClient CLIENTE_HTTP = HttpClient.newHttpClient();

    public static void main(String[] args) throws IOException, InterruptedException {
        String numeroPedido = "PED-1001";

        List<Consulta> consultas = List.of(
                new Consulta("Consulta completa", List.of("numero", "cliente", "status", "valorTotal")),
                new Consulta("Consulta resumida", List.of("numero", "status"))
        );

        for (Consulta consulta : consultas) {
            System.out.println();
            System.out.println("=== " + consulta.descricao() + " ===");

            HttpResponse<String> resposta = consultarPedido(numeroPedido, consulta.campos());

            System.out.println("Status HTTP: " + resposta.statusCode());
            System.out.println("Resposta JSON recebida:");
            System.out.println(resposta.body());
        }
    }

    private static HttpResponse<String> consultarPedido(String numeroPedido, List<String> campos)
            throws IOException, InterruptedException {
        LinkedHashSet<String> camposUnicos = new LinkedHashSet<>(campos);

        if (camposUnicos.isEmpty()) {
            throw new IllegalArgumentException("Informe pelo menos um campo para consultar.");
        }

        for (String campo : camposUnicos) {
            if (!CAMPOS_PERMITIDOS.contains(campo)) {
                throw new IllegalArgumentException("Campo inválido: " + campo);
            }
        }

        String selecao = String.join("\n        ", camposUnicos);

        String consultaGraphQL = """
                query ConsultarPedido($numeroPedido: String!) {
                    pedido(numeroPedido: $numeroPedido) {
                        %s
                    }
                }
                """.formatted(selecao);

        String corpoJson = """
                {
                  "query": "%s",
                  "variables": {
                    "numeroPedido": "%s"
                  }
                }
                """.formatted(
                escaparJson(consultaGraphQL),
                escaparJson(numeroPedido)
        );

        HttpRequest requisicao = HttpRequest.newBuilder()
                .uri(URI.create(ENDPOINT))
                .header("Content-Type", "application/json; charset=UTF-8")
                .header("Accept", "application/json")
                .POST(HttpRequest.BodyPublishers.ofString(corpoJson, StandardCharsets.UTF_8))
                .build();

        return CLIENTE_HTTP.send(requisicao, HttpResponse.BodyHandlers.ofString(StandardCharsets.UTF_8));
    }

    private static String escaparJson(String texto) {
        StringBuilder resultado = new StringBuilder();

        for (char caractere : texto.toCharArray()) {
            switch (caractere) {
                case '"' -> resultado.append("\\\"");
                case '\\' -> resultado.append("\\\\");
                case '\b' -> resultado.append("\\b");
                case '\f' -> resultado.append("\\f");
                case '\n' -> resultado.append("\\n");
                case '\r' -> resultado.append("\\r");
                case '\t' -> resultado.append("\\t");
                default -> {
                    if (caractere < 0x20) {
                        resultado.append(String.format("\\u%04x", (int) caractere));
                    } else {
                        resultado.append(caractere);
                    }
                }
            }
        }

        return resultado.toString();
    }

    private record Consulta(String descricao, List<String> campos) { }
}
