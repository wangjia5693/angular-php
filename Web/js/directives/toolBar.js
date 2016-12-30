/**
 * Created by Administrator on 2016/12/19.
 */
define([
    // 公共库
    'routes/routes'

], function (routes) {
    "use strict";

    var directive_tool = [function(){
        return {
            restrict: "EA",
            templateUrl: "Web/js/views/subTpl/toolBar.tpl.html"
            //scope : {
            //    message : "=",
            //    type : "="
            //},
            //link: function(scope, element, attrs){
            //
            //    scope.hideAlert = function() {
            //        scope.message = null;
            //        scope.type = null;
            //    };
            //
            //}
        };
    }];
    return directive_tool;
});
