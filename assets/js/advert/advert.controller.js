'use strict'

advert.controller('AdvertController', function ($scope, $window, Advertfactory) {
  var self = this;
  $scope.vendors = [];
  self.Initialize = function () {
    Advertfactory.getVendors()
      .then(function (results) {
        var data = results.data;
        $scope.vendors = data;
      })
      .catch(function () {
        $window.setTimeout(function () {
          self.Initialize();
        }, 2500);
      });
  }
  self.Initialize();
  $scope.posts = _.union(adverts.posts);
})
  .config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('[[').endSymbol(']]');

  });