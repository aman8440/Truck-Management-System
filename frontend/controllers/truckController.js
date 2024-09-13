angular
  .module("truckingApp")
  .controller("TruckController", [
    "$scope",
    "$location",
    "$routeParams",
    "AuthService",
    "TruckService",
    function ($scope, $location, $routeParams, AuthService, TruckService) {
      $scope.trucks = [];
      $scope.newTrucks = {};
      $scope.message = {
        truck: "",
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
        $location.path("/truck/create");
      };
      $scope.redirecttoback = function () {
        $location.path("/truck");
      };
      $scope.loadTrucks = function () {
        var params = {
          search: $scope.queryText,
          sort: $scope.sortKey,
          order: $scope.sortReverse ? "desc" : "asc",
          page: $scope.currentPage,
          limit: $scope.itemsPerPage,
        };
        TruckService.getAll(params).then(
          function (response) {
            $scope.trucks = response.data.data;
            $scope.totalItems = response.data.total;
            $scope.totalPages = Math.ceil(
              $scope.totalItems / $scope.itemsPerPage
            );
          },
          function (error) {
            console.error("Error fetching trucks:", error);
          }
        );
      };
      $scope.sort = function (keyname) {
        if ($scope.sortKey === keyname) {
          $scope.sortReverse = !$scope.sortReverse; // if true make it false and vice versa
        } else {
          $scope.sortKey = keyname;
          $scope.sortReverse = false; // set the default sort order
        }
        $scope.loadTrucks();
      };
      // $scope.applyFilters = function () {
      //   const filtered = $scope.trucks.filter((truck) => {
      //     return (
      //       truck.truck_number
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       truck.model
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       truck.capacity
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       truck.truck_milege
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       truck.registration_date
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       truck.status
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       truck.dri_name
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       truck.status.toLowerCase().includes($scope.queryText.toLowerCase())
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
      //   $scope.filteredTrucks = sorted.slice(
      //     ($scope.currentPage - 1) * $scope.itemsPerPage,
      //     $scope.currentPage * $scope.itemsPerPage
      //   );
      // };

      $scope.prevPage = function () {
        if ($scope.currentPage > 1) {
          $scope.currentPage--;
          $scope.loadTrucks();
        }
      };

      $scope.nextPage = function () {
        if ($scope.currentPage < $scope.totalPages) {
          $scope.currentPage++;
          $scope.loadTrucks();
        }
      };
      $scope.setPage = function (page) {
        $scope.currentPage = page;
        $scope.loadTrucks();
      };

      $scope.getPageRange = function () {
        var pages = [];
        for (var i = 1; i <= $scope.totalPages; i++) {
          pages.push(i);
        }
        return pages;
      };
      $scope.loadDrivers = function () {
        TruckService.getAllDrivers().then(
          function (response) {
            $scope.drivers = response.data;
          },
          function (error) {
            console.error("Error fetching drivers:", error);
          }
        );
      };
      $scope.saveTrucks = function () {
        if ($scope.newTrucks.id) {
          TruckService.update($scope.newTrucks.id, $scope.newTrucks).then(
            function (response) {
              $scope.loadTrucks();
              $scope.newTrucks = {};
              console.log("hii", response);
              Swal.fire({
                title: "Updated!",
                text: "Truck has been updated successfully.",
                icon: "success",
                confirmButtonText: "OK",
              });
              $location.path("/truck");
            },
            function (error) {
              console.error("Error updating Truck:", error);
              Swal.fire({
                title: "Error!",
                text: "There was an error updating the trucks.",
                icon: "error",
                confirmButtonText: "OK",
              });
            }
          );
        } else {
          TruckService.create($scope.newTrucks).then(
            function (response) {
              $scope.loadTrucks();
              $scope.newTrucks = {};
              Swal.fire({
                title: "Created!",
                text: "Truck has been created successfully.",
                icon: "success",
                confirmButtonText: "OK",
              });
              $location.path("/truck");
            },
            function (error) {
              console.error("Error creating Truck:", error);
              Swal.fire({
                title: "Error!",
                text: "There was an error creating the truck.",
                icon: "error",
                confirmButtonText: "OK",
              });
            }
          );
        }
      };

      $scope.editTruck = function (trucks) {
        $scope.newTrucks = angular.copy(trucks);
        $location.path("/truck/edit/" + trucks.id);
      };
      $scope.deleteTruck = function (id) {
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
            TruckService.delete(id).then(
              function (response) {
                $scope.loadTrucks();
                Swal.fire(
                  "Deleted!",
                  "Your truck has been deleted.",
                  "success"
                );
              },
              function (error) {
                console.error("Error deleting truck:", error);
              }
            );
          }
        });
      };

      if ($routeParams.id) {
        TruckService.get($routeParams.id).then(
          function (response) {
            $scope.newTrucks = response.data;
          },
          function (error) {
            console.error("Error fetching trucks:", error.data);
          }
        );
      }
      $scope.isEditing = false;
      $scope.checkUniqueFieldsTruck = function () {
        if (!$scope.isEditing) return;
        TruckService.checkUniqueTruck($scope.newTrucks.truck_number).then(
          function (response) {
            const data = response.data;
            console.log("ghf===", data.truck);
            console.log("object", data.message);
            if (data.status === "success") {
              console.log("Truck Number is available.");
              $scope.message1 = "";
            } else {
              if ($scope.isEditing) {
                $scope.message1 = data.message;
                console.log("===", $scope.message1);
              }
            }
          },
          function (error) {
            console.log("666g", error.data);
          }
        );
      };
      $scope.$watch("newTrucks.truck_number", function (oldVal, newVal) {
        if (oldVal && newVal !== oldVal) {
          $scope.isEditing = true;
          $scope.checkUniqueFieldsTruck();
        }
      });
      // $scope.isFormValid = function () {
      //   return !$scope.message1 && !$scope.message2 && !$scope.message3;
      // };
      $scope.loadTrucks();
      $scope.loadDrivers();
    },
  ])
  .directive("datepicker", function () {
    return {
      restrict: "A",
      link: function (scope, element, attrs) {
        $(element).datepicker({
          dateFormat: "yy-mm-dd",
          onSelect: function (dateText) {
            scope.$apply(function () {
              scope.newTrucks.registration_date = dateText;
            });
          },
        });

        $(element).hover(
          function () {
            $(this).datepicker("show");
          },
          function () {}
        );
      },
    };
  });
