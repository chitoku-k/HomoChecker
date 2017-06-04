import webpack from "webpack";
import path from "path";
import HtmlWebpackPlugin from "html-webpack-plugin";

module.exports = {
    entry: "./client/src/app.js",
    output: {
        path: path.join(__dirname, "/client/dist"),
        filename: "bundle.js",
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    { loader: "style-loader" },
                    { loader: "css-loader" },
                ],
            },
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
                test: /\.js$|\.tag$/,
                enforce: "post",
                exclude: /node_modules/,
                use: [
                    { loader: "babel-loader" },
                ],
            },
            {
                test: /\.(woff2?|ttf|eot|svg)(\?v=[\d.]+|\?[\s\S]+)?$/,
                use: [
                    { loader: "file-loader" },
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
        new webpack.optimize.UglifyJsPlugin(),
        new webpack.ProvidePlugin({
            riot: "riot",
        }),
        new HtmlWebpackPlugin({
            title: "まっぴー (@mpyw) 被害者の会",
            filename: "index.html",
        }),
    ],
};
