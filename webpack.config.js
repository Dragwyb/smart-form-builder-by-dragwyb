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

const makeScssConfig = (scssFile) => ({
    entry: {
        [scssFile]: `./assets/sass/${scssFile}.scss`
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'assets/css'),
        clean: false
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css' // Automatically uses the entry name
        }),
        {
            apply: (compiler) => {
                // Use 'done' instead of 'afterEmit' to ensure Webpack is finished writing
                compiler.hooks.done.tap('DeleteJsArtifact', (stats) => {
                    const unwantedFile = path.resolve(
                        compiler.options.output.path,
                        `${scssFile}.js`
                    );

                    // Add a tiny delay to allow Windows to release the file lock
                    if (fs.existsSync(unwantedFile)) {
                        setTimeout(() => {
                            try {
                                fs.unlinkSync(unwantedFile);
                            } catch (e) {
                                // If it fails, just log it instead of crashing the build
                                console.warn(`Could not delete ${scssFile}.js: ${e.message}`);
                            }
                        }, 100);
                    }
                });
            }
        }
    ],
    module: {
        rules: [
            {
                test: /\.scss$/,
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
        extensions: ['.scss']
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

const validScssFilesFilter = (scssFiles) => {
    if (!scssFiles || scssFiles.length === 0) {
        console.warn("⚠️ No SCSS files were provided to Webpack.");
        return [];
    }

    const valid = scssFiles.filter(file =>
        fs.existsSync(path.resolve(__dirname, `assets/sass/${file}.scss`))
    );

    if (valid.length === 0) {
        console.warn(`⚠️ No valid SCSS files found in: [${scssFiles.join(', ')}]`);
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

    const scssFiles = [
        'editor-global',
        'form-frontend',
        'editor-preview'
    ]

    if (env && env.type === 'editor') {
        console.log("ℹ️  Running Webpack in *editor* mode...");
        validScssFiles = validScssFilesFilter(scssFiles);
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
    return [...validFolders.map(folder => makeConfig(folder)), ...validScssFiles.map(file => makeScssConfig(file))];
};
