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

const validFoldersFilter=(folders)=>{
    if(folders.length > 0){
       return folders.filter(folder =>
            fs.existsSync(path.resolve(__dirname, `assets/src/${folder}/index.js`))
        );
    }

    console.warn("⚠️ empty folders");
    return 
}

module.exports = (env, argv) => {
    let validFolders=[];

    const editorFolders = [
        'editor',
        'core',
        'editorFields',
        'editorControls',
        'toolbars',
    ];

    const frontendFolders=[
        'frontend'
    ];

    if(env && env.type === 'editor'){
        validFolders = validFoldersFilter(editorFolders);
    }else if(env && env.type === 'frontend'){
        validFolders = validFoldersFilter(frontendFolders);
    }else{
        console.warn("⚠️ Comman not valid");
        return {};
    }
    
    if (validFolders.length === 0) {
        console.warn("⚠️  No valid folders found with index.js");
        return {};
    }

    // Return multiple configs (Webpack will build each)
    return validFolders.map(folder => makeConfig(folder));
};
