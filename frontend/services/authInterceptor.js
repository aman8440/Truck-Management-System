angular.module('truckingApp').factory('AuthInterceptor', [
  '$q', 
  '$location',
  'AuthService',
  function($q, $location, AuthService) {
    return {
      request: function(config) {
        // Retrieve the token from local storage
        var token = localStorage.getItem('authToken');
        if (token) {
          // Add the token to the headers
          config.headers.Authorization = 'Bearer ' + token;
        }
        return config;
      },
      responseError: function(response) {
        if (response.status === 401 || response.status === 403) {
          console.error('Unauthorized access - token may be invalid');
          console.log("object", response);
          AuthService.removeToken();
          $location.path('/login');

        }
        return $q.reject(response);
      }
    };

  }
]);

