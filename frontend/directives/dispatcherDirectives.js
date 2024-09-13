angular.module('truckingApp').directive('uniqueField', ['$q', 'DispatcherService', function($q, DispatcherService) {
  return {
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      ngModel.$asyncValidators.unique = function(modelValue) {
        var deferred = $q.defer();
        DispatcherService.checkUnique(scope.newDispatcher.dis_email, scope.newDispatcher.dis_phone, scope.newDispatcher.dis_name)
          .then(function(response) {
            if (response.data.status === 'success') {
              deferred.resolve();
            } else {
              deferred.reject('This value is already taken');
            }
          })
          .catch(function(error) {
            deferred.reject('Error checking uniqueness');
          });
        return deferred.promise;
      };
    }
  };
}]);