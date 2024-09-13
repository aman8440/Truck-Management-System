angular.module("truckingApp").factory("DispatcherService", [
  "$http",
  "AuthService",
  function ($http, AuthService) {
    const apiUrl = "http://localhost/truck_management/api/dispatcher";

    function getAuthHeaders() {
      const token = AuthService.getToken();
      return {
        Authorization: `Bearer ${token}`,
      };
    }

    return {
      getAll: function (params) {
        return $http
          .get(apiUrl,{ params: params })
          .then(handleSuccess, handleError);
      },
      get: function (id) {
        return $http.get(`${apiUrl}/${id}`).then(handleSuccess, handleError);
      },
      create: function (dispatcher) {
        return $http
          .post(`${apiUrl}/create`, dispatcher, {
            headers: getAuthHeaders(),
          })
          .then(handleSuccess, handleError);
      },
      update: function (id, dispatcher) {
        return $http
          .put(`${apiUrl}/update/${id}`, dispatcher, {
            headers: getAuthHeaders(),
          })
          .then(handleSuccess, handleError);
      },
      delete: function (id) {
        return $http
          .delete(`${apiUrl}/delete/${id}`, {
            headers: getAuthHeaders(),
          })
          .then(handleSuccess, handleError);
      },
      checkUniqueEmail: function(email){
        return $http.post(`${apiUrl}/checkEmail`,{
          dis_email:email,
        }).then(handleSuccess, handleError);
      },
      checkUniqueName: function(name){
        return $http.post(`${apiUrl}/checkName`,{
          dis_name:name,
        }).then(handleSuccess, handleError);
      },
      checkUniquePhone: function(phone){
        return $http.post(`${apiUrl}/checkPhone`,{
          dis_phone:phone,
        }).then(handleSuccess, handleError);
      }
    };
    function handleSuccess(response) {
      return response;
    }

    function handleError(error) {
      console.error("API request error:", error);
      throw error;
    }
  },
]);
