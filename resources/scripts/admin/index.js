// eslint-disable-next-line no-unused-vars
import config from '@config'
import '@styles/admin'

import buttonOnPage from './createButton'

// button on product categories
buttonOnPage({
  path: 'edit-tags.php',
  param: 'taxonomy',
  value: 'product_cat',
  dest: 'categories',
})

// button on products list
buttonOnPage({
  path: 'edit.php',
  param: 'post_type',
  value: 'product',
  dest: 'products/list',
})
