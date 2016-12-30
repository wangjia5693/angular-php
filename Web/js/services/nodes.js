/**
 * 获取该用户所有有效的url
 * Created by Administrator on 2016/12/05.
 */
define(['console'], function (Console) {
    "use strict";
    //Console.group("Entering nodes module.");

    var service = [ '$http', '$rootScope',
        function($http, $rootScope) {
            var nodeService = {};
            //登录
            nodeService.all = function() {
                //获取多有的nodes
                var conf = {params:{'service':'Public.allNodes'}};
                $http.get('Entrance/index.php',conf).success(function(data) {
                    if(data.ret !='200'||data.data.code !='200'){
                        var loginE = (data.ret !='200') ? data.msg : data.data.msg;
                        var code = (data.ret !='200') ? data.ret : data.data.code;
                        $rootScope.debug = true;
                        $rootScope.debug_info = {'msg':loginE,'code':code};
                        return;
                    }
                    $rootScope.nodes = data.data.node_info;
                });
            };
            return nodeService;
        } ];
    //Console.info('auth',service);
    Console.groupEnd();
    return service;
});
