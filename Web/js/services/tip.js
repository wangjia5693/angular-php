/**
 * Created by Administrator on 2016/12/10.
 * 提示框数据 限时自动消失
 */

define(['console'], function (Console) {
    "use strict";
    Console.group("Entering modal module.");

    var service = [ function() {
        return {
            err_tip_title : null,
            err_tip_message : null,
            err_tip_type : null,
            setMessage : function(title,msg,type){
                this.err_tip_title = title;
                this.err_tip_message = msg;
                this.err_tip_type = type;
            },
            clear : function(){
                this.err_tip_title = null;
                this.err_tip_message = null;
                this.err_tip_type = null;
            }
        };
    }];
    Console.info('modal',service);
    Console.groupEnd();
    return service;
});
