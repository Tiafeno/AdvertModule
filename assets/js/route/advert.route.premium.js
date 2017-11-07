'use strict'

var Error = null
if (_.isUndefined( window.advert )) 
  console.warn( Error = 'Module advert is not define'); 
if (_.isUndefined( window.routeAdvert ))
  console.warn( Error = 'Module `routeAdvert` is not define' );

if (_.isNull( Error )) {
  routeAdvert
    .controller('putForward', ['$scope', '$routeParams', '$routeServices', 'factoryServices', function( 
      $scope, 
      $routeParams, 
      $routeServices, 
      factoryServices,  
    ) {
      
      
    }])
}