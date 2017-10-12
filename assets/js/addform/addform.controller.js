'use strict'

app.controller('AdvertFormAddCtrl', function (
  $scope,
  $http,
  $log,
  $window,
  $element,
  alertify,
  factoryServices
) {

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

  /* Verify file extension before upload */
  var validateFileExtension = function ( file ) {
    if (!/(\.bmp|\.gif|\.jpg|\.jpeg|\.png)$/i.test( file )) {    
        return false;   
    }   
    return true; 
 } 
  /*
  ** upload image and set thumbnail
  */
  $scope.uploadFile = function () {
    if ($scope.thumbnailGalleryIDs.length == parseInt($window.atob("Mw=="))) {
      alertify.error( $window.atob("Tm9tYnJlIGxpbWl0ZSBkZXMgcGhvdG9zIGF0dGVpbnQ=") );
      return true;
    }
    var files = event.target.files; // @return array of FileList
    var verifyFile = validateFileExtension( files[0].name );
    if (!verifyFile) {
      alertify.error("File format invalid, please upload an jpg, jpeg, png, gif or bmp");
      return true;
    }
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
      .then(function successCallback( resp ) {
        var data = resp.data;
        if (resp.status != undefined && resp.status == 200) {
          if (true === data.type) {
            $scope.thumbnailGalleryIDs.push({ file: data.url, id: data.attach_id });
          } else {
            var params = {};
            params.content = (data.data === undefined) ? data : data.data;
            $scope.showDialog( params );
          }
        }
        $scope.picProgress = false;
      }, function errorCallback( errno ) {
        console.debug( errno );
        $scope.picProgress = false;
      });

  };

  $scope.showDialog = function( params ) {
    var title = (params.title === undefined) ? 'Information' : params.title;
    alertify.alert(params.content, function ( ev ) {
      // user clicked "ok"
      ev.preventDefault();
      alertify.success("You've clicked OK");
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

    factoryServices.httpPostFormdata( advertdata )
    .then(function successCallback( results ) {
      if (parseInt(results) === 0) return $scope.activated = false; 
      $scope.activated = false;
      var data = results.data;
      if (data.type) {
        $scope.thumbnailGalleryIDs = [];
        $scope.advertPost = {};
        $scope.setAdvertForm.$setUntouched();
        $scope.setAdvertForm.$setPristine();
      }
      /* redirect */
      $window.setTimeout(function() {
        if (data.type)
          $window.location.href = data.redirect_url;
      }, 2500);
    }, function erroCallback( errno ) {
      $scope.activated = false;
    });
    
  };

  /* Event on click button set default image */
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

  /* Event on click delete image */
  $scope.onClickDeleteThumb = function (post_id) {
    if (typeof post_id == "number") {
      $scope.picProgress = true;

      var delete_formdata = new FormData();
      delete_formdata.append('action', 'action_delete_post');
      delete_formdata.append('post_type', 'attachment');
      delete_formdata.append('id', post_id);

      factoryServices.httpPostFormdata( delete_formdata )
        .then(function successCallback( results ){
          var data = results.data;
          if (!data.type) { console.warn( data ); return $scope.picProgress = false; }
          var galleries = _.reject( $scope.thumbnailGalleryIDs, function( gallery ){
            return gallery.id == data.ID;
          });
          $scope.thumbnailGalleryIDs = galleries;
          angular.element('#fileInput').val("");
          $scope.picProgress = false;
        }, function errorCallback( errno ) {
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