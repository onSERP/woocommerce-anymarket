**Atenção**

Este plugin foi descontinuado e não daremos mais suporte. Caso tenha alguma dúvida você ainda pode abrir uma issue que responderemos.


# Woocommerce AnyMarket

Plugin desenvolvido por onSERP Marketing

## Fluxos de Integração

### Criação de Produto

-   O cliente cria o produto no Woocommerce.
-   Escolhe exportar o produto para a Anymarket
-   O produto recebe dois parãmetros extras (custom fields):
    -   exported_to_anymarket (bool)
    -   anymarket_id (string)

### Venda

-   Anymarket notifica via webhook da venda.
-   Recebe notificação e envia um `POST` para puxar os dados da venda.
-   Cria novo pedido no woocommerce
-   Recebe os status e atualizações desse pedido

## Estrutura de arquivos

```
wp-content/plugins/woocommerce-anymarket
├── app/
│   ├── helpers/              			# Helper files, add your own here as well.
│   ├── routes/               			# Registro de rotas.
│   │   └── rest.php
│   ├── src/                  			# PSR-4 autoloaded classes.
│   │   ├── Controllers/      			# Controllers para cada rota.
│   │   ├── Anymarket/        			# Classes do Anymarket.
│   │   ├── WordPress/        			# Registro post types, taxonomias, hooks em geral.
│   │   └── ...
│   ├── config.php            			# WP Emerge configuration.
│   ├── helpers.php           			# Require your helper files here.
│   ├── hooks.php             			# Registro de actions e filters mais avuslsos.
│   └── version.php           			# WP Emerge version handling.
├── dist/                     			# Bundles, optimized images etc.
├── languages/                			# Language files.
├── resources/
│   ├── build/                			# Build process configuration.
│   ├── fonts/
│   ├── images/
│   ├── scripts/
│   │   ├── admin/            			# Administration scripts.
│   │   └── frontend/         			# Front-end scripts.
│   ├── styles/
│   │   ├── admin/            			# Administration styles.
│   │   ├── frontend/         			# Front-end styles.
│   │   └── shared/           			# Shared styles.
│   └── vendor/              			# Any third-party, non-npm assets.
├── vendor/                   			# Composer packages.
├── views/
│   ├── layouts/
│   └── partials/
├── screenshot-1.png          			# Plugin screenshot.
├── wpemerge                  			# WP Emerge CLI shortcut.
├── woocommerce-anymarket.php           # Bootstrap plugin.
└── ...
```

### Diretórios importantes

#### `app/helpers/`

Adicione arquivos auxiliares PHP aqui. Os arquivos auxiliares devem incluir __definições de função apenas__. Veja abaixo informações sobre onde colocar ações, filtros, classes etc.

#### `app/src/`

Adicione as classes PHP aqui. Todas as classes no namespace `Anymarket\` são carregadas de acordo com o [PSR-4](http://www.php-fig.org/psr/psr-4/).

#### `resources/images/`

Adicione imagens aqui. As cópias otimizadas serão colocadas em `dist/images/` ao executar o processo de compilação.

#### `resources/styles/frontend/`

Adicione arquivos .css e .scss para adicioná-los ao bundle do front-end. Não se esqueça de importá-los com `@import` no index.scss`.

#### `resources/styles/admin/`

O diretório contém os estilos referentes ao painel administrativo do WP que funciona de forma idêntica ao diretório `resources/styles/frontend/`.

#### `resources/scripts/frontend/`

Adicione arquivos JavaScript aqui para adicioná-los ao bundle do front-end. O entrypoint é `index.js`.

#### `resources/scripts/admin/`

Os scripts do admin funcionam de forma idêntica aos do diretório `resources/scripts/frontend/`.

#### `views/`

1. `views/layouts/` - Layouts that other views extend.
2. `views/partials/` - Small snippets that are meant to be reused throughout other views.
3. `views/` - Full page views that may extend layouts and may include partials.

Evite adicionar qualquer lógica PHP em qualquer uma dessas visualizações, a menos que se refira ao layout. A lógica de negócios deve entrar em:
- Helper files (`app/helpers/*.php`)
- Service classes
