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
  const id = document.querySelector('input[name*="_anymarket_id"]')

  if (id) {
    // button on product edit page
    buttonOnPage({
      admin: 'post-php',
      page: 'product',
      dest: 'products/edit/' + id.value,
    })
  }
})
