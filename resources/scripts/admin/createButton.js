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
 * @param {string} obj object of params
 * @param {string} obj.path path
 * @param {string} obj.param query param
 * @param {string} obj.value query value
 * @param {string} obj.dest destination
 *
 * @returns {void}
 */
export default function buttonOnPage(obj) {
  const searchParams = new URL(document.location).searchParams
  const pathname = '/wp-admin/' + obj.path

  if (
    window.location.pathname === pathname &&
    searchParams.get(obj.param) === obj.value
  ) {
    const headingInline = document.querySelector('.wp-heading-inline')
    headingInline.after(createButton(obj.dest))
  }
}
