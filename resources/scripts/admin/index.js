// eslint-disable-next-line no-unused-vars
import config from '@config'
import '@styles/admin'

// button on product categories

function buttonOnCategories() {
  const params = new URL(document.location).searchParams

  if (params.get('taxonomy') === 'product_cat') {
    const headingInline = document.querySelector('.wp-heading-inline')

    const protocol = anymarket_is_sandbox ? 'http://' : 'https://'
    const subdomain = anymarket_is_sandbox ? 'sandbox' : 'api'
    const link = `${protocol}${subdomain}.anymarket.com.br/#/categories`

    const a = document.createElement('a')
    a.setAttribute('href', link)
    a.setAttribute('target', '_blank')
    a.classList.add('page-title-action')
    a.innerText = 'Ver no Anymarket '
    a.style.display = 'inline-block'

    const span = document.createElement('span')
    span.classList.add('dashicons', 'dashicons-external')

    a.append(span)

    headingInline.after(a)
  }
}

buttonOnCategories()
