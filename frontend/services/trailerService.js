angular.module("truckingApp").factory("TrailerService", [
  "$http",
  "AuthService",
  function ($http, AuthService) {
    const apiUrl = "http://localhost/truck_management/api/trailer";
    function getAuthHeaders() {
      const token = AuthService.getToken();
      return {
        Authorization: `Bearer ${token}`,
      };
    }
    return {
      getAll: function (params) {
        return $http
          .get(apiUrl,{params:params})
          .then(handleSuccess, handleError);
      },
      get: function (id) {
        return $http.get(`${apiUrl}/${id}`,{
          headers: getAuthHeaders(),
        }).then(handleSuccess, handleError);
      },
      create: function (truck) {
        return $http
          .post(`${apiUrl}/create`, truck,{
            headers: getAuthHeaders(),
          })
          .then(handleSuccess, handleError);
      },
      update: function (id, truck) {
        return $http
          .put(`${apiUrl}/update/${id}`, truck, {
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
      checkUniqueTrailer: function(trailer){
        return $http.post(`${apiUrl}/checkTrailer`,{
          trailer_number:trailer,
        }).then(handleSuccess, handleError);
      },
      getAllTrucks: function () {
        return $http
          .get("http://localhost/truck_management/api/trucks/all", {
            headers: getAuthHeaders(),
          })
          .then(handleSuccess, handleError);
      },
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
