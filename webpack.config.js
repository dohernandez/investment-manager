const path = require('path');
const webpack = require('webpack');

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
                    'style-loader',
                    'css-loader',
                ]
            },
            {
                test: /\.scss$/,
                use: [
                    'style-loader',
                    'css-loader',
                    'sass-loader',
                ]
            },
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery'
        }),
    ]
};
