const mix = require('laravel-mix');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MomentTimezoneDataPlugin = require('moment-timezone-data-webpack-plugin');
// const AntdDayjsWebpackPlugin = require('antd-dayjs-webpack-plugin')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
const webpack = require('webpack');

let chunkFilename = 'js/vue-bundles/[name].bundle.js';
if (mix.inProduction()) {
	chunkFilename = 'js/vue-bundles/[name].[contenthash].bundle.js';
}

mix.webpackConfig({
	output: {
		chunkFilename,
	},
	resolve: {
		modules: [path.resolve(__dirname), 'node_modules'],
		// Shortcuts to Vue app root path
		alias: {
			'@': path.resolve(__dirname, 'resources/js/'),
			'@components': path.resolve(__dirname, 'resources/js/components/'),
			'@ui': path.resolve(__dirname, 'resources/js/ui/'),
		},
	},
	plugins: [
		// https://github.com/jmblog/how-to-optimize-momentjs-with-webpack
		new webpack.ContextReplacementPlugin(/moment[\/\\]locale$/, /en/),
		new CleanWebpackPlugin({
			cleanOnceBeforeBuildPatterns: ['js/vue-bundles/*'],
		}),
		new MomentTimezoneDataPlugin({
			// matchZones: /Europe\/(Belfast|London|Paris|Athens)/,
			matchCountries: 'US',
			startYear: 2020,
			endYear: 2030,
		}),
		// new AntdDayjsWebpackPlugin(),
		// new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/)
	],
	externals: {
		Draggable: 'Draggable',
	},
});

mix.js('resources/js/app.js', 'public/js')
	.js('resources/js/pbx.js', 'public/js')
	.js('resources/js/sentry.js', 'public/js')
	.scripts(
		[
			'public/smartadmin/js/notifications/toastr/toastr.js',
			'public/smartadmin/js/notifications/sweetalert2/sweetalert2.bundle.js',
			'resources/js/vendor/tooltipster/tooltipster.bundle.min.js',
			'resources/js/vendor/tooltipster/tooltipster-scrollableTip.min.js',
		],
		'public/js/lib-bundle.js'
	)
	.js('resources/js/datatables-editor.js', 'public/js')
	.js('resources/js/order.js', 'public/js')
	.js('resources/js/flatpicker-plugins.js', 'public/js')
	.js('resources/js/customer.js', 'public/js')
	.js('resources/js/orders.list.js', 'public/js')
	.js('resources/js/clients.list.js', 'public/js')
	.js('resources/js/reports.audit.js', 'public/js')
	.js('resources/js/reports.efficiency.js', 'public/js')
	.js('resources/js/reports.sales.js', 'public/js')
	.js('resources/js/settings.intrastate.js', 'public/js')
	.js('resources/js/settings.interstate.js', 'public/js')
	.js('resources/js/multiselect.js', 'public/js')
	.js('resources/js/dispatch.js', 'public/js')
	.scripts(
		[
			'public/js/datatables-editor.js',
			'resources/js/vendor/datatables/buttons.server-side.js',
		],
		'public/js/datatables-editor-bundle.js'
	)
	.js('resources/js/dt/type_ColorPicker.js', 'public/js/dt')
	.js('resources/js/dt/helpers.js', 'public/js/dt')
	.sass('resources/sass/app.scss', 'public/css')
	.sass('resources/sass/flatpicker.scss', 'public/css')
	.sass('resources/sass/order.scss', 'public/css')
	.less(
		'resources/sass/vendor/multiselect/bootstrap-multiselect.less',
		'public/css'
	)
	.sass('resources/sass/datatables.scss', 'public/css')
	.sass('resources/sass/dispatch.scss', 'public/css')
	.sass('resources/sass/customer-order.scss', 'public/css');

if (mix.inProduction()) {
	mix.version();
}
