/**
 * Created by Administrator on 2016/11/30.
 */
define(['console'], function (Console) {
    "use strict";
    //Console.group("Entering authInter module.");
    var service = [ '$rootScope', '$q', 'Session', 'AUTH_EVENTS',
        function($rootScope, $q, Session, AUTH_EVENTS) {
            return {
                responseError : function(response) {
                    $rootScope.$broadcast({
                        401 : AUTH_EVENTS.notAuthenticated,
                        403 : AUTH_EVENTS.notAuthorized,
                        419 : AUTH_EVENTS.sessionTimeout,
                        440 : AUTH_EVENTS.sessionTimeout
                    }[response.status], response);
                    return $q.reject(response);
                }
            };
        } ];
    //Console.info('authinter',service);
    //Console.groupEnd();
    return service;
});