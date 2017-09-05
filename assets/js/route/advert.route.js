'use strict'

advert.config(['$routeProvider', function($routeProvider) {
  $routeProvider
    .when('/advert', {
      templateUrl : jsRoute.partials_uri + 'advert-lists.html',
      controller: 'AdvertListsController'
    })
    .otherwise({
      redirectTo: '/advert'
    });
}]);

var routeAdvert = angular.module('routeAdvert', []);
routeAdvert.controller('AdvertListsController', function( $scope ) {

});