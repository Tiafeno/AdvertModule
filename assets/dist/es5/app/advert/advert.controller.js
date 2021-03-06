'use strict';

advert.controller('AdvertController', ['$scope', '$location', '$window', '$filter', 'Advertfactory', function ($scope, $location, $window, $filter, Advertfactory) {
  var self = this;
  $scope.vendors = [];

  $scope.go = function (path) {
    $location.path(path);
  };

  self.Initialize = function () {
    Advertfactory.getVendors().then(function (results) {
      var data = results.data;
      $scope.vendors = data;
    }).catch(function () {
      $window.setTimeout(function () {
        self.Initialize();
      }, 2500);
    });
  };
  self.Initialize();
  var _pt = _.union(adverts.posts);
  $scope.posts = _.map(_pt, function (el) {
    el.img_url = $filter('thumbnail_url')(el.ID);
    return el;
  });
}]).config(['$interpolateProvider', function ($interpolateProvider) {
  $interpolateProvider.startSymbol('[[').endSymbol(']]');
}]);