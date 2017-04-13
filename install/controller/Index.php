<?php
namespace app\install\controller;

use think\Db;
use think\Controller;
use think\Request;
use think\Config;

class Index extends Controller
{
    public function index()
    {
        if(is_file(APP_PATH.'install'.DS.'godoa.sql.lock')) {
            $this->error('已经安装');
        }
        if ( Request::instance()->isPost() ) {
            restore_exception_handler();
            if(!is_file(APP_PATH.'install'.DS.'goauth.sql')) {
                return ['code'=>-1000, 'msg'=>'未找到数据库备份文件！'];
            }
            
            $data = Request::instance()->only('hostname,database,username,password,hostport','post');
            $config = "<?php
 // +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // 数据库类型
    'type'           => 'mysql',
    // 服务器地址 
    'hostname'       => '".$data['hostname']."',
    // 数据库名
    'database'       => '".$data['database']."',
    // 用户名
    'username'       => '".$data['username']."',
    // 密码
    'password'       => '".$data['password']."',
    // 端口
    'hostport'       => '".$data['hostport']."',
    // 连接dsn
    'dsn'            => '',
    // 数据库连接参数
    'params'         => [],
    // 数据库编码默认采用utf8
    'charset'        => 'utf8',
    // 数据库表前缀
    'prefix'         => 'goa_',
    // 数据库调试模式
    'debug'          => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'         => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'    => false,
    // 读写分离后 主服务器数量
    'master_num'     => 1,
    // 指定从服务器序号
    'slave_no'       => '',
    // 是否严格检查字段是否存在
    'fields_strict'  => true,
    // 数据集返回类型 array 数组 collection Collection对象
    'resultset_type' => 'array',
    // 是否自动写入时间戳字段
    'auto_timestamp' => false,
    // 是否需要进行SQL性能分析
    'sql_explain'    => false,
];
            ";
            file_put_contents(APP_PATH.'database.php',$config);
            try {
                $conn = new \PDO("mysql:host=".$data['hostname'].";port=".$data['hostport'], $data['username'], $data['password']);
                $conn->exec("SET NAMES 'utf8';");
                $conn->exec("CREATE DATABASE IF NOT EXISTS ".$data['database']." DEFAULT CHARSET utf8 COLLATE utf8_general_ci");
                $conn = null;
            } catch (\Exception $e) {
                return ['code'=>-1020, 'msg'=>'创建数据库失败,请检查数据库链接信息是否正确'];
            }
            
            try {
                $conn = new \PDO("mysql:host=".$data['hostname'].";port=".$data['hostport'].";dbname=".$data['database'], $data['username'], $data['password']);
                $conn->exec("SET NAMES 'utf8';");
                $sql = explode(';', file_get_contents(APP_PATH.'install'.DS.'godoa.sql'));
                foreach($sql as $s) {
                    if( !empty($s) ) {
                        $conn->exec($s);
                    }
                } 
                return ['code'=>0, 'msg'=>'数据导入成功！记得删除安装包哦'];
            } catch (\Exception $e) {
                return ['code'=>-1020, 'msg'=>'导入数据失败'];
            }
            
        } else {
            return $this->fetch();
        }
		
    }


}
