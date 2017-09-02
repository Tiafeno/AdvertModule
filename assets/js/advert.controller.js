app.controller('AdvertFormAddCtrl', function (
  $scope,
  $http,
  $log,
  $window,
  $element,
  $mdToast,
  factoryServices
) {

  var last = { bottom: true, top: false, left: false, right: true };
  $scope.toastPosition = angular.extend({}, last);
  function sanitizePosition() {
    var current = $scope.toastPosition;
    if (current.bottom && last.top) current.top = false;
    if (current.top && last.bottom) current.bottom = false;
    if (current.right && last.left) current.left = false;
    if (current.left && last.right) current.right = false;

    last = angular.extend({}, current);
  }

  $scope.getToastPosition = function () {
    sanitizePosition();

    return Object.keys($scope.toastPosition)
      .filter(function (pos) {
        return $scope.toastPosition[pos];
      })
      .join(' ');
  };
  $scope.showSimpleToast = function () {
    var pinTo = $scope.getToastPosition();
    $mdToast.show(
      $mdToast.simple()
        .textContent($window.atob("Tm9tYnJlIGxpbWl0ZSBkZXMgcGhvdG9zIGF0dGVpbnQ="))
        .position(pinTo)
        .hideDelay(3000)
    );
  };
  $scope.range = function (min, max, step) {
    step = step || 1;
    var input = [];
    for (var i = min; i <= max; i += step) input.push(i);
    return input;
  };

  $scope.activated = false;
  $scope.searchTerm;
  $scope.clearSearchTerm = function () {
    $scope.searchTerm = '';
  };
  $element.find('input.demo-header-searchbox').on('keydown', function (ev) {
    ev.stopPropagation();
  });

  $scope.optionalInput = {};
  $scope.thumbnailGalleryIDs = [];
  $scope.thumbnailID = null;
  $scope.showHints = true;
  $scope.picProgress = false;
  $scope.imagePath = advert.assets_plugins_url + 'img/washedout.png';
  $scope.advertPost = {
    hidephone: false
  };
  $scope.product_cat = []; //content all categories

  /*
  ** Watch variable advertPost
  */
  $scope.$watch('advertPost', function (newValue, oldValue, scope) {
    var InputPreferences = Object.keys($scope.optionalInput);
    var AdvertPostKeys = Object.keys($scope.advertPost);
    if (AdvertPostKeys.length === 0) return;
    if (newValue.categorie != oldValue.categorie) {
      _.each(InputPreferences, function (el) {
        $scope.optionalInput[el] = false;
      });
      var currentCategorie = _.find($scope.product_cat, function (ctg) {
        return ctg.term_id == newValue.categorie;
      });
      var vendors = _.findWhere(advert.products_cat_child, { name: currentCategorie.name }).vendor; // Array
      _.each(vendors, function (el, index) {
        var preference = _.find(advert.vendors, function (vendor) {
          return el == vendor.id;
        });
        $scope.optionalInput[preference.validate] = true;
      });
    }
  }, true);

  /*
  ** upload image and set thumbnail
  */
  $scope.uploadFile = function () {
    if ($scope.thumbnailGalleryIDs.length == parseInt($window.atob("Mw=="))) {
      $scope.showSimpleToast();
      $log.debug($window.atob("Tm9tYnJlIGxpbWl0ZSBkZXMgcGhvdG9zIGF0dGVpbnQ="));
      return true;
    }

    var files = event.target.files;
    var formdata = new FormData();
    $scope.imagePath = $window.URL.createObjectURL(files[0]);
    $scope.$apply();

    angular.forEach(files, function (value, key) {
      formdata.append('file', value);
    });
    $scope.picProgress = true;
    $http({
      url: advert.ajax_url,
      method: "POST",
      headers: { 'Content-Type': undefined },
      data: formdata,
      params: {
        action: "action_set_thumbnail_post",
        post_id: advert.post_id,
        thumbnail_upload_nonce: angular.element('#thumbnail_upload_nonce').val()
      }
    }).success(function (resp) {
      if (resp.type === 'success')
        $scope.thumbnailGalleryIDs.push({ file: resp.url, id: resp.attach_id });
      if (resp.type === 'error')
        $log.debug(resp.msg);
      $scope.picProgress = false;
    }).error(function (errno) {
      $log.debug(errno);
      $scope.picProgress = false;
    });

  };

  $scope.setFormSubmit = function (isValid) {
    if (!isValid) return;
    var Gallery = [];
    var advertdata = new FormData();
    $scope.activated = true;
    $scope.thumbnailGalleryIDs.forEach(function (el) {
      Gallery.push(el.id);
    });
    $scope.advertPost.hidephone = ($scope.advertPost.hidephone == true) ? 1 : 0;
    var Attributs = [];
    _.map($scope.advertPost.attributs, function (val, key) {
      if ($scope.optionalInput[ key ])
        Attributs.push({ 'value': val, '_id': key }); // e.g {"value":"0","_id":"real_estate_type"}
    });
    advertdata.append('cost', $scope.advertPost.cost);
    advertdata.append('title', $scope.advertPost.title);
    advertdata.append('description', $scope.advertPost.description);
    advertdata.append('state', $scope.advertPost.state);
    advertdata.append('adress', $scope.advertPost.adress);
    advertdata.append('phone', $scope.advertPost.phone);
    advertdata.append('hidephone', $scope.advertPost.hidephone);
    advertdata.append('gallery', JSON.stringify(Gallery));
    advertdata.append('categorie', $scope.advertPost.categorie);
    advertdata.append('attributs', angular.toJson(Attributs));
    advertdata.append('action', "action_add_new_advert");
    advertdata.append('post_id', advert.post_id);

    factoryServices.addAdvert(advertdata).success(function (results) {
      if (parseInt(results) === 0) return;
      $scope.thumbnailGalleryIDs = [];
      $scope.activated = false;
      $scope.advertPost = {};
      $scope.setAdvertForm.$setUntouched();
      $scope.setAdvertForm.$setPristine();
    }).error(function () { $scope.activated = false; });

  };

  $scope.onClicksetDefaultThumb = function (thumb_id, $event) {
    $scope.picProgress = true;
    $http({
      url: advert.ajax_url,
      method: "GET",
      params: {
        action: "action_set_thumbnail_id",
        attachment_id: parseInt(thumb_id),
        post_id: advert.post_id
      }
    }).success(function (resp) {
      angular.element(".advert-pic").removeClass('active');
      angular.element("#" + thumb_id).addClass('active');
      $scope.picProgress = false;
    }).error(function () {
      $scope.picProgress = false;
    });
  };
  $scope.onClickDeleteThumb = function (post_id) {
    if (typeof post_id == "number") {
      $scope.picProgress = true;
      $http({
        url: advert.ajax_url,
        method: "GET",
        params: {
          action: "action_delete_post",
          id: parseInt(post_id),
        }
      }).success(function (resp) {
        if (parseInt(resp) === 0) return;
        $scope.thumbnailGalleryIDs.forEach(function (el, index) {
          if (el.id == post_id) {
            if (index == 0) {
              $scope.thumbnailGalleryIDs.splice(index, index + 1);
              return true;
            }
            $scope.thumbnailGalleryIDs.splice(index, index);
          }
        });
        $scope.picProgress = false;
        $log.debug($scope.thumbnailGalleryIDs);
      }).error(function () {
        $scope.picProgress = false;
      });
    }
  };

  this.Initialise = function () {
    factoryServices.getTermsProductCategory().then(function (results) {
      results.data.forEach(function (el) {
        if (el.term_id == 1 || el.slug == 'all') return false;
        $scope.product_cat.push(el);
      });
    }).catch(function () { console.warn('Terms products error') });
  };

  this.Initialise();
})
  .config(function ($mdThemingProvider, $interpolateProvider) {
    $interpolateProvider.startSymbol('[[').endSymbol(']]');
    $mdThemingProvider.theme('docs-dark', 'default')
      .primaryPalette('yellow')
      .dark();

  });