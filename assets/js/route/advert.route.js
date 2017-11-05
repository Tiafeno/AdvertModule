'use strict'

/*
** route for Advert Controller
** @params global
** * jsRoute is wp_localize_script in advertcode.php
*/
advert.config(['$routeProvider', function( $routeProvider ) {
  $routeProvider
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
    .otherwise({
      redirectTo: '/advert'
    });
}]);

var routeAdvert = angular.module('routeAdvert', [ 'ngAlertify', 'ngSanitize', 'angularTrix' ]);
routeAdvert
  .factory('factoryServices', ( $location, $http, $q ) => {
    return {
      getAdvertDetails : function( id ) {
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
      getNonceField : function( nonce ) {
        if (_.isEmpty( nonce )) return false;
        return $http.get( jsRoute.ajax_url, {
          params : {
            fieldnonce: nonce,
            action: 'action_render_nonce'
          }
        });
      },
      xhrHttp : function( form ) {
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
  .service('$routeServices', function() {
    var self = this;
    var post_details = {};
    self.getDetails = () => { return post_details; };
    self.setDetails =  details => { return post_details = details; };
  })

routeAdvert
  .controller('AdvertEdit', function( 
    $scope, 
    $routeServices, 
    $routeParams, 
    $location, 
    alertify, 
    factoryServices 
  ) {
    var self = this;
    var Details = $routeServices.getDetails();
    $scope.products = {};
    $scope.product_id = parseInt( $routeParams.id );
    $scope.submitEditForm = function( isValid ) {
      if (!isValid) return false;
      var nonceField = 'update_product_nonce';
      factoryServices
        .getNonceField( nonceField )
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
          factoryServices.xhrHttp( formdata )
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
    };

    self.Initialize = function() {
      /* user have access to edit */
      if (_.isEmpty( Details )){
        factoryServices
          .getAdvertDetails( $scope.product_id )
          .then( results => {
            $scope.__set( results.data.data );
          })
          .catch()
      } else {
        $scope.__set( Details );
      }
    };
    self.Initialize();
  });

routeAdvert
  .controller( 'AdvertContactEmail', function( 
    $scope, 
    $location, 
    $routeServices, 
    $routeParams, 
    factoryServices, 
    alertify 
  ) {
    var post = $routeServices.getDetails();
    $scope.Error = null;
    $scope.mail = {}; /* sender, sendername, and message */
    $scope.product_id = parseInt( $routeParams.id );
    $scope.sendMail = function( isValid ) {
      if (!isValid) return;
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
    
    /* Initialize */
    $scope.Error =  _.isEmpty( post ) ? 'Post is empty' : null;
  });

/* Controller `AdvertDetails` */
routeAdvert
  .controller('AdvertDetails', function( 
    $scope, 
    $window, 
    $routeParams, 
    $location, 
    $routeServices, 
    factoryServices, 
    alertify 
  ) 
    {

      $scope.product_id = parseInt( $routeParams.id );
      $scope.refer = 0;
      $scope.showLoading = true;
      $scope.product_details = {};
      if (!isNaN($scope.product_id)) {
        /* get products post details */
        factoryServices
          .getAdvertDetails( $scope.product_id )
          .then(function( results ) {
            $scope.showLoading = false;
            var details = results.data;
            if (details.type) {
              $scope.product_details = details.data;
              $routeServices.setDetails( $scope.product_details );
              /* set image in slider */
              var pictures =  $scope.product_details.post.pictures;
              if (!_.isEmpty( pictures )) {
                jQuery( '.advert-slider' )
                  .find( '.advert-bg' )
                  .css({
                    'background' : '#151515 url( ' + pictures[ 0 ].full + ' )'
                  });
              }
            } else console.warn( details.data );
          })
          .catch(function() {});
      }

      $scope.EventClickSlide = function( idx ) {
        $scope.refer = parseInt( idx );
      };

      /* Event on click show phone number button */
      $scope.EventviewPhoneNumber = function( ev ) {
        var _hidephone = $scope.product_details.post.hidephone;
        var _phone = $scope.product_details.post.phone;
        var __elementPhone = angular.element( numberView );
        var _hide = parseInt( _hidephone );
        if (!_hide) {
          __elementPhone.html( _phone );
        } else {
          /* alert user */
          var content = "Numero de telephone n'est pas disponible";
          alertify.alert(content, ev => {
            ev.preventDefault();
          });
        }
      };

      /* run on click delete this product */
      $scope.EventdeletePost = function( ev ) {
        var _message = "Voulez vous vraiment supprimer cette annonce";
        var formVerify = new FormData();
        var formdata = new FormData();

        alertify
          .okBtn("Oui")
          .cancelBtn("Non")
          .confirm( _message , ev => { /* ok */
            ev.preventDefault();

            formVerify.append('action', 'action_edit_post_verify');
            formVerify.append('post_id', $scope.product_id);
            factoryServices
              .xhrHttp( formVerify )
              .then( results => {
                if (results.data.authorized) {
                  formdata.append('action', 'action_delete_product');
                  formdata.append('post_id', $scope.product_id);
                  factoryServices
                    .xhrHttp( formdata )
                    .then( results => {
                      var resp = results.data;
                      if (resp.type) alertify.success( 'Advert delete with success' );
                      $window.setTimeout(() => {
                        $scope.$apply(() => {
                          $location.path( '/advert' );
                        });
                      }, 2500);
                      
                    },  errno => { console.warn( errno ); return; });
                } else {
                  alertify.alert( results.data.error, ev => { ev.preventDefault(); });
                  return;
                }
              })
          }, ev => { /* cancel */
              ev.preventDefault();

          });
      };
  })
  .directive('advertslider', ( $parse ) => {
    return {
      restrict: 'A', /* Attribut */
      scope: true,
      link: (scope, element, attrs) => {
         element
           .bind('click', (e) => {
              var refer = scope.$eval( attrs.pictureRefer );
              var currentSlide = scope.product_details.post.pictures[ refer ];
              /* $parse method, this allows parameters to be passed */
              var invoker = $parse(attrs.advertsClick);
              invoker(scope, { idx: refer.toString() });
              jQuery( '.advert-bg' ).css({
                'background': '#151515 url(' + currentSlide.full + ')'
              });
            });
      }
    }
  })
  .directive('editadvert', ( $location, factoryServices, alertify ) => {
    return {
      restrict: 'A', /* Attribut */
      scope: true,
      link: ( scope, element, attrs ) => {
        element
          .bind('click', e => {
            var formEditVerify = new FormData();
            formEditVerify.append('action', 'action_edit_post_verify');
            formEditVerify.append('post_id', scope.product_id);
      
            factoryServices.xhrHttp( formEditVerify )
              .then( results => {
                var resp = results.data; /* { 'authorized' : true } */
                if (resp.authorized) {
                  $location.path( '/advert/' + scope.product_id + '/edit' );
                } else {
                  /* user don't have access to edit this post */
                  alertify.alert(resp.error, ev => {
                    ev.preventDefault();
                  });
                }
              }, errno => {
                
              });
          })
      }
    }
  })
  .directive('contactAdvertiser', function($location, factoryServices) {
    return {
      restrict: 'A', /* Attribut */
      scope: true,
      link: (scope, element, attrs) => {
        element
          .bind('click', e => {
            scope.$apply(() => {
              $location.path( '/advert/' + scope.product_id + '/contact' );
            });
          })
      }
    }
  })
  .directive('zoombg', ( $window ) => {
    return {
      link: (scope, element, attrs) => {
        element.bind('click', e => {
          var _pts = scope.product_details.post.pictures;
          var strWindowFeatures = "menubar=yes, location=yes, resizable=yes, scrollbars=yes, status=yes";
          if (!_.isEmpty( _pts )) {
            var windowObjectReference = $window.open(_pts[ scope.refer ].full, scope.product_details.post.post_title, strWindowFeatures);
          }
        });
      }
    }
  })
  .filter('moment', function() {
    return  input => {
      var postDate = input;
      var Madagascar = moment( postDate );
      Madagascar.tz( 'Europe/Kirov' );
      moment.locale('fr');
      return Madagascar.startOf( 'day' ).fromNow() + ', le ' + Madagascar.format('Do MMMM YYYY, h:mm ');
    }
  })
