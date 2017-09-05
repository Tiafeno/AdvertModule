'use strict'

advert.controller('AdvertController', function( $scope ){
  $scope.posts = _.union(adverts.posts);
})
.config(function ($mdThemingProvider, $interpolateProvider) {
  $interpolateProvider.startSymbol( '[[' ).endSymbol( ']]' );

});