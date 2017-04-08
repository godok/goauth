<?php
namespace app\auth\controller;

use think\Request;
use think\Db;
use think\Controller;
use think\Config;

use Godok\Org\Auth;
use Godok\Org\Filter;

class User extends Controller
{
    /**
     * 用户列表
     */
    // http://127.0.0.1/auth/User/index
    public function index()
    {
        
        $keywords = Request::instance()->param("keywords",'');
        $query= Db::name( Config::get('auth.table_user') )
                ->field("id,username,name,nickname,avatar,phone,status,listorder")
                ->where('deleted',0)
                ->order('listorder asc');
        if ( !empty($keywords) ) {
            $query->where('name|nickname|phone', 'like', '%'.$keywords.'%');
        }
        $data['list'] = $query->paginate(10);
		return $this->fetch('index',$data);
    }
    // http://127.0.0.1/auth/User/recycle
    public function recycle()
    {
        if ( Request::instance()->isPost() ) {
            $data = Request::instance()->only('id,do','post');
            if (!isset($data['id']) || empty($data['id']) ) {
                return ['code'=>-1000, 'msg'=>'参数错误！'];
            }
            if (isset($data['do']) && 'delete' == $data['do'] ) {
                //彻底删除
                if (
                    Db::name( Config::get('auth.table_user') )
                    ->where('id', $data['id'])
                    ->delete()
                ) {
                    Db::name( Config::get('auth.table_group_relation') )
                    ->where('uid', $data['id'])
                    ->delete();
                    return ['code'=>0, 'msg'=>'删除成功！'];
                } else {
                    return ['code'=>-1010, 'msg'=>'删除失败！'];
                }
            } else {
                //恢复
                if (
                    Db::name( Config::get('auth.table_user') )
                    ->where('id', $data['id'])
                    ->update(['deleted'=>0])
                ) {
                    return ['code'=>0, 'msg'=>'恢复成功！'];
                } else {
                    return ['code'=>-1010, 'msg'=>'操作失败！'];
                }
            }
        } else {
            $keywords = Request::instance()->param("keywords",'');
            $query= Db::name( Config::get('auth.table_user') )
            ->field("id,username,name,nickname,avatar,phone,status,listorder")
            ->where('deleted',1)
            ->order('listorder asc');
            if ( !empty($keywords) ) {
                $query->where('name|nickname|phone', 'like', '%'.$keywords.'%');
            }
            $data['list'] = $query->paginate(10);
            return $this->fetch('recycle',$data);
        }
        
    }
    /**
     * 创建帐号
     * -----------------
     * /get  : view
     * -----------------
     * /post : insert 
     * @param ↓
     * $_POST[
     *  'username' => '用户名',
     *  'password' => '密码',
     *  'name'     => '真是姓名',
     *  'nickname' => '昵称',
     *  'avator'   => '头像url',
     *  'email'    => '电子邮箱',
     *  'phone'    => '手机号码',
     * ]
     * -----------------
     */
    // http://127.0.0.1/auth/User/add
    public function add()
    {
        if ( Request::instance()->isPost() ) {
            $data = Request::instance()->only('username,password,name,nickname,avator,email,phone','post');
            if (!isset($data['username']) || !Filter::username($data['username']) ) {
                return ['code'=>-1000, 'msg'=>'用户名格式错误！'];
            }
            if ( isset($data['password']) && Filter::password($data['password']) ) {
                $data['password'] = md5($data['password']);
            } else {
                return ['code'=>-1010, 'msg'=>'密码格式错误！'];
            }
            if ( Db::name( Config::get('auth.table_user') )->where('username',$data['username'])->find() ){
                return ['code'=>-1020, 'msg'=>'用户名已存在！'];
            }
            //新增
            if ( !isset($data['avatar']) || empty($data['avatar']) ) {
                $data['avatar'] = Config::get('site.resource_url').'images/avatar/default.jpg';
            }
            if ( empty($data) ) {
                return ['code'=>-1030, 'msg'=>'创建失败！'];
            }
            if (
                Db::name( Config::get('auth.table_user') )
                ->insert($data)
            ) {
                $data['id'] = Db::name(Config::get('auth.table_group'))->getLastInsID();
                return ['code'=>0, 'msg'=>'创建成功！', 'data'=>$data];
            } else {
                return ['code'=>-1040, 'msg'=>'创建失败！'];
            }
        } else {
            return $this->fetch('edit',['title'=>'创建用户']);
        }
        
    }
    /**
     * 修改，内置管理员不能禁用
     * -----------------
     * /get  : view
     * -----------------
     * /post : update 
     * @$_POST[
     *  {
     *  'id'       => 'id',
     *  'username' => '用户名',
     *  'password' => '密码',
     *  'name'     => '真是姓名',
     *  'nickname' => '昵称',
     *  'avator'   => '头像url',
     *  'email'    => '电子邮箱',
     *  'phone'    => '手机号码',
     *  },{...},{...}
     * ]
     * -----------------
     * /call :
     * @param $id = int
     * @param $data = [
     *  'username' => '用户名',
     *  'password' => '密码',
     *  'name'     => '真是姓名',
     *  'nickname' => '昵称',
     *  'avator'   => '头像url',
     *  'email'    => '电子邮箱',
     *  'phone'    => '手机号码',
     * ]
     */
    public function edit($id='', $data = '')
    {
        if ( Request::instance()->isPost() ) {
            //do update
            if ( empty($id) ){
                $id = Request::instance()->get('id','','number_int');
            }
            
            if ( empty($id)  ){
                //批量修改
                $data = json_decode(file_get_contents('php://input'));
                if ( is_array($data) ){
                    foreach ( $data as $item ) {
                        if( is_object($item) && property_exists($item,'id') && !empty($item->id)) {
                            $column = [];
                            foreach (['username','password','name','nickname','avator','email','phone','status','listorder'] as $key) {
                                if( property_exists($item,$key) ) {
                                    $column[$key] = $item->$key;
                                }
                            }
                            if ( !empty($column) ) {
                                $this->edit($item->id, $column);
                            }
                        } else {
                            return ['code'=>-1070, 'msg'=>'提交数据错误！'];
                        }
                    }
                } else {
                    return ['code'=>-1080, 'msg'=>'提交数据错误！'];
                }
                return ['code'=>0, 'msg'=>'修改成功！'];
            } else {
                //修改单条
                if ( empty( $data) ) {
                    $data = Request::instance()->only('username,password,name,nickname,avator,email,phone,status,listorder','post');
                }
                
                if ( isset($data['username']) && !Filter::username($data['username']) ) {
                    return ['code'=>-1000, 'msg'=>'用户名格式错误！'];
                }
                if (isset($data['password']) && Filter::password($data['password']) ) {
                    $data['password'] = md5($data['password']);
                } elseif ( isset($data['password']) && !empty($data['password']) ) {
                    return ['code'=>-1010, 'msg'=>'密码格式错误！'];
                } elseif (isset($data['password'])) {
                    unset($data['password']);
                }
                //判断用户名是否重复
                if (
                    isset($data['username'])
                    && Db::name( Config::get('auth.table_user') )
                    ->where('username',$data['username'])
                    ->where('id', 'neq', $id)
                    ->find()
                ){
                    return ['code'=>-1020, 'msg'=>'用户名已存在！'];
                }
                //超级管理员只能自己修改
                if ( $id == 1 && 1 != Auth::user('id')) {
                    return ['code'=>-1030, 'msg'=>'您不能修改超级管理员！'];
                }
                if ( $id == 1 && isset($data['status']) && 1 != $data['status']) {
                    return ['code'=>-1040, 'msg'=>'超级管理员不能禁用！'];
                }
                if ( empty($data) ) {
                    return ['code'=>-1050, 'msg'=>'提交的数据错误！'];
                }
                //更新数据库
                if (
                    Db::name( Config::get('auth.table_user') )
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
            if ( empty($id) ){
                return '$_GET["id"]不能为空！';
            }
            $data = ['id'=>$id,'title'=>'修改帐号'];
            $data['user'] = Db::name(Config::get('auth.table_user'))->where('id',$id)->find();
            //获取用户组信息
            $data['groups'] = Auth::getGroups($id);
            $data['groupname'] = '';
            if ( !empty( $data['groups'] ) ) {
            
                foreach( $data['groups'] as $group) {
                    $data['groupname'] .= '['.$group['title'].']';
                }
            
            }
            $data['user']['last_login_time'] = date('Y-m-d H:i:s',$data['user']['last_login_time']);
            return $this->fetch('edit',$data);
        }
        
        
    }
    /**
     * 删除菜单
     * ---------------
     * @$_GET['id']
     */
    public function delete()
    {
        $request = Request::instance();
        $id = $request->post('id','');
    
        if ( empty($id) ) {
            return ['code'=>-1000, 'msg'=>'缺少参数！'];
        }
        if ( !is_array($id) ) {
            $id = [$id];
        }
        if (in_array('1',$id)){
            return ['code'=>-1020, 'msg'=>'超级管理不能删除！'];
        }
        if (
            Db::name( Config::get('auth.table_user') )
            ->where('id', 'in', $id)
            ->update(['deleted'=>1])
        ) {
            return ['code'=>0, 'msg'=>'删除成功！'];
        } else {
            return ['code'=>-1010, 'msg'=>'删除失败！'];
        }
    }
    /**
     * 绑定权限组
     * 非系统管理员仅能绑定自己拥有的权限组
     * ----------------------------
     * /get : view
     * @$_GET['id']
     * -----------------------------
     * /post : update
     * @$_POST[
     *   
     * ]
     */
    public function group()
    {
        $id = Request::instance()->get('id','');
        
        if(empty($id)){
            return '缺少参数！';
        }
        $mygroups = Auth::getGroups(false,true);
        if ( Request::instance()->isPost() ) {
            //超级管理员的权限组其他人不能修改
            if( $id == 1 ) {
                return ['code'=>-1010, 'msg'=>'不能修改超级管理员的权限组！'];
            }
            //update
            $data = explode(',', trim(Request::instance()->post('group',''), ',') );
            Db::name( Config::get('auth.table_group_relation') )->where('uid',$id)->delete();
            foreach ($data as $group_id) {
                if (empty($group_id) ) {
                    continue;
                }
                if( !in_array(1,$mygroups) && 1 != Auth::user('id') && !in_array($group_id, $mygroups)) {
                    return ['code'=>-1020, 'msg'=>'非系统管理员只能绑定自己拥有的权限组！'];
                }
                if(
                   !Db::name( Config::get('auth.table_group_relation') )
                    ->insert(['uid'=>$id, 'group_id'=>$group_id])
                ) {
                    return ['code'=>-1030, 'msg'=>'保存失败！'];
                }
            }
            return ['code'=>0, 'msg'=>'保存成功！'];
        } else {
            //view
            //超级管理员的权限组其他人不能修改
            if ( $id == 1 ) {
                return '不能修改超级管理员的权限组！';
            } 
            $query= Db::name( Config::get('auth.table_group') )->field("id,title,status,description,listorder")->order('listorder asc');
            if ( !in_array(1,$mygroups) && 1 != Auth::user('id')) {
                $query->where('id','in',$mygroups);
            }
            $keywords = Request::instance()->param("keywords",'');
            if ( !empty($keywords) ) {
                $query->where('title', 'like', '%'.$keywords.'%');
            }
            $data['list'] = $query->limit(255)->select();
            $groups = Auth::getGroups($id);
            $data['groups'] = [];
            $data['id'] = $id;
            foreach ($groups as $val) {
                $data['groups'][] = $val['id'];
            }
            return $this->fetch('group',$data);
        }
        
    }

}
