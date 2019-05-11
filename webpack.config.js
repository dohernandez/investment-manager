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
    plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery'
        }),
    ]
};
