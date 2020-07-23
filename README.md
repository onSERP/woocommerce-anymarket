# Woocommerce AnyMarketing

Plugin desenvolvido por onSERP Marketing

## Fluxos de Integração

### Criação de Produto

-   O cliente cria o produto no Woocommerce.
-   Escolhe exportar o produto para a Anymarket
-   O produto recebe dois parãmetros extras:
    -   exported_to_anymarket (bool)
    -   anymarket_id (string)

### Venda

-   Anymarket notifica via webhook da venda.
-   Recebe notificação e envia um `POST` para puxar os dados da venda.
-   Cria novo pedido no woocommerce
-   Recebe os status e atualizações desse pedido

## Atividades

### Pré dev

-   [x] Levantamento de campos equvalentes entre as duas plataformas.
-   [x] Levantamento de campos novos no Woocommerce.
-   [x] Fazer equivalencia dos campos da Anymarket com os novos campos do Woocommerce.

### Funcionalidades gerais

-   [x] Exportação em massa - Categoria
-   [x] Exportação individual - Categoria
-   [x] Exportação seletiva - Categoria
-   [ ] Exportação em massa - Produto
-   [ ] Exportação individual - Produto
-   [ ] Exportação seletiva - Produto
-   [ ] Links nas áreas do woocommerce para o cliente editar algo no Anymarket.
-   [ ] Tempo de garantia padrão nas configurações do plugin.

### Opções de Preço

-   [ ] Preço automático (Custo + Markup)
-   [ ] Preço manual por anúncio (Editado somente na Anymarket)
-   [ ] Preço manual por SKU (Editado nas variações de produtos do Woocommerce)

### Produto

-   [x] Adicionar campo de código de barras no produto
-   [x] Adicionar campo de tempo de garantia (meses) no produto
-   [x] Checkbox - "Exportar para o Anymarket?"

### Pedido

-   [x] Adicionar campo marketplace
-   [x] Inserir na visualização de pedidos o nome do marketplace
-   [x] Link para verificar pedido no ANYMARKET

### Status de pedido

-   [ ] Pendente - Pagamento Pendente ou Aguardando
-   [x] Pago - Pago (CRIAR)
-   [x] Faturado - Faturado (CRIAR)
-   [x] Enviado - Enviado (CRIAR)
-   [ ] Concluído - Concluído
-   [ ] Cancelado - Cancelado

### Dúvidas

-   [x] Integração com NFE e informações de envio

---

**Importante: Depois que o plugin estiver pronto e rodando, adicionar funcionalidade de contratação pelo próprio plugin**
