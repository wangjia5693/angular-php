/**
 * Created by Administrator on 2016/12/21.
 */
//常规controller写法

define(['appregister','jquery','underscore'],function(app,$,_){

    app.controller('NodeController', ['$scope','$rootScope','$state','$http','webSource','NgTableParams','toastr','$uibModal',function($scope,$rootScope,$state,$http,webSource,NgTableParams,toastr,$uibModal){

        var service = {'service': 'User.usrlist'};

        //获取数据（排序、搜索）
        //webSource.fillTbody($scope,service,NgTableParams,toastr,$http);
        var data = [{"name":"Jasper","age":45,"role":"Media Relations"},{"name":"Deirdre","age":67,"role":"Sales and Marketing"},{"name":"Ferris","age":10,"role":"Quality Assurance"},{"name":"Nerea","age":15,"role":"Media Relations"},{"name":"Addison","age":47,"role":"Accounting"},{"name":"Nita","age":29,"role":"Research and Development"},{"name":"Nadine","age":73,"role":"Human Resources"},{"name":"Demetria","age":90,"role":"Quality Assurance"},{"name":"Kelly","age":31,"role":"Accounting"},{"name":"Tanner","age":21,"role":"Customer Service"},{"name":"Leah","age":98,"role":"Finances"},{"name":"Ursa","age":2,"role":"Sales and Marketing"},{"name":"Lareina","age":30,"role":"Payroll"},{"name":"Craig","age":88,"role":"Tech Support"},{"name":"Beck","age":37,"role":"Finances"},{"name":"Dai","age":12,"role":"Asset Management"},{"name":"Ifeoma","age":51,"role":"Sales and Marketing"},{"name":"Guinevere","age":35,"role":"Legal Department"},{"name":"Kirestin","age":91,"role":"Advertising"},{"name":"Martha","age":99,"role":"Legal Department"},{"name":"Nichole","age":77,"role":"Legal Department"},{"name":"Wesley","age":10,"role":"Legal Department"},{"name":"Carissa","age":72,"role":"Customer Relations"},{"name":"Tiger","age":90,"role":"Public Relations"},{"name":"Griffin","age":44,"role":"Research and Development"},{"name":"Gay","age":18,"role":"Quality Assurance"},{"name":"Shea","age":37,"role":"Legal Department"},{"name":"Warren","age":13,"role":"Accounting"},{"name":"Brenden","age":71,"role":"Quality Assurance"},{"name":"Basia","age":22,"role":"Accounting"},{"name":"Unity","age":8,"role":"Advertising"},{"name":"Ramona","age":100,"role":"Public Relations"},{"name":"Zahir","age":78,"role":"Asset Management"},{"name":"Thomas","age":46,"role":"Accounting"},{"name":"Zelda","age":85,"role":"Customer Relations"},{"name":"Gil","age":24,"role":"Public Relations"},{"name":"Kieran","age":31,"role":"Legal Department"},{"name":"Sophia","age":15,"role":"Research and Development"},{"name":"Emma","age":55,"role":"Research and Development"},{"name":"Reese","age":66,"role":"Advertising"},{"name":"Cassady","age":74,"role":"Media Relations"},{"name":"Nicholas","age":88,"role":"Accounting"},{"name":"Belle","age":88,"role":"Customer Relations"},{"name":"Desirae","age":50,"role":"Quality Assurance"},{"name":"Kirk","age":43,"role":"Customer Service"},{"name":"Emmanuel","age":52,"role":"Advertising"},{"name":"Raja","age":31,"role":"Tech Support"},{"name":"Simone","age":55,"role":"Public Relations"},{"name":"Lee","age":60,"role":"Customer Relations"},{"name":"Nayda","age":30,"role":"Accounting"},{"name":"Hasad","age":59,"role":"Quality Assurance"},{"name":"Halee","age":36,"role":"Customer Service"},{"name":"Dorothy","age":36,"role":"Tech Support"},{"name":"Maite","age":41,"role":"Customer Service"},{"name":"Selma","age":26,"role":"Public Relations"},{"name":"Laurel","age":9,"role":"Public Relations"},{"name":"Castor","age":18,"role":"Payroll"},{"name":"Jason","age":49,"role":"Research and Development"},{"name":"Quamar","age":90,"role":"Customer Relations"},{"name":"Kirk","age":35,"role":"Media Relations"},{"name":"Audra","age":65,"role":"Customer Relations"},{"name":"Stella","age":48,"role":"Advertising"},{"name":"Malik","age":6,"role":"Quality Assurance"},{"name":"Aurora","age":47,"role":"Human Resources"},{"name":"Gray","age":59,"role":"Payroll"},{"name":"Erasmus","age":22,"role":"Media Relations"},{"name":"Nita","age":82,"role":"Human Resources"},{"name":"Jordan","age":56,"role":"Sales and Marketing"},{"name":"Shaine","age":90,"role":"Payroll"},{"name":"Maxine","age":11,"role":"Public Relations"},{"name":"Camilla","age":33,"role":"Tech Support"},{"name":"Walker","age":3,"role":"Customer Relations"},{"name":"Amos","age":75,"role":"Media Relations"},{"name":"Audra","age":54,"role":"Legal Department"},{"name":"Florence","age":83,"role":"Payroll"},{"name":"Tyler","age":33,"role":"Advertising"},{"name":"Shannon","age":12,"role":"Accounting"},{"name":"Driscoll","age":57,"role":"Legal Department"},{"name":"Clementine","age":51,"role":"Asset Management"},{"name":"Levi","age":18,"role":"Human Resources"},{"name":"Michael","age":66,"role":"Public Relations"},{"name":"Guy","age":32,"role":"Tech Support"},{"name":"Orli","age":7,"role":"Payroll"},{"name":"Philip","age":68,"role":"Finances"},{"name":"Anne","age":67,"role":"Advertising"},{"name":"Abdul","age":4,"role":"Public Relations"},{"name":"Rachel","age":8,"role":"Payroll"},{"name":"Iona","age":67,"role":"Advertising"},{"name":"Porter","age":31,"role":"Customer Relations"},{"name":"Yoshi","age":81,"role":"Asset Management"},{"name":"Kirsten","age":11,"role":"Payroll"},{"name":"Miranda","age":79,"role":"Quality Assurance"},{"name":"Briar","age":83,"role":"Research and Development"},{"name":"Anjolie","age":70,"role":"Finances"},{"name":"Kadeem","age":5,"role":"Research and Development"},{"name":"Glenna","age":96,"role":"Customer Relations"},{"name":"Rhoda","age":19,"role":"Customer Relations"},{"name":"Sybill","age":22,"role":"Customer Service"},{"name":"Medge","age":87,"role":"Advertising"},{"name":"Jacqueline","age":98,"role":"Quality Assurance"}];

        $scope.tableParams = new NgTableParams({
            group: 'role'
        }, {
            groupOptions: {
                isExpanded: false
            },
            data: data
        });


        //工具栏显示
        $scope.node = {
            'title': '节点管理'
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






    }])

})