angular.module("truckingApp").service("AuthService", [
  "$window",
  function ($window) {
    this.getToken = function () {
      return $window.localStorage.getItem("authToken");
    };

    this.isAuthenticated = function () {
      return !!this.getToken();
    };
    this.getUserName = function() {
      return $window.localStorage.getItem('userName');
    };
    this.removeToken = function() {
      $window.localStorage.removeItem('authToken');
      $window.localStorage.removeItem('userName');
    };
  },
]);
