var app = angular.module("truckingApp");

app.controller("DispatcherController", [
  "$scope",
  "$location",
  "$routeParams",
  "AuthService",
  "DispatcherService",
  function ($scope, $location, $routeParams, AuthService, DispatcherService) {
    $scope.dispatchers = [];
    $scope.newDispatcher = {};
    $scope.message = {
      email: "",
      phone: "",
      name: "",
    };
    $scope.queryText = "";
    $scope.sortKey = "";
    $scope.sortReverse = false;
    $scope.totalItems = 0;
    $scope.currentPage = 1;
    $scope.itemsPerPage = 5;
    $scope.totalPages = 0;

    if (!AuthService.isAuthenticated()) {
      $location.path("/login");
    }

    $scope.logout = function () {
      AuthService.removeToken();
      $location.path("/login");
    };

    $scope.userName = AuthService.getUserName();

    $scope.redirect = function () {
      $location.path("/dispatchers/create");
    };

    $scope.redirecttoback = function () {
      $location.path("/dispatcher");
    };
    $scope.sort = function (keyname) {
      if ($scope.sortKey === keyname) {
        $scope.sortReverse = !$scope.sortReverse; // if true make it false and vice versa
      } else {
        $scope.sortKey = keyname;
        $scope.sortReverse = false; // set the default sort order
      }
      $scope.loadDispatchers();
    };

    $scope.loadDispatchers = function () {
      var params = {
        search: $scope.queryText,
        sort: $scope.sortKey,
        order: $scope.sortReverse ? "desc" : "asc",
        page: $scope.currentPage,
        limit: $scope.itemsPerPage,
      };
      DispatcherService.getAll(params).then(
        function (response) {
          $scope.dispatchers = response.data.data.data;
          $scope.totalItems = response.data.data.total;
          $scope.totalPages = Math.ceil(
            $scope.totalItems / $scope.itemsPerPage
          );
        },
        function (error) {
          console.error("Error fetching dispatchers:", error);
        }
      );
    };

    // $scope.applyFilters = function () {
    //   const filtered = $scope.dispatchers.filter((dispatcher) => {
    //     return (
    //       dispatcher.dis_name
    //         .toLowerCase()
    //         .includes($scope.queryText.toLowerCase()) ||
    //       dispatcher.dis_email
    //         .toLowerCase()
    //         .includes($scope.queryText.toLowerCase()) ||
    //       dispatcher.dis_phone
    //         .toLowerCase()
    //         .includes($scope.queryText.toLowerCase())
    //     );
    //   });

    //   const sorted = filtered.sort((a, b) => {
    //     const fieldA = a[$scope.sortKey];
    //     const fieldB = b[$scope.sortKey];
    //     if (fieldA < fieldB) return $scope.sortReverse ? 1 : -1;
    //     if (fieldA > fieldB) return $scope.sortReverse ? -1 : 1;
    //     return 0;
    //   });

    //   $scope.totalItems = sorted.length;
    //   $scope.totalPages = Math.ceil($scope.totalItems / $scope.itemsPerPage);
    //   $scope.dispatchers = sorted.slice(
    //     ($scope.currentPage - 1) * $scope.itemsPerPage,
    //     $scope.currentPage * $scope.itemsPerPage
    //   );
    // };

    $scope.prevPage = function () {
      if ($scope.currentPage > 1) {
        $scope.currentPage--;
        $scope.loadDispatchers();
      }
    };

    $scope.nextPage = function () {
      if ($scope.currentPage < $scope.totalPages) {
        $scope.currentPage++;
        $scope.loadDispatchers();
      }
    };
    $scope.setPage = function (page) {
      $scope.currentPage = page;
      $scope.loadDispatchers();
    };

    $scope.getPageRange = function () {
      var pages = [];
      for (var i = 1; i <= $scope.totalPages; i++) {
        pages.push(i);
      }
      return pages;
    };

    $scope.saveDispatcher = function () {
      if ($scope.newDispatcher.id) {
        DispatcherService.update(
          $scope.newDispatcher.id,
          $scope.newDispatcher
        ).then(
          function (response) {
            $scope.loadDispatchers();
            $scope.newDispatcher = {};
            Swal.fire({
              title: "Updated!",
              text: "Dispatcher has been updated successfully.",
              icon: "success",
              confirmButtonText: "OK",
            });
            $location.path("/dispatcher");
          },
          function (error) {
            console.error("Error updating dispatcher:", error);
            Swal.fire({
              title: "Error!",
              text: "There was an error updating the dispatcher.",
              icon: "error",
              confirmButtonText: "OK",
            });
          }
        );
      } else {
        DispatcherService.create($scope.newDispatcher).then(
          function (response) {
            $scope.loadDispatchers();
            $scope.newDispatcher = {};
            Swal.fire({
              title: "Created!",
              text: "Dispatcher has been created successfully.",
              icon: "success",
              confirmButtonText: "OK",
            });
            $location.path("/dispatcher");
          },
          function (error) {
            console.error("Error creating dispatcher:", error);
            Swal.fire({
              title: "Error!",
              text: "There was an error creating the dispatcher.",
              icon: "error",
              confirmButtonText: "OK",
            });
          }
        );
      }
    };

    $scope.editDispatcher = function (dispatcher) {
      $scope.newDispatcher = angular.copy(dispatcher);
      $location.path("/dispatchers/edit/" + dispatcher.id);
    };

    $scope.deleteDispatcher = function (id) {
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.isConfirmed) {
          DispatcherService.delete(id).then(
            function (response) {
              $scope.loadDispatchers();
              Swal.fire(
                "Deleted!",
                "Your dispatcher has been deleted.",
                "success"
              );
            },
            function (error) {
              console.error("Error deleting dispatcher:", error);
            }
          );
        }
      });
    };

    if ($routeParams.id) {
      DispatcherService.get($routeParams.id).then(
        function (response) {
          $scope.newDispatcher = response.data;
        },
        function (error) {
          console.error("Error fetching dispatcher:", error.data);
        }
      );
    }

    $scope.isEditing = false; // Flag to determine if the fields are being edited

    $scope.checkUniqueFieldsEmail = function () {
      if (!$scope.isEditing) return; // Skip check if not editing
      DispatcherService.checkUniqueEmail($scope.newDispatcher.dis_email).then(
        function (response) {
          const data = response.data;
          if (data.status === "success") {
            $scope.message1 = "";
          } else {
            $scope.message1 = data.message;
          }
        },
        function (error) {
          console.log("Error checking email uniqueness:", error.data);
        }
      );
    };

    $scope.checkUniqueFieldsName = function () {
      if (!$scope.isEditing) return; // Skip check if not editing
      DispatcherService.checkUniqueName($scope.newDispatcher.dis_name).then(
        function (response) {
          const data = response.data;
          if (data.status === "success") {
            $scope.message2 = "";
          } else {
            $scope.message2 = data.message;
          }
        },
        function (error) {
          console.error("Error checking name uniqueness:", error);
        }
      );
    };

    $scope.checkUniqueFieldsPhone = function () {
      if (!$scope.isEditing) return; // Skip check if not editing
      DispatcherService.checkUniquePhone($scope.newDispatcher.dis_phone).then(
        function (response) {
          const data = response.data;
          if (data.status === "success") {
            $scope.message3 = "";
          } else {
            $scope.message3 = data.message;
          }
        },
        function (error) {
          console.log("Error checking phone uniqueness:", error.data);
        }
      );
    };

    $scope.$watch("newDispatcher.dis_name", function (newVal, oldVal) {
      if (oldVal && newVal !== oldVal) {
        $scope.isEditing = true;
        $scope.checkUniqueFieldsName();
      }
    });

    $scope.$watch("newDispatcher.dis_email", function (newVal, oldVal) {
      if (oldVal && newVal !== oldVal) {
        $scope.isEditing = true;
        $scope.checkUniqueFieldsEmail();
      }
    });

    $scope.$watch("newDispatcher.dis_phone", function (newVal, oldVal) {
      if (oldVal && newVal !== oldVal) {
        $scope.isEditing = true;
        $scope.checkUniqueFieldsPhone();
      }
    });

    $scope.loadDispatchers();
  },
]);
