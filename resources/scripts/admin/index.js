// eslint-disable-next-line no-unused-vars
import config from '@config'
import '@styles/admin'

/**
 * Create button element
 *
 * @param {string} page
 * @returns {object} a
 */
function createButton(page) {
  const protocol = anymarket_is_sandbox ? 'http://' : 'https://'
  const subdomain = anymarket_is_sandbox ? 'sandbox' : 'api'
  const link = `${protocol}${subdomain}.anymarket.com.br/#/${page}`

  const a = document.createElement('a')
  a.setAttribute('href', link)
  a.setAttribute('target', '_blank')
  a.classList.add('page-title-action')
  a.innerText = 'Ver no Anymarket '
  a.style.display = 'inline-block'
  a.style.paddingBottom = '1px'

  const span = document.createElement('span')
  span.classList.add('dashicons', 'dashicons-external')

  a.append(span)

  return a
}

/**
 * Append button on page
 *
 * @param {string} path path
 * @param {string} param query param
 * @param {string} value query value
 * @param {string} dest destination
 *
 * @returns {void}
 */
function buttonOnPage(path, param, value, dest) {
  const params = new URL(document.location).searchParams
  const pathname = '/wp-admin/' + path

  if (window.location.pathname === pathname && params.get(param) === value) {
    const headingInline = document.querySelector('.wp-heading-inline')
    headingInline.after(createButton(dest))
  }
}

// button on product categories
buttonOnPage('edit-tags.php', 'taxonomy', 'product_cat', 'categories')

// button on products list
buttonOnPage('edit.php', 'post_type', 'product', 'products/list')
