// Require path.
const path = require('path');

// ProgressBar
const ProgressBarPlugin = require('progress-bar-webpack-plugin');
const chalk = require('chalk');

const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries');


var config = {
    // TODO: Add common Configuration
	// Setup a loader to transpile down the latest and great JavaScript so older browsers
	// can understand it.
	module: {
		rules: [
			{
				// Look for any .js files.
				test: /\.js$/,
				// Exclude the node_modules folder.
				exclude: /node_modules/,
				// Use babel loader to transpile the JS files.
				// loader: 'babel-loader'
				use: {
					loader: 'babel-loader',
					options: {
						// plugins: ['lodash'],
						// presets: ['@wordpress/default'] use for creating gutenberg components you need to "npm install @wordpress/babel-preset-default --save-dev"
						presets: ['@babel/preset-env']
					}
				}
			},
			{
				test: /\.scss$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: 'css/[name].min.css',
						}
					},
					{
						loader: 'extract-loader'
					},
					{
						loader: 'css-loader?-url'
					},
					{
						loader: 'postcss-loader',
						options: {
							plugins: () => [require('autoprefixer')]
						}
					},
					{
						loader: 'sass-loader',
						options: {
							sassOptions: {
								sourceMap: true,
								outputStyle: 'compressed',
							},
						}
					}
				]
			}
		]
	},

	plugins: [
		new FixStyleOnlyEntriesPlugin(),
		new ProgressBarPlugin({
			format: 'build [:bar] ' + chalk.green.bold(':percent') + ' (:elapsed seconds)',
			clear: false
		})
	],


};

var BackEnd = Object.assign({}, config, {
	entry: {
		// Scripts should be separated by array
		// This will compile into one file if is not
		'app': ['./src/js/app.js'],
		// Styles should be separated by comma
		'styles': [
			'./src/scss/app.scss',
 		]
	},

	output: {
		// [name] allows for the entry object keys to be used as file names.
		filename: 'js/[name].min.js',
		// Specify the path to the JS files.
		path: path.resolve(__dirname, 'resources')
	},
	
	resolve: {
		extensions: ['*', '.js', '.json']
	},
});

module.exports =  [
	BackEnd,
];