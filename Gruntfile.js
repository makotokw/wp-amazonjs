module.exports = function(grunt) {
	grunt.initConfig({
		compass: {
			dist: {
				options: {
					basePath: 'sass',
					environment: 'production',
					force: true
				}
			}
		}
	});
	grunt.loadNpmTasks('grunt-contrib-compass');
	grunt.registerTask('default', ['compass']);
};
