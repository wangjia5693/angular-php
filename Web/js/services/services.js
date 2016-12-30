define([
  //标准库
  'underscore'
  ,'console'

  // 自定义服务
  , 'services/session'//（依赖的具体服务内容）
  , 'services/authInterceptor'//（依赖的具体服务内容）
  , 'services/auth'//（依赖的具体服务内容）
  , 'services/nodes'//（依赖的具体服务内容）
  , 'services/wrong'//（依赖的具体服务内容）
  , 'services/tip'//（依赖的具体服务内容）modal
  , 'services/webSource'//（依赖的具体服务内容）modal

], function(_,Console,sess,aui,au,nodes,wrong,tip,webSource) {
  "use strict";
  //Console.group("预启动各种服务.");
  //Console.info("DataService", 'dasdasd');
  var services = {
    //服务名称：服务内容
      Session:sess
      ,AuthInterceptor:aui
      ,Auth: au
      ,Nodes: nodes
    ,tipService: tip
    ,wrongService: wrong
    ,webSource: webSource
  };
  //console.info('services',services);

  var initialize = function (angModule) {
    //Console.group("启动服务.");
    _.each(services,function(service,name){
      //Console.info("Service", name);
      angModule.factory(name,service);
    })
    //Console.groupEnd();
  }
  //Console.groupEnd();
  return {
    initialize: initialize
  };
});
