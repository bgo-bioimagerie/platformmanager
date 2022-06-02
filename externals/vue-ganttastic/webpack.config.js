// Generated using webpack-cli https://github.com/webpack/webpack-cli

//const path = require('path');
import path from "path";
import {fileURLToPath} from "url";
const __dirname = path.dirname(fileURLToPath(import.meta.url))

const isProduction = process.env.NODE_ENV == 'production';


export default  {
    mode: isProduction ? 'production': 'development',
    entry: './src/index.js',
    output: {
        path: path.resolve(__dirname, 'dist'),
        library: "pfmGant"
    },
    plugins: [

    ],
    module: {
        rules: [
            {
                test: /\.(eot|svg|ttf|woff|woff2|png|jpg|gif)$/i,
                type: 'asset',
            },
            { test: /\.m?js/, resolve: {
                fullySpecified: false
              }
            }


        ],
    },
};

/*
module.exports = () => {
    if (isProduction) {
        config.mode = 'production';
        
        
    } else {
        config.mode = 'development';
    }
    return config;
};
*/