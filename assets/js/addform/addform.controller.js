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
  $scope.imageGallery = [];
  $scope.Images = [];
  $scope.showHints = true;
  $scope.Progress = { Image: false };
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
      _.each(InputPreferences, el => {
        $scope.optionalInput[ el ] = false;
      });
      var currentCategorie = _.find($scope.product_cat, ctg => {
        return ctg.term_id == newValue.categorie;
      });
      var vendors = _.findWhere(advert.products_cat_child, { name: currentCategorie.name }).vendor; // Array
      _.each(vendors, (el, index) => {
        var preference = _.find(advert.vendors, vendor => {
          return el == vendor.id;
        });
        $scope.optionalInput[ preference.validate ] = true;
      });
    }
  }, true);

  var setFirstImage = false; 
  /* Verify file extension before upload */
  var validateFileExtension = function ( file ) {
    if (!/(\.bmp|\.gif|\.jpg|\.jpeg|\.png)$/i.test( file )) {
        return false;
    }
    return true;
  }

  $scope.range = function (min, max, step) {
    step = step || 1;
    var input = [];
    for (var i = min; i <= max; i += step) input.push(i);
    return input;
  };

  /*
  ** upload image and set thumbnail
  */
  $scope.uploadFile = function () {
    if ($scope.imageGallery.length == parseInt($window.atob("Mw=="))) {
      alertify.error( $window.atob("Tm9tYnJlIGxpbWl0ZSBkZXMgcGhvdG9zIGF0dGVpbnQ=") );
      return true;
    }
    var files = event.target.files; // @return array of FileList
    if (files[0].name === undefined) return true;
    var verifyFile = validateFileExtension( files[0].name );
    if (!verifyFile) {
      alertify.error("File format invalid, please upload an jpg, jpeg, png, gif or bmp");
      return true;
    }
    var formdata = new FormData();
    /* $scope.imagePath = $window.URL.createObjectURL(files[0]); */
    angular.forEach(files, (value, key) => {
      formdata.append('file', value);
    });
    formdata.append('action', "action_set_thumbnail_post");
    formdata.append('post_id', advert.post_id);
    formdata.append('thumbnail_upload_nonce', angular.element('#thumbnail_upload_nonce').val());
    $scope.Progress.Image = true;
    $scope.$apply();
    factoryServices.httpPostFormdata( formdata )
      .then( resp => {
        var dataResponse = resp.data;
        if (resp.status != undefined && resp.status == 200) {
          if (true === dataResponse.type) {
            if (setFirstImage)
              $scope.imageGallery.push({ file: dataResponse.url, id: dataResponse.attach_id });
            if (!setFirstImage) { setFirstImage = true; }
            $scope.Images.push({ file: dataResponse.url, id: dataResponse.attach_id })
          } else {
            var params = {};
            params.content = (dataResponse.data === undefined) ? dataResponse : dataResponse.data;
            $scope.showDialog( params );
          }
        }
        $scope.Progress.Image = false;
      }, errno => {
        console.debug( errno );
        $scope.Progress.Image = false;
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
    $scope.imageGallery.forEach(el => {
      Gallery.push( el.id );
    });
    $scope.advertPost.hidephone = ($scope.advertPost.hidephone == true) ? 1 : 0;
    var attrs = [];
    _.map($scope.advertPost.attributs, (val, key) => {
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

    factoryServices
      .httpPostFormdata( advertdata )
      .then( results => {
        if (parseInt(results) === 0) return $scope.activated = false;
        $scope.activated = false;
        var data = results.data;
        if (data.type) {
          $scope.imageGallery = [];
          $scope.advertPost = {};
          $scope.setAdvertForm.$setUntouched();
          $scope.setAdvertForm.$setPristine();
        }
        /* redirect */
        $window.setTimeout(() => {
          if (data.type)
            $scope.$apply(() => {
              $window.location.href = data.redirect_url;
            });
        }, 2500);
      },  errno => {
        $scope.activated = false;
      });

  };

  /* Event on click button set default image */
  $scope.onClicksetDefaultThumb = function (thumb_id, $event) {
    $scope.Progress.Image = true;
    $http({
      url: advert.ajax_url,
      method: "GET",
      params: {
        action: "action_set_thumbnail_id",
        attachment_id: parseInt(thumb_id),
        post_id: advert.post_id
      }
    }).then(results => {
      var data = results.data;
      if (data.type) {
        angular.element(".advert-pic").removeClass( 'active' );
        angular.element("#" + thumb_id).addClass( 'active' );
      } else {
        console.warn( resp );
      }
      $scope.Progress.Image = false;
    }, () => {
      $scope.Progress.Image = false;
    });
  };

  /* Event on click delete image */
  $scope.onClickDeleteThumb = function (post_id) {
    if (typeof post_id == "number") {
      alertify
        .okBtn("Oui")
        .cancelBtn("Non")
        .confirm( 'Voulez vous vraiment effacer cette image?' , function (ev) { /* ok */
          ev.preventDefault();

          $scope.Progress.Image = true;
          var delete_formdata = new FormData();
          delete_formdata.append('action', 'action_delete_post');
          delete_formdata.append('post_type', 'attachment');
          delete_formdata.append('id', post_id);
    
          factoryServices.httpPostFormdata( delete_formdata )
            .then(results => {
              var data = results.data;
              if (!data.type) { console.warn( data ); return $scope.Progress.Image = false; }
              $scope.Images = _.reject($scope.Images, Image => { return Image.id === data.ID });
              $scope.imageGallery = _.reject( $scope.imageGallery, gallery => { return gallery.id === data.ID; });
              if (_.isEmpty( $scope.Images ) && _.isEmpty( $scope.imageGallery )) setFirstImage = false;
              angular.element('#fileInput').val("");
              $scope.Progress.Image = false;
            }, errno => {
              $scope.Progress.Image = false;
              console.debug( errno );
            });
      }, function(ev) { /* cancel */
          ev.preventDefault();

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
