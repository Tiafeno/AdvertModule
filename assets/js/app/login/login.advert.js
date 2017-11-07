var login = angular.module('LoginAdvertApp', ['ngMaterial', 'ngMessages']);
login.controller('LoginAdvertCtrl', function ($scope, $http) {
    this.Initialize = function () {
        angular.element(jQuery( 'input[type="password"]' )).triggerHandler( 'input' );
    };

    this.Initialize();
});
