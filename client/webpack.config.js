const riot = require("riot-compiler");
const sass = require("sass");
const path = require("path");
const { DefinePlugin } = require("webpack");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const { LicenseWebpackPlugin } = require("license-webpack-plugin");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const ESLintPlugin = require("eslint-webpack-plugin");
const HtmlWebpackPlugin = require("html-webpack-plugin");
const { GitRevisionPlugin } = require("git-revision-webpack-plugin");

riot.parsers.css["dart-sass"] = (tagName, css) => sass.renderSync({ data: css }).css + "";

module.exports = {
    mode: process.env.HOMOCHECKER_ENV || "production",
    devtool: process.env.HOMOCHECKER_ENV === "development" ? "eval-cheap-module-source-map" : undefined,
    entry: "./src/app.js",
    output: {
        path: path.join(__dirname, "/dist"),
        filename: "bundle.js",
        assetModuleFilename: "[name][ext]",
    },
    target: ["web", "es5"],
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
                test: /\.js$/,
                enforce: "post",
                use: [
                    { loader: "babel-loader" },
                ],
            },
            {
                test: /\.tag$/,
                enforce: "post",
                exclude: /node_modules/,
                use: [
                    { loader: "babel-loader" },
                ],
            },
            {
                test: /\.(woff2?|ttf|eot|svg)(\?v=[\d.]+|\?[\s\S]+)?$/,
                type: "asset/resource",
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
        new CleanWebpackPlugin({
            cleanStaleWebpackAssets: process.env.HOMOCHECKER_ENV === "production",
            cleanOnceBeforeBuildPatterns: [
                "**/*.{html,js,ico}",
            ],
        }),
        new CopyWebpackPlugin({
            patterns: [
                {
                    from: path.join(__dirname, "/src/resources/favicon.ico"),
                    to: path.join(__dirname, "/dist"),
                },
            ],
        }),
        new ESLintPlugin(),
        new DefinePlugin({
            COMMIT_HASH: JSON.stringify((new GitRevisionPlugin()).commithash()),
        }),
        new HtmlWebpackPlugin({
            title: "まっぴー (@mpyw) 被害者の会",
            filename: "index.html",
            meta: {
                "viewport": "initial-scale=1.0, viewport-fit=cover",
                "description":
                    "HomoChecker はホモ（@mpyw）にリダイレクトするホモのためのホモの輪です。" +
                    "まっぴーの Twitter へのリダイレクトにかかる時間を競うエクストリーム・スポーツを主催しています。",
                "keywords": "HomoChecker, Homo Checker, まっぴー, mpyw",
                "theme-color": "#7a6544",
            },
        }),
        new LicenseWebpackPlugin({
            perChunkOutput: false,
        }),
    ],
};
