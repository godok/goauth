<?php
// +----------------------------------------------------------------------
// | goAuth config
// +----------------------------------------------------------------------

return [
    // 路由设置
    'app_debug'              => true,
    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'name'           => 'ssid',
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],
    //模版
    'dispatch_success_tmpl'  => APP_PATH . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => APP_PATH . 'dispatch_jump.tpl',
    // goAuth配置
    'auth'    => [
        // 权限开关
        'auth_on'           => 1,
        // 认证方式，0为登录认证；1为实时认证；n为n分钟更新一次权限。
        'auth_type'         => 10,
        // 用户组数据不带前缀表名
        'table_group'        => 'auth_group',
        // 用户-用户组关系不带前缀表
        'table_group_relation' => 'auth_group_relation',  
        // 权限规则不带前缀表
        'table_rule'         => 'auth_rule',
        // 用户信息不带前缀表
        'table_user'         => 'users',
    ],
    //站点设置
    'site' => [
        //前端静态资源URL
        'resource_url' => 'http://goui.godok.cn/',
        'title' => 'GOA权限系统',
        'keywords' => 'GOA权限系统',
        'description' => 'GOA权限系统'
    ],
  
];
