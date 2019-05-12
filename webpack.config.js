const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserJSPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const useSourceMap = process.env.NODE_ENV !== 'production';

const styleLoader = {
    loader: 'style-loader',
    options: {
        sourceMap: useSourceMap
    }
};

// cssLoader to parser css files into a js object
const cssLoader = {
    loader: 'css-loader',
    options: {
        sourceMap: useSourceMap
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

const devMode = process.env.NODE_ENV !== 'production';

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
        filename: devMode ? "[name].js" : '[name].[hash:6].js',
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
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery',
        }),
        new MiniCssExtractPlugin({
            // Options similar to the same options in webpackOptions.output
            // options are optional
            filename: devMode ? '[name].css' : '[name].[hash:6].css',
        }),
    ],
    optimization: {
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
};
