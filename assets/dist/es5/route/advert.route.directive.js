'use strict';

routeAdvert.directive('advertslider', function ($parse) {
  return {
    restrict: 'A', /* Attribut */
    scope: true,
    link: function link(scope, element, attrs) {
      element.bind('click', function (e) {
        var refer = scope.$eval(attrs.pictureRefer);
        var currentSlide = scope.product_details.post.pictures[refer];
        /* $parse method, this allows parameters to be passed */
        var invoker = $parse(attrs.advertsClick);
        invoker(scope, { idx: refer.toString() });
        jQuery('.advert-bg').css({
          'background': '#151515 url(' + currentSlide.full + ')'
        });
      });
    }
  };
}).directive('editadvert', function ($location, $routeServices, factoryServices, alertify) {
  return {
    restrict: 'A', /* Attribut */
    scope: true,
    link: function link(scope, element, attrs) {
      element.bind('click', function (e) {
        if (!$routeServices.isAuthorize()) {
          /* user don't have access to edit this post */
          alertify.alert($routeServices.getDeniedMessage(), function (ev) {
            ev.preventDefault();
          });
        } else $location.path('/advert/' + scope.product_id + '/edit');
      });
    }
  };
}).directive('contactAdvertiser', function ($location, factoryServices) {
  return {
    restrict: 'A', /* Attribut */
    scope: true,
    link: function link(scope, element, attrs) {
      element.bind('click', function (e) {
        scope.$apply(function () {
          $location.path('/advert/' + scope.product_id + '/contact');
        });
      });
    }
  };
}).directive('zoombg', function ($window) {
  return {
    link: function link(scope, element, attrs) {
      element.bind('click', function (e) {
        var _pts = scope.product_details.post.pictures;
        var strWindowFeatures = "menubar=yes, location=yes, resizable=yes, scrollbars=yes, status=yes";
        if (!_.isEmpty(_pts)) {
          var windowObjectReference = $window.open(_pts[scope.refer].full, scope.product_details.post.post_title, strWindowFeatures);
        }
      });
    }
  };
});