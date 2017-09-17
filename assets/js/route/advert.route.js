'use strict'

/*
** route for Advert Controller
** @params global
** * jsRoute is wp_localize_script in advertcode.php
*/
advert.config(['$routeProvider', function( $routeProvider ) {
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

var routeAdvert = angular.module('routeAdvert', [ ]);
routeAdvert.factory('factoryServices', function($http, $q) {
  return {
    getAdvertDetails : function( id ) {
      var advert_post = parseInt( id );
      if (isNaN(advert_post)) {
        console.warn( 'Error Type: Variable `id` isn\'t int' );
        return false;
      }
      return $http.get(jsRoute.ajax_url, {
        params : {
          post_id: id,
          action: 'action_get_advertdetails'
        }
      });
    }
  }
});

routeAdvert.controller('AdvertListsController', function( $scope,) {
});
routeAdvert.controller('AdvertDetails', function( $scope, factoryServices, $routeParams ) {
  $scope.product_id = parseInt( $routeParams.id );
  $scope.product_details = {};
  if (!isNaN($scope.product_id)) {
    factoryServices.getAdvertDetails( $scope.product_id )
      .then(function( results ){
        var details = results.data;
        if (details.type) {
          $scope.product_details = details.data;
          console.log( details );
        }
      })
      .catch(function() {});
  }
});