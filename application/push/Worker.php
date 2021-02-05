<?php 
namespace app\push\controller;
 
use think\worker\Server;
use think\Controller;
use think\Session;
use think\Cache;
use Redis;
use Workerman\Lib\Timer;
 
// 心跳间隔55秒
define('HEARTBEAT_TIME', 3600);

class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2346';
    protected $msg = ['code' => 1001, 'msg' => '您的账号已在别处登录', 'data' => ''];
    protected $redis_arr = '';
    protected $global_uid = 0;
    protected $data = '';
    /**
     * 收到信息
     * @param $connection
     * @param $data    用户登录后返回的用户id
     */
    public function onMessage($connection, $data)
    {
        $this->data = $data;
        //登录连接时分配一个全局唯一的uid
        $connection ->uid = uniqid('key_');
        //不存在则，追入redis中
        $this ->redis_arr ->hSet('socket',$connection ->uid,$data);
        $expireTime = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
        //设置键的过期时间
        $this ->redis_arr->expireAt('socket', $expireTime);
    }
    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        if($this ->redis_arr == ''){
            $this ->redis_arr = new Redis();
            $this ->redis_arr ->connect('59.110.29.237',6379);
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
        //var_dump($worker->connections);
        Timer::add(1, function()use($worker){
            $time_now = time();
            // var_dump($connection);
            if($this ->redis_arr == ''){
                $this ->redis_arr = new Redis();
                $this ->redis_arr ->connect('59.110.29.237',6379);
            }
            //取出redis中所有连接客户端id
            $table_arr = $this ->redis_arr ->hGetAll('socket');
            //查询当前登录id是否在redis中
            $is_true = array_search($this->data,$table_arr);
            $sum=$this->get_array_repeats($table_arr,$this->data);
            foreach($worker->connections as $connection) {
                if(isset($connection ->uid) && $connection ->uid == $is_true && $sum>=2){
                    $connection ->send(json_encode($this ->msg));
                    $connection->close();
                }
                // var_dump($worker->connections);
                $jicheng = $connection;
                // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                //echo $jicheng->lastMessageTime;
                if (empty($jicheng->lastMessageTime)) {
                    $jicheng->lastMessageTime = $time_now;
                    continue;
                }
                // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                if ($time_now - $jicheng->lastMessageTime > HEARTBEAT_TIME) {
                // $connection->close();
                    echo "下线";
                }
            }
        });
    }
    //计算$string在$array(需为数组)中重复出现的次数
    public function get_array_repeats(array $array,$string) {
    
        $count = array_count_values($array);
        //统计中重复元素的次数，再重组数组， 
        //打印array_count_values($array)出，结果：
        //Array(
        //    [1] => 2
        //    [hello] => 2
        //    [world] => 1
        //)
        if (key_exists($string,$count)){
        return $count[$string];
        }else{
            return 0;
        }
    }
}