'use strict'

routeAdvert
.directive('advertslider', ['$parse', ( $parse ) => {
  return {
    restrict: 'A', /* Attribut */
    scope: true,
    link: (scope, element, attrs) => {
       element
         .bind('click', e => {
            var refer = scope.$eval( attrs.pictureRefer );
            var currentSlide = scope.product_details.post.pictures[ refer ];
            /* $parse method, this allows parameters to be passed */
            var invoker = $parse( attrs.advertsClick );
            invoker(scope, { idx: refer.toString() });
            jQuery( '.advert-bg' ).css({
              'background': '#151515 url(' + currentSlide.full + ')'
            });
          });
    }
  }
}])
.directive('editadvert', ['$location', '$routeServices', 'factoryServices', 'alertify', 
( $location, $routeServices, factoryServices, alertify ) => {
  return {
    restrict: 'A', /* Attribut */
    scope: true,
    link: ( scope, element, attrs ) => {
      element
        .bind('click', e => {
          if ( ! $routeServices.isAuthorize()) {
            /* user don't have access to edit this post */
            alertify.alert($routeServices.getDeniedMessage(), ev => {
              ev.preventDefault();
            });
          } else 
            $location.path( '/advert/' + scope.product_id + '/edit' );
        })
    }
  }
}])
.directive('contactAdvertiser', ['$location', 'factoryServices', 
( $location, factoryServices ) => {
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
}])
.directive('zoombg', ['$window', ( $window ) => {
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
}])
.directive('modalDialog', ['$routeServices', 
( $routeServices ) => {
  return {
    restrict: 'AEC',
    replace: true,
    transclude: true,
    scope: true,
    controller: ['$scope', function modalController( $scope ) {
      
    }],
    templateUrl: jsRoute.partials_uri + "dialog.html",
  }
}])