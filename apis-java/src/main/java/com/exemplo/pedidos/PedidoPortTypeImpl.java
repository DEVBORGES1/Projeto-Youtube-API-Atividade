package com.exemplo.pedidos;

import jakarta.jws.WebService;
import jakarta.xml.soap.SOAPConstants;
import jakarta.xml.soap.SOAPException;
import jakarta.xml.soap.SOAPFactory;
import jakarta.xml.soap.SOAPFault;
import jakarta.xml.ws.WebServiceException;
import jakarta.xml.ws.soap.SOAPFaultException;

import javax.xml.namespace.QName;
import java.math.BigDecimal;
import java.util.HashMap;
import java.util.Map;

@WebService(endpointInterface = "com.exemplo.pedidos.PedidoPortType")
public class PedidoPortTypeImpl implements PedidoPortType {
    private static final Map<String, Pedido> PEDIDOS = criarPedidos();

    private static Map<String, Pedido> criarPedidos() {
        Map<String, Pedido> pedidos = new HashMap<>();

        Pedido pedido1 = new Pedido();
        pedido1.setNumero("PED-1001");
        pedido1.setCliente("Ana Souza");
        pedido1.setStatus("Em separação");
        pedido1.setValorTotal(new BigDecimal("159.90"));
        pedidos.put(pedido1.getNumero(), pedido1);

        Pedido pedido2 = new Pedido();
        pedido2.setNumero("PED-1002");
        pedido2.setCliente("Carlos Lima");
        pedido2.setStatus("Enviado");
        pedido2.setValorTotal(new BigDecimal("89.50"));
        pedidos.put(pedido2.getNumero(), pedido2);

        return Map.copyOf(pedidos);
    }

    @Override
    public Pedido consultarPedido(String numeroPedido) {
        System.out.println("Consulta recebida para o pedido: " + numeroPedido);

        Pedido pedido = PEDIDOS.get(numeroPedido);

        if (pedido == null) {
            throw criarFalhaPedidoNaoEncontrado(numeroPedido);
        }

        return pedido;
    }

    private SOAPFaultException criarFalhaPedidoNaoEncontrado(String numeroPedido) {
        try {
            SOAPFault falha = SOAPFactory
                    .newInstance(SOAPConstants.SOAP_1_1_PROTOCOL)
                    .createFault(
                            "Pedido " + numeroPedido + " não encontrado.",
                            new QName(
                                    "http://schemas.xmlsoap.org/soap/envelope/",
                                    "Client",
                                    "soap"
                            )
                    );

            return new SOAPFaultException(falha);
        } catch (SOAPException erro) {
            throw new WebServiceException("Não foi possível criar a falha SOAP.", erro);
        }
    }
}
