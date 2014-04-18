module.exports = function(grunt) {
	require('load-grunt-tasks')(grunt);
	grunt.initConfig({
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
		watch: {
			sass_dev: {
				files: ['sass/*.scss'],
				tasks: ['compass:dev']
			}
		}
	});

	grunt.registerTask('build', [
		'compass:prod'
	]);

	grunt.registerTask('debug', [
	       'compass:dev',
		'watch:sass_dev'
	]);

	grunt.registerTask('default', ['build']);
};
