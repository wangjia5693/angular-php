/**
 * Created by Administrator on 2016/12/7.
 */
define([   'underscore'
    ,'console','angular',  'uiRouter', 'btuiTpli', 'routes/routes', 'services/auth','ngTable','angularFileUpload','angularPrint','uiRouterTabs'], function (_,Console,angular,uiRouter,btuiTpli,routes,Auth,ngTable,angularFileUpload,angularPrint,uiRouterTabs) {

    var mainModule = angular.module('myApp',['ui.router', 'ui.bootstrap','chieffancypants.loadingBar', 'ngAnimate','ngTable','tm.pagination','angularFileUpload','AngularPrint','ui.router.tabs','angular-confirm','toastr']);

    mainModule.config(function ($controllerProvider, $compileProvider, $filterProvider, $provide, $stateProvider) {
        mainModule.register = {
            //得到$controllerProvider的引用
            controller: $controllerProvider.register,
            //同样的，这里也可以保存directive／filter／service的引用
            directive: $compileProvider.directive,
            filter: $filterProvider.register,
            service: $provide.service,
            factory: $provide.factory,
            stateProvider: $stateProvider
        };
    })

    //异步promise模块
    var ctrlRegister = function(ctrlName, ctrlModule,$controllerProvider) {
        return function ($q) {
            var defer = $q.defer();
            require(ctrlModule, function (controller) {
                defer.resolve();
            });
            return defer.promise;
        }
    }

    mainModule.constant('USER_ROLES', {
        all : '*',
        admin : 'admin',
        editor : 'editor',
        guest : 'guest'
    });
    mainModule.constant('AUTH_EVENTS', {
            loginSuccess : 'auth-login-success',
            loginFailed : 'auth-login-failed',
            logoutSuccess : 'auth-logout-success',
            sessionTimeout : 'auth-session-timeout',
            notAuthenticated : 'auth-not-authenticated',
            notAuthorized : 'auth-not-authorized'
        })
        .config(function ($httpProvider) {//每一个$http请求都添加auth interceptor
            $httpProvider.interceptors.push([
                '$injector',
                function ($injector) {
                    return $injector.get('AuthInterceptor');
                }
            ]);
        });
    // 启动路由
    mainModule.config(['$stateProvider', '$urlRouterProvider', 'USER_ROLES','$controllerProvider',
        function($stateProvider, $urlRouterProvider, USER_ROLES,$controllerProvider) {
            $urlRouterProvider.otherwise("/");
            _.each(routes, function(value, key) {
                $stateProvider.state(
                    value.route
                    , {
                        url:key
                        , templateUrl: value.templateUrl
                        , controller: value.controller
                        , title: value.title
                        , data: value.data
                        ,  resolve:{
                            delay : ctrlRegister(value.controller,[value.controller_url],$controllerProvider)
                        }
                    }
                );
            });
        }]);

    mainModule.config(function(cfpLoadingBarProvider) {
        cfpLoadingBarProvider.includeSpinner = true;
    });

    mainModule.run(['$rootScope','$state','$stateParams', 'Auth', 'AUTH_EVENTS','$timeout','cfpLoadingBar', function($rootScope, $state,$stateParams, Auth, AUTH_EVENTS,$timeout, cfpLoadingBar) {

        $rootScope.$state = $state;
        $rootScope.$stateParams = $stateParams;

        //开始跳转触发
        $rootScope.$on('$stateChangeStart', function (event, next) {
            cfpLoadingBar.start();

            var authorizedRoles = next.data.authorizedRoles;
            if (!Auth.isAuthorized(authorizedRoles)) {
                event.preventDefault();
                if (Auth.isAuthenticated()) {
                    // 登录失败
                    $rootScope.$broadcast(AUTH_EVENTS.notAuthorized);
                } else {
                    // 未登录
                    $rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
                }
            }
        });
        //跳转完成
        $rootScope.$on("$stateChangeSuccess",  function(event, toState, toParams, fromState, fromParams) {
            cfpLoadingBar.complete();
            $rootScope.previousState_name = fromState.name;
            $rootScope.previousState_params = fromParams;
        });

        //回退
        $rootScope.nodeBack = function() {//实现返回的函数
            $state.go($rootScope.previousState_name,$rootScope.previousState_params);
        };
        /* 当前页*/
        $rootScope.getClass = function(path) {
            if ($state.current.name == path) {
                return "active";
            } else {
                return "";
            }
        };
        //退出
        $rootScope.logout = function(){
            Auth.logout();
        };

    }]);

    return mainModule;
})