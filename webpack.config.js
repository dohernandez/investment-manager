const path = require('path');
const webpack = require('webpack');

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

module.exports = {
    mode: "development", // "production" | "development" | "none"

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
                    styleLoader,
                    cssLoader,
                ]
            },
            {
                test: /\.scss$/,
                use: [
                    styleLoader,
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
