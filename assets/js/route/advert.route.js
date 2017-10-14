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
      }
    }
  });

routeAdvert
  .controller('AdvertListsController', function( $scope,) {
  });

routeAdvert
  .controller('AdvertDetails', function( $scope, factoryServices, alertify, $routeParams ) {
    $scope.product_id = parseInt( $routeParams.id );
    $scope.refer = 0;
    $scope.showLoading = true;
    $scope.product_details = {};
    if (!isNaN($scope.product_id)) {
      factoryServices.getAdvertDetails( $scope.product_id )
        .then(function( results ){
          $scope.showLoading = false;
          var details = results.data;
          if (details.type) {
            $scope.product_details = details.data;
            /* set image in slider */
            var pictures =  $scope.product_details.post.pictures;
            if (!_.isEmpty( pictures )) {
              jQuery( '.advert-slider' )
                .find( '.advert-bg' )
                .css({
                  'background' : '#151515 url( ' + pictures[ 0 ].full + ' )'
                });
            }
          }
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
      var elementPhone = angular.element( numberView );
      var test = parseInt( _hidephone );
      if (test) {
        elementPhone.html( _phone );
      } else {
        /* alert user */
        var content = "Numero de telephone n'est pas disponible";
        alertify.alert(content, function ( ev ) {
          // user clicked "ok"
          ev.preventDefault();
        });
      }
    }

    /* run on click delete this product */
    $scope.EventdeletePost = function( ev ) {
      var message = "Voulez vous vraiment supprimer cette annonce";
      alertify
        .okBtn("Oui")
        .cancelBtn("Non")
        .confirm( message , function (ev) {
            // ok
            ev.preventDefault();
        }, function(ev) {
            // cancel
            ev.preventDefault();

        });
    }
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
