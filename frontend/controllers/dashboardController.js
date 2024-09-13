angular.module("truckingApp").controller("DashboardController", [
  "$scope",
  "$location",
  "AuthService",
  function ($scope, $location, AuthService) {
    $scope.message = "Welcome to the Dashboard!";
    if (!AuthService.isAuthenticated()) {
      $location.path("/login");
    }
    $scope.logout = function () {
      AuthService.removeToken();
      $location.path("/login");
    };
    $scope.userName = AuthService.getUserName();
    $scope.redirect= function(){
        $location.path("/dashboard");
    }
  },
]);
