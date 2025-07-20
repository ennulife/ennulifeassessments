const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

module.exports = (env, argv) => {
    const isProduction = argv.mode === 'production';
    
    return {
        entry: {
            'user-dashboard': './assets/js/user-dashboard.js',
            'biomarker-admin': './assets/js/biomarker-admin.js',
            'admin-scores-enhanced': './assets/js/admin-scores-enhanced.js',
            'assessment-details': './assets/js/assessment-details.js',
            'health-goals-manager': './assets/js/health-goals-manager.js',
            'ennu-admin-enhanced': './assets/js/ennu-admin-enhanced.js',
            'main-styles': './assets/css/ennu-life-plugin.css',
            'admin-styles': './assets/css/ennu-admin-enhanced.css',
            'biomarker-styles': './assets/css/biomarker-admin.css'
        },
        
        output: {
            path: path.resolve(__dirname, 'dist'),
            filename: 'js/[name].min.js',
            clean: true
        },
        
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env']
                        }
                    }
                },
                {
                    test: /\.css$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        'css-loader',
                        'postcss-loader'
                    ]
                },
                {
                    test: /\.(png|jpg|jpeg|gif|svg)$/,
                    type: 'asset/resource',
                    generator: {
                        filename: 'images/[name][ext]'
                    }
                },
                {
                    test: /\.(woff|woff2|eot|ttf|otf)$/,
                    type: 'asset/resource',
                    generator: {
                        filename: 'fonts/[name][ext]'
                    }
                }
            ]
        },
        
        plugins: [
            new MiniCssExtractPlugin({
                filename: 'css/[name].min.css'
            })
        ],
        
        optimization: {
            minimize: isProduction,
            minimizer: [
                new TerserPlugin({
                    terserOptions: {
                        compress: {
                            drop_console: isProduction
                        }
                    }
                }),
                new CssMinimizerPlugin()
            ],
            splitChunks: {
                chunks: 'all',
                cacheGroups: {
                    vendor: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendors',
                        chunks: 'all'
                    }
                }
            }
        },
        
        devtool: isProduction ? 'source-map' : 'eval-source-map',
        
        resolve: {
            extensions: ['.js', '.json'],
            alias: {
                '@': path.resolve(__dirname, 'assets')
            }
        }
    };
};
