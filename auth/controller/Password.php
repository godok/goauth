<?php
namespace app\auth\controller;

use think\Request;
use think\Db;
use think\Config;
use think\Controller;

use Godok\Org\Auth;
use Godok\Org\FileManager;


class Password extends Controller
{
	/**
	 * 修改密码
	 */
	public function index()
	{
	    if ( Request::instance()->isPost() ) {
	        $id = Auth::user('id');
	        $oldpassword = Request::instance()->post('oldpassword','','Godok\Org\Filter::password');
	        $newpassword = Request::instance()->post('password','','Godok\Org\Filter::password');
	        if ( empty($oldpassword) ) {
	            return ['code'=>-1000, 'msg'=>"原密码格式错误！"];
	        }
	        if ( empty($newpassword) ) {
	            return ['code'=>-1010, 'msg'=>"新密码格式错误！"];
	        }
	        if (
	            $info = Db::name( Config::get('auth.table_user') )
	            ->where(['id' => $id])
	            ->find()
	        ) {
	            if (md5($oldpassword) != $info['password']) {
	                return ['code'=>-1020, 'msg'=>'原密码错误！'];
	            } elseif (
	                Db::name( Config::get('auth.table_user') )
	                ->where(['id' => $id])
	                ->update(['password'=>md5($newpassword)])
	            ){
	                return ['code'=>0, 'msg'=>'修改成功！'];
	            } else {
	                 
	                return ['code'=>-1030, 'msg'=>'修改失败！'];
	            }
	        } else {
	            return ['code'=>-1040, 'msg'=>'帐号不存在！'];
	        }
	    } else {
	        return $this->fetch();
	    }
	}



}

