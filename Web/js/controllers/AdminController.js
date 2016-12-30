/**
 * Created by Administrator on 2016/12/12.
 */

define(['appregister'],function(app){
    app.controller('AdminController', ['$scope','$http','webSource','$state',function($scope,$http,webSource,$state){
        $scope.formData = {};
        $state.go('admin.profile');

        // function to process the form
        $scope.processForm = function() {
            alert('awesome!');
        };

        $scope.node = {
            'title': '运维管理'
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