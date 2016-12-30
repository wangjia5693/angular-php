/**
 * Created by Administrator on 2016/12/10.
 * 提示框
 */

define([
    // 公共库
    'routes/routes'

], function (routes) {
    "use strict";

    var directive_alert = [function(){
        return {
            restrict: 'EA',
            templateUrl: 'Web/js/views/subTpl/alert.tpl.html',
            scope : {
                message : "=",
                type : "="
            },
            link: function(scope, element, attrs){

                scope.hideAlert = function() {
                    scope.message = null;
                    scope.type = null;
                };

            }
        };
    }];
    return directive_alert;
});
