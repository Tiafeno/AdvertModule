'use strict'

dashboard.config(['$routeProvider', function( $routeProvider ) {
  $routeProvider
    .when('/profil', {
      templateUrl: jsDashboard.partials_uri + 'dashboard.profil.html',
      controller: 'Dashboard_ProfilController'
    })
    .when('/profil/edit', {
      templateUrl: jsDashboard.partials_uri + 'dashboard.edit.html',
      controller: 'Dashboard_EditPostCtrl'
    })
    .when('/timeline', {
      templateUrl: jsDashboard.partials_uri + 'dashboard.timeline.html',
      controller: 'Dashboard_TimelineCtrl'
    })
    .otherwise({
      redirectTo: '/profil'
    })
}]);

var routeDashboard = angular.module('routeDashboard', []);
routeDashboard.factory('factoryRouteDashboard', function( $http, $q ) {
  return {
    getUser : function() {
      return jsDashboard._user;
    }
  };
});
routeDashboard.controller('Dashboard_ProfilCtrl', function( $scope, factoryRouteDashboard ) {
  $scope._user = factoryRouteDashboard.getUser();
  $scope.profil = {};
  $scope.EventSubmit = function( $event ) {

  };
});
routeDashboard.controller('Dashboard_EditPostCtrl', function( $scope ) {

});

/*
* This controller show all advert have current user 
* with dropdown edit, remove and update
*/
routeDashboard.controller('Dashboard_TimelineCtrl', function( $scope ) {

});