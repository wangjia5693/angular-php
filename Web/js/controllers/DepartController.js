/**
 * Created by Administrator on 2016/12/12.
 */
define(['appregister'],function(app){
    app.controller('DepartController', ['$scope','$http','webSource','$state',function($scope,$http,webSource,$state){

        $scope.node = {
            'title': '部门管理'
            ,'return': true
            ,'fresh': true
            ,'print': true
            ,'bar': true
            ,'home': true
            ,'addinfo': true
            ,'import': false
            ,'export': false
        }

        var vm = $scope.vm = {};

    }])

})

