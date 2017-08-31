/**
* Created by Ьк Аштуд on 5/6/17.
*/
(function (angular) {
	var app = angular.module('RegisterAdvertApp', ['ngMaterial', 'ngMessages']);
	
	app.controller('AdvertFormRegisterCtrl', function ($scope, $http, $log) {
		$scope.register = {};
		$scope.CheckPass = false;
		$scope.activated = false;
		$scope.$watch('register', function(){
			if(typeof $scope.register.password != 'undefined'){
				if($scope.register.password != $scope.register.rpassword){
					if(typeof $scope.register.rpassword != "undefined")
						$scope.CheckPass = true;
				} else  $scope.CheckPass = false;
				
			}
		}, true);
		
		$scope.registerFormSubmit = function( isValid ) {
			if (!isValid) return;
			if ($scope.register.password != $scope.register.rpassword) {
				$scope.CheckPass = true;
				return;
			}
			
			var registerdata = new FormData();
			registerdata.append('lastname', $scope.register.lastname);
			registerdata.append('firstname',$scope.register.firstname);
			registerdata.append('SIRET',    $scope.register.SIRET);
			registerdata.append('society',  $scope.register.society);
			registerdata.append('adress',   $scope.register.adress);
			registerdata.append('postal_code', $scope.register.postal_code);
			registerdata.append('phone',       $scope.register.phone);
			
			registerdata.append('email',    $scope.register.email);
			registerdata.append('password', $scope.register.password);
			$scope.activated = true;
			$http({
				url: advert.ajax_url,
				method: "POST",
				headers: {'Content-Type': undefined},
				data: registerdata,
				params: {
					action: "action_register_user"
				}
				
			}).success(function (resp) {
				if (parseInt( resp ) === 0)  return;
				$scope.activated = false;
				
				$scope.setAdvertRegisterForm.$setUntouched();
				$scope.setAdvertRegisterForm.$setPristine();
				
			}).error(function () {
				$scope.activated = false;
			});
		};
		
		this.Initialize = function () {
		};
		
		this.Initialize();
	});
})(window.angular);
