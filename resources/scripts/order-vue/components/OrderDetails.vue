<template>
  <div>
    <div v-if="loaded === true">
      <vue-good-table
        :columns="columns"
        :rows="rows"
        styleClass="vgt-table anymarket-order-table"
        >>
        <template slot="table-row" slot-scope="props">
          <div v-if="props.column.field == 'item'">
            <p>
              <a
                :href="`${props.row.item.anymarketProtocol}://${props.row.item.anymarketSubdomain}.anymarket.com.br/#/products/edit/${props.row.item.productId}`"
                target="_blank"
              >
                {{ props.row.item.title }}
              </a>
            </p>
            <p><b>SKU: </b>{{ props.row.item.sku }}</p>
          </div>
          <p v-else>
            {{ props.formattedRow[props.column.field] }}
          </p>
        </template>
      </vue-good-table>
      <div class="anymarket-order-totals">
        <table>
          <tbody>
            <tr>
              <td>Subtotal de itens:</td>
              <td>
                <b>{{ formatCurrency(subtotal) }}</b>
              </td>
            </tr>
            <tr>
              <td>Frete:</td>
              <td>
                <b>{{ formatCurrency(shipping) }}</b>
              </td>
            </tr>
            <tr>
              <td>Descontos:</td>
              <td>
                <b>{{ formatCurrency(discount) }}</b>
              </td>
            </tr>
            <tr>
              <td>Total do pedido:</td>
              <td>
                <b>{{ formatCurrency(paid) }}</b>
              </td>
            </tr>
            <tr>
              <td><p>Pago pelo cliente:</p></td>
              <td>
                <p>
                  <b>{{ formatCurrency(paid) }}</b>
                </p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <ContentLoader
      v-else
      :speed="2"
      :width="1000"
      :height="550"
      viewBox="0 0 1000 550"
      backgroundColor="#eaeced"
      foregroundColor="#ffffff"
    >
      <rect x="51" y="45" rx="3" ry="3" width="906" height="17" />
      <circle cx="879" cy="123" r="11" />
      <circle cx="914" cy="123" r="11" />
      <rect x="104" y="115" rx="3" ry="3" width="141" height="15" />
      <rect x="305" y="114" rx="3" ry="3" width="299" height="15" />
      <rect x="661" y="114" rx="3" ry="3" width="141" height="15" />
      <rect x="55" y="155" rx="3" ry="3" width="897" height="2" />
      <circle cx="880" cy="184" r="11" />
      <circle cx="915" cy="184" r="11" />
      <rect x="105" y="176" rx="3" ry="3" width="141" height="15" />
      <rect x="306" y="175" rx="3" ry="3" width="299" height="15" />
      <rect x="662" y="175" rx="3" ry="3" width="141" height="15" />
      <rect x="56" y="216" rx="3" ry="3" width="897" height="2" />
      <circle cx="881" cy="242" r="11" />
      <circle cx="916" cy="242" r="11" />
      <rect x="106" y="234" rx="3" ry="3" width="141" height="15" />
      <rect x="307" y="233" rx="3" ry="3" width="299" height="15" />
      <rect x="663" y="233" rx="3" ry="3" width="141" height="15" />
      <rect x="57" y="274" rx="3" ry="3" width="897" height="2" />
      <circle cx="882" cy="303" r="11" />
      <circle cx="917" cy="303" r="11" />
      <rect x="107" y="295" rx="3" ry="3" width="141" height="15" />
      <rect x="308" y="294" rx="3" ry="3" width="299" height="15" />
      <rect x="664" y="294" rx="3" ry="3" width="141" height="15" />
      <rect x="58" y="335" rx="3" ry="3" width="897" height="2" />
      <circle cx="881" cy="363" r="11" />
      <circle cx="916" cy="363" r="11" />
      <rect x="106" y="355" rx="3" ry="3" width="141" height="15" />
      <rect x="307" y="354" rx="3" ry="3" width="299" height="15" />
      <rect x="663" y="354" rx="3" ry="3" width="141" height="15" />
      <rect x="57" y="395" rx="3" ry="3" width="897" height="2" />
      <circle cx="882" cy="424" r="11" />
      <circle cx="917" cy="424" r="11" />
      <rect x="107" y="416" rx="3" ry="3" width="141" height="15" />
      <rect x="308" y="415" rx="3" ry="3" width="299" height="15" />
      <rect x="664" y="415" rx="3" ry="3" width="141" height="15" />
      <rect x="55" y="453" rx="3" ry="3" width="897" height="2" />
      <rect x="51" y="49" rx="3" ry="3" width="2" height="465" />
      <rect x="955" y="49" rx="3" ry="3" width="2" height="465" />
      <circle cx="882" cy="484" r="11" />
      <circle cx="917" cy="484" r="11" />
      <rect x="107" y="476" rx="3" ry="3" width="141" height="15" />
      <rect x="308" y="475" rx="3" ry="3" width="299" height="15" />
      <rect x="664" y="475" rx="3" ry="3" width="141" height="15" />
      <rect x="55" y="513" rx="3" ry="3" width="897" height="2" />
      <rect x="52" y="80" rx="3" ry="3" width="906" height="17" />
      <rect x="53" y="57" rx="3" ry="3" width="68" height="33" />
      <rect x="222" y="54" rx="3" ry="3" width="149" height="33" />
      <rect x="544" y="55" rx="3" ry="3" width="137" height="33" />
      <rect x="782" y="56" rx="3" ry="3" width="72" height="33" />
      <rect x="933" y="54" rx="3" ry="3" width="24" height="33" />
    </ContentLoader>
  </div>
