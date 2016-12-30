/**
 * Created by Administrator on 2016/11/29.
 * 开发过程中需要特别注意的；每增加一个新的页面，需要在此处添加新的路由控制配置；
 */
define([
  //'text!templates/Home.html',
  //'text!templates/Data.html'
],function(){//homeTemplate,dataTemplate
  return {
      '/': {
              title: 'Home'
            , route: 'home'
            , controller: 'HomeController'
            , templateUrl: "Web/js/views/Home.html"
            , controller_url:"controllers/HomeController"
            , data: {
                authorizedRoles: ['admin', 'editor','guest']
              }
      },
      '/user_list':{
          title: 'user_list'
          , route: 'user_list'
          , controller: 'UserController'
          , templateUrl: "Web/js/views/User.html"
          , controller_url:"controllers/UserController"
          , data: {
            authorizedRoles: ['admin', 'editor','guest']
          }
      },
      '/node_list':{
          title: 'node_list'
          , route: 'node_list'
          , controller: 'NodeController'
          , templateUrl: "Web/js/views/Node.html"
          , controller_url:"controllers/NodeController"
          , data: {
              authorizedRoles: ['admin', 'editor','guest']
          }
      },
      '/department_list':{
          title: 'department_list'
          , route: 'department_list'
          , controller: 'DepartController'
          , templateUrl: "Web/js/views/Depart.html"
          , controller_url:"controllers/DepartController"
          , data: {
              authorizedRoles: ['admin', 'editor','guest']
          }
      },
      '/admin':{
          title: 'admin'
          , route: 'admin'
          , controller: 'AdminController'
          , templateUrl: "Web/js/views/Admin.html"
          , controller_url:"controllers/AdminController"
          , data: {
              authorizedRoles: ['admin', 'editor','guest']
          }
      },
      '/profile':{
          title: 'admin.profile'
          , route: 'admin.profile'
          , templateUrl: "Web/js/views/form-profile.html"
      },
      '/interests':{
          title: '.interests'
          , route: 'admin.interests'
          , templateUrl: "Web/js/views/form-interests.html"
      },
      '/payment':{
          title: 'admin.payment'
          , route: 'admin.payment'
          , templateUrl: "Web/js/views/form-payment.html"
      },
      '/password':{
          title: 'password'
          , route: 'password'
          , controller: 'PasswordController'
          , templateUrl: "Web/js/views/Password.html"
          , controller_url:"controllers/PasswordController"
          , data: {
              authorizedRoles: ['admin', 'editor','guest']
          }
      },
      '/password/settings':{
          title: '.settings'
          , route: 'password.settings'
          , templateUrl: "Web/js/views/password-settings.html"
      },
      '/password/accounts':{
          title: '.accounts'
          , route: 'password.accounts'
          , templateUrl: "Web/js/views/password-accounts.html"
      },

    //"admin/node.list": {
    //  title: 'Node'
    //  , route: '/admin/node.list'
    //  , controller: 'node'
    //  , templateUrl: "Web/js/views/Home.html"
    //  ,controller_url:"Web/js/controller/HomeController"
    //  ,data: {
    //    authorizedRoles: ['admin', 'editor','guest']
    //  }
    //}
    //, creation: {
    //  title: 'Data List'
    //  , route: '/data'
    //  , controller: 'data'
    //,controller_url:"Web/js/controller/HomeController"
    //  ,  templateUrl: "Web/js/views/state1.html"
    //  ,data: {
    //    authorizedRoles: ['admin', 'editor','guest']
    //  }
    //}
  };
})
