/**
 * Created by Administrator on 2016/11/30.
 */
define(['console'], function (Console) {
    "use strict";
    //Console.group("Entering session module.");

    var service = ['$rootScope','USER_ROLES', function ($rootScope, USER_ROLES) {
        var sessionService = {user : '',userRole : ''};

        sessionService.create = function(user) {
            sessionService.user = user;
            sessionService.userRole = user.userRole;
        };

        sessionService.destroy = function() {
            sessionService. user = null;
            sessionService.userRole = null;
        };
        return sessionService;
    }];
    //Console.info('session',service);
    //Console.groupEnd();
    return service;
});