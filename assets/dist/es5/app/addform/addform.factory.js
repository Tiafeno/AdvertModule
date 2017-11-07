"use strict";

app.factory("factoryServices", function ($http, $q) {
  return {
    httpPostFormdata: function httpPostFormdata(formdata) {
      return $http({
        url: advert.ajax_url,
        method: "POST",
        headers: { 'Content-Type': undefined },
        data: formdata
      });
    },
    getParentsTermsCat: function getParentsTermsCat() {
      return $http.get(advert.ajax_url, {
        params: {
          action: "getParentsTermsCat"
        }
      });
    },
    getTermsProductCategory: function getTermsProductCategory() {
      return $http.get(advert.ajax_url, {
        params: {
          action: "getTermsProductCategory"
        }
      });
    }
  };
});