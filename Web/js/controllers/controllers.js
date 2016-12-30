/**
 * Created by Administrator on 2016/11/19.
 */
define([
    //标准库
    'underscore'
    ,'console'
    ,'require'
    //自定义服务
    , 'controllers/parentController'//（依赖的具体控制器）
    , 'controllers/login'//（依赖的具体控制器）

], function ( _,Console,require,parent,login) { //routes
    "use strict";
    Console.group("预启动各种控制器");
    Console.info("loginController", login);

    //所有控制器列表
    var controllers = {
        LoginCtrl: login
    };

    //启动路由配置
    var setUpRoutes = function(angModule) {


    }

    //启动函数
    var initialize = function(angModule) {
        Console.group("启动控制器.");
        angModule.controller('ParentCtrl', parent);
        _.each(controllers,function(controller,name){
            Console.info("controller.",name);
            angModule.controller(name, controller);

        })
        setUpRoutes(angModule);
        Console.groupEnd();
    };
    Console.groupEnd();
    return {
        initialize: initialize
    };
});
