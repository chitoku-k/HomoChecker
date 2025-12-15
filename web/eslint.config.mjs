import eslintJs from "@eslint/js";
import globals from "globals";

/** @type {import('eslint').Linter.FlatConfig[]} */
export default [
    eslintJs.configs.all,
    {
        ignores: [
            ".yarn",
            "dist",
        ],
    },
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
            },
        },
        files: [
            "**/*.{js,mjs}",
        ],
        rules: {
            "indent": [ "error", 4 ],
            "no-magic-numbers": "off",
            "no-ternary": "off",
            "no-undefined": "off",
            "one-var": "off",
            "quotes": [ "error", "double" ],
            "semi": "error",
            "sort-keys": "off",
            "sort-imports": [
                "warn",
                {
                    ignoreDeclarationSort: true,
                },
            ],
        },
    },
];
