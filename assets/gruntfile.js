module.exports = function (grunt) {

  // Project configuration.
  grunt.initConfig({
    uglify: {
      options: {
        mangle: {
          reserved: ['_', '$']
        }
      },
      compress: {
        files: {
          'dist/min/advert.min.js': ['dist/es5/advert/advert.js'],
          'dist/min/advert.controller.min.js': [ 'dist/es5/advert/advert.controller.js'],
          'dist/min/advert.directive.min.js': [ 'dist/es5/advert/advert.directive.js'],
          'dist/min/advert.factory.min.js': [ 'dist/es5/advert/advert.factory.js'],
          'dist/min/advert.filter.min.js': [ 'dist/es5/advert/advert.filter.js'],

          'dist/min/advert.route.min.js': [ 'dist/es5/route/advert.route.js'],
          'dist/min/advert.directive.min.js': [ 'dist/es5/route/advert.directive.js'],
          'dist/min/advert.services.min.js': [ 'dist/es5/route/advert.service.js'],
        }
      }
    }
  });

  // Load the plugin that provides the "uglify" task.
  grunt.loadNpmTasks('grunt-contrib-uglify');

  // Default task(s).
  grunt.registerTask('default', ['uglify']);

};