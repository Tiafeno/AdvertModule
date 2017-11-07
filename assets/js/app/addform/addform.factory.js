app.factory("factoryServices", function( $http, $q ) {
  return {
    httpPostFormdata: function( formdata ){
      return $http({
        url: advert.ajax_url,
        method: "POST",
        headers: {'Content-Type': undefined},
        data: formdata
      })
    },
    getParentsTermsCat : function() {
      return $http.get(advert.ajax_url, {
        params: {
          action: "getParentsTermsCat"
        }
      });
    },
    getTermsProductCategory: function() {
      return $http.get(advert.ajax_url, {
        params: {
          action: "getTermsProductCategory"
        }
      });
    }
  }
})