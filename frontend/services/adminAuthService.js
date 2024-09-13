angular.module("truckingApp").factory("AdminAuthService", [
  "$http",
  "$window",
  function ($http, $window) {
    var baseUrl = "http://localhost/truck_management";

    return {
      login: function (admin) {
        return $http({
          method: "POST",
          url: baseUrl + "/api/login",
          data: admin,
          headers: {
            "Content-Type": "application/json",
          },
        }).then(function(response) {
          var token = response.data.token;
          var userName = response.data.admin_name;
          $window.localStorage.setItem('authToken', token);
          $window.localStorage.setItem('userName', userName);
          return response;
        });
      },
      forgotPassword: function (email) {
        return $http({
          method: "POST",
          url: baseUrl + "/forgot_password",
          data: { admin_email: email },
          headers: {
            "Content-Type": "application/json",
          },
        });
      },
      resetPassword: function (token, password) {
        return $http({
          method: "POST",
          url: baseUrl + "/reset_password",
          data: { reset_token: token, new_password: password },
          headers: {
            "Content-Type": "application/json",
          },
        });
      },
    };
  },
]);
