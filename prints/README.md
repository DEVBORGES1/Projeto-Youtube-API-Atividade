# Prints dos exercícios

O PDF da atividade, já com os prints inseridos, está em [`entrega/APIs-com-prints.pdf`](../entrega/APIs-com-prints.pdf).

## 1. YouTube API (`1-youtube/`)

| Arquivo | Exercício | Página do PDF |
|---|---|---|
| `v4-pesquisa.png` | 4.4.6 – Versão 4, pesquisa na caixa "pesquisar vídeos" | 52 |
| `v5-pesquisa.png` | 4.4.7 – Versão 5 (front-end + FastAPI), pesquisa | 68 |
| `v5-modal.png` | 4.4.7 – Versão 5, vídeo aberto no modal | – |

## 2. SOAP (`2-soap/`)

| Arquivo | Conteúdo | Página do PDF |
|---|---|---|
| `soap-1-servidor-php.png` | Servidor SOAP PHP (porta 8081) | 95 |
| `soap-2-cliente-php.png` | Cliente PHP: `PED-1001` e falha com `PED-9999` | 95 |
| `soap-3-cliente-java.png` | Cliente Java consumindo o servidor PHP | 95 |
| `soap-4-servidor-java.png` | Servidor SOAP Java (porta 8081) | – |
| `soap-5-cliente-php-servidor-java.png` | Cliente PHP consumindo o servidor Java | – |
| `soap-6-servidor-java-documento.png` | Servidor Java document/literal (porta 8082) | – |
| `soap-7-clientes-documento.png` | Clientes PHP e Java document/literal | – |

## 3. GraphQL (`3-graphql/`)

| Arquivo | Conteúdo | Página do PDF |
|---|---|---|
| `graphql-1-servidor-php.png` | Servidor GraphQL PHP (porta 8082) | 108 |
| `graphql-2-clientes-php.png` | `consulta.php`, `mutacao.php` e `criar-pedido.php` | 108 |
| `graphql-3-cliente-java.png` | `ClienteGraphQL.java` | 108 |

## 4. gRPC (`4-grpc/`)

| Arquivo | Conteúdo | Página do PDF |
|---|---|---|
| `grpc-1-servidor.png` | Servidor gRPC Java (porta 50051) | 114 |
| `grpc-2-cliente.png` | Cliente gRPC: `PED-1001` e falha `NOT_FOUND` com `PED-9999` | 114 |

## Observações

- Os prints de SOAP, GraphQL e gRPC são de execuções reais feitas em um terminal Linux
  (PHP 8.3, Java 21). Os programas Java foram executados com `java -cp "$CP" <classe>`.
- No Linux, `iniciar-servidor.php` não funciona, porque usa a opção `SO_EXCLUSIVEADDRUSE`,
  que só existe no Windows. Por isso, o servidor SOAP PHP foi iniciado diretamente com o
  comando que esse script executa: `php -S 127.0.0.1:8081 -t . servidor.php`.
- No PDF, as chaves de API do YouTube que apareciam no material (págs. 20 e 46) foram tarjadas.
