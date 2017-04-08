<?php
namespace app\auth\controller;

use think\Request;
use think\Db;
use think\Controller;
use Godok\Org\Auth;
use think\Config;

class Group extends Controller
{
    /**
     * 规则列表
     */
    // http://127.0.0.1/auth/Group/index
    public function index()
    {
        
        $keywords = Request::instance()->param("keywords",'');
        $query= Db::name( Config::get('auth.table_group') )->field("id,title,status,description,listorder")->order('listorder asc');
        if ( !empty($keywords) ) {
            $query->where('title', 'like', '%'.$keywords.'%');
        }
        $data['list'] = $query->order("listorder asc,id asc")->limit(255)->select();
		return $this->fetch('index',$data);
    }
    /**
     * 绑定规则
     */
    public function rule()
    {
        $id = Request::instance()->get('id','','number_int');
        
        if ( Request::instance()->isPost() ) {
            if (empty($id)) {
                return ['code'=>-1010, 'msg'=>'参数错误！'];
            }
            if (1 == $id ) {
                return ['code'=>-1020, 'msg'=>'系统内置权限组不能修改！'];
            }
            $data = ['rules'=> trim( Request::instance()->post('rules',''), ',') ];
            if (
                Db::name( Config::get('auth.table_group') )
                ->where('id',$id)
                ->update($data)
            ) { 
                return ['code'=>0, 'msg'=>'保存成功！'];
            } else {
                return ['code'=>-1030, 'msg'=>'保存失败！'];
            }
            
        } else {
            if (empty($id)) {
                return '参数错误！';
            }
            if (1 == $id ) {
                return '系统内置权限组不能修改！';
            }
            $data = ['id'=>$id];
            $group = Db::name(Config::get('auth.table_group'))->where('id',$id)->find();
            $this->rules = explode(',', $group['rules']);//已绑定的规则
            $tree = \Godok\Org\Auth::menu();
            $tree[] = ['id'=>'all', 'pid'=>0, 'title'=>'所有权限','module'=>'','controller'=>'','action'=>'', 'ismenu'=>0];
            $data['menu'] = $this->menu2Html( $tree );
            return $this->fetch('rule',$data);
        }
        
    }
    /**
     * 格式化菜单
     */
    private  function menu2Html($list)
    {
        $menu = '';
        if( is_array($list) ) {
            $menu .= '<ol class="dd-list">';
            foreach ( $list as $li ) {
                $menu .= '<li class="dd-item ">';
                $menu .= '<div class="dd-handle "><div class="dd-nodrag">';
                if( !empty($li['icon']) ) {
                    $menu .= '<i class="' . $li['icon'] . '"></i> ';
                }
                if( in_array($li['id'], $this->rules) ) {
                    $menu .= '<span > <input name="rule[]" checked  type="checkbox" class="i-checks" value="'.$li['id'].'"> </span> ';
                } else {
                    $menu .= '<span > <input name="rule[]"  type="checkbox" class="i-checks" value="'.$li['id'].'"> </span> ';
                }
                
                $menu .= $li['title'];
                if ( $li['ismenu'] ) {
                    $menu .= ' ≡';
                }
                $menu .= '<span class="dd-tool-right"> <input name="selectChildren"  type="checkbox" class="i-checks-all" value="1"> 全选</span> ';
                if ( !empty($li['module']) && !empty($li['controller']) && '*' != $li['module'] && '*' != $li['controller'] ) {
                    $url = \think\Request::instance()->root().'/'. $li['module'] . '/' . $li['controller'] . '/' . $li['action'];
                    if( !empty($li['condition']) ) {
                        $url .= '?' . $li['condition'];
                    }
                    $menu .= ' <em class="dd-url dd-nodrag">' . $url . '</em>';
                }
                $menu .= '</div></div>';

                if ( isset($li['children']) ) {
                    $menu .= $this->menu2Html($li['children']);
                }
                $menu .= '</li>';
            }
    
            $menu .= '</ol>';
            return $menu;
        } else {
            return '';
        }
    }
    /**
     * 添加权限组
     */
    public function add()
    {
        if ( Request::instance()->isPost() ) {
            $data = Request::instance()->only('listorder,status,description,title','post');
            if ( empty($data) ) {
                return ['code'=>-1010, 'msg'=>'参数错误！'];
            }
            if (
                Db::name( Config::get('auth.table_group') )
                ->insert($data)
            ) {
                $data['id'] = Db::name(Config::get('auth.table_group'))->getLastInsID();
                return ['code'=>0, 'msg'=>'添加成功！', 'data'=>$data];
            } else {
                return ['code'=>-1020, 'msg'=>'添加失败！'];
            }
        } else {
            $this->fetch();
        }
    }
    /**
     * 修改，内置管理员不能禁用
     * -----------------
     * /get  : view
     * -----------------
     * /post : update 
     * @$_POST[
     *  'id'       => 'id',
     *  'title' => '名称',
     *  'description' => '描述',
     *  'listorder'     => '排序编号',
     *  'status' => '状态'
     *  ]
     *  修改多条
     *  $_POST[{'id' => 'id','title'=>'名称'},{},{}]
     * -----------------
     * /call :
     * @param $id = int
     * @param $data = [
     *  'title' => '名称',
     *  'description' => '描述',
     *  'listorder'     => '排序编号',
     *  'status' => '状态'
     *  ]
     */
    public function edit($id='', $data= '')
    {
        if ( Request::instance()->isPost() ) {
            
            //do update
            if( empty($id) ){
                $id = Request::instance()->get('id','','number_int');
            }
            
            if ( empty($id) ){
                //批量修改
                $data = json_decode(file_get_contents('php://input'));
                if( is_array($data) ){
                    foreach ( $data as $item ) {
                        if ( is_object($item) && property_exists($item,'id') && !empty($item->id)) {
                            $column = [];
                            foreach(['title','description','listorder','status'] as $key) {
                                if( property_exists($item,$key) ) {
                                    $column[$key] = $item->$key;
                                }
                            }
                            if ( !empty($column) ) {
                                $this->edit($item->id, $column);
                            }
                        } else {
                            return ['code'=>-1010, 'msg'=>'提交数据错误！'];
                        }
                    }
                } else {
                    return ['code'=>-1020, 'msg'=>'提交数据错误！'];
                }
                return ['code'=>0, 'msg'=>'修改成功！'];
            } else {
                //修改单条
                if ( empty( $data) ) {
                    $data = Request::instance()->only('title,description,listorder,status','post');
                }
            
                if (isset($data['title']) && empty($data['title'])) {
                    return ['code'=>-1020, 'msg'=>'权限组名称不能为空！'];
                }
               
       
                if( $id == 1 && isset($data['status']) && 1 != $data['status']) {
                    return ['code'=>-1030, 'msg'=>'超级组不能禁用！'];
                }
                //内置3个用户组不能修改
                if ( $id < 4) {
                    if (isset($data['title']) ) {
                        unset($data['title']);
                    }
                    if (isset($data['description']) ) {
                        unset($data['description']);
                    }
                }
                if ( empty($data) ) {
                    return ['code'=>-1040, 'msg'=>'提交的数据错误！'];
                }
                //更新数据库
                if(
                    Db::name( Config::get('auth.table_group') )
                    ->where('id',$id)
                    ->update($data)
                ) {
                    return ['code'=>0, 'msg'=>'修改成功！'];
                } else {
                    return ['code'=>-1060, 'msg'=>'修改失败！'];
                }
            }
        } else {
            //get view
            $id = Request::instance()->get('id','','number_int');
            if(empty($id)){
                return '$_GET["id"]不能为空！';
            }
            $group = Db::name(Config::get('auth.table_group'))->where('id',$id)->find();
            $this->fetch('edit',['group'=>$group]);
        }
    }
    /**
     * 删除菜单
     * /post
     * @$_POST['id'] int
     */
    public function delete()
    {
        $request = Request::instance();
        $id = $request->post('id','','number_int');
    
        if (empty($id)) {
            return ['code'=>-1000, 'msg'=>'缺少参数id！'];
        }
        if (1 == $id) {
            return ['code'=>-1010, 'msg'=>'内置管理组不能删除！'];
        }
        if (2 == $id) {
            return ['code'=>-1020, 'msg'=>'登录用户组删除！'];
        }
        if (3 == $id) {
            return ['code'=>-1030, 'msg'=>'游客组不能删除！'];
        }
        $mygroups = Auth::getGroups(false,true);
        if (!in_array(1,$mygroups) && 1 != Auth::user('id') && !in_array($id, $mygroups)) {
            return ['code'=>-1040, 'msg'=>'无权限删除！'];
        }
        if (
            Db::name( Config::get('auth.table_group') )
            ->where('id', $id)
            ->delete()
        ) {
            return ['code'=>0, 'msg'=>'删除成功！'];
        } else {
            return ['code'=>-1050, 'msg'=>'删除失败！'];
        }
    }

}
