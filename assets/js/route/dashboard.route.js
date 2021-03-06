'use strict'

/* Route provider */
dashboard.config(['$routeProvider', function( $routeProvider ) {
  $routeProvider
    .when('/profil', {
      templateUrl: jsDashboard.partials_uri + 'dashboard.profil.html',
      controller: 'Dashboard_ProfilCtrl'
    })
    .when('/profil/edit', {
      templateUrl: jsDashboard.partials_uri + 'dashboard.edit.html',
      controller: 'Dashboard_EditCtrl',
      controllerAs: 'EditCtrl'
    })
    .when('/timeline', {
      templateUrl: jsDashboard.partials_uri + 'dashboard.timeline.html',
      controller: 'Dashboard_TimelineCtrl'
    })
    .otherwise({
      redirectTo: '/profil/edit'
    })
}]);

var routeDashboard = angular.module('routeDashboard', []);
/* Module filter */
routeDashboard
  .filter('fromNow', function() {
    return function( input ) {
      var _dt = input.trim();
      if (typeof moment == 'undefined') return _dt + ' - moment.js not define';
      moment.locale('fr');
      return moment( _dt ).fromNow();
    }
  });

/* Module directive */
routeDashboard
  .directive("uploadavatar", function () {
    return {
        restrict: 'A',
        link: function (scope, element) {
          element.bind('click', function (e) {
              angular.element('#fileInput').trigger('click');
          });
        }
    }
  })
  .directive('ngUpload', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
          var onChangeFunc = scope.$eval(attrs.ngUpload);
          element.bind('change', onChangeFunc);
        }
    };
  })
  .directive('logout', function() {
    return {
      restrict: 'A',
      scope: {},
      controller: function($scope, $window) {
        $scope.redirectURL = function( url ) {
          $window.location.href = url;
          $scope.$apply();
        }
      },
      link: function(scope, element, attrs) {
        element.bind('click', function() {
          var url = jsDashboard.logout_url;
          scope.redirectURL( url );
        });
      }
    }
  })

/* Module factory services */
routeDashboard.factory('factoryRouteDashboard', function( $http, $window, $q ) {
  return {
    getUser : function() {
      return jsDashboard._user;
    },
    get_nonce_field: function( field ) {
      return $http.get( jsDashboard.ajax_url, {
        params: {
          action: 'action_render_nonce',
          fieldnonce: field
        }
      });
    },
    verifyPassword: function( $pass ) {
      return $http.get( jsDashboard.ajax_url, {
        params : {
          pass: $window.btoa( $pass ),
          action: 'action_verify_password'
        }
      });
    },
    $httpPostForm: function( form ) {
      return $http({
        url: jsDashboard.ajax_url,
        method: "POST",
        headers: { 'Content-Type': undefined },
        data: form
      });
    }
  };
});

