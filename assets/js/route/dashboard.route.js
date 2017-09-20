'use strict'

dashboard.config(['$routeProvider', function( $routeProvider ) {
  $routeProvider
    .when('/profil', {
      templateUrl : jsDashboard.partials_uri + 'dashboard.profil.html',
      controller: 'Dashboard_ProfilController'
    })
    .otherwise({
      redirectTo: '/profil'
    })
}]);

var routeDashboard = angular.module('routeDashboard', []);
routeDashboard.controller('Dashboard_ProfilCtrl', function( $scope ) {

});
routeDashboard.controller('Dashboard_EditPostCtrl', function( $scope ) {

});

/*
* this controller show all advert have current user 
* with dropdown edit, remove and update
*/
routeDashboard.controller('Dashboard_ListAdvertCtrl', function( $scope ) {

});