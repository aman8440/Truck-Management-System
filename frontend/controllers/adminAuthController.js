angular.module("truckingApp").controller("AdminAuthController", [
  "$scope",
  "$location",
  "$routeParams", 
  "AdminAuthService",
  "AuthService",
  function ($scope, $location, $routeParams, AdminAuthService, AuthService) {
    $scope.loginData = {};
    $scope.forgotPasswordData = {};
    $scope.resetPasswordData = {
      reset_token: $routeParams.reset_token,
    };
    // console.log(AuthService.getToken());
    if (AuthService.getToken()) {
      $location.path("/dashboard");
    }
    $scope.login = function () {
      if (
        !$scope.loginData ||
        !$scope.loginData.admin_email ||
        !$scope.loginData.admin_password
      ) {
        $scope.loginMessage = "Email and Password are required";
        return;
      }
      AdminAuthService.login($scope.loginData)
        .then(function (response) {
          if (response.data && response.data.status === "success") {
            $location.path("/dashboard");
          } else {
            $scope.loginMessage = response.data.message || "Login failed";
          }
        })
        .catch(function (error) {
          console.error("Login error:", error);
          if (error.data && error.data.message) {
            $scope.loginMessage = error.data.message;
          } else {
            $scope.loginMessage = "An unexpected error occurred.";
          }
        });
    };

    $scope.tooglevisibility = function () {
      var passwordField = document.getElementById("password");
      const togglePassword = document.getElementById("togglePassword");
      if (passwordField.type === "password") {
        passwordField.type = "text";
        togglePassword.src ="https://icons.veryicon.com/png/o/miscellaneous/hekr/action-hide-password.png";
      } else {
        passwordField.type = "password";
        togglePassword.src ="https://static.thenounproject.com/png/4334035-200.png";
      }
    };
    $scope.forgotPassword = function () {
      if (
        !$scope.forgotPasswordData ||
        !$scope.forgotPasswordData.admin_email
      ) {
        $scope.forgotPasswordMessage = "Email are required";
        return;
      }

      // AdminAuthService.forgotPassword($scope.forgotPasswordData.admin_email)
      //   .then(function (response) {
      //     console.log(response);
      //     if (response.data.status === "success") {
      //       $scope.forgotPasswordMessageSuccess = "Email Sent Successfully.";
      //       $location.path("/login");
      //     } else {
      //       $scope.forgotPasswordMessage = "Error: " + response.data.message;
      //     }
      //   })
      //   .catch(function (error) {
      //     console.error("Error sending password reset email:", error);
      //     if (error.data && error.data.message) {
      //       $scope.forgotPasswordMessage = error.data.message;
      //     } else {
      //       $scope.forgotPasswordMessage =
      //         "Error sending password reset email.";
      //     }
      //   });
        AdminAuthService.forgotPassword($scope.forgotPasswordData.admin_email);
        $scope.forgotPasswordMessageSuccess = "Email Sent Successfully.";
        $location.path("/login");
    };

    $scope.resetPassword = function () {
      if (
        !$scope.resetPasswordData ||
        !$scope.resetPasswordData.reset_token ||
        !$scope.resetPasswordData.new_password ||
        !$scope.resetPasswordData.confirmPassword
      ) {
        $scope.resetPasswordMessage =
          "Password and Confirm password are required";
        return;
      }
      if (
        $scope.resetPasswordData.new_password !==
        $scope.resetPasswordData.confirmPassword
      ) {
        $scope.resetPasswordMessage = "Passwords do not match";
        return;
      }
      AdminAuthService.resetPassword(
        $scope.resetPasswordData.reset_token,
        $scope.resetPasswordData.new_password
      )
        .then(function (response) {
          if (response.data.status === "success") {
            $scope.resetPasswordMessageSuccess =
              "Password has been reset successfully.";
            $location.path("/login");
          } else {
            $scope.resetPasswordMessage = "Error: " + response.data.message;
          }
        })
        .catch(function (error) {
          console.error("Error resetting password.", error);
          if (error.data && error.data.message) {
            $scope.resetPasswordMessage = error.data.message;
          } else {
            $scope.resetPasswordMessage = "Error resetting password.";
          }
        });
    };
  },
]);
