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
            restrict:'AE',
            templateUrl: 'Web/js/views/subTpl/modal.tpl.html'
            ,scope : {
                errormessage : "=",
                errortitle : "=",
                errortype : "="
            },
            link: function(scope, element, attrs){
                scope.hideModali = function() {
                    scope.errortitle = null;
                    scope.errormessage = null;
                    scope.errortype = null;
                };

            }
        }
    }];
    return directive_alert;
});