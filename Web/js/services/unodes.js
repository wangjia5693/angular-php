/**
 * 获取该用户所有有效的url
 * Created by Administrator on 2016/12/05.
 */
define(['console'], function (Console) {
    "use strict";
    //Console.group("Entering nodes module.");

    var service = [ '$http', '$rootScope', '$window', 'Session', 'AUTH_EVENTS',
        function($http, $rootScope, $window, Session, AUTH_EVENTS) {
            var nodeService = {};
            //登录
            nodeService.all = function(user, success, error) {
                //Console.info(user);
                //用户数据请求
                $http.post('Entrance/index.php',user,user).success(function(data) {

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
            return nodeService;
        } ];
    //Console.info('auth',service);
    Console.groupEnd();
    return service;
});
