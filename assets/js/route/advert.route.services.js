'use strict'

routeAdvert
.factory('factoryServices', ( $location, $http, $q ) => {
  return {
    getProduct : id => {
      var advert_post = parseInt( id );
      if (isNaN( advert_post )) {
        console.warn( 'Error Type: Variable `id` isn\'t int' );
        return false;
      }
      return $http.get(jsRoute.ajax_url, {
        params : {
          post_id: id,
          action: 'action_get_advertdetails'
        }
      });
    },
    getNonce : nonce => {
      if (_.isEmpty( nonce )) return false;
      return $http.get( jsRoute.ajax_url, {
        params : {
          fieldnonce: nonce,
          action: 'action_render_nonce'
        }
      });
    },
    xhrHttp : form => {
      return $http({
        url: jsRoute.ajax_url,
        method: "POST",
        headers: { 'Content-Type': undefined },
        data: form
      });
    },
    go : path => {
      $location.path( path );
    }
  }
})
.service('$routeServices', function( $http, $window ) {
  var self = this;
  var post_details = {};
  var authorizeEdit = false;
  var Error = [];
  var deniedMessage = null;

  self.getDeniedMessage = () => { return deniedMessage; };
  self.isAuthorize = () => { return authorizeEdit; };
  self.authorizeAccess = () => { 
    deniedMessage = null;
    authorizeEdit = true; 
  };
  self.deniedAccess = ( errorMessage ) => {
    deniedMessage = errorMessage; 
    authorizeEdit = false; 
  };

  self.getDetails = () => { return post_details; };
  self.setDetails =  details => { return post_details = details; };

  self.getErrors = () => {
    $http.get(jsRoute.schema + 'errorcode.json')
      .then( response => {
        Error = _.union( response.data );
      }, () => { $widows.setTimeout( () => { self.getErrors(); }, 1500); })
  };
  self.getErrors();

})