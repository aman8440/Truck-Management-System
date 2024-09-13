angular.module("truckingApp").controller("LoadDocController", [
  "$scope",
  "$location",
  "AuthService",
  function ($scope, $location, AuthService) {
    $scope.message = "Welcome to the Loads Document!";
    if (!AuthService.isAuthenticated()) {
      $location.path("/login");
    }
    $scope.logout = function () {
      AuthService.removeToken();
      $location.path("/login");
    };
    $scope.userName = AuthService.getUserName();
  },
]);
