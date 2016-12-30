define([
  // Standard Libs
   'jquery'
  ,'console'
  , 'underscore'
  , 'angular'
  , 'directives/alertBar'
  , 'directives/modalBar'
  , 'directives/imgPreview'
  , 'directives/toolBar'
  // Application Widgets

], function($,Console, _, angular,alertBar,modalBar,imgPreview,toolBar){
  "use strict";
  Console.group("预启动各种指令");
  var directives = {
    'alertBar':alertBar
    ,'testmodal':modalBar
    ,'ng-thumb':imgPreview
    ,'toolbar':toolBar

  };
  var initialize = function (angModule) {
    Console.group("启动指令.");
    _.each(directives,function(directive,name){
      Console.info("directive: ", name);
       angModule.directive(name,directive);
    })
    //Console.debug("Custom directive initialized.");
    Console.groupEnd();
  }

  Console.groupEnd();
  return {
    initialize: initialize
  };
});
