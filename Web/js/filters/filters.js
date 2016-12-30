define([
  // 标准库
  'underscore'
  ,'console'
  // Filters

], function (_,Console){
  "use strict";
  //Console.group("预启动自定义过滤器");

  var filters = {};

  var initialize = function (angModule) {
    //Console.group("启动过滤器.");
    _.each(filters,function(filter,name){
      //Console.info("filters: ", name);
      angModule.filter(name,filter);
    })
    //Console.debug("Custom filters initialized.");
    //Console.groupEnd();
  }

  //Console.groupEnd();
  return {
    initialize: initialize
  };
});
