/**
* Created by Tiafeno Finel on 5/6/17.
*/

register.controller('AdvertFormRegisterCtrl', function (
	$scope, 
	$http, 
	$window,
	$log, 
	$mdToast,
	registerFactory
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
	
	$scope.showActionToast = function(msg, btn) {
		var pinTo = $scope.getToastPosition();
		var toast = $mdToast.simple()
			.textContent( msg )
			.action( btn )
			.highlightAction(true)
			.hideDelay(3000)
			.position( pinTo );
		$mdToast.show( toast ).then(function( response ) { /* @return string, ok */
			
		});
	}
	
	$scope.register = {};
	$scope.CheckPass = false;
	$scope.activated = false;
	$scope.$watch('register', function(newValue, oldValue){
		
	}, true);
	
	$scope.registerFormSubmit = function( isValid ) {
		if (!isValid) return;
		
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

		registerdata.append('action', 'action_register_user');
		$scope.activated = true;

		registerFactory.register_user( registerdata )
			.then(function successCallback( resp ) {
				var data = resp.data;
				$scope.activated = false;
				$scope.RegisterForm.$setUntouched();
				$scope.RegisterForm.$setPristine();
				/* Set toasted */
				$scope.showActionToast(data.data, 'OK');
				/* redirect add form if success */
				if (resp.type == 'success')
					$window.setTimeout(function(){
						$window.location.href = data.redirect_url;
					}, 2500)
				
			}, function errorCallback( errno ) {
				$scope.activated = false;
			});
	};
	
	this.Initialize = function () {
	};
	
	this.Initialize();
});
