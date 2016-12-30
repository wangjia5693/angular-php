/**
 * Author: wangjia <wangj01@lizi.com>
 * Created by Administrator on 2016/12/09
 * 资源服务
 */
define(['jquery','console','angular'], function ($,Console,angular) {
    "use strict";
    Console.group("Entering webSource module.");
    var service = [ '$http','$rootScope', '$window',
        function($http,$rootScope,$window) {
            var webSource = {};
            var baseUrl = 'Entrance/index.php';
            //get方法；config : obj
            webSource.getData = function(config, success, error) {
                if(!angular.isObject(config)){
                    $rootScope.mlService.setMessage('getData发生错误','第二参数config必须为对象','error');
                    return;
                }
                if(angular.isUndefined(config.service)){
                    $rootScope.mlService.setMessage('getData发生错误','第二参数config必须含有service参数','error');
                    return;
                }
                config = {params:config};
                $http.get(baseUrl,config).then(
                    function(response){
                        response = response.data;
                        if (response.ret != '200' || response.data.code != '200') {
                            var loginE = (response.ret != '200') ? response.msg : response.data.msg;
                            var code = (response.ret != '200') ? response.ret : response.data.code;
                            $rootScope.mlService.setMessage('getData返回错误','错误报告：'+loginE+'; code：'+code,'error');
                            return;
                        }

                        if(angular.isUndefined(success) || !angular.isFunction(success)){
                            return response;
                        }else{
                            return success(response);
                        }
                    }
                    ,function(response){
                        if(angular.isObject(response)){
                            response = angular.toJson(response);
                        }
                        $rootScope.mlService.setMessage('getData发生错误','错误详情：'+response,'error');
                    }
                );

            };

            //post方法；data为对象类型，config必须为obj
            webSource.postData = function(data, success, error,config) {

                if(!angular.isObject(data)){
                    $rootScope.mlService.setMessage('postData','第一参数data必须为对象','error');
                    return;
                }
                if(angular.isUndefined(data.service)){
                    $rootScope.mlService.setMessage('postData发生错误','第一参数data必须含有service参数','error');
                    return;
                }
                if(!angular.isUndefined(config)&&!angular.isObject(config)){
                    $rootScope.mlService.setMessage('postData','第二参数config必须为对象','error');
                    return;
                }
                if(!angular.isUndefined(config)&&angular.isUndefined(config.service)){
                    $rootScope.mlService.setMessage('postData发生错误','第二参数config必须含有service参数','error');
                    return;
                }
                $http({
                    method : 'POST',
                    url: baseUrl,
                    data: $.param(data), //序列化参数
                    params: config,
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(
                    function(response){
                        //Console.info(response);
                        response = response.data;
                        if (response.ret != '200' || response.data.code != '200') {
                            var loginE = (response.ret != '200') ? response.msg : response.data.msg;
                            var code = (response.ret != '200') ? response.ret : response.data.code;
                            $rootScope.mlService.setMessage('postData','错误报告：'+loginE+'; code：'+code,'error');
                            return;
                        }

                        if(angular.isUndefined(success) || !angular.isFunction(success)){
                            return response.data;
                        }else{
                            return success(response.data);
                        }
                    }
                    ,function(response){
                        if(angular.isObject(response)){
                            response = angular.toJson(response);
                        }
                        $rootScope.mlService.postData('postData发生错误','错误详情：'+response,'error');
                    }
                );

            };

            //file upload
            webSource.upload = function(){

            }

            //自定义分页列表
            webSource.fillTbodyo = function($scope,data, success, error){
                if(!angular.isObject(data)){
                    $rootScope.mlService.setMessage('postData','第一参数data必须为对象','error');
                    return;
                }
                if(angular.isUndefined(data.service)){
                    $rootScope.mlService.setMessage('postData发生错误','第一参数data必须含有service参数','error');
                    return;
                }
               data.pageIndex =  $scope.paginationConf.currentPage;
               data.pageSize =  $scope.paginationConf.itemsPerPage;
                $http({
                    method : 'POST',
                    url: baseUrl,
                    data: $.param(data), //序列化参数
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).then(
                    function(response){
                        //Console.info(response);
                        response = response.data;
                        if (response.ret != '200' || response.data.code != '200') {
                            var loginE = (response.ret != '200') ? response.msg : response.data.msg;
                            var code = (response.ret != '200') ? response.ret : response.data.code;
                            $rootScope.mlService.setMessage('postData','错误报告：'+loginE+'; code：'+code,'error');
                            return;
                        }
                        $scope.paginationConf.totalItems = response.data.total;
                        if(angular.isUndefined(success) || !angular.isFunction(success)){
                            return response.data;
                        }else{
                            return success(response.data);
                        }
                    }
                    ,function(response){
                        if(angular.isObject(response)){
                            response = angular.toJson(response);
                        }
                        $rootScope.mlService.postData('postData发生错误','错误详情：'+response,'error');
                    }
                );

            }

            //ng-table分页列表
            webSource.fillTbody = function($scope,datai,NgTableParams,toastr,$http){
                //搜索
                $scope.searchi = {};
                $scope.search = function(){
                    if(_.isEmpty($scope.searchi)){
                        toastr.warning ('请填写具体的搜索项后点击查询！', '提示');
                        return;
                    }
                    var source = {};
                    angular.copy( $scope.searchi,source);
                    $scope.tableParams.filter(source);
                }

                //ng-table封装表格
                $scope.tableParams = new NgTableParams({
                    page: 1,
                    count: 10  ,
                    filterDelay:3000,
                }, {
                    total: 0,
                    filterDelay:3000,
                    // length of data
                    getData: function (params) {

                        datai.pageIndex = params.page();
                        datai.pageSize = params.count();
                        datai.sort = _.isEmpty(params.sorting()) ? angular.toJson({id: "asc"}) : angular.toJson(params.sorting());
                        if (!_.isEmpty(params.filter()))
                            datai.filter = angular.toJson(params.filter());
                        return $http({
                            method: 'POST',
                            url: 'Entrance/index.php',
                            data: $.param(datai), //序列化参数
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                        }).then(
                            function (response) {
                                response = response.data;
                                if (response.ret != '200' || response.data.code != '200') {
                                    var loginE = (response.ret != '200') ? response.msg : response.data.msg;
                                    var code = (response.ret != '200') ? response.ret : response.data.code;
                                    $rootScope.mlService.setMessage('postData', '错误报告：' + loginE + '; code：' + code, 'error');
                                    return;
                                }
                                params.total(response.data.total);
                                $scope.users = response.data.list;
                                return response.data.list;

                            }
                        )
                    }
                })
            }

            return webSource;
        } ];
    Console.info('webSource',service);
    Console.groupEnd();
    return service;
});
