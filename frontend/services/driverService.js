angular.module("truckingApp").factory("DriverService", [
  "$http",
  "AuthService",
  function ($http, AuthService) {
    const apiUrl = "http://localhost/truck_management/api/driver";
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
        return $http.get(`${apiUrl}/${id}`,{
          headers: getAuthHeaders(),
        }).then(handleSuccess, handleError);
      },
      create: function (driver) {
        return $http
          .post(`${apiUrl}/create`, driver,{
            headers: getAuthHeaders(),
          })
          .then(handleSuccess, handleError);
      },
      update: function (id, driver) {
        return $http
          .put(`${apiUrl}/update/${id}`, driver, {
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
          dri_email:email,
        },{
          headers: getAuthHeaders(),
        }).then(handleSuccess, handleError);
      },
      checkUniqueName: function(name){
        return $http.post(`${apiUrl}/checkName`,{
          dri_name:name,
        }, {
          headers: getAuthHeaders(),
        }).then(handleSuccess, handleError);
      },
      checkUniquePhone: function(phone){
        return $http.post(`${apiUrl}/checkPhone`,{
          dri_phone:phone,
        }, {
          headers: getAuthHeaders(),
        }).then(handleSuccess, handleError);
      },
      checkUniqueLicense: function(license){
        return $http.post(`${apiUrl}/checkLicense`,{
          license_number:license,
        }, {
          headers: getAuthHeaders(),
        }).then(handleSuccess, handleError);
      },
      getAllDispatcher: function () {
        return $http
          .get("http://localhost/truck_management/api/dispatcher/all", {
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
