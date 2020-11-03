// eslint-disable-next-line no-unused-vars
import config from '@config'
import '@styles/admin'

import domReady from '@wordpress/dom-ready'

import buttonOnPage from './createButton'

// button on product categories
buttonOnPage({
  admin: 'edit-tags-php',
  page: 'edit-product_cat',
  dest: 'categories',
})

// button on products list
buttonOnPage({
  admin: 'edit-php',
  page: 'edit-product',
  dest: 'products/list',
})

buttonOnPage({
  admin: 'product_page_product_attributes',
  page: 'product_page_product_attributes',
  dest: 'variations/list',
})

// wait for wp domready event to get field id
domReady(function () {
  const idField = document.querySelector('input[name*="_anymarket_id"]')

  if (idField) {
    // button on product edit page
    buttonOnPage({
      admin: 'post-php',
      page: 'product',
      dest: 'products/edit/' + idField.value,
    })

    buttonOnPage({
      admin: 'post-php',
      page: 'shop_order',
      dest: 'orders/edit/' + idField.value,
    })
  }
})

//block everything on anymarket order
domReady(function () {
  if (
    pagenow === 'shop_order' &&
    adminpage === 'post-php' &&
    document.querySelector('input[name*="_anymarket_id"]').value !== ''
  ) {
    document.querySelector('#woocommerce-order-downloads').style.display =
      'none'

    document.querySelectorAll('#order_data input').forEach((item) => {
      item.setAttribute('readonly', 'readonly')
      item.setAttribute('disabled', 'disabled')
    })

    document.querySelectorAll('#order_data textarea').forEach((item) => {
      item.setAttribute('readonly', 'readonly')
      item.setAttribute('disabled', 'disabled')
    })

    document.querySelectorAll('#order_data button').forEach((item) => {
      item.setAttribute('readonly', 'readonly')
      item.setAttribute('disabled', 'disabled')
    })

    document.querySelectorAll('#order_data a.edit_address').forEach((item) => {
      item.style.display = 'none'
    })
  }
})

//delete category button
if (pagenow === 'edit-product_cat') {
  domReady(function () {
    const id = document.querySelector('input[name*="_anymarket_id"]').value
    const button = document.querySelector('#button-delete-category')

    if (id === '') {
      button.setAttribute('disabled', 'disabled')
    }

    button.addEventListener('click', (e) => {
      e.preventDefault()

      if (id === '') return

      const confirmation = confirm(
        'Você tem certeza? Esta ação não pode ser desfeita.'
      )

      if (confirmation) {
        window.location = e.target.attributes.href.value
      }
    })
  })
}
