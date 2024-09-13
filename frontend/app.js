angular.module("truckingApp", ["ngRoute"]).config([
  "$routeProvider",
  "$httpProvider",
  function ($routeProvider, $httpProvider) {
    $routeProvider
      .when("/login", {
        templateUrl: "views/login.html",
        controller: "AdminAuthController",
      })
      .when("/forgot-password", {
        templateUrl: "views/forgot-password.html",
        controller: "AdminAuthController",
      })
      .when("/reset-password/:reset_token", {
        templateUrl: "views/reset-password.html",
        controller: "AdminAuthController",
        resolve: {
          reset_token: [
            "$route",
            function ($route) {
              return $route.current.params.reset_token;
            },
          ],
        },
      })
      .when("/dashboard", {
        templateUrl: "views/dashboard.html",
        controller: "DashboardController",
      })
      .when("/dispatcher", {
        templateUrl: "views/dispatcher-list.html",
        controller: "DispatcherController",
      })
      .when("/dispatchers/create", {
        templateUrl: "views/dispatcher-form.html",
        controller: "DispatcherController",
      })
      .when("/dispatchers/edit/:id", {
        templateUrl: "views/dispatcher-form.html",
        controller: "DispatcherController",
      })
      .when("/driver", {
        templateUrl: "views/driver-list.html",
        controller: "DriverController",
      })
      .when("/driver/create", {
        templateUrl: "views/driver-form.html",
        controller: "DriverController",
      })
      .when("/driver/edit/:id", {
        templateUrl: "views/driver-form.html",
        controller: "DriverController",
      })
      .when("/load", {
        templateUrl: "views/load-list.html",
        controller: "LoadController",
      })
      .when("/load/create", {
        templateUrl: "views/load-form.html",
        controller: "LoadController",
      })
      .when("/load/edit/:id", {
        templateUrl: "views/load-form.html",
        controller: "LoadController",
      })
      .when("/loadDoc", {
        templateUrl: "views/loadDoc-list.html",
        controller: "LoadDocController",
      })
      .when("/trailer", {
        templateUrl: "views/trailer-list.html",
        controller: "TrailerController",
      })
      .when("/trailer/create", {
        templateUrl: "views/trailer-form.html",
        controller: "TrailerController",
      })
      .when("/trailer/edit/:id", {
        templateUrl: "views/trailer-form.html",
        controller: "TrailerController",
      })
      .when("/truck", {
        templateUrl: "views/truck-list.html",
        controller: "TruckController",
      })
      .when("/truck/create", {
        templateUrl: "views/truck-form.html",
        controller: "TruckController",
      })
      .when("/truck/edit/:id", {
        templateUrl: "views/truck-form.html",
        controller: "TruckController",
      })
      .otherwise({
        redirectTo: "/login",
      });
    $httpProvider.interceptors.push("AuthInterceptor");
  },
]);
// app.run(['$rootScope', '$location', '$cookies', function($rootScope, $location, $cookies) {
//   $rootScope.$on('$routeChangeStart', function(event, next, current) {
//       if (!$cookies.get('ci_session') && next.templateUrl !== 'login.html') {
//           $location.path('/login');
//       }
//   });
// }]);
