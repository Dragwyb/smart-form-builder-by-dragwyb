const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const fs = require('fs');

const makeConfig = (folder) => ({
    entry: {
        [folder]: `./assets/src/${folder}/index.js`
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
                            implementation: require('sass'),
                            api: 'modern',
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
});

// 🔹 Default fallback config (safe dummy build)
const defaultConfig = {
    entry: {},
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'assets/dist')
    },
    plugins: [],
    module: { rules: [] },
};

const validFoldersFilter = (folders) => {
    if (!folders || folders.length === 0) {
        console.warn("⚠️ No folders were provided to Webpack.");
        return [];
    }

    const valid = folders.filter(folder =>
        fs.existsSync(path.resolve(__dirname, `assets/src/${folder}/index.js`))
    );

    if (valid.length === 0) {
        console.warn(`⚠️ No valid folders found with an index.js file in: [${folders.join(', ')}]`);
    } else {
        console.log(`✅ Building configs for: [${valid.join(', ')}]`);
    }

    return valid;
};

module.exports = (env, argv) => {
    let validFolders = [];

    const editorFolders = [
        'editor',
        'core',
        'editorFields',
        'editorControls',
        'toolbars',
    ];

    const frontendFolders = [
        'frontend'
    ];

    if (env && env.type === 'editor') {
        console.log("ℹ️  Running Webpack in *editor* mode...");
        validFolders = validFoldersFilter(editorFolders);
    } else if (env && env.type === 'frontend') {
        console.log("ℹ️  Running Webpack in *frontend* mode...");
        validFolders = validFoldersFilter(frontendFolders);
    } else {
        console.warn("⚠️ Invalid build type provided. Use `--env type=editor` or `--env type=frontend`.");
        return defaultConfig;
    }

    if (validFolders.length === 0) {
        console.warn("⚠️ Falling back to default Webpack config (no builds generated).");
        return defaultConfig;
    }

    // ✅ Return multiple configs (Webpack multi-compiler mode)
    return validFolders.map(folder => makeConfig(folder));
};
