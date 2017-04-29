<?php
namespace app\auth\controller;

use think\Request;
use think\Db;
use think\Session;
use think\Cookie;
use think\Controller;
use think\Config;
use Godok\Org\Auth;
/**
 * Login Controller
 */
class Login extends Controller
{
	/**
	 * 首页
	 */
    // http://127.0.0.1/auth/Login/index
	public function index(){
	    if ( Request::instance()->isPost() ) {
	        // 做一个简单的登录 组合where数组条件
	        $username = Request::instance()->post('username', '', 'Godok\Org\Filter::username');
	        if( empty( $username ) ) {
	            return ['code'=>-1001,'msg'=>'用户名格式错误！'];
	        }
	        $password =Request::instance()->post('password', '', 'Godok\Org\Filter::password');
	        if( empty( $password ) ) {
	            return ['code'=>-1002,'msg'=>'密码格式错误！'];
	        }
	        $password = md5( $password );
	        Cookie::set('username', $username, 3600*24*7);
	        $data=Db::name( Config::get('auth.table_user') )->where(['username' => $username,'deleted'=>0])->find();
	        if (empty($data)) {
	            return ['code'=>-1000,'msg'=>'用户名错误'];
	        } elseif ($data['password'] != $password) {
	            return ['code'=>-1010,'msg'=>'密码错误'];
	        } elseif ( 0 == $data['status']) {
	            return ['code'=>-1020,'msg'=>'帐号被禁用'];
	        } elseif ( 2 == $data['status']) {
	            return ['code'=>-1030,'msg'=>'帐号审核中'];
	        } else {
	            $user=[
	                'id'=>$data['id'],
	                'username'=>$data['username'],
	                'nickname'=>$data['nickname'],
	                'avatar'=>$data['avatar'],
	                'email'=>$data['email'],
	                'phone'=>$data['phone'],
	                'rules'=>Auth::getRules($data['id']),
	                'groupids'=>Auth::getGroupids($data['id'])
	            ];
	            //保存基本信息
	            Cookie::set('nickname', $data['nickname'], 3600*24*7);
	            Cookie::set('avatar', $data['avatar'] ?: Config::get('site.resource_url').'images/avatar/default.jpg', 3600*24*7);
	            //保存登录信息
	            Auth::user($user);
	            Db::name( Config::get('auth.table_user') )
	            ->where(['id' => $data['id']])
	            ->update([
	            'last_login_ip' => Request::instance()->ip(),
	            'last_login_time' => time()
	            ]);
	            return ['code'=>0,'msg'=>'登录成功','url'=>\think\Request::instance()->root().'/auth/Index/index'];
	        }
	    } else {
	        return $this->fetch();
	    }
        
	}
    /**
     * 退出
     */
    // http://127.0.0.1/auth/Login/logout
    public function logout()
    {
        Auth::clean();
        $this->success('退出成功、前往登录页面',\think\Request::instance()->root().'/auth/Login/index');
    }

    /**
     * 忘记密码
     */
    public function forget()
    {
        return $this->fetch();
    }



}

