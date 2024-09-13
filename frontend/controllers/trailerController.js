angular
  .module("truckingApp")
  .controller("TrailerController", [
    "$scope",
    "$location",
    "$routeParams",
    "AuthService",
    "TrailerService",
    function ($scope, $location, $routeParams, AuthService, TrailerService) {
      $scope.trailers = [];
      $scope.newTrailers = {};
      $scope.message = {
        trailer: "",
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
        $location.path("/trailer/create");
      };
      $scope.redirecttoback = function () {
        $location.path("/trailer");
      };
      $scope.loadTrailers = function () {
        var params = {
          search: $scope.queryText,
          sort: $scope.sortKey,
          order: $scope.sortReverse ? "desc" : "asc",
          page: $scope.currentPage,
          limit: $scope.itemsPerPage,
        };
        TrailerService.getAll(params).then(
          function (response) {
            $scope.trailers = response.data.data;
            $scope.totalItems = response.data.total;
            $scope.totalPages = Math.ceil(
              $scope.totalItems / $scope.itemsPerPage
            );
          },
          function (error) {
            console.error("Error fetching trailers:", error);
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
        $scope.loadTrailers();
      };
      // $scope.applyFilters = function () {
      //   const filtered = $scope.trailers.filter((trailer) => {
      //     return (
      //       trailer.trailer_number
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       trailer.model
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       trailer.capacity
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       trailer.registration_date
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       trailer.trailer_type
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       trailer.status
      //         .toLowerCase()
      //         .includes($scope.queryText.toLowerCase()) ||
      //       trailer.truck_number
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
      //   $scope.filteredTrailers = sorted.slice(
      //     ($scope.currentPage - 1) * $scope.itemsPerPage,
      //     $scope.currentPage * $scope.itemsPerPage
      //   );
      // };

      $scope.prevPage = function () {
        if ($scope.currentPage > 1) {
          $scope.currentPage--;
          $scope.loadTrailers();
        }
      };

      $scope.nextPage = function () {
        if ($scope.currentPage < $scope.totalPages) {
          $scope.currentPage++;
          $scope.loadTrailers();
        }
      };
      $scope.setPage = function (page) {
        $scope.currentPage = page;
        $scope.loadTrailers();
      };

      $scope.getPageRange = function () {
        var pages = [];
        for (var i = 1; i <= $scope.totalPages; i++) {
          pages.push(i);
        }
        return pages;
      };
      $scope.loadTrucks = function () {
        TrailerService.getAllTrucks().then(
          function (response) {
            $scope.trucks = response.data;
          },
          function (error) {
            console.error("Error fetching trucks:", error);
          }
        );
      };
      $scope.saveTrailers = function () {
        if ($scope.newTrailers.id) {
          TrailerService.update($scope.newTrailers.id, $scope.newTrailers).then(
            function (response) {
              $scope.loadTrailers();
              $scope.newTrailers = {};
              console.log("hii", response);
              Swal.fire({
                title: "Updated!",
                text: "Trailer has been updated successfully.",
                icon: "success",
                confirmButtonText: "OK",
              });
              $location.path("/trailer");
            },
            function (error) {
              console.error("Error updating Trailer:", error);
              Swal.fire({
                title: "Error!",
                text: "There was an error updating the trailers.",
                icon: "error",
                confirmButtonText: "OK",
              });
            }
          );
        } else {
          TrailerService.create($scope.newTrailers).then(
            function (response) {
              $scope.loadTrailers();
              $scope.newTrailers = {};
              Swal.fire({
                title: "Created!",
                text: "Trailer has been created successfully.",
                icon: "success",
                confirmButtonText: "OK",
              });
              $location.path("/trailer");
            },
            function (error) {
              console.error("Error creating Trailer:", error);
              Swal.fire({
                title: "Error!",
                text: "There was an error creating the trailer.",
                icon: "error",
                confirmButtonText: "OK",
              });
            }
          );
        }
      };

      $scope.editTrailer = function (trailers) {
        $scope.newTrailers = angular.copy(trailers);
        $location.path("/trailer/edit/" + trailers.id);
      };
      $scope.deleteTrailer = function (id) {
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
            TrailerService.delete(id).then(
              function (response) {
                $scope.loadTrailers();
                Swal.fire(
                  "Deleted!",
                  "Your trailer has been deleted.",
                  "success"
                );
              },
              function (error) {
                console.error("Error deleting trailer:", error);
              }
            );
          }
        });
      };

      if ($routeParams.id) {
        TrailerService.get($routeParams.id).then(
          function (response) {
            $scope.newTrailers = response.data;
          },
          function (error) {
            console.error("Error fetching trailers:", error.data);
          }
        );
      }
      $scope.isEditing = false;
      $scope.checkUniqueFieldsTrailer = function () {
        if (!$scope.isEditing) return;
        TrailerService.checkUniqueTrailer(
          $scope.newTrailers.trailer_number
        ).then(
          function (response) {
            const data = response.data;
            console.log("ghf===", data.trailer);
            console.log("object", data.message);
            if (data.status === "success") {
              console.log("Trailer Number is available.");
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
      $scope.$watch("newTrailers.trailer_number", function (oldVal, newVal) {
        if (oldVal && newVal !== oldVal) {
          $scope.isEditing = true;
          $scope.checkUniqueFieldsTrailer();
        }
      });
      // $scope.isFormValid = function () {
      //   return !$scope.message1 && !$scope.message2 && !$scope.message3;
      // };
      $scope.loadTrailers();
      $scope.loadTrucks();
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
              if (scope.newTrailers) {
                console.log("Selected date:", dateText);
                scope.newTrailers.registration_date = dateText;
              } else {
                console.warn("newTrailers is undefined");
              }
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
