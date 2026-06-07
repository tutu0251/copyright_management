const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const webpack = require('webpack');

module.exports = (env, argv) => {
  const isProd = argv && argv.mode === 'production';

  return {
    entry: path.resolve(__dirname, 'src/main.jsx'),
    output: {
      path: path.resolve(__dirname, 'dist'),
      filename: isProd ? 'assets/[name].[contenthash:8].js' : 'assets/[name].js',
      publicPath: '/',
    },
    resolve: {
      extensions: ['.js', '.jsx'],
    },
    module: {
      rules: [
        {
          test: /\.(js|jsx)$/,
          // Transpile our source AND node_modules (react-router 6, chart.js 4 ship
          // modern syntax FF52 can't parse), but never re-process the polyfills.
          exclude: /node_modules[\\/](core-js|@babel[\\/]runtime|webpack[\\/]buildin)/,
          use: {
            loader: 'babel-loader',
            options: { cacheDirectory: true },
          },
        },
        {
          test: /\.css$/,
          use: ['style-loader', { loader: 'css-loader', options: { sourceMap: false } }],
        },
        {
          test: /\.(png|jpe?g|gif|svg|woff2?|ttf|eot)$/,
          use: [
            {
              loader: 'file-loader',
              options: { name: 'assets/[name].[hash:8].[ext]' },
            },
          ],
        },
      ],
    },
    plugins: [
      new CleanWebpackPlugin(),
      new HtmlWebpackPlugin({
        template: path.resolve(__dirname, 'index.html'),
        inject: 'body',
      }),
      new CopyWebpackPlugin({
        patterns: [
          {
            from: path.resolve(__dirname, 'public'),
            to: path.resolve(__dirname, 'dist'),
            noErrorOnMissing: true,
          },
        ],
      }),
      // Replace import.meta.env.VITE_API_URL usage (now process.env.VITE_API_URL).
      new webpack.DefinePlugin({
        'process.env.VITE_API_URL': JSON.stringify(process.env.VITE_API_URL || ''),
        'process.env.NODE_ENV': JSON.stringify(isProd ? 'production' : 'development'),
      }),
    ],
    devtool: isProd ? 'source-map' : 'cheap-module-source-map',
    devServer: {
      port: 5173,
      host: '0.0.0.0',
      // Serve copied static assets (public/) in dev too.
      contentBase: path.resolve(__dirname, 'public'),
      // BrowserRouter client-side routes fall back to index.html.
      historyApiFallback: true,
      disableHostCheck: true,
      proxy: {
        '/api': {
          target: 'http://localhost:5000',
          changeOrigin: true,
        },
      },
    },
    performance: { hints: false },
  };
};
