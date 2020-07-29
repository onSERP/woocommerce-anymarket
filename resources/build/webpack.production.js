/**
 * The external dependencies.
 */
const { ProvidePlugin, WatchIgnorePlugin } = require('webpack')
const CleanWebpackPlugin = require('clean-webpack-plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const ImageminPlugin = require('imagemin-webpack-plugin').default
const ManifestPlugin = require('webpack-manifest-plugin')

/**
 * The internal dependencies.
 */
const utils = require('./lib/utils')
const configLoader = require('./config-loader')
const changelog = require('./changelog')
const spriteSmith = require('./spritesmith')
const spriteSvg = require('./spritesvg')
const postcss = require('./postcss')

/**
 * Setup the env.
 */
const env = utils.detectEnv()

/**
 * Setup babel loader.
 */
const babelLoader = {
  loader: 'babel-loader',
  options: {
    cacheDirectory: false,
    presets: [
      [
        '@babel/preset-env',
        {
          targets: '> 1%, last 2 versions',
        },
      ],
    ],
    plugins: [
      '@babel/plugin-syntax-dynamic-import',
      ['@babel/plugin-proposal-class-properties', { loose: true }],
      '@babel/plugin-proposal-object-rest-spread',
      '@babel/plugin-proposal-json-strings',
      '@babel/plugin-syntax-import-meta',
    ],
  },
}

/**
 * Setup webpack plugins.
 */
const plugins = [
  new WatchIgnorePlugin([
    utils.distImagesPath('sprite.png'),
    utils.distImagesPath('sprite@2x.png'),
  ]),
  new ProvidePlugin({
    $: 'jquery',
    jQuery: 'jquery',
  }),
  new MiniCssExtractPlugin({
    filename: `styles/[name]${env.filenameSuffix}.css`,
  }),
  spriteSmith,
  spriteSvg,
  new VueLoaderPlugin(),
  new ImageminPlugin({
    optipng: {
      optimizationLevel: 7,
    },
    gifsicle: {
      optimizationLevel: 3,
    },
    svgo: {
      plugins: [
        { cleanupAttrs: true },
        { removeDoctype: true },
        { removeXMLProcInst: true },
        { removeComments: true },
        { removeMetadata: true },
        { removeUselessDefs: true },
        { removeEditorsNSData: true },
        { removeEmptyAttrs: true },
        { removeHiddenElems: false },
        { removeEmptyText: true },
        { removeEmptyContainers: true },
        { cleanupEnableBackground: true },
        { removeViewBox: true },
        { cleanupIDs: false },
        { convertStyleToAttrs: true },
      ],
    },
    plugins: [
      require('imagemin-mozjpeg')({
        quality: 100,
      }),
    ],
  }),
  new ManifestPlugin(),
]

// When doing a combined build, only clean up the first time.
if (process.env.WPEMERGE_COMBINED_BUILD && env.isDebug) {
  plugins.push(
    new CleanWebpackPlugin(utils.distPath(), {
      root: utils.rootPath(),
    })
  )
}

/**
 * Export the configuration.
 */
module.exports = {
  /**
   * The input.
   */
  entry: require('./webpack/entry'),

  /**
   * The output.
   */
  output: {
    ...require('./webpack/output'),
    ...(env.isProduction
      ? {
          publicPath: '/wp-content/plugins/woocommerce-anymarket-release/dist/',
        }
      : {}),
  },

  /**
   * Resolve utilities.
   */
  resolve: require('./webpack/resolve'),

  /**
   * Resolve the dependencies that are available in the global scope.
   */
  externals: require('./webpack/externals'),

  /**
   * Setup the transformations.
   */
  module: {
    rules: [
      /**
       * Add support for blogs in import statements.
       */
      {
        enforce: 'pre',
        test: /\.(js|jsx|css|scss|sass|vue)$/,
        use: 'import-glob',
      },

      /**
       * Handle config.json.
       */
      {
        type: 'javascript/auto',
        test: utils.rootPath('config.json'),
        use: configLoader,
      },

      /**
       * Handle changelog.
       */
      {
        test: /\.md$/,
        use: changelog,
      },

      /**
       * Vue loader
       * */
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },

      /**
       * Handle scripts.
       */
      {
        test: utils.tests.scripts,
        exclude: /node_modules/,
        use: babelLoader,
      },

      /**
       * Handle styles.
       */
      {
        test: utils.tests.styles,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              publicPath: '../',
            },
          },
          'css-loader',
          {
            loader: 'postcss-loader',
            options: postcss,
          },
          {
            loader: 'sass-loader',
            options: {
              sassOptions: {
                outputStyle: env.isDebug ? 'compact' : 'compressed',
              },
            },
          },
        ],
      },

      /**
       * Handle images.
       */
      {
        test: utils.tests.images,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: (file) =>
                `[name].${utils.filehash(file).substr(0, 10)}.[ext]`,
              outputPath: 'images',
              esModule: false,
            },
          },
        ],
      },

      /**
       * Handle SVG sprites.
       */
      {
        test: utils.tests.spriteSvgs,
        use: [
          {
            loader: 'svg-sprite-loader',
            options: {
              extract: true,
              spriteFilename: 'images/sprite.svg',
            },
          },
        ],
      },

      /**
       * Handle fonts.
       */
      {
        test: utils.tests.fonts,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: (file) =>
                `[name].${utils.filehash(file).substr(0, 10)}.[ext]`,
              outputPath: 'fonts',
            },
          },
        ],
      },
    ],
  },

  /**
   * Setup the transformations.
   */
  plugins,

  /**
   * Setup optimizations.
   */
  optimization: {
    minimize: env.minify,
  },

  /**
   * Setup the development tools.
   */
  mode: 'production',
  cache: false,
  bail: false,
  watch: false,
  devtool: false,
}
