/**
 * Created by Administrator on 2016/12/10.
 * 错误提示框数据
 */
define(['console'], function (Console) {
    "use strict";
    //Console.group("Entering wrong module.");

    var service = [function() {
        return {
            message : null,
            type : null,
            setMessage : function(msg,type){
                this.message = msg;
                this.type = type;

                ////提示框显示最多3秒消失
                //var _self = this;
                //$timeout(function(){
                //    _self.clear();
                //},2000);

            },
            clear : function(){
                this.message = null;
                this.type = null;
            }
        };
    }];
    //Console.info('wrong',service);
    //Console.groupEnd();
    return service;
});