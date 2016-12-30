define([
  // Standard Libs
  'routes/routes'

], function (routes) {
  "use strict";

  var appCtrl = ['$scope',function ($scope) {
    $scope.navigation = routes;

  }];

  return appCtrl;
});
