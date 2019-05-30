const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserJSPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const WebpackChunkHash = require('webpack-chunk-hash');
const CleanWebpackPlugin = require('clean-webpack-plugin');

const devMode = process.env.NODE_ENV !== 'production';

const styleLoader = {
    loader: 'style-loader',
    options: {
        sourceMap: devMode
    }
};

// cssLoader to parser css files into a js object
const cssLoader = {
    loader: 'css-loader',
    options: {
        sourceMap: devMode
    }
};

// sassLoader to parser sass files into a css.
// sourceMap MUST be always true.
const sassLoader = {
    loader: 'sass-loader',
    options: {
        sourceMap: true
    }
};

// miniCssExtractLoader to extract css from js to a separate file
const miniCssExtractLoader = {
    loader: MiniCssExtractPlugin.loader,
    options: {
        publicPath: (resourcePath, context) => {
            // publicPath is the relative path of the resource to the context
            // e.g. for ./css/admin/main.css the publicPath will be ../../
            // while for ./css/main.css the publicPath will be ../
            return path.relative(path.dirname(resourcePath), context) + '/';
        },
        hmr: process.env.NODE_ENV === 'development',
    },
};

module.exports = {
    mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',

    entry: {
        crud_manager: './assets/js/CRUDManage.js',
        transfer_form: './assets/js/TransferForm.js',
        stock_market_form: './assets/js/StockMarketForm.js',
        account_from: './assets/js/AccountForm.js',
        stock_form: './assets/js/StockForm.js',
        stock_dividend_form: './assets/js/StockDividendForm.js',
        broker_form: './assets/js/BrokerForm.js',
        wallet_form: './assets/js/WalletForm.js',
        wallet_dashboard: './assets/js/WalletDashboard.js',
        broker_stock_form: './assets/js/BrokerStockForm.js',
    },
    output: {
        path: path.resolve(__dirname, 'public', 'build'),
        filename: devMode ? "[name].js" : '[name].[chunkhash:6].js',
        publicPath: '/',
    },
    module: {
        rules: [
            {
                // Enable babel loader. Transpile modern js to old js.
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env'],
                        cacheDirectory: true,
                    }
                }
            },
            {
                // Enable css loader.
                test: /\.css$/,
                use: [
                    miniCssExtractLoader,
                    cssLoader,
                ]
            },
            {
                // Enable sass loader.
                test: /\.scss$/,
                use: [
                    miniCssExtractLoader,
                    cssLoader,
                    sassLoader,
                ]
            },
        ],
    },
    plugins: [
        // autoimport jquery library whenever $, jQuery and window.jQuery is used.
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery',
        }),
        new MiniCssExtractPlugin({
            // Options similar to the same options in webpackOptions.output
            // options are optional
            filename: devMode ? '[name].css' : '[name].[chunkhash:6].css',
        }),
        // create manifest json file for versioning support.
        new ManifestPlugin({
            basePath: 'build/',
            publicPath: 'build/',
            // always dump manifest
            writeToFileEmit: true
        }),
        // allows for [chunkhash]
        new WebpackChunkHash(),
        // clean
        new CleanWebpackPlugin(),
    ],
    optimization: {
        namedModules: true,
        minimizer: [
            new TerserJSPlugin({}),
            new OptimizeCSSAssetsPlugin({}),
        ],
        splitChunks: {
            cacheGroups: {
                commons: {
                    test: /[\\/]node_modules[\\/]/,
                    name: "vendors",
                    chunks: "all"
                }
            }
        }
    },
    devtool: "inline-source-map",
    devServer: {
        host: '0.0.0.0',
        useLocalIp: true,
        // to set base web folder "public"
        contentBase: path.resolve(__dirname, 'public'),
        compress: true,
        // to run server in port
        port: 9000,
        // to set where to find the build files
        publicPath: '/build/',
        // to remove host check in CORS policy
        disableHostCheck: true,

    },
};
