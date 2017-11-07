'use strict';

routeAdvert.factory('factoryServices', ['$location', '$http', '$q', function ($location, $http, $q) {
  return {
    getProduct: function getProduct(id) {
      var advert_post = parseInt(id);
      if (isNaN(advert_post)) {
        console.warn('Error Type: Variable `id` isn\'t int');
        return false;
      }
      return $http.get(jsRoute.ajax_url, {
        params: {
          post_id: id,
          action: 'action_get_advertdetails'
        }
      });
    },
    getNonce: function getNonce(nonce) {
      if (_.isEmpty(nonce)) return false;
      return $http.get(jsRoute.ajax_url, {
        params: {
          fieldnonce: nonce,
          action: 'action_render_nonce'
        }
      });
    },
    xhrHttp: function xhrHttp(form) {
      return $http({
        url: jsRoute.ajax_url,
        method: "POST",
        headers: { 'Content-Type': undefined },
        data: form
      });
    },
    go: function go(path) {
      $location.path(path);
    }
  };
}]).service('$routeServices', ['$http', '$window', function ($http, $window) {
  var self = this;
  var post_details = {};
  var authorizeEdit = false;
  var Error = [];
  var deniedMessage = null;

  self.getDeniedMessage = function () {
    return deniedMessage;
  };
  self.isAuthorize = function () {
    return authorizeEdit;
  };
  self.authorizeAccess = function () {
    deniedMessage = null;
    authorizeEdit = true;
  };
  self.deniedAccess = function (errorMessage) {
    deniedMessage = errorMessage;
    authorizeEdit = false;
  };

  self.getDetails = function () {
    return post_details;
  };
  self.setDetails = function (details) {
    return post_details = details;
  };

  self.getErrors = function () {
    $http.get(jsRoute.schema + 'errorcode.json').then(function (response) {
      Error = _.union(response.data);
    }, function () {
      $widows.setTimeout(function () {
        self.getErrors();
      }, 1500);
    });
  };
  self.getErrors();
}]);