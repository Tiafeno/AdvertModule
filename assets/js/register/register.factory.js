register.factory("registerFactory", function( $http, $q) {
  return {
    register_user : function( formdata ) {
      return $http({
        url: advert.ajax_url,
        method: "POST",
        headers: {'Content-Type': undefined},
        data: formdata
      })
    }
  };
})