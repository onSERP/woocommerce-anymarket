// eslint-disable-next-line no-unused-vars
import config from '@config'
import '@styles/admin'

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

// wait for wp domready event to get field id
wp.domReady(function () {
  const id = document.querySelector('input[name*="_anymarket_id"]').value

  if (id !== '') {
    // button on product edit page
    buttonOnPage({
      admin: 'post-php',
      page: 'product',
      dest: 'products/edit/' + id.value,
    })

    buttonOnPage({
      admin: 'post-php',
      page: 'shop_order',
      dest: 'orders/edit/' + id.value,
    })
  }
})

//block everything on anymarket order
wp.domReady(function () {
  if (
    pagenow === 'shop_order' &&
    adminpage === 'post-php' &&
    document.querySelector('input[name*="_anymarket_id"]').value !== ''
  ) {
    document.querySelector('#postcustom').style.display = 'none'
    document.querySelector('#woocommerce-order-actions').style.display = 'none'
    document.querySelector('#wc_correios').style.display = 'none'
    document.querySelector('#woocommerce-order-downloads').style.display =
      'none'
    document.querySelector('#woocommerce-order-items').style.display = 'none'

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

    document.querySelectorAll('#order_data select').forEach((item) => {
      item.setAttribute('readonly', 'readonly')
      item.setAttribute('disabled', 'disabled')
    })

    document.querySelectorAll('#order_data a.edit_address').forEach((item) => {
      item.style.display = 'none'
    })
  }
})

//delete category button
wp.domReady(function () {
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
