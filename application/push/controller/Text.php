<?php
namespace app\push\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;
use think\Cache;
use think\worker\Server;

/**
 * 案例测试文件
 */
class Text extends Controller
{

    protected  $redis;
    protected $global_uid = 0;
    // protected $socket = 'websocket://127.0.0.1:2346';
    public function _initialize()
    {
        $data = "key1";
        $table_arr = ['key1', 'key2', 'key3'];

        $istrue = array_search($data,$table_arr);
        dump($istrue);die;
        // $this->redis = Cache::store('redis')->handler();
        // $table_arr = $this->redis->hGetAll('socket');
        // dump($table_arr);
        for ($i = 1; $i < 10; $i++) {
            $data = uniqid();
            // $table_arr = $this->redis->lPush($i, 1);
            // $this ->redis ->hSet('socket', $i, $data);
        }

        // $table_arr = $this->redis->hGetAll('socket');
    }

    /**
     * 测试小程序发送模板消息
     */
    public function index()
    {
        $accessToken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$accessToken;
        $data = [
            'touser' =>  'o5hkp4ztYteM0FFabZBzv-hnbA7w',
            'template_id' => 'L9mxNsEVQ7XmTU_Uve1c25tTEd0f87OTLgsiH0DCY7s',
            'form_id' => '24be89b76f6d4409a9d93bd391461f92',
            'data' => [
                'keyword1' => [
                    'value' => 'dkds' 
                ],
                'keyword2' => [
                    'value' => '123456' 
                ],
                'keyword3' => [
                    'value' =>  '789'
                ]
            ]
        ];
      
        $res = $this->post($url, $data);
        // dump($res);
    }


    /**
     * 获取access_token
     */
    public function getAccessToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx0a503ddac9fdb1f3&secret=93a20fcabd2781c784f25574aa4b8295";
        // 验证access_token是否有效
        $html = file_get_contents($url);
        $res = json_decode($html, true);
        
        return $res['access_token'];
    }

    /**
     * post 请求方法
     */
    function post($url, $params)
    {
        $curl = curl_init();
        $datas =  json_encode($params);
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $datas);
        
        $data = curl_exec($curl);
        // if (curl_errno($curl)) {
        //     return [
        //         'success' => false,
        //         'msg' => curl_error($curl)
        //     ];
        // }
        curl_close($curl);
        return json_decode($data, true);
    }


    /**
     * 微信支付测试demo
     */
    public function wechatPay()
    {
        return $this->fetch('/index/text/index');
    }

    public function jiecheng()
    {
        // $n = 1000;
        // return array_product(range(1, $n));
        $num = 10;
        $resul = 1;
        foreach (range(1, $num) as $index => $val) {
            $index === 0 OR $resul *= $val;
        }
        return $resul;
    }


    /**
     * 测试视频下载
     */
    public function testDownload()
    {
        return $this->fetch('/index/text/index');
    }

    /**
     * 测试
     */
    public function test()
    {
        return $this->fetch('/index/text/index');
    }

    

}