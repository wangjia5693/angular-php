//常规controller写法

define(['appregister','jquery','underscore'],function(app,$,_){

  app.controller('UserController', ['$scope','$rootScope','$state','$http','webSource','NgTableParams','toastr','$uibModal',function($scope,$rootScope,$state,$http,webSource,NgTableParams,toastr,$uibModal){

    var service = {'service': 'User.usrlist'};

    //获取数据（排序、搜索）
    webSource.fillTbody($scope,service,NgTableParams,toastr,$http);

    //行点击事件
    $scope.changeSelection = function(user) {
      console.info(user);
    }

    //工具栏显示
    $scope.node = {
      'title': '用户管理'
      ,'return': true
      ,'fresh': true
      ,'print': true
      ,'home': true
      ,'addinfo': true
      ,'bar': true
      ,'import': false
      ,'export': false
    }

    var toast = function(data){
      toastr.info (data.data, '完成');
    }

    //新增
    $scope.addinfo = function(){
      var modalInstance = $uibModal.open({
        templateUrl: 'userModalContent.html',
        controller: function($scope, $uibModalInstance){
            $scope.modalTitle = "新增用户";
            $scope.modalAction = "新增";
            $scope.ok = function (valid) {
              if(valid){
                var data = $scope.entity;
                data.service = 'User.adduser';
                webSource.postData(data,toast);
                $uibModalInstance.close('ok');
              }else{
                toastr.error ('请检验表单中填写项！', 'Sorry');
              }
            };
            $scope.cancel = function () {
              $uibModalInstance.dismiss('cancel');
            };
        },
        backdrop: "static"
      });

    }




  }])

})



