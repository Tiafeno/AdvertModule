'use strict';

advert.directive('description', function () {
  return {
    restrict: "E",
    replace: true,
    transclude: true,
    scope: { _id: '=data-id' },
    templateUrl: "templates/descriptions-card.html",
    link: function link(scope, element) {}
  };
});