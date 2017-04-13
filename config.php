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
];
