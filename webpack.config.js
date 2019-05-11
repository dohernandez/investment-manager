const path = require('path');
const webpack = require('webpack');

module.exports = {
    mode: "development", // "production" | "development" | "none"

    entry: {
        crud_manager: './public/assets/js/CRUDManage.js',
        transfer_form: './public/assets/js/TransferForm.js',
        stock_market_form: './public/assets/js/StockMarket.js',
        account_from: './public/assets/js/AccountForm.js',
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
            }
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery'
        }),
    ]
};
