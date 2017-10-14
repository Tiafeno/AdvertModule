'use strict'

app
  .directive("uploadfile", function () {
    return {
       restrict: 'A',
       link: function (scope, element) {
          element.bind('click', function (e) {
             angular.element('#fileInput').trigger('click');
          });
       }
    }
  })
  .directive('ngfChange', function () {
    return {
       restrict: 'A',
       link: function (scope, element, attrs) {
          var onChangeFunc = scope.$eval(attrs.ngfChange);
          element.bind('change', onChangeFunc);
       }
    };
  })