</template>

<script>
import { anymarket } from '../utils/api'
import { ContentLoader } from 'vue-content-loader'

export default {
  components: {
    ContentLoader,
  },
  data: () => {
    return {
      orderId: '',
      loaded: false,
      subtotal: 0,
      total: 0,
      discount: 0,
      paid: 0,
      shipping: 0,
      columns: [
        {
          label: 'Item',
          field: 'item',
          html: true,
        },
        {
          label: 'Preço',
          field: 'price',
          type: 'number',
        },
        {
          label: 'Quantidade',
          field: 'quantity',
          type: 'number',
        },
        {
          label: 'Total',
          field: 'total',
          type: 'number',
        },
      ],
      rows: [],
    }
  },
  mounted() {
    this.defineOrder()
    this.getData()
  },
  methods: {
    defineOrder() {
      this.orderId = document.querySelector(
        'input[name*="_anymarket_id"]'
      ).value
    },
    getData() {
      anymarket
        .get(`orders/${this.orderId}`)
        .then((response) => {
          this.loaded = true
          this.subtotal = response.data.gross
          this.paid = response.data.total
          this.discount = response.data.discount
          this.shipping = response.data.freight

          response.data.items.forEach((el, index) => {
            const obj = {
              id: index + 1,
              item: {
                title: el.sku.title,
                sku: el.idInMarketPlace,
                productId: el.product.id,
                anymarketProtocol:
                  window.anymarket.sandbox === true ? 'http' : 'https',
                anymarketSubdomain:
                  window.anymarket.sandbox === true ? 'sandbox' : 'app',
              },
              price: this.formatCurrency(el.unit),
              quantity: 'x ' + el.amount,
              total: this.formatCurrency(el.unit * el.amount),
            }

            this.rows.push(obj)
          })

          console.log(response.data)
        })
        .catch((err) => {
          console.log(err)
        })
    },
    formatCurrency(number) {
      const format = {
        minimumFractionDigits: 2,
        style: 'currency',
        currency: 'BRL',
      }
      return number.toLocaleString('pt-BR', format)
    },
  },
}
</script>

<style lang="scss">
.anymarket-order-table {
  thead {
    background-color: #f8f8f8;
    span {
      font-size: 13px;
      font-weight: 400;
      color: #999;
    }
  }

  tbody {
    p {
      color: #444;
    }
  }
}
.anymarket-order-totals {
  display: flex;
  justify-content: flex-end;
  background-color: #f8f8f8;
  color: #000;

  & > table {
    width: 50%;
    max-width: 320px;
    padding-bottom: 20px;
    padding-top: 20px;

    td {
      padding: 10px 20px;
      text-align: right;
    }

    tfoot {
      display: block;
      border-top: 1px solid #333 !important;
      border-collapse: collapse;
    }

    @media (max-width: 580px) {
      width: 100%;
    }
  }
}
</style>
