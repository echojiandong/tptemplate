<?php 
namespace app\push\controller;
 
use think\worker\Server;
use think\Session;
use think\Cache;
use think\cache\driver\Redis;

class Worker extends Server
{
    // protected $socket = 'websocket://39.106.90.110:2346';
    protected $socket = 'websocket://127.0.0.1:2346';
    protected $msg = ['code' => 1001, 'msg' => '您的账号已在别处登录', 'data' => ''];
    protected $redis_arr = '';
    protected $global_uid = 0;

    
    public function _initialize()
    {
        $config = [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '',
            'select' => 0,
            'timeout' => 0,
            'expire' => 0,
            'persistent' => false,
            'prefix' => '',
            ];
            
        $this->redis_arr=new Redis($config);
    }

    /**
     * 收到信息
     * @param $connection
     * @param $data    用户登录后返回的用户id
     */
    public function onMessage($connection, $data)
    {
        //登录连接时分配一个全局唯一的uid
        $connection ->uid = 'key'.++$this ->global_uid;
        //取出redis中所有连接客户端id
        $table_arr = $this ->redis_arr->hGetAll('socket');
        //查询当前登录id是否在redis中
        $is_true = array_search($data,$table_arr);
        if($is_true){
            //存在
            $this ->redis_arr ->hDel('socket',$is_true);
            $this ->redis_arr ->hSet('socket',$connection ->uid,$data);
            //找出对应客户端，强制下线
            foreach($this ->worker ->connections as $key =>$val){
                if(isset($val ->uid) && $val ->uid == $is_true){
                    $val ->send(json_encode($this ->msg));
                }
            }
        }else{
            //不存在则，追入redis中
            $this ->redis_arr ->hSet('socket',$connection ->uid,$data);
            echo $connection ->uid;
        }
    }
    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        if($this ->redis_arr == ''){
            $config = [
                'host' => '127.0.0.1',
                'port' => 6379,
                'password' => '',
                'select' => 0,
                'timeout' => 0,
                'expire' => 0,
                'persistent' => false,
                'prefix' => '',
                ];
                
            $this->redis_arr=new Redis($config);
        }

    }
 
    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        if(isset($connection ->uid)){
            //找出userid 对应key
            $client_id = $connection ->uid;
            //清除redis中对应uid
            $this ->redis_arr ->hDel('socket',$client_id);
            echo 'delete redis id is'.$client_id;
        }
    }
    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }
 
    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {

    }
}