angular.module("truckingApp").controller("LoadController", [
  "$scope",
  "$location",
  "AuthService",
  function ($scope, $location, AuthService) {
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
