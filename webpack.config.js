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
            'user-dashboard-styles': './assets/css/user-dashboard.css'
        },
        
        devServer: {
            static: {
                directory: path.join(__dirname, 'dist'),
            },
            compress: true,
            port: 9000,
            hot: true,
            open: false,
            watchFiles: ['assets/**/*', 'templates/**/*'],
            client: {
                overlay: {
                    errors: true,
                    warnings: false,
                },
            },
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
                            drop_console: isProduction,
                            drop_debugger: isProduction,
                            pure_funcs: isProduction ? ['console.log', 'console.info', 'console.debug'] : [],
                            dead_code: true,
                            unused: true
                        },
                        mangle: isProduction,
                        format: {
                            comments: false
                        }
                    },
                    extractComments: false
                }),
                new CssMinimizerPlugin({
                    minimizerOptions: {
                        preset: [
                            'default',
                            {
                                discardComments: { removeAll: true },
                                normalizeWhitespace: true,
                                colormin: true,
                                convertValues: true,
                                discardDuplicates: true,
                                discardEmpty: true,
                                mergeRules: true,
                                minifySelectors: true,
                                autoprefixer: { add: true },
                                calc: true,
                                reduceIdents: true
                            }
                        ]
                    }
                })
            ],
            splitChunks: {
                chunks: 'all',
                minSize: 20000,
                maxSize: 244000,
                cacheGroups: {
                    vendor: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendors',
                        chunks: 'all',
                        priority: 10,
                        reuseExistingChunk: true
                    },
                    styles: {
                        name: 'styles',
                        test: /\.css$/,
                        chunks: 'all',
                        enforce: true,
                        priority: 20,
                        reuseExistingChunk: true
                    },
                    common: {
                        name: 'common',
                        minChunks: 2,
                        chunks: 'all',
                        priority: 5,
                        reuseExistingChunk: true
                    }
                }
            },
            usedExports: true,
            sideEffects: false
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
