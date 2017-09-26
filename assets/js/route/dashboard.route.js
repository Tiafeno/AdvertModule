'use strict'

dashboard.config(['$routeProvider', function( $routeProvider ) {
  $routeProvider
    .when('/profil', {
      templateUrl: jsDashboard.partials_uri + 'dashboard.profil.html',
      controller: 'Dashboard_ProfilCtrl'
    })
    .when('/profil/edit', {
      templateUrl: jsDashboard.partials_uri + 'dashboard.edit.html',
      controller: 'Dashboard_EditCtrl'
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
    routeDashboard
      .filter('fromNow', function() {
        return function( input ) {
          var _dt = input.trim();
          if (typeof moment == 'undefined') return _dt + ' - moment.js not define';
          moment.locale('fr');
          return moment( _dt ).fromNow();
        }
      });

routeDashboard.directive("uploadavatar", function () {
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
  $scope.profil = {};
  $scope.verifyPassword = true;
  $scope.nonce = null;
  
  var initilize = function() {
    var KeysUser = _.keys( $scope._user );
    KeysUser = _.without( KeysUser, 'id_user', 'id_advert_user');
    _.each( KeysUser, function(el, index) {
      $scope.profil[ el ] = $scope._user[ el ];
    });
    if ($scope.profil.img_url == null || false === $scope.profil.img_url)
      $scope.profil.img_url = jsDashboard.assets_plugins_url + 'img/no-avatar-male.jpg';
  }

  initilize();
  $scope.EventSubmit = function( $event ) {
    var formdata = new FormData();
    var profil = _.omit( $scope.profil, ['id_user', 'img_url', 'id_advert_user'])
    formdata.append('action', "action_update_dashboard");
    _.each(profil, function ($value, $key) {
      formdata.append($key, $value);
    });

    factoryRouteDashboard.get_nonce_field( 'update_profil' )
      .then(function( results ) {
        factoryRouteDashboard.$httpPostForm( formdata )
          .success(function( results ) {
            
          })
          .error(function( errno ) {});
      });
  }

  $scope.EventformProfilValidate = function( $event ){
    return ($scope.profilForm.$dirty && $scope.profilForm.$valid) ? false : 
    ($scope.profilForm.$invalid ? ($scope.profilForm.$dirty ? true : false) : true);
  }

  /* Event ngBlur */
  $scope.EventVerifyPassword = function( $event, form ) {
    factoryRouteDashboard.verifyPassword( form.old_password.$modelValue ).then(function( results ) {
      var resp = results.data;
      var status = (resp.type && resp.token ) ? true : false;
      form.old_password.$setValidity('verifyPassword', status);
    })
  };

  /* Event ngBlur */
  $scope.EventEqualsPassword = function( $event, form) {
    var pass = form.new_password.$modelValue;
    var confirmPass = form.confirm_password.$modelValue;
    var status = (pass != confirmPass) ? false : true;
    form.confirm_password.$setValidity('equalsPassword', status);
  };

  /* Event ngKeydown */
  $scope.EventTypePassword = function( $event, form ) {
    var old_password = form.old_password.$modelValue;
    var new_password = form.new_password.$modelValue;
    var status = (old_password == new_password);
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

  $scope.EventClickchangeAvatar = function( ) {
    var files = event.target.files;
    var formdata = new FormData();
    formdata.append('action', "action_upload_avatar");
    angular.forEach(files, function (value, key) {
      formdata.append('file', value);
    });
    factoryRouteDashboard.get_nonce_field( 'avatar_upload' )
      .then(function( results) {
        var response = results.data;
        $scope.nonce = response.nonce;
        formdata.append('nonce', $scope.nonce);
        factoryRouteDashboard.$httpPostForm( formdata )
          .success(function( results ) {
            angular.element('#fileInput').val("");
            if (results.type)
              $scope.profil.img_url = results.url;
          })
          .error(function( errno ) { console.debug( errno ); })

      })
    
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