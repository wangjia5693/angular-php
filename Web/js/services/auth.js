/**
 * Created by Administrator on 2016/11/30.
 */
define(['jquery','console'], function ($,Console) {
    "use strict";
    //Console.group("Entering Auth module.");

    var service = [ '$http', '$rootScope', '$window', 'Session', 'AUTH_EVENTS',
        function($http, $rootScope, $window, Session, AUTH_EVENTS) {
            var authService = {};

            //登录
            authService.login = function(user, success, error) {
                //Console.info(user);
                //用户数据请求
                var userp = {params:user};
                $http.get('Entrance/index.php',userp).success(function(data) {

                    if(data.ret !='200'||data.data.code !='200'){
                        $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
                        var loginE = (data.ret !='200') ? {'msg':data.msg} : {'msg':data.data.msg};
                        error(loginE);
                        return;
                    }
                    var loginData =  data.data;
                    //insert your custom login function here
                    if(user.username == loginData.username && user.password == loginData.password){
                        //set the browser session, to avoid relogin on refresh
                        $window.sessionStorage["userInfo"] = JSON.stringify(loginData);
                        //delete password not to be seen clientside
                        delete loginData.password;
                        //update current user into the Session service or $rootScope.currentUser
                        //whatever you prefer
                        Session.create(loginData);
                        $rootScope.currentUser = loginData;
                        //fire event of successful login
                        $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                        //run success function
                        success(loginData);
                    } else{
                        $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
                        var loginEi = {'msg':'请刷新后重新登录！'};
                        error(loginEi);
                    }

                });

            };

            //登录验证
            authService.isAuthenticated = function() {
                return !!Session.user;
            };

            //验证是否拥有权限
            //同样可以. <p ng-if="isAuthorized(authorizedRoles)">show this only to admins</p>
            authService.isAuthorized = function(authorizedRoles) {
                if (!angular.isArray(authorizedRoles)) {
                    authorizedRoles = [authorizedRoles];
                }
                return (authService.isAuthenticated() &&
                authorizedRoles.indexOf(Session.userRole) !== -1);
            };
            //退出
            authService.logout = function(){
                Session.destroy();
                $window.sessionStorage.removeItem("userInfo");
                $('#load').remove();
                $rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);
            }
            return authService;
        } ];
    //Console.info('auth',service);
    Console.groupEnd();
    return service;
});
