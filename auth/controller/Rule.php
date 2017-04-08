<?php
namespace app\auth\controller;

use think\Request;
use think\Db;
use think\Controller;
use Godok\Org\Auth;
use think\Config;

class Rule extends Controller
{
    /**
     * 规则列表
     */
    // http://127.0.0.1/auth/Index/index
    public function index()
    {
        $data['menu'] = $this->menu2Html( \Godok\Org\Auth::menu() );
		return $this->fetch('index',$data);
    }
    /**
     * 格式化菜单
     */
    private  function menu2Html($list)
    {
        $menu = '';
        if( is_array($list) ) {
            $menu .= '<ol class="dd-list">';
            foreach( $list as $li ) {
                $menu .= '<li class="dd-item" id="rid' . $li['id'] . '" data-id="' . $li['id'] . '" data-listorder="' . $li['listorder'] . '" data-pid="' . $li['pid'] . '" title="' . $li['title'] . '" data-icon="' . $li['icon'] . '" data-module="' . $li['module'] . '" data-controller="' . $li['controller'] . '" data-action="' . $li['action'] . '" data-ismenu="' . $li['ismenu'] . '" data-condition="' . $li['condition'] . '" data-status="' . $li['status'] . '">';
                $menu .= $this->buildHandle($li);
                
                if( isset($li['children']) ) {
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
     * 把单条权限组装成nestable样式
     */
    private function buildHandle($li){
        $menu = '<div class="dd-handle dd-status-'.$li['status'].'">';
        if( !empty($li['icon']) ) {
            $menu .= '<i class="' . $li['icon'] . '"></i> ';
        }
        $menu .= $li['title'];
        if( $li['ismenu'] ) {
            $menu .= ' ≡';
        }
        if( !empty($li['module']) && !empty($li['controller']) && '*' != $li['module'] && '*' != $li['controller'] ) {
            $url = \think\Request::instance()->root().'/'. $li['module'] . '/' . $li['controller'] . '/' . $li['action'];
            if( !empty($li['condition']) ) {
                $url .= '?' . $li['condition'];
            }
            $menu .= ' <em class="dd-url">' . $url . '</em>';
        }
        if($li['status']) {
            $menu .= '<span class="dd-tool-right dd-nodrag"> <i class="fa fa-check-circle" title="点击禁用"></i> <i class="fa fa-plus" title="添加子菜单"></i>  <i class="fa fa-edit" title="修改"></i> <i class="fa fa-trash-o" title="删除"></i> </span>';
        } else {
            $menu .= '<span class="dd-tool-right dd-nodrag"> <i class="fa fa-ban" title="点击启用"></i> <i class="fa fa-plus" title="添加子菜单"></i> <i class="fa fa-edit" title="修改"></i> <i class="fa fa-trash-o" title="删除"></i> </span>';
        
        }
        $menu .= '</div>';

        return $menu;
    }
    /**
     * 添加权限规则
     */
    public function add()
    {
        if ( Request::instance()->isPost() ) {
            $request = Request::instance();
            $data = $request->only('pid,title,module,controller,action,status,ismenu,condition,icon,listorder','post');
            isset($data['status']) || $data['status'] = 1;
            isset($data['ismenu']) || $data['ismenu'] = 0;

            if( empty($data['title']) ) {
                return ['code'=>-1070, 'msg'=>'菜单名不能为空！'];
            }
            if (
                Db::name( Config::get('auth.table_rule') )->insert($data)
            ) {
                $id = Db::name(Config::get('auth.table_rule'))->getLastInsID();
                $data = Db::name(Config::get('auth.table_rule'))->where('id',$id)->find();
   
                $menu = '<li class="dd-item"  id="rid' . $data['id'] . '" data-id="' . $data['id'] . '" data-listorder="' . $data['listorder'] . '" data-pid="' . $data['pid'] . '" title="' . $data['title'] . '" data-icon="' . $data['icon'] . '" data-module="' . $data['module'] . '" data-controller="' . $data['controller'] . '" data-action="' . $data['action'] . '" data-ismenu="' . $data['ismenu'] . '" data-condition="' . $data['condition'] . '" data-status="' . $data['status'] . '">'
                    .$this->buildHandle($data).'</li>';
                return ['code'=>0, 'msg'=>'添加成功！', 'menu'=>$menu, 'pid'=>'#rid'.$data['pid']];
            } else {
                return ['code'=>-1030, 'msg'=>'添加失败！'];
            }
            
        } else {
            $this->fetch();
        }
    }
    /**
     * 修改权限规则
     * -----------------
     * /get  : view
     * -----------------
     * /post : update 
     * @$_POST[
     *  'id'       => 'id',
     *  'pid' => '上级id',
     *  'title' => '名称',
     *  ...
     *  'listorder'     => '排序编号',
     *  'status' => '状态'
     *  ]
     *  修改多条
     *  $_POST[{'id' => 'id','title'=>'名称',...},{...},{...}]
     * -----------------
     * /call :
     * @param $id = int
     * @param $data = [
     *  'pid' => '上级id',
     *  'title' => '名称',
     *  ...
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
                            foreach(['pid','title','module','controller','action','status','ismenu','condition','icon','listorder'] as $key) {
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
                    $data = Request::instance()->only('pid,title,module,controller,action,status,ismenu,condition,icon,listorder','post');
                }
                
                if(isset($data['pid']) && $data['pid'] == $id ) {
                    unset($data['pid']);
                }
                if (isset($data['title']) && empty($data['title'])) {
                    return ['code'=>-1030, 'msg'=>'菜单名不能为空！'];
                }
                
                if ( empty($data) ) {
                    return ['code'=>-1040, 'msg'=>'提交的数据错误！'];
                }
                if(
                    Db::name( Config::get('auth.table_rule') )
                    ->where(['id' => $id])
                    ->update($data)
                ) {
                    $data = Db::name(Config::get('auth.table_rule'))->where('id',$id)->find();

                    return ['code'=>0, 'msg'=>'修改成功！', 'id'=>'#rid'.$id, 'handle'=>$this->buildHandle($data), 'data'=> $data];
                } else {
                    return ['code'=>-1040, 'msg'=>'修改失败！'];
                }
            }
        } else {
            return $this->fetch();
        }
       
        
    }
    
    /**
     * 删除菜单
     * @$_POST[id]
     * @$_POST[more] 连同子菜单一起删除，否则子菜单移至顶级菜单
     */
    public function delete(){
        $request = Request::instance();
        $id = $request->post('id','','number_int');
        $more = $request->post('more','','number_int');
        if(empty($id)){
            return ['code'=>-1000, 'msg'=>'缺少参数！'];
        }
        if ( empty($more) ) {
            if(
                Db::name( Config::get('auth.table_rule') )
                ->where(['id' => $id])
                ->delete()
            ) {
                $children = Db::name( Config::get('auth.table_rule') )
                ->where(['pid' => $id])
                ->update(['pid'=>0]);
                return ['code'=>0, 'msg'=>'删除成功！', 'children'=>$children];
            } else {
                return ['code'=>-1010, 'msg'=>'删除失败！'];
            }
        } else {
            $menu = \Godok\Org\Auth::menu(false,$id);
            
            $ids = $this->tree2id($menu);
            $ids[] = $id;
            if(
                Db::name( Config::get('auth.table_rule') )
                ->where('id', 'in', $ids)
                ->delete()
            ) {
                return ['code'=>0, 'msg'=>'删除成功！'];
            } else {
                return ['code'=>-1010, 'msg'=>'删除失败！'];
            }
        }
        
        
    }
 
    /**
     * 获取子菜单ID
     */
    private  function tree2id($list)
    {
        $ids = [];
        if( is_array($list) ) {
            foreach( $list as $li ) {
                $ids[] = $li['id'];
                if( isset($li['children']) ) {
                    $ids= array_merge($ids, $this->tree2id($li['children']));
                }
            }
            return $ids;
        } else {
            return [];
        }
    }

}
