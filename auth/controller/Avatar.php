<?php
namespace app\auth\controller;

use think\Request;
use think\File;
use think\Db;
use think\Config;
use think\Controller;

use Godok\Org\Auth;
use Godok\Org\FileManager;


class Avatar extends Controller
{
	/**
	 * 编辑头像
	 */
	public function index()
	{
	    if ( Request::instance()->isPost() ) {
	        $file = Request::instance()->file('image');
	        if ( ! $file instanceof File) {
	            return ['code'=>-1000, 'msg'=>"非法上传！"];
	        }
	        if ( !$file->validate(['size'=>1000*200,'ext'=>'jpg,png,gif'])->check() ) {
	            return ['code'=>-1010, 'msg'=>$file->getError()];
	        }
	        $info = FileManager::save($file);
	        if ( $info ) {
	            if(
	                Db::name( Config::get('auth.table_user') )
	                ->where(['id' => Auth::user('id')])
	                ->update(['avatar'=> $info[1]])
	            ) {
	                \think\Cookie::set('avatar',$info[1], 3600*24*7);
	                return ['code'=>0, 'msg'=>'保存成功！'];
	            } else {
	                return ['code'=>-1010, 'msg'=>'数据更新失败'];
	            }
	             
	        } else {
	            return ['code'=>-1000, 'msg'=>FileManager::getError()];
	        }
	    } else {
	        return $this->fetch();
	    }
       
	}



}

