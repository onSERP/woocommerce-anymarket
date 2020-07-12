/**
 * The external dependencies.
 */
const marked = require('marked')
const renderer = new marked.Renderer()

module.exports = [
  {
    loader: 'html-loader',
  },
  {
    loader: 'markdown-loader',
    options: {
      pedantic: true,
      renderer,
    },
  },
]
