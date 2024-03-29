/**
 * The internal dependencies.
 */
const utils = require('../lib/utils')

module.exports = {
  modules: [utils.srcScriptsPath(), 'node_modules'],
  extensions: ['.js', '.jsx', '.vue', '.json', '.css', '.scss', 'md'],
  alias: {
    vue$: 'vue/dist/vue.esm.js',
    '@config': utils.rootPath('config.json'),
    '@changelog': utils.rootPath('changelog.md'),
    '@scripts': utils.srcScriptsPath(),
    '@styles': utils.srcStylesPath(),
    '@images': utils.srcImagesPath(),
    '@fonts': utils.srcFontsPath(),
    '@vendor': utils.srcVendorPath(),
    '@dist': utils.distPath(),
    '~': utils.rootPath('node_modules'),
    isotope: 'isotope-layout',
    masonry: 'masonry-layout',
    'jquery-ui': 'jquery-ui-dist/jquery-ui.js',
  },
}
