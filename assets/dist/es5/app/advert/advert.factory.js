'use strict';

advert.factory('Advertfactory', ['$http', '$q', function ($http, $q) {
  return {
    getVendors: function getVendors() {
      return $http.get(jsRoute.ajax_url, {
        params: {
          action: 'action_get_vendors'
        }
      });
    }
  };
}]);