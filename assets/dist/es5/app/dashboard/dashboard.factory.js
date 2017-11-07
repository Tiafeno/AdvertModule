'use strict';

dashboard.factory('DashboardServices', function ($http, $q) {
  return {
    getCurrentUserAdverts: function getCurrentUserAdverts() {
      return $http.get(jsDashboard.ajax_url, {
        params: {
          action: 'action_get_adverts'
        }
      });
    }
  };
});