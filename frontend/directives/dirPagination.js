(function () {
  "use strict";

  var paginationModule = angular.module(
    "angularUtils.directives.dirPagination",
    []
  );

  paginationModule.directive("dirPaginate", [
    "$compile",
    "$parse",
    function ($compile, $parse) {
      return {
        terminal: true,
        multiElement: true,
        compile: function (element, attrs) {
          attrs.$set("dir-paginate-start", "");
          var expression = attrs.dirPaginate;
          var ngRepeatEndComment = document.createComment(
            " end dirPaginate: " + expression + " "
          );
          element.after(ngRepeatEndComment);
          element.attr("ng-repeat", expression);
          return function (scope, element, attrs) {
            $compile(element)(scope);
          };
        },
      };
    },
  ]);

  paginationModule.directive("dirPaginationControls", function () {
    return {
      restrict: "AE",
      templateUrl: function (element, attrs) {
        return attrs.templateUrl || "dirPagination.tpl.html";
      },
      scope: {
        maxSize: "=?",
        onPageChange: "&?",
        paginationId: "=?",
      },
      controller: [
        "$scope",
        "paginationService",
        function ($scope, paginationService) {
          var paginationId = $scope.paginationId || "__default";
          var paginationRange = [];
          $scope.currentPage = 1;

          $scope.pages = function () {
            var pages = [];
            var totalPages =
              paginationService.getCollectionLength(paginationId);
            var startPage = Math.max(
              1,
              $scope.currentPage - Math.floor($scope.maxSize / 2)
            );
            var endPage = Math.min(totalPages, startPage + $scope.maxSize - 1);
            for (var i = startPage; i <= endPage; i++) {
              pages.push(i);
            }
            return pages;
          };

          $scope.$watch("currentPage", function (currentPage) {
            if ($scope.onPageChange) {
              $scope.onPageChange({ newPageNumber: currentPage });
            }
          });

          $scope.setCurrentPage = function (page) {
            $scope.currentPage = page;
          };

          paginationService.registerInstance(paginationId, $scope);
        },
      ],
    };
  });

  paginationModule.service("paginationService", function () {
    var instances = {};
    this.registerInstance = function (id, scope) {
      instances[id] = scope;
    };
    this.getCollectionLength = function (id) {
      return instances[id].$parent[instances[id].itemsPerPage];
    };
  });
})();
