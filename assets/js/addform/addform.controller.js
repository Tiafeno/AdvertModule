'use strict'

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
        return $scope.toastPosition[ pos ];
      })
      .join(' ');
  };

  $scope.showSimpleToast = function ( msg = '' ) {
    var pinTo = $scope.getToastPosition();
    $mdToast.show(
      $mdToast.simple()
        .textContent( msg )
        .position(pinTo)
        .hideDelay(3000)
    );
  };

  $scope.showActionToast = function(msg, btn) {
    var pinTo = $scope.getToastPosition();
    var toast = $mdToast.simple()
      .textContent( msg )
      .action( btn )
      .highlightAction(true)
      .position( pinTo );
    $mdToast.show( toast ).then(function( response ) {
      console.log(response);
    });
  }

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

  $element.find( 'input.demo-header-searchbox' ).on('keydown', function (ev) {
    ev.stopPropagation();
  });

  $scope.optionalInput = {};
  /* e.g [{ file: '...', id: 'attachment id...'}] */
  $scope.thumbnailGalleryIDs = [];
  $scope.showHints = true;
  $scope.picProgress = false;
  $scope.imagePath = advert.assets_plugins_url + 'img/washedout.png';
  $scope.advertPost = {
    hidephone: false
  };
  $scope.product_cat = []; /* content all categories */

  /*
  ** Watch variable advertPost
  */
  $scope.$watch('advertPost', function (newValue, oldValue, scope) {
    var InputPreferences = Object.keys($scope.optionalInput);
    var AdvertPostKeys = Object.keys($scope.advertPost);
    if (AdvertPostKeys.length === 0) return;
    if (newValue.categorie != oldValue.categorie) {
      _.each(InputPreferences, function (el) {
        $scope.optionalInput[ el ] = false;
      });
      var currentCategorie = _.find($scope.product_cat, function (ctg) {
        return ctg.term_id == newValue.categorie;
      });
      var vendors = _.findWhere(advert.products_cat_child, { name: currentCategorie.name }).vendor; // Array
      _.each(vendors, function (el, index) {
        var preference = _.find(advert.vendors, function (vendor) {
          return el == vendor.id;
        });
        $scope.optionalInput[ preference.validate ] = true;
      });
    }
  }, true);

  /*
  ** upload image and set thumbnail
  */
  $scope.uploadFile = function () {
    if ($scope.thumbnailGalleryIDs.length == parseInt($window.atob("Mw=="))) {
      $scope.showSimpleToast( $window.atob("Tm9tYnJlIGxpbWl0ZSBkZXMgcGhvdG9zIGF0dGVpbnQ=") );
      return true;
    }
    var files = event.target.files;
    var formdata = new FormData();
    /* $scope.imagePath = $window.URL.createObjectURL(files[0]); */
    angular.forEach(files, function (value, key) {
      formdata.append('file', value);
    });
    formdata.append('action', "action_set_thumbnail_post");
    formdata.append('post_id', advert.post_id);
    formdata.append('thumbnail_upload_nonce', angular.element('#thumbnail_upload_nonce').val());
    $scope.picProgress = true;
    $scope.$apply();
    factoryServices.httpPostFormdata( formdata )
      .success(function( resp ) {
        if (resp.type)
          $scope.thumbnailGalleryIDs.push({ file: resp.url, id: resp.attach_id });
        if (!resp.type)
          console.debug( resp.data );
        $scope.picProgress = false;
      })
      .error(function( errno ) {
        console.debug( errno );
        $scope.picProgress = false;
      });

  };

  $scope.setFormSubmit = function (isValid) {
    if (!isValid) return;
    var Gallery = [];
    var advertdata = new FormData();
    $scope.activated = true;
    $scope.thumbnailGalleryIDs.forEach(function (el) {
      Gallery.push( el.id );
    });
    $scope.advertPost.hidephone = ($scope.advertPost.hidephone == true) ? 1 : 0;
    var attrs = [];
    _.map($scope.advertPost.attributs, function (val, key) {
      if ($scope.optionalInput[ key ])
        attrs.push({ 'value': val, '_id': key }); // e.g {"value":"0","_id":"real_estate_type"}
    });
    advertdata.append('cost', $scope.advertPost.cost);
    advertdata.append('title', $scope.advertPost.title);
    advertdata.append('description', $scope.advertPost.description);
    advertdata.append('state', $scope.advertPost.state);
    advertdata.append('adress', $scope.advertPost.adress);
    advertdata.append('phone', $scope.advertPost.phone);
    advertdata.append('hidephone', $scope.advertPost.hidephone);
    advertdata.append('gallery', JSON.stringify( Gallery ));
    advertdata.append('categorie', $scope.advertPost.categorie);
    advertdata.append('attributs', angular.toJson( attrs ));
    advertdata.append('action', "action_add_new_advert");
    advertdata.append('post_id', advert.post_id);

    factoryServices.httpPostFormdata( advertdata ).success(function (results) {
      if (parseInt(results) === 0) return $scope.activated = false; 
      $scope.activated = false;

      if (results.type) {
        $scope.thumbnailGalleryIDs = [];
        $scope.advertPost = {};
        $scope.setAdvertForm.$setUntouched();
        $scope.setAdvertForm.$setPristine();
      }
      /* redirect */
      $window.setTimeout(function() {
        if (results.type)
          $window.location.href = results.redirect_url;
      }, 2500);
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
      if (resp.type) {
        angular.element(".advert-pic").removeClass( 'active' );
        angular.element("#" + thumb_id).addClass( 'active' );
      } else { console.warn( resp ); }
      $scope.picProgress = false;
    }).error(function () {
      $scope.picProgress = false;
    });
  };

  $scope.onClickDeleteThumb = function (post_id) {
    if (typeof post_id == "number") {
      $scope.picProgress = true;

      var delete_formdata = new FormData();
      delete_formdata.append('action', 'action_delete_post');
      delete_formdata.append('post_type', 'attachment');
      delete_formdata.append('id', post_id);

      factoryServices.httpPostFormdata( delete_formdata )
        .success(function( results ){
          if (!results.type) { console.warn(results.data); return $scope.picProgress = false; }
          var galleries = _.reject( $scope.thumbnailGalleryIDs, function( gallery ){
            return gallery.id == results.ID;
          });
          $scope.thumbnailGalleryIDs = galleries;
          angular.element('#fileInput').val("");
          $scope.picProgress = false;
        })
        .error(function( errno ) {
          $scope.picProgress = false; 
          console.debug( errno );
        });
    }
  };

  this.Initialise = function () {
    /* Get all category product term */
    factoryServices.getTermsProductCategory().then(function (results) {
      results.data.forEach(function (el) {
        if (el.term_id == 1 || el.slug == 'all') return false;
        $scope.product_cat.push( el );
      });
    }).catch(function () { console.warn('Terms products error') });

    /* Get current post gallery and thumbnail */

  };

  this.Initialise();
})
  .config(function ($mdThemingProvider, $interpolateProvider) {
    $interpolateProvider.startSymbol( '[[' ).endSymbol( ']]' );
    $mdThemingProvider.theme('docs-dark', 'default')
      .primaryPalette( 'yellow' )
      .dark();

  });