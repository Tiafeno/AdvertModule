'use strict'

advert.controller('AdvertController', function ($scope, $window, $filter, Advertfactory) {
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
  var _pt = _.union(adverts.posts);
  $scope.posts = _.map(_pt, function( el ) {
    el.img_url = $filter( 'thumbnail_url' )( el.ID );
    return el;
  });
})
  .config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('[[').endSymbol(']]');

  });