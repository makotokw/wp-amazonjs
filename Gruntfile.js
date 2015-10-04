module.exports = function(grunt) {
	require('load-grunt-tasks')(grunt);
	grunt.initConfig({
		makepot: {
			target: {
				options: {
					type: 'wp-plugin'
				}
			}
		},
		exec: {
			phpcs: {
				cmd: 'phpcs --standard=build/phpcs.xml --report-width=150 --colors -s *.php',
				exitCode: [0, 1]
			},
			msgmerge: {
				cmd: 'msgmerge --update ./languages/amazonjs-ja.po ./languages/amazonjs.pot --backup=off'
			},
			msgfmt: {
				cmd: 'msgfmt -o ./languages/amazonjs-ja.mo ./languages/amazonjs-ja.po'
			}
		},
		compass: {
			prod: {
				options: {
					basePath: 'sass',
					environment: 'production',
					force: true
				}
			},
			dev: {
				options: {
					basePath: 'sass',
					environment: 'development',
					force: true,
					trace: true
				}
			}
		},
		bower: {
			install: {
				options: {
					targetDir: './components',
					cleanTargetDir: true,
					layout: 'byType'
				}
			}
		},
		watch: {
			php: {
				files: ['*.php', 'lib/*.php'],
				tasks: ['exec:phpcs']
			},
			sass_dev: {
				files: ['sass/*.scss'],
				tasks: ['compass:dev']
			}
		}
	});

	grunt.registerTask('phpcs', [
		'exec:phpcs'
	]);

	grunt.registerTask('update-po', [
		'makepot',
		'exec:msgmerge'
	]);
	grunt.registerTask('update-mo', [
		'exec:msgfmt'
	]);

	grunt.registerTask('build', [
		'bower:install',
		'compass:prod'
	]);

	grunt.registerTask('debug', [
		'bower:install',
		'compass:dev',
		'exec:phpcs',
		'watch'
	]);

	grunt.registerTask('default', ['build']);
};
