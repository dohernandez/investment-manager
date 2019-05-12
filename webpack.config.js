const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const styleLoader = {
    loader: 'style-loader',
    options: {
        sourceMap: true
    }
};

const cssLoader = {
    loader: 'css-loader',
    options: {
        sourceMap: true
    }
};

const sassLoader = {
    loader: 'sass-loader',
    options: {
        sourceMap: true
    }
};

const devMode = process.env.NODE_ENV !== 'production';

const miniCssExtractLoader = {
    loader: MiniCssExtractPlugin.loader,
    options: {
        publicPath: (resourcePath, context) => {
            // publicPath is the relative path of the resource to the context
            // e.g. for ./css/admin/main.css the publicPath will be ../../
            // while for ./css/main.css the publicPath will be ../
            return path.relative(path.dirname(resourcePath), context) + '/';
        },
        hmr: devMode,
    },
};

module.exports = {
    mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',

    entry: {
        crud_manager: './assets/js/CRUDManage.js',
        transfer_form: './assets/js/TransferForm.js',
        stock_market_form: './assets/js/StockMarket.js',
        account_from: './assets/js/AccountForm.js',
    },
    output: {
        path: path.resolve(__dirname, 'public', 'build'),
        filename: "[name].js",
    },
    module: {
        rules: [
            {
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
                test: /\.css$/,
                use: [
                    miniCssExtractLoader,
                    cssLoader,
                ]
            },
            {
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
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery',
        }),
        new MiniCssExtractPlugin({
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            filename: devMode ? '[name].css' : '[name].[hash].css',
            chunkFilename: devMode ? '[id].css' : '[id].[hash].css',
        }),
    ],
    optimization: {
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
};
