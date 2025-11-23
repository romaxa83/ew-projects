import { configDefaults, defineConfig } from 'vitest/config';
import { getPlugins, getResolve } from './vite.shared-config';

export default defineConfig({
	plugins: getPlugins(),
	resolve: getResolve(),
	test: {
		pool: 'vmThreads',
		poolOptions: {
			vmThreads: {
				memoryLimit: '450MB',
				useAtomics: true,
			},
		},
		environment: 'jsdom',
		globals: true,
		passWithNoTests: true,
		coverage: {
			enabled: false,
			all: false,
			provider: 'istanbul',
			reporter: ['json-summary', 'html'],
			exclude: [
				...configDefaults.coverage.exclude,
				'**/__mocks__/**',
				'**/__specs__/**',
			],
		},
		reporters: ['default', 'junit'],
		outputFile: { junit: './coverage/test-reports/report.xml' },
		setupFiles: ['./vitest.setup.js'],
		disableConsoleIntercept: true,
	},
});
