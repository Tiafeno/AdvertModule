module.exports = function (grunt) {

  // Project configuration.
  grunt.initConfig({
    uglify: {
      options: {
        mangle: {
          reserved: [
            '_', '$', 
            'jQuery','$routeProvider','$scope',
            '$location', '$window', '$http', '$q', "$routeParams"
          ]
        }
      },
      compress: {
        files: {
          'dist/app/advert.min.js': ['dist/es5/app/advert/advert.js'],
          'dist/app/advert.controller.min.js': [ 'dist/es5/app/advert/advert.controller.js'],
          'dist/app/advert.directive.min.js': [ 'dist/es5/app/advert/advert.directive.js'],
          'dist/app/advert.factory.min.js': [ 'dist/es5/app/advert/advert.factory.js'],
          'dist/app/advert.filter.min.js': [ 'dist/es5/app/advert/advert.filter.js'],

          'dist/app/addform.min.js': [ 'dist/es5/app/addform/addform.js'],
          'dist/app/addform.controller.min.js': [ 'dist/es5/app/addform/addform.controller.js'],
          'dist/app/addform.directive.min.js': [ 'dist/es5/app/addform/addform.directive.js'],
          'dist/app/addform.factory.min.js': [ 'dist/es5/app/addform/addform.factory.js'],

          'dist/app/dashboard.min.js': [ 'dist/es5/app/dashboard/dashboard.js'],
          'dist/app/dashboard.controller.min.js': [ 'dist/es5/app/dashboard/dashboard.controller.js'],
          'dist/app/dashboard.controller.min.js': [ 'dist/es5/app/dashboard/dashboard.controller.js'],
          'dist/app/dashboard.factory.min.js': [ 'dist/es5/app/dashboard/dashboard.factory.js'],

          'dist/app/login.advert.min.js': [ 'dist/es5/app/login/login.advert.js'],
          
          'dist/app/register.min.js': [ 'dist/es5/app/register/register.js'],
          'dist/app/register.controller.min.js': [ 'dist/es5/app/register/register.controller.js'],
          'dist/app/register.controller.min.js': [ 'dist/es5/app/register/register.controller.js'],
          'dist/app/register.factory.min.js': [ 'dist/es5/app/register/register.factory.js'],

          'dist/route/advert.route.min.js': [ 'dist/es5/route/advert.route.js'],
          'dist/route/advert.route.directive.min.js': [ 'dist/es5/route/advert.route.directive.js'],
          'dist/route/advert.route.services.min.js': [ 'dist/es5/route/advert.route.services.js'],
          'dist/route/advert.route.premium.min.js': [ 'dist/es5/route/advert.route.premium.js'],

          'dist/route/dashboard.route.min.js': [ 'dist/es5/route/dashboard.route.js'],
        }
      }
    }
  });

  // Load the plugin that provides the "uglify" task.
  grunt.loadNpmTasks('grunt-contrib-uglify');

  // Default task(s).
  grunt.registerTask('default', ['uglify']);

};