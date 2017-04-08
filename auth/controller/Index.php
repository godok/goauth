<?php
namespace app\auth\controller;

use think\Db;
use think\Controller;
use Godok\Org\Auth;
use think\Config;

class Index extends Controller
{
    /**
     * 控制台
     */
    // http://127.0.0.1/auth/Index/index
    public function index()
    {
        $groupids = \Godok\Org\Auth::user('groupids');
        $data = ['groupname'=>'游客'];
        if( !empty($groupids) ) {
            $group = Db::name( Config::get('auth.table_group') )->where(['id' => $groupids[0]])->find();
            if ( $group ) {
                $data['groupname'] = $group['title'];
            }
        }
        $data['menu'] = $this->menu2Html( \Godok\Org\Auth::menu("ismenu=1 AND status=1") );

		return $this->fetch('index',$data);
    }
    
    /**
     * 首页
     */
    public function home(){
        return $this->fetch();
    }

    /**
     * 格式化菜单
     */
    private  function menu2Html($list,$level = 0)
    {
        $menu = '';
        $menulevel = ['first','second','third'];
        if ( is_array($list) ) {
            if( $level > 2 ) {
                $level = 2;
            }
            $menu .= $level > 0  ? '<ul class="nav nav-'.$menulevel[$level].'-level">' : '';
            foreach ( $list as $li ) {
                $menu .= '<li>';
                if( empty($li['module']) || empty($li['controller']) || '*' == $li['module'] || '*' == $li['controller'] ) {
                    $menu .= '<a href="javascript:void(0)" >';
                } else {
                    $href = \think\Request::instance()->root().'/'. $li['module'] . '/' . $li['controller'] . '/' . $li['action'];
                    if( !empty($li['condition']) ) {
                        $href .= '?' . $li['condition'];
                    }
                    $menu .= '<a href="' . $href . '" class="J_menuItem">';
                }
    
                if ( !empty($li['icon']) ) {
                    $menu .= '<i class="' . $li['icon'] . '"></i> ';
                }
                $menu .= '<span class="nav-label">' . $li['title'] . '</span>';
                if ( isset($li['children']) ) {
                    $children = $this->menu2Html($li['children'], $level + 1 );
                    $menu .= ' <span class="fa arrow"></span>';
                } else {
                    $children = '';
                }
                $menu .= '</a>' . $children;
                $menu .= '</li>';
            }
    
            $menu .= $level > 0 ? '</ul>' : '';
            return $menu;
        } else {
            return '';
        }
    }
}
