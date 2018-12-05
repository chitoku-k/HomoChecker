const webpack = require("webpack");
const path = require("path");
const CleanWebpackPlugin = require("clean-webpack-plugin");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const HtmlWebpackPlugin = require("html-webpack-plugin");

module.exports = {
    mode: process.env.HOMOCHECKER_ENV || "production",
    entry: "./src/app.js",
    output: {
        path: path.join(__dirname, "/dist"),
        filename: "bundle.js",
    },
    module: {
        rules: [
            {
                test: /\.scss$/,
                use: [
                    { loader: "style-loader" },
                    { loader: "css-loader" },
                    { loader: "sass-loader" },
                ],
            },
            {
                test: /\.tag$/,
                enforce: "pre",
                exclude: /node_modules/,
                use: [
                    { loader: "riot-tag-loader" },
                ],
            },
            {
                test: /\.(js|tag)$/,
                enforce: "pre",
                exclude: /node_modules/,
                use: [
                    { loader: "eslint-loader" },
                ],
            },
            {
                test: /\.(js|tag)$/,
                enforce: "post",
                exclude: /node_modules/,
                use: [
                    { loader: "babel-loader" },
                ],
            },
            {
                test: /\.(woff2?|ttf|eot|svg)(\?v=[\d.]+|\?[\s\S]+)?$/,
                use: [
                    { loader: "file-loader?name=[name].[ext]" },
                ],
            },
        ],
    },
    resolve: {
        extensions: [
            ".js",
            ".tag",
        ],
    },
    plugins: [
        new CleanWebpackPlugin(
            path.join(__dirname, "/dist/*.{html,js,ico}"),
        ),
        new CopyWebpackPlugin([
            {
                from: path.join(__dirname, "/src/resources/favicon.ico"),
                to: path.join(__dirname, "/dist"),
            },
        ]),
        new HtmlWebpackPlugin({
            title: "まっぴー (@mpyw) 被害者の会",
            filename: "index.html",
            meta: {
                "viweport": "initial-scale=1.0, viewport-fit=cover",
                "theme-color": "#7a6544",
            },
        }),
    ],
};
