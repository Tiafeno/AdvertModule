'use strict'

/*
** route for Advert Controller
** @params global
** * jsRoute is wp_localize_script in advertcode.php
*/
advert.config(['$routeProvider', function( $routeProvider ) {
  $routeProvider
    .when('/shop', {
      templateUrl : jsRoute.partials_uri + 'shop-list.html',
      controller: 'ShopController'
    })
    .when('/advert', {
      templateUrl : jsRoute.partials_uri + 'advert-lists.html',
      controller: 'AdvertController'
    })
    .when('/advert/:id', {
      templateUrl : jsRoute.partials_uri + 'advert-details.html',
      controller : 'AdvertDetails'
    })
    .when('/advert/:id/edit', {
      templateUrl : jsRoute.partials_uri + 'advert-edit.html',
      controller : 'AdvertEdit'
    })
    .when('/advert/:id/contact', {
      templateUrl : jsRoute.partials_uri + 'advert-contact.html',
      controller : 'AdvertContactEmail'
    })
    .when('/advert/:id/putforward', {
      templateUrl : jsRoute.partials_uri + 'advert-putforward.html',
      controller : 'putForward'
    })
    .when('/restricted', {
      templateUrl : jsRoute.partials_uri + 'restricted.html',
      controller : 'AdvertRestricted'
    })
    .when('/error/:code', {
      templateUrl : jsRoute.partials_uri + 'error.html',
      controller : 'AdvertError'
    })
    .otherwise({
      redirectTo: '/advert'
    });
}]);

var routeAdvert = angular.module('routeAdvert', [ 'ngAlertify', 'ngSanitize', 'angularTrix' ]); 

routeAdvert
  .controller('AdvertRestricted', ['$scope', function( $scope ) { }])
routeAdvert
  .controller('AdvertError', ['$scope', '$routeParams', function( $scope, $routeParams ) {
    $scope.errorCode = parseInt( $routeParams.code );
  }])
routeAdvert
  .controller('ShopController', ['$scope', '$routeServices', 'factoryServices', 
  function( $scope, $routeServices, factoryServices) {
    $scope.shops = [];
    if (_.isEmpty( $scope.shops )) {
      var formShop = new FormData();
      formShop.append('action', 'action_get_shops');
      factoryServices
        .xhrHttp( formShop )
        .then( results => {
          var response = results.data;
          if (response.return) {
            $scope.shops = _.union( response.results );
          }
        }, error => {});
    }
  }])

routeAdvert
  .controller('AdvertEdit', ['$scope', '$routeServices', '$routeParams', '$location', 'alertify', 'factoryServices', 
  function( $scope, $routeServices, $routeParams, $location, alertify, factoryServices ) {
    var self = this;
    var Details = $routeServices.getDetails();
    $scope.showLoading = true;
    $scope.products = {};
    $scope.product_id = parseInt( $routeParams.id );
    $scope.submitEditForm = function( isValid ) {
      if (!isValid) return false;
      var nonceField = 'update_product_nonce';
      factoryServices
        .getNonce( nonceField )
        .then( results => {
          var formdata = new FormData();
          var nonce = results.data.nonce;
          formdata.append('action', 'action_update_product');
          formdata.append('inputTitle', $scope.products.post_title);
          formdata.append('inputContent', $scope.products.post_content);
          formdata.append('inputState', $scope.products.state);
          formdata.append('inputAdress', $scope.products.adress);
          formdata.append('inputPhone', $scope.products.phone);
          formdata.append('post_id', $scope.products.ID);
          if (_.isEmpty( nonce )) { console.warn( 'Nonce variable is empty' ); return false; }
          formdata.append('inputNonce', nonce);
          factoryServices
            .xhrHttp( formdata )
            .then( () => {
              alertify.success("Advert update with success");
            },  errno => {
              alertify.error( errno );
            });
        });
    };

    $scope.__set = function( _data ) {
      if (_data.attributs == undefined) console.warn( 'Property `attributs` is undefined' );
      $scope.products[ 'attributs' ] = _data.attributs;
      _.each( _data.post, (value, key) => {
        $scope.products[ key ] =  value;
      });
      $scope.showLoading = false;
    };

    self.Initialize = function() {
      /* verify if user hava access to edit */
      if ($routeServices.isAuthorize()) {
        /* user have access to edit */
        if (_.isEmpty( Details )){
          factoryServices
            .getProduct( $scope.product_id )
            .then( results => {
              $scope.__set( results.data.data );
            })
            .catch()
        } else {
          $scope.__set( Details );
        }
      } else {
        var formEditVerify = new FormData();
        formEditVerify.append('action', 'action_edit_post_verify');
        formEditVerify.append('post_id', $scope.product_id);
        factoryServices.xhrHttp( formEditVerify )
        .then( results => {
          var resp = results.data; /* { 'authorized' : true } */
          if (resp.authorized) {
            $routeServices.authorizeAccess();
            self.Initialize();
          } else {
            $routeServices.deniedAccess();
            $location.path( '/restricted' );
          }
        }, errno => {
          console.warn( errno );
        });
      }
      
    };
    self.Initialize();
  }]);

