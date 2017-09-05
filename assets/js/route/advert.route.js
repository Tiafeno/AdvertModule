'use strict'

/*
** route for Advert Controller
** @params global
** * jsRoute is wp_localize_script in advertcode.php
*/
advert.config(['$routeProvider', function($routeProvider) {
  $routeProvider
    .when('/advert', {
      templateUrl : jsRoute.partials_uri + 'advert-lists.html',
      controller: 'AdvertListsController'
    })
    .when('/advert/:id', {
      templateUrl : jsRoute.partials_uri + 'advert-details.html',
      controller : 'AdvertDetails'
    })
    .otherwise({
      redirectTo: '/advert'
    });
}]);

var routeAdvert = angular.module('routeAdvert', []);
routeAdvert.controller('AdvertListsController', function( $scope ) {

});
routeAdvert.controller('AdvertDetails', function( $scope, $routeParams ) {
  $scope.post_id = parseInt($routeParams.id);
  if (!isNaN($scope.post_id)) {
  }
})