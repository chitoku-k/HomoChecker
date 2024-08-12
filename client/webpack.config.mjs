import path from "node:path";
import { createRequire } from "node:module";
import { fileURLToPath } from "node:url";
import webpack from "webpack";
import * as sass from "sass";
import { CleanWebpackPlugin } from "clean-webpack-plugin";
import { LicenseWebpackPlugin } from "license-webpack-plugin";
import CopyWebpackPlugin from "copy-webpack-plugin";
import ESLintPlugin from "eslint-webpack-plugin";
import HtmlWebpackPlugin from "html-webpack-plugin";
import { GitRevisionPlugin } from "git-revision-webpack-plugin";

const require = createRequire(import.meta.url);
const { registerPreprocessor } = require("@riotjs/compiler");

registerPreprocessor("css", "scss", code => ({
    code: sass.compileString(code).css,
    map: null,
}));

const { DefinePlugin } = webpack;
const dirname = path.dirname(fileURLToPath(import.meta.url));

export default {
    mode: process.env.HOMOCHECKER_ENV || "production",
    devtool: process.env.HOMOCHECKER_ENV === "development" ? "eval-cheap-module-source-map" : undefined,
    entry: "./src/app.mjs",
    output: {
        path: path.join(dirname, "/dist"),
        filename: "bundle.js",
        assetModuleFilename: "[name][ext]",
    },
    target: [ "web", "es2023" ],
    module: {
        rules: [
            {
                test: /\.scss$/u,
                use: [
                    { loader: "style-loader" },
                    { loader: "css-loader" },
                    { loader: "sass-loader" },
                ],
            },
            {
                test: /\.riot$/u,
                exclude: /node_modules/u,
                use: [
                    { loader: "@riotjs/webpack-loader" },
                ],
            },
            {
                test: /\.(?:woff2?|ttf|eot|svg)(?:\?v=[\d.]+|\?[\s\S]+)?$/u,
                type: "asset/resource",
            },
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
                    from: path.join(dirname, "/src/resources/favicon.ico"),
                    to: path.join(dirname, "/dist"),
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
                    "まっぴーの X へのリダイレクトにかかる時間を競うエクストリーム・スポーツを主催しています。",
                "keywords": "HomoChecker, Homo Checker, まっぴー, mpyw",
                "theme-color": "#7a6544",
            },
        }),
        new LicenseWebpackPlugin({
            perChunkOutput: false,
        }),
    ],
};
