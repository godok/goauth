<?php
namespace app\auth\controller;

use think\Request;
use think\Db;
use think\Session;
use think\Controller;
use Godok\Org\Auth;
/**
 * 公共控制器
 */
class PublicDemo extends Controller {
	/**
	 * fa字体选择
	 */
	public function fontawesome(){
        return $this->fetch();
	}


}

