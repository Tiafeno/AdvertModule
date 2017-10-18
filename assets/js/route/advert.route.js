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

var routeAdvert = angular.module('routeAdvert', [ 'ngAlertify', 'ngSanitize', 'angularTrix' ]);
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
  .controller('AdvertEdit', function( $scope, $routeServices, $routeParams, alertify, factoryServices ) {
    var self = this;
    var Details = $routeServices.getDetails();
    $scope.tinymceOptions = {
      plugins: 'table',
      toolbar: 'undo redo | bold italic | alignleft aligncenter alignright'
    };

    $scope.products = {};
    $scope.products_id = parseInt( $routeParams.id );
    $scope.submitEditForm = function( isValid ) {
      if (!isValid) return false;
      var nonceField = 'update_product_nonce';
      factoryServices.getNonceField( nonceField )
        .then(function( results ) {
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
            .then( function successCallback() {
              alertify.success("Advert update with success");
            }, function errorCallback( errno ) {
              alertify.error( errno );
            });
        });
    };

    self.__set = function( _data ) {
      if (_data.attributs == undefined) console.warn( 'Property `attributs` is undefined' );
      $scope.products[ 'attributs' ] = _data.attributs;
      _.each( _data.post, function(value, key) {
        $scope.products[ key ] =  value;
      });
    };

    self.Initialize = function() {
      console.warn( $scope.products );
      if (_.isEmpty( Details )){
        factoryServices.getAdvertDetails( $scope.products_id )
          .then(function( results ) {
            var details = results.data;
            self.__set( details.data );
          })
          .catch()
      } else {
        self.__set( Details );
      }
    };
    self.Initialize();
  });

routeAdvert
  .controller('AdvertDetails', function( $scope, $window, $routeParams, $location, $routeServices, factoryServices, alertify ) {
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
            var formdata = new FormData();
            formdata.append('action', 'action_delete_product');
            formdata.append('post_id', $scope.product_id);
            factoryServices.xhrHttp( formdata )
              .then(function successCallback( results ) {
                var resp = results.data;
                if (resp.type) alertify.success( 'Advert delete with success' );
                $window.setTimeout(function() {
                  $scope.go( '/' );
                }, 3000);
              }, function errorCallback( errno ) {});
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
//   .config(['$provide', function ($provide) {
//     $provide.decorator('taOptions', ['$delegate', function (taOptions) {
//         taOptions.forceTextAngularSanitize = true;
//         taOptions.keyMappings = [];
//         taOptions.toolbar = [
//             ['h1', 'h2', 'h3', 'p', 'pre', 'quote'],
//             ['bold', 'italics', 'underline', 'ul', 'ol', 'redo', 'undo', 'clear'],
//             ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
//             ['html']
//         ];
//         taOptions.classes = {
//             focussed: '',
//             toolbar: 'ta-toolbar',
//             toolbarGroup: 'ta-button-group',
//             toolbarButton: '',
//             toolbarButtonActive: 'active',
//             disabled: 'disabled',
//             textEditor: 'ta-text-editor',
//             htmlEditor: 'md-input'
//         };
//         return taOptions; // whatever you return will be the taOptions
//     }]);
//     $provide.decorator('taTools', ['$delegate', function (taTools) {
//         taTools.h1.display = '<md-button aria-label="Heading 1">H1</md-button>';
//         taTools.h2.display = '<md-button aria-label="Heading 2">H2</md-button>';
//         taTools.h3.display = '<md-button aria-label="Heading 3">H3</md-button>';
//         taTools.p.display = '<md-button aria-label="Paragraph">P</md-button>';
//         taTools.pre.display = '<md-button aria-label="Pre">pre</md-button>';
//         taTools.quote.display = '<md-button class="md-icon-button" aria-label="Quote"><md-icon md-font-set="material-icons">format_quote</md-icon></md-button>';
//         taTools.bold.display = '<md-button class="md-icon-button" aria-label="Bold"><md-icon md-font-set="material-icons">format_bold</md-icon></md-button>';
//         taTools.italics.display = '<md-button class="md-icon-button" aria-label="Italic"><md-icon md-font-set="material-icons">format_italic</md-icon></md-button>';
//         taTools.underline.display = '<md-button class="md-icon-button" aria-label="Underline"><md-icon md-font-set="material-icons">format_underlined</md-icon></md-button>';
//         taTools.ul.display = '<md-button class="md-icon-button" aria-label="Buletted list"><md-icon md-font-set="material-icons">format_list_bulleted</md-icon></md-button>';
//         taTools.ol.display = '<md-button class="md-icon-button" aria-label="Numbered list"><md-icon md-font-set="material-icons">format_list_numbered</md-icon></md-button>';
//         taTools.undo.display = '<md-button class="md-icon-button" aria-label="Undo"><md-icon md-font-set="material-icons">undo</md-icon></md-button>';
//         taTools.redo.display = '<md-button class="md-icon-button" aria-label="Redo"><md-icon md-font-set="material-icons">redo</md-icon></md-button>';
//         taTools.justifyLeft.display = '<md-button class="md-icon-button" aria-label="Align left"><md-icon md-font-set="material-icons">format_align_left</md-icon></md-button>';
//         taTools.justifyRight.display = '<md-button class="md-icon-button" aria-label="Align right"><md-icon md-font-set="material-icons">format_align_right</md-icon></md-button>';
//         taTools.justifyCenter.display = '<md-button class="md-icon-button" aria-label="Align center"><md-icon md-font-set="material-icons">format_align_center</md-icon></md-button>';
//         taTools.justifyFull.display = '<md-button class="md-icon-button" aria-label="Justify"><md-icon md-font-set="material-icons">format_align_justify</md-icon></md-button>';
//         taTools.clear.display = '<md-button class="md-icon-button" aria-label="Clear formatting"><md-icon md-font-set="material-icons">format_clear</md-icon></md-button>';
//         taTools.html.display = '<md-button class="md-icon-button" aria-label="Show HTML"><md-icon md-font-set="material-icons">code</md-icon></md-button>';
//         taTools.insertLink.display = '<md-button class="md-icon-button" aria-label="Insert link"><md-icon md-font-set="material-icons">insert_link</md-icon></md-button>';
//         taTools.insertImage.display = '<md-button class="md-icon-button" aria-label="Insert photo"><md-icon md-font-set="material-icons">insert_photo</md-icon></md-button>';
//         return taTools;
//     }]);
// }]);
