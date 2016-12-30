/**
 * Created by Administrator on 2016/12/13.
 */
define(['appregister'],function(app){
    app.controller('PasswordController', ['$scope','$http','webSource','$state',function($scope,$http,webSource,$state){
        $scope.tabData   = [
            {
                heading: 'Settings',
                route:   'password.settings'
            },
            {
                heading: 'Accounts',
                route:   'password.accounts',
                //disable: true
            }
        ];
        $scope.node = {
            'title': '修改密码'
            ,'return': true
            ,'fresh': true
            ,'print': true
            ,'import': false
            ,'export': false
        }
        $scope.fresh = function(){
            alert(11);
            $state.reload();
        }

    }])


})
