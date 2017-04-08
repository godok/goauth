<?php
namespace app\auth\controller;

use think\Request;
use think\Db;
use think\Config;
use think\Controller;

use Godok\Org\Auth;
use Godok\Org\FileManager;

class Profile extends Controller
{
	/**
	 * 用户资料
	 */
	public function index()
	{
	    if ( Request::instance()->isPost() ) {
	        $id = Auth::user('id');
	        $request = Request::instance();
	        $data = [
	            'name'=>$request->post('name','','strip_tags'),
	            'nickname'=>$request->post('nickname','','strip_tags'),
	            'phone'=>$request->post('phone','','number_int'),
	            'email'=>$request->post('email','','email')
	        ];
	        
	        if (
	            $info = Db::name( Config::get('auth.table_user') )
	            ->where(['id' => $id])
	            ->update($data)
	        ) {
	            return ['code'=>0, 'msg'=>'修改成功！'];
	        } else {
	            return ['code'=>-1010, 'msg'=>'修改失败！'];
	        }
	    } else {
	        $user = \Godok\Org\Auth::user();
	        $info = Db::name( Config::get('auth.table_user') )->where('id',$user['id'])->find();
	        $user = array_merge($user, $info);
	        $user['last_login_time'] = date('Y-m-d H:i:s',$info['last_login_time']);
	        $data = ['groupname'=>'游客','user'=>$user];
	        if ( !empty( $user['groupids'] ) ) {
	            $groups = Db::name( Config::get('auth.table_group') )->field('GROUP_CONCAT(title SEPARATOR \'][\') as name')->where('id', 'in', $user['groupids'])->find();
	             
	            if ($groups) {
	                $data['groupname'] = '['.$groups['name'].']';
	            }
	        
	        }
	        return $this->fetch('index',$data);
	    }
	    
	}



}

