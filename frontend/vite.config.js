import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const themeAssets = path.resolve( __dirname, '../wordpress/wp-content/themes/kaju-blog/assets' );
const scssRoot = path.resolve( __dirname, 'src/scss' );

/**
 * base と outDir は常に対応させる（ズレると本番で 404）
 * origin: ブラウザがアクセスする Vite の URL（開発サーバの公開オリジン）
 */
export default defineConfig( {
	base: '/wp-content/themes/kaju-blog/assets/',
	css: {
		preprocessorOptions: {
			scss: {
				includePaths: [ scssRoot ],
			},
		},
	},
	build: {
		outDir: themeAssets,
		manifest: true,
		emptyOutDir: true,
		rollupOptions: {
			input: path.resolve( __dirname, 'src/js/main.js' ),
		},
	},
	server: {
		host: true,
		port: 5173,
		strictPort: true,
		cors: true,
		origin: 'http://localhost:5173',
	},
} );
