/**
 * Created by Administrator on 2016/11/18.
 */
require.config({
    //baseUrl:"js/",//默认与data-main的路径一致
    paths: {
        "jquery":     "vender/jquery203",
        "console":     "vender/console-min",
        "underscore": 'vender/underscore-min',
        "angular":    'vender/angular',
        "uiRouter" : "http://cdn.bootcss.com/angular-ui-router/0.2.10/angular-ui-router.min",
        "sanitize" : "vender/angular-sanitize.min",
        "animate" : "vender/angular-animate.min"
        ,"btuiTpli" : "vender/ui-bootstrap-tpls.min"
        ,"angularToastr" : "vender/angular-toastr.tpls.min"
        ,"angularConfirm" : "vender/angular-confirm.min"
        ,"loading" : "vender/loading-bar.min"
        ,"bootstrap" : "vender/bootstrap.min"
        ,"angularFileUpload" : "vender/angular-file-upload.min"
        ,"angularPrint" : "vender/angularPrint"
        ,"pagination" : "vender/pagination"
        ,"uiRouterTabs" : "vender/ui-router-tabs"
        ,"ngTable" : "vender/ng-table.min"
        ,"templates":  'views'
        ,"appreg":  'appregister'
        ,"appmodule":  'appmodule'
        //,"app":  'app'
    },
    shim: {
        'angular': {
            exports: 'angular'
        },
        'console': {
            exports: 'console'
        },
        'loading':{
            deps: ["angular"],
            exports: 'loading'
        },
        'ngTable':{
            deps: ["angular"],
            exports: 'ngTable'
        },
        'uiRouterTabs':{
            deps: ["angular"],
            exports: 'uiRouterTabs'
        },
        'angularToastr':{
            deps: ["angular"],
                exports: 'angularToastr'
        },
        'bootstrap':{
            exports: 'bootstrap'
        },
        'btuiTpli':{
            deps: ["angular"],
            exports: 'btuiTpli'
        },
        'angularConfirm':{
            deps: ["angular","btuiTpli"],
            exports: 'angularConfirm'
        },
        'uiRouter':{
            deps: ["angular"],
            exports: 'uiRouter'
        },
        'angularFileUpload':{
            deps: ["angular"],
            exports: 'angularFileUpload'
        },
        'sanitize':{
            deps: ["angular"],
            exports: 'sanitize'
        },
        'angularPrint':{
            deps: ["angular"],
            exports: 'angularPrint'
        },
        'animate':{
            deps: ["angular"],
            exports: 'animate'
        },
        'pagination':{
            deps: ["angular"],
            exports: 'pagination'
        }
    }
    , priority: [
        "console"
        , "jquery"
        , "underscore"
        , "angular"
        ,"btuiTpli"
        , "uiRouter"
    ]
    ,urlArgs: "v=" +  (new Date()).getTime()//开发测试环节必要，正式版本发布生产环境下可去除
});

require([
     'require'
    ,'jquery'
    ,'console'
    , 'underscore'
    , 'angular'
    ,'loading'
    ,'btuiTpli'
    ,'angularConfirm'
    ,'animate'
    ,'bootstrap'
    ,'ngTable'
    , 'appmodule'
    , 'pagination'
    , 'angularFileUpload'
    , 'angularPrint'
    , 'sanitize'
    , 'uiRouterTabs'
    , 'angularToastr'
], function (require,$,Console,_,angular,loading,btuiTpli,angularConfirm,animate,bootstrap,ngTable, appmodule,pagination,angularFileUpload,angularPrint,sanitize,uiRouterTabs,angularToastr) {
    Console.group("启动，加载常规依赖！");
    sessionStorage['loaded'] = true;
    Console.info(sessionStorage['loaded']);
    //Console.info("Console", Console);
    //Console.info("angular", angular);
    //Console.info("jquery", $);
    //Console.info("underscore", _);
    Console.debug("debug测试信息.");
    Console.groupEnd();
    require(['app'], function (app) {
        Console.group("依赖app ");
        //Console.info("app", app);
        app.initialize();
        Console.groupEnd();
    });
    Console.groupEnd();
});
