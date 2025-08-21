const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const fs = require('fs');

const config = {
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env', '@babel/preset-react']
                    }
                }
            },
            {
                test: /\.(scss|css)$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    {
                        loader: 'sass-loader',
                        options: {
                          // Explicitly tell sass-loader to use Dart Sass
                          implementation: require('sass'),
                          api: 'modern', // <— This ensures modern API usage
                        }
                      }
                ],
            }
        ]
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    externals: {
        react: 'React',
        'react-dom': 'ReactDOM',
        '@wordpress/element': 'wp.element',
        '@wordpress/components': 'wp.components',
        '@wordpress/i18n': 'wp.i18n'
    }
};

module.exports = (env, argv) => {
    if (argv.env.folder) {

        const folder = argv.env.folder;

        return {
            ...config,
            entry:{
                [folder]:`./assets/src/${folder}/index.js`
            },
            output: {
                filename: `[name].js`,
                path: path.resolve(__dirname, `assets/dist/${folder}`)
            },
            plugins: [
                new MiniCssExtractPlugin({
                    filename: `../${folder}/${folder}.css`
                })
            ],
        }
    }

}; 