routeAdvert
  .controller( 'AdvertContactEmail', [
    '$scope',
    '$location',
    '$routeServices',
    '$routeParams',
    'factoryServices',
    'alertify', function( 
    $scope, 
    $location, 
    $routeServices, 
    $routeParams, 
    factoryServices, 
    alertify 
  ) {
    $scope.Error = null;
    $scope.mail = {}; /* sender, sendername, and message */
    $scope.product_id = parseInt( $routeParams.id );
    $scope.product_details = $routeServices.getDetails();

    if (isNaN( $scope.product_id)) $scope.Error = 'Id parameter isn\'t int variable type' ;

    $scope.sendMail = function( isValid ) {
      if ( ! isValid) return;
      var mailerForm = new FormData()
      mailerForm.append('action', 'action_send_mail');
      mailerForm.append('post_id', $scope.product_id);
      mailerForm.append('senderName', $scope.mail.senderName);
      mailerForm.append('sender', $scope.mail.sender);
      mailerForm.append('message', $scope.mail.message);
      factoryServices
        .xhrHttp( mailerForm )
        .then( results => {
          var response = results.data;
          if (results.status != undefined && results.status == 200) {
            if (response.send) {
              $scope.mail = {};
              alertify.alert(response.data, ev => {
                ev.preventDefault();
                $scope.$apply(() => {
                  $location.path('/advert/' + $scope.product_id);
                });
              });
            } else alertify.alert( response.error, ev => {
              ev.preventDefault();
            })
          }
        }, errno => { console.warn( errno ); })
    };
    
    if ( ! _.isNull( $scope.Error )) return;
    /* Initialize */
    if (_.isEmpty($scope.product_details)) {
      factoryServices
        .getProduct( $scope.product_id )
        .then(results => {
          var details = results.data;
          if (details.type) {
            $scope.product_details = details.data;
            $routeServices.setDetails( $scope.product_details );
          } else console.warn( details.data );
        });
    }
  }]);

/* Controller `AdvertDetails` */
routeAdvert
  .controller('AdvertDetails',
  ['$scope','$window','$routeParams','$location','$routeServices', '$shopServices', 'factoryServices','alertify',
  function( $scope, $window, $routeParams, $location, $routeServices, $shopServices, factoryServices, alertify 
  ) 
    {
      /* Verification authorization */
      var AuthorizationFn = () => {
        var OthForm = new FormData();
        OthForm.append('action', 'action_edit_post_verify');
        OthForm.append('post_id', $scope.product_id);
        factoryServices
          .xhrHttp( OthForm )
          .then( results => {
            if (results.data.authorized) {
              $routeServices.authorizeAccess();
            } else $routeServices.deniedAccess( results.data.error );
          });
      };
      AuthorizationFn();

      $scope.product_id = parseInt( $routeParams.id );
      $scope.refer = 0;
      $scope.showLoading = true;
      $scope.product_details = {};
      if ( ! isNaN($scope.product_id)) {
        /* get products post details */
        factoryServices
          .getProduct( $scope.product_id )
          .then(function( results ) {
            $scope.showLoading = false;
            var details = results.data;
            if (details.type) {
              $scope.product_details = details.data;
              $shopServices.setShopFn( $scope.product_details.post.post_author );
              $routeServices.setDetails( $scope.product_details );
              /* set image in slider */
              var pictures =  $scope.product_details.post.pictures;
              if ( ! _.isEmpty( pictures )) {
                jQuery( '.advert-slider' )
                  .find( '.advert-bg' )
                  .css({ 'background' : '#151515 url( ' + pictures[ 0 ].full + ' )' });
              }
            } else console.warn( details.data );
          })
          .catch(function() {});
      } else { $location.path( '/advert' ); }

      $scope.EventClickSlide = function( idx ) {
        $scope.refer = parseInt( idx );
      };

      $scope.EventviewshopFn = function( ev ) {
         UIkit.modal( '#modalDialog' ).hide();
      };

      /* Run on click delete this product */
      $scope.EventdeletePost = function( ev ) {
        var _message = "Voulez vous vraiment supprimer cette annonce";
        var formdata = new FormData();
        alertify
          .okBtn("Oui")
          .cancelBtn("Non")
          .confirm( _message , ev => { /** Ok **/
            ev.preventDefault();
            if ( ! $routeServices.isAuthorize()) {
              alertify.alert( $routeServices.getDeniedMessage(), ev => { ev.preventDefault(); })
            } else {
              formdata.append('action', 'action_delete_product');
              formdata.append('post_id', $scope.product_id);
              factoryServices
                .xhrHttp( formdata )
                .then( results => {
                  var resp = results.data;
                  if (resp.type) alertify.success( 'Advert delete with success' );
                  adverts.posts = _.reject( adverts.posts, element => {
                    return element.ID == $scope.product_id;
                  });
                  $window.setTimeout(() => {
                    $scope.$apply(() => {
                      $location.path( '/advert' );
                    });
                  }, 2500);
                  
                },  errno => { console.warn( errno ); return; });
              }
          }, ev => { /** Cancel **/
              ev.preventDefault();

          });
      };
  }])
  
  .filter('moment', () => {
    return  input => {
      var postDate = input;
      var Madagascar = moment( postDate );
      Madagascar.tz( 'Europe/Kirov' );
      moment.locale('fr');
      return Madagascar.startOf( 'day' ).fromNow() + ', le ' + Madagascar.format('Do MMMM YYYY, h:mm ');
    }
  })
