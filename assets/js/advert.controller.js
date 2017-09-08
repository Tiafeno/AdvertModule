'use strict'

advert.controller('AdvertController', function( $scope ){
  $scope.posts = _.union( adverts.posts );
})
.config(function ( $interpolateProvider ) {
  $interpolateProvider.startSymbol( '[[' ).endSymbol( ']]' );

});