module.exports = function(grunt) {
	grunt.initConfig({
		cssmin: {
			compress: {
				files: {
					'css/amazonjs.min.css': ['css/amazonjs.css']
				}
			}
		},
		uglify: {
			dist: {
				files: {
					'js/amazonjs.min.js': ['js/amazonjs.js']
				}
			}
		}
	});
	grunt.registerTask('default', ['cssmin', 'uglify']);
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
};