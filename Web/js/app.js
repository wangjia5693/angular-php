/**
 * Created by Administrator on 2016/11/19.
 */
define([
    // 基本库
    'jquery'
    , 'console'
    , 'underscore'
    , 'angular'
    , 'uiRouter'
    , 'btuiTpli'
    , 'appmodule'
    // 项目库(这里加载的为通用组件，具体的组建加载建议按需加载，加速首次启动慢问题)
    , 'services/services'
    , 'directives/directives'
    , 'filters/filters'
    , 'controllers/controllers'
], function ($,Console, _, angular,uiRouter,btuiTpl,appmodule,services,directives,filters,controllers) {
    "use strict";
    Console.group("初始化模块，启动application");
    Console.info("启动application");

    var initialize = function () {
        //声明主模块
        Console.group("初始化模块");
        Console.info('启动服务、指令、过滤器、控制器');
        Console.info('启动服|指|滤|控');

        services.initialize(appmodule);//启动服务
        filters.initialize(appmodule);//启动过滤器
        controllers.initialize(appmodule);//启动控制器
        directives.initialize(appmodule);//启动指令
        var load_state = $('.load_f').attr('id');
        if(load_state=='load'){
            setTimeout(function(){
                $('.load_f').hide().attr('id','loaded');
                //启动主模块
                angular.bootstrap(window.document, ['myApp']);
            },500);
        }else{
            //启动主模块
            angular.bootstrap(window.document, ['myApp']);
        }
        Console.info('启sgsdg');
        Console.groupEnd();
    };
    return {
        initialize: initialize
    };
});
