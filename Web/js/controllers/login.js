/**
 * Created by Administrator on 2016/11/30.
 */
define([
    // Standard Libs
    'underscore'
    ,'console'
], function (_,console) {
    "use strict";
    var appCtrl = [ '$scope', '$state', '$uibModalInstance' , '$window', 'Auth','$timeout','cfpLoadingBar',
        function($scope, $state, $modalInstance, $window, Auth,$timeout, cfpLoadingBar ) {
            $scope.credentials = {};
            $scope.loginForm = {};
            $scope.error = false;
            //表单提交
            $scope.submit = function() {
                //loading显示隐藏
                //cfpLoadingBar.start();
                //cfpLoadingBar.complete();
                $scope.submitted = true;
                if (!$scope.loginForm.$invalid) {
                    $scope.login($scope.credentials);
                } else {
                    $scope.error = true;
                    return;
                }
            };

            //登录
            $scope.login = function(credentials) {
                $scope.error = false;
                Auth.login(credentials, function(user) {
                    //console.info(user);
                    $modalInstance.close();
                    $state.go('home');
                }, function(err) {
                    $scope.error = true;
                    console.info(err);
                    $scope.alerts = [
                        { type: 'success', msg: err.msg }
                    ];
                });
            };

            // 如果当前用户已登录
            if ($window.sessionStorage["userInfo"]) {
                var credentials = JSON.parse($window.sessionStorage["userInfo"]);
                $scope.login(credentials);
            }
        } ];

    return appCtrl;
});