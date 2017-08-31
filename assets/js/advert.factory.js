app.factory("factoryServices", function( $http, $q ) {
  return {
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
     },
     addAdvert: function( formdata ){
      return $http.post(advert.ajax_url, formdata, {
        headers : {
          'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
        },
        params: {
          action: "action_add_new_advert",
          post_id: advert.post_id,
        }
      })
     }
  }
})