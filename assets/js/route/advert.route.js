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
      controller: 'AdvertListsController'
    })
    .when('/advert/:id', {
      templateUrl : jsRoute.partials_uri + 'advert-details.html',
      controller : 'AdvertDetails'
    })
    .when('/advert/:id/edit', {
      templateUrl : jsRoute.partials_uri + 'advert-edit.html',
      controller : 'AdvertEdit'
    })
    .otherwise({
      redirectTo: '/advert'
    });
}]);

var routeAdvert = angular.module('routeAdvert', [ 'ngAlertify' ]);
routeAdvert
  .factory('factoryServices', function($http, $q) {
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
            name_nonce: nonce,
            action: 'action_get_nonce'
          }
        });
      }
    }
  })
  .service('$routeServices', function() {
    var self = this;
    var post_details = {};
    self.getDetails = function() {
      return post_details;
    };
    self.setDetails = function( details ) {
      return post_details = details;
    };
  })

routeAdvert
  .controller('AdvertListsController', function( $scope, $routeServices ) {

  });

routeAdvert
  .controller('AdvertEdit', function( $scope, $routeServices, $routeParams, factoryServices ) {
    $scope.products = $routeServices.getDetails();
    $scope.products_id = parseInt( $routeParams.id );
    $scope.submitEditForm = function( isValid ) {

    };
    this.Initialize = function() {
      if (_.isEmpty( $scope.products )){
        factoryServices.getAdvertDetails( $scope.products_id )
          .then(function( results ) {
            var details = results.data;
            $scope.products = details.data;
          })
          .catch()
      }
    };
    this.Initialize();
  });

routeAdvert
  .controller('AdvertDetails', function( $scope, $routeParams, $location, $routeServices, factoryServices, alertify ) {
    $scope.product_id = parseInt( $routeParams.id );
    $scope.refer = 0;
    $scope.showLoading = true;
    $scope.product_details = {};
    if (!isNaN($scope.product_id)) {
      /* get products post details */
      factoryServices.getAdvertDetails( $scope.product_id )
        .then(function( results ){
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

    $scope.go = function( path ) {
      $location.path( path );
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
        alertify.alert(content, function ( ev ) {
          ev.preventDefault();
        });
      }
    };

    /* run on click delete this product */
    $scope.EventdeletePost = function( ev ) {
      var _message = "Voulez vous vraiment supprimer cette annonce";
      alertify
        .okBtn("Oui")
        .cancelBtn("Non")
        .confirm( _message , function (ev) { /* ok */
            ev.preventDefault();
        }, function(ev) { /* cancel */
            ev.preventDefault();

        });
    };
  })
  .directive('advertslider', function( $parse ) {
    return {
      restrict: 'A',
      scope: true,
      link: function (scope, element, attrs) {
         element
           .bind('click', function (e) {
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
  .directive('zoombg', function( $window ) {
    return {
      link: function(scope, element, attrs) {
        element.bind('click', function(e) {
          var _pts = scope.product_details.post.pictures;
          var strWindowFeatures = "menubar=yes, location=yes, resizable=yes, scrollbars=yes, status=yes";
          if (!_.isEmpty( _pts )) {
            windowObjectReference = $window.open(_pts[ scope.refer ].full, scope.product_details.post.post_title, strWindowFeatures);
          }
        });
      }
    }
  })
  .filter('moment', function() {
    return function( input ) {
      var postDate = input;
      var Madagascar = moment( postDate );
      Madagascar.tz( 'Europe/Kirov' );
      moment.locale('fr');
      return Madagascar.startOf( 'day' ).fromNow() + ', le ' + Madagascar.format('Do MMMM YYYY, h:mm ');
    }
  })
