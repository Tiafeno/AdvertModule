'use strict'

advert.factory('Advertfactory', ['$http', '$q', function( $http, $q ) {
  return {
    getVendors : function() {
      return $http.get(jsRoute.ajax_url, {
        params : {
          action : 'action_get_vendors'
        }
      });
    }
  }
}])