routeDashboard.controller('Dashboard_EditCtrl', function( $scope, $window, factoryRouteDashboard ) {
  $scope._user = factoryRouteDashboard.getUser();
  $scope.progress = {};
  $scope.profil = {};
  $scope.verifyPassword = true;
  $scope.nonce = null;
  
  $scope.progress.avatar = true;
  $scope.progress.password_change = true;
  
  var initialize = function() {
    var KeysUser = _.keys( $scope._user );
    KeysUser = _.without( KeysUser, 'id_user', 'id_advert_user');
    _.each( KeysUser, function(el, index) {
      $scope.profil[ el ] = $scope._user[ el ];
    });
    if ($scope.profil.img_url == null || false === $scope.profil.img_url)
      $scope.profil.img_url = jsDashboard.assets_plugins_url + 'img/no-avatar-male.jpg';
  };

  initialize();
  
  /* Event on submit form profil edit  */
  $scope.EventSubmit = function( $event ) {
    var formdata = new FormData();
    var profil = _.omit( $scope.profil, ['id_user', 'img_url', 'id_advert_user'])
    formdata.append('action', "action_update_dashboard");
    _.each(profil, function ($value, $key) {
      formdata.append($key, $value.trim());
    });

    var nonceField = 'update_profil_nonce';
    factoryRouteDashboard.get_nonce_field( nonceField )
      .then(function( results ) {
        if (results.data.nonce == undefined) {
          console.warn( 'Missing nonce!' );
          return false;
        }
        formdata.append('inputNonce', results.data.nonce);
        factoryRouteDashboard.$httpPostForm( formdata )
          .then(function successCallback( results ) {
            var data = results.data;
            if (data.reload != undefined && data.reload) 
              $window.setTimeout(function() {
                location.reload();
              }, 1500);
          }, function errorCallback( errno ) {
            console.warn( errno );
          });
      });
  }

  /* Check if form is dirty and valid */
  $scope.EventformProfilValidate = function( $event ){
    return ($scope.profilForm.$dirty && $scope.profilForm.$valid) ? false : 
    ($scope.profilForm.$invalid ? ($scope.profilForm.$dirty ? true : false) : true);
  }

  /* Event ngBlur, Verify if password is correct before change */
  $scope.EventVerifyPassword = function( $event, form ) {
    factoryRouteDashboard.verifyPassword( form.old_password.$modelValue ).then(function( results ) {
      var resp = results.data;
      var status = (resp.type && resp.token ) ? true : false;
      form.old_password.$setValidity('verifyPassword', status);
    })
  };

  /* Event ngBlur, Verify if verify password is equal a new password */
  $scope.EventEqualsPassword = function( $event, form) {
    var pass = form.new_password.$modelValue;
    var confirmPass = form.confirm_password.$modelValue;
    var status = (pass != confirmPass) ? false : true;
    form.confirm_password.$setValidity('equalsPassword', status);
  };

  /* Event ngKeydown, Verify if old password is not equal a new password */
  $scope.EventTypePassword = function( $event, form ) {
    var old_password = form.old_password.$modelValue;
    var new_password = form.new_password.$modelValue;
    var status = (old_password != new_password);
    if (form.old_password.$modelValue != undefined) {
      form.new_password.$setValidity('oldEqualnew', status);
    }
  }
  
  /* Event ngBlur */
  $scope.EventPassword = function( $event, form ) {
    if ( form.confirm_password.$modelValue != undefined ) {
      $scope.EventEqualsPassword( $event, form );
    }
  }
  
  /* Event on change avatar and save */
  $scope.EventClickchangeAvatar = function( ) {
    var files = event.target.files;
    var formdata = new FormData();
    formdata.append('action', "action_upload_avatar");
    angular.forEach(files, function (value, key) {
      formdata.append('file', value);
    });
    $scope.progress.avatar = false;
    factoryRouteDashboard.get_nonce_field( 'avatar_upload' )
      .then(function( results) {
        var response = results.data;
        $scope.nonce = response.nonce;
        formdata.append('nonce', $scope.nonce);
        factoryRouteDashboard.$httpPostForm( formdata )
          .then(function successCallback( results ) {
            var $data = results.data;
            $scope.progress.avatar = true;
            angular.element('#fileInput').val("");
            if ($data.type) {
              $scope.profil.img_url = $data.url;
            } else {
              console.debug( $data );
            }
          }, function errorCallback( errno ) {
            $scope.progress.avatar = true;
            console.debug( errno ); 
          });

      })
    
  };

  $scope.EventChangePassword = function( isValid ) {
    if (isValid) {
      $scope.progress.password_change = false;
      var httpform = new FormData();
      httpform.append('action', 'action_change_password');
      httpform.append('pass', $window.btoa( $scope.new_password ))
      factoryRouteDashboard.$httpPostForm( httpform )
        .then(function success( results ) {
          var $data = results.data;
          if (!$data.type) console.debug( $data.data );
          $scope.passwordForm.$setUntouched();
          $scope.passwordForm.$setPristine();
          $window.setTimeout(function() {
            location.reload();
          }, 1500);
        }, function error( errno ) {
          $scope.EventChangePassword( true );
        });
    } else {
      return false;
    }
  }

});

routeDashboard.controller('Dashboard_ProfilCtrl', function( $scope ) {

});

/*
* This controller show all advert have current user 
* with dropdown edit, remove and update
*/
routeDashboard.controller('Dashboard_TimelineCtrl', function( $scope ) {

});