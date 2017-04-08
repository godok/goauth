<?php
namespace app\index\controller;

use think\Db;
use think\Controller;
use Godok\Org\Auth;
use think\Config;

class Index extends Controller
{
    public function index()
    {


		return $this->fetch();
    }


}
