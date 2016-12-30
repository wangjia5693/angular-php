/**
 * Created by Administrator on 2016/11/30.
 */
define([
    //标准库
    'underscore'
    ,'console'
    ,'jquery'

], function (_,console,$) {
    "use strict";

    var appCtrl = ['$scope', '$rootScope', '$uibModal', 'Auth', 'AUTH_EVENTS','USER_ROLES','Nodes','wrongService','tipService','$window',
        function($scope, $rootScope, $modal, Auth, AUTH_EVENTS, USER_ROLES,Nodes,wrongService,tipService,$window){
            //一直显示的提示框
            $rootScope.tipService = wrongService;
            //需要一个模态错误提示框
            $rootScope.mlService = tipService;

            //是否为调试状态
            $rootScope.is_debug = true;
            $rootScope.nav_side = false;
            $rootScope.taggle_nav = function(){
               if($scope.nav_side==false){
                   $rootScope.nav_side = true;
                   $('.dom_contain').removeClass("full_screen").addClass('erp_content');
               }else{
                   $rootScope.nav_side = false;
                   $('.dom_contain').removeClass("erp_content").addClass('full_screen');
               }
            }
            $rootScope.nodehide = function(){
                $rootScope.nav_side = false;
                $('.dom_contain').removeClass("erp_content").addClass('full_screen');
            }


            $rootScope.nodeFresh = function(){
                $window.location.reload();
            }

            //加载所有nodes
            Nodes.all();
            //// 所有控制器的父控制器，处理控制器与登录处理，其他控制器继承此控制器
            $scope.modalShown = false;
            $scope.currentUser = false;
            $rootScope.debug = false;
            var showLoginDialog = function() {
                $scope.currentUser = false;
                if(!$scope.modalShown){
                    $scope.modalShown = true;
                    var modalInstance = $modal.open({
                        templateUrl : 'Web/js/views/login.tpl.html',
                        controller : "LoginCtrl",
                        backdrop : 'static',
                    });
                    modalInstance.result.then(function() {
                        $scope.modalShown = false;

                    });
                }
            };

            var setCurrentUser = function(){
                $scope.currentUser = $rootScope.currentUser;
            }

            var showNotAuthorized = function(){
                alert("Not Authorized");
            }
            $scope.userRoles = USER_ROLES;
            $scope.isAuthorized = Auth.isAuthorized;


            //监听登录状态
            $rootScope.$on(AUTH_EVENTS.notAuthorized, showNotAuthorized);
            $rootScope.$on(AUTH_EVENTS.notAuthenticated, showLoginDialog);
            $rootScope.$on(AUTH_EVENTS.sessionTimeout, showLoginDialog);
            $rootScope.$on(AUTH_EVENTS.logoutSuccess, showLoginDialog);
            $rootScope.$on(AUTH_EVENTS.loginSuccess, setCurrentUser);

        } ];

    return appCtrl;
});

