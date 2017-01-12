const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
  context: `${__dirname}/app/Resources/static/js`,
  entry: {
    app: './app.js',
  },
  output: {
    path: `${__dirname}/web`,
    filename: 'strut.bundle.js',
  },
  plugins: [
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
    }),
    new ExtractTextPlugin('strut.bundle.css', {
      allChunks: true,
    }),
  ],
  module: {
    loaders: [
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: '/node_modules',
        query: {
          presets: ['latest'],
        },
      },
      {
        test: /\.twig$/,
        loader: 'twig-loader',
      },
      {
        enforce: 'pre',
        test: /\.js$/,
        loader: 'eslint-loader',
        exclude: /node_modules/,
      },
      {
        test: /\.scss$/,
        loader: ExtractTextPlugin.extract('css!sass'),
      },
      {
        test: /\.css$/,
        loader: ExtractTextPlugin.extract('css'),
      },
      {
        test: /\.(eot|svg|ttf|woff|woff2)$/,
        loader: 'file?name=public/fonts/[name].[ext]'
      }
    ],
  },
};
