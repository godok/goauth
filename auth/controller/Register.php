<?php
namespace app\auth\controller;

use think\Request;
use think\Db;
use think\Session;
use think\Controller;
use Godok\Org\Auth;

class Register extends Controller
{
	/**
	 * 首页
	 */
	public function index(){
        return $this->fetch();
	}


}

