angular
  .module("truckingApp")
  .controller("DriverController", [
    "$scope",
    "$location",
    "$routeParams",
    "AuthService",
    "DriverService",
    function ($scope, $location, $routeParams, AuthService, DriverService) {
      $scope.drivers = [];
      $scope.newDrivers = {};
      $scope.message = {
        email: "",
        phone: "",
        name: "",
        license: "",
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
        $location.path("/driver/create");
      };
      $scope.redirecttoback = function () {
        $location.path("/driver");
      };
      $scope.regex =
        "^(([A-Z]{2}[0-9]{2})( )|([A-Z]{2}-[0-9]{2}))((19|20)[0-9][0-9])[0-9]{7}$";
      $scope.loadDrivers = function () {
        var params = {
          search: $scope.queryText,
          sort: $scope.sortKey,
          order: $scope.sortReverse ? "desc" : "asc",
          page: $scope.currentPage,
          limit: $scope.itemsPerPage,
        };
        DriverService.getAll(params).then(
          function (response) {
            $scope.drivers = response.data.data.data;
            $scope.totalItems = response.data.data.total;
            $scope.totalPages = Math.ceil(
              $scope.totalItems / $scope.itemsPerPage
            );
          },
          function (error) {
            console.error("Error fetching drivers:", error);
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
        $scope.loadDrivers();
      };
      // $scope.applyFilters = function () {
      //   const filtered = $scope.drivers.filter((driver) => {
      //     return (
      //       driver.dri_name
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       driver.dri_email
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       driver.dri_phone
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       driver.license_number
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       driver.license_expiry_date
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       driver.dis_name
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       driver.status.toLowerCase().includes($scope.queryText.toLowerCase())
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
      //   $scope.filteredDrivers = sorted.slice(
      //     ($scope.currentPage - 1) * $scope.itemsPerPage,
      //     $scope.currentPage * $scope.itemsPerPage
      //   );
      // };

      $scope.prevPage = function () {
        if ($scope.currentPage > 1) {
          $scope.currentPage--;
          $scope.loadDrivers();
        }
      };

      $scope.nextPage = function () {
        if ($scope.currentPage < $scope.totalPages) {
          $scope.currentPage++;
          $scope.loadDrivers();
        }
      };
      $scope.setPage = function (page) {
        $scope.currentPage = page;
        $scope.loadDrivers();
      };

      $scope.getPageRange = function () {
        var pages = [];
        for (var i = 1; i <= $scope.totalPages; i++) {
          pages.push(i);
        }
        return pages;
      };
      $scope.loadDispatchers = function () {
        DriverService.getAllDispatcher().then(
          function (response) {
            $scope.dispatchers = response.data;
          },
          function (error) {
            console.error("Error fetching dispatchers:", error);
          }
        );
      };
      $scope.saveDrivers = function () {
        if ($scope.newDrivers.id) {
          console.log("updating function");
          DriverService.update($scope.newDrivers.id, $scope.newDrivers).then(
            function (response) {
              $scope.loadDrivers();
              $scope.newDrivers = {};
              console.log("hii", response);
              Swal.fire({
                title: "Updated!",
                text: "Driver has been updated successfully.",
                icon: "success",
                confirmButtonText: "OK",
              });
              $location.path("/driver");
            },
            function (error) {
              console.error("Error updating driver:", error);
              Swal.fire({
                title: "Error!",
                text: "There was an error updating the drivers.",
                icon: "error",
                confirmButtonText: "OK",
              });
            }
          );
        } else {
          console.log("inserting function");
          DriverService.create($scope.newDrivers).then(
            function (response) {
              $scope.loadDrivers();
              $scope.newDrivers = {};
              Swal.fire({
                title: "Created!",
                text: "Driver has been created successfully.",
                icon: "success",
                confirmButtonText: "OK",
              });
              console.log("object inserted ");
              $location.path("/driver");
            },
            function (error) {
              console.error("Error creating Driver:", error);
              Swal.fire({
                title: "Error!",
                text: "There was an error creating the driver.",
                icon: "error",
                confirmButtonText: "OK",
              });
            }
          );
        }
      };

      $scope.editDriver = function (driver) {
        $scope.newDrivers = angular.copy(driver);
        $location.path("/driver/edit/" + driver.id);
      };
      $scope.deleteDriver = function (id) {
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
            DriverService.delete(id).then(
              function (response) {
                $scope.loadDrivers();
                Swal.fire(
                  "Deleted!",
                  "Your driver has been deleted.",
                  "success"
                );
              },
              function (error) {
                console.error("Error deleting driver:", error);
              }
            );
          }
        });
      };

      if ($routeParams.id) {
        DriverService.get($routeParams.id).then(
          function (response) {
            $scope.newDrivers = response.data;
            console.log("data", $scope.newDrivers);
          },
          function (error) {
            console.error("Error fetching drivers:", error.data);
          }
        );
      }
      $scope.isEditing = false;

      $scope.checkUniqueFieldsEmail = function () {
        if (!$scope.isEditing) return;

        DriverService.checkUniqueEmail($scope.newDrivers.dri_email).then(
          function (response) {
            const data = response.data;
            console.log(data.email);
            console.log("object", data.message);
            if (data.status === "success") {
              console.log("Email is available.");
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
      $scope.checkUniqueFieldsName = function () {
        if (!$scope.isEditing) return;

        DriverService.checkUniqueName($scope.newDrivers.dri_name)
          .then(function (response) {
            const data = response.data;
            console.log("ghf===", response.data);
            console.log("===", data.name);
            console.log("object", data.message);
            if (data.status === "success") {
              console.log("Name is available.");
              $scope.message2 = "";
            } else {
              if ($scope.isEditing) {
                $scope.message2 = data.message;
                console.log("===", $scope.message2);
              }
            }
          })
          .catch(function (error) {
            console.error("Error checking name uniqueness:", error);
          });
      };
      $scope.checkUniqueFieldsPhone = function () {
        if (!$scope.isEditing) return;

        DriverService.checkUniquePhone($scope.newDrivers.dri_phone).then(
          function (response) {
            const data = response.data;
            console.log("ghf===", response.data);
            console.log("object", data.message);
            if (data.status === "success") {
              console.log("Phone is available.");
              $scope.message3 = "";
            } else {
              if ($scope.isEditing) {
                $scope.message3 = data.message;
                console.log("===", $scope.message3);
              }
            }
          },
          function (error) {
            console.log("666g", error.data);
          }
        );
      };
      $scope.checkUniqueFieldsLicense = function () {
        if (!$scope.isEditing) return;

        DriverService.checkUniqueLicense($scope.newDrivers.license_number).then(
          function (response) {
            const data = response.data;
            console.log("ghf===", response.data);
            console.log("object", data.message);
            if (data.status === "success") {
              console.log("License Number is available.");
              $scope.message4 = "";
            } else {
              if ($scope.isEditing) {
                $scope.message4 = data.message;
                console.log("===", $scope.message4);
              }
            }
          },
          function (error) {
            console.log("666g", error.data);
          }
        );
      };
      $scope.$watch("newDrivers.dri_name", function (newVal, oldVal) {
        if (oldVal && newVal !== oldVal) {
          $scope.isEditing = true;
          $scope.checkUniqueFieldsName();
        }
      });
      $scope.$watch("newDrivers.dri_email", function (oldVal, newVal) {
        if (oldVal && newVal !== oldVal) {
          $scope.isEditing = true;
          $scope.checkUniqueFieldsEmail();
        }
      });
      $scope.$watch("newDrivers.dri_phone", function (oldVal, newVal) {
        if (oldVal && newVal !== oldVal) {
          $scope.isEditing = true;
          $scope.checkUniqueFieldsPhone();
        }
      });
      $scope.$watch("newDrivers.license_number", function (oldVal, newVal) {
        if (oldVal && newVal !== oldVal) {
          $scope.isEditing = true;
          $scope.checkUniqueFieldsLicense();
        }
      });
      // $scope.isFormValid = function () {
      //   return !$scope.message1 && !$scope.message2 && !$scope.message3;
      // };
      $scope.loadDrivers();
      $scope.loadDispatchers();
    },
  ])
  .directive("datepicker1", function () {
    return {
      restrict: "A",
      link: function (scope, element, attrs) {
        $(element).datepicker({
          dateFormat: "yy-mm-dd",
          onSelect: function (dateText) {
            scope.$apply(function () {
              scope.newDrivers.license_expiry_date = dateText;
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
