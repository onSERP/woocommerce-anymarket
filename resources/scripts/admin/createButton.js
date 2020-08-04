/**
 * Create button element
 *
 * @param {string} page
 * @returns {object} a
 */
function createButton(page) {
  const protocol = anymarket.sandbox ? 'http://' : 'https://'
  const subdomain = anymarket.sandbox ? 'sandbox' : 'api'
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
 * @param {string} obj.page pagenow
 * @param {string} obj.admin admin page
 * @param {string} obj.dest destination
 *
 * @returns {void}
 */
export default function buttonOnPage(obj) {
  if (pagenow === obj.page && adminpage === obj.admin) {
    const headingInline = document.querySelector('.wp-heading-inline')

    if (headingInline) headingInline.after(createButton(obj.dest))
  }
}
