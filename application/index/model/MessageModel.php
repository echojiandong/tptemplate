<?php
namespace app\index\model;
use think\Db;
use think\Session;
class MessageModel
{
    private $Message;

    public function __construct()
    {
        $this->Message = Db::name('message');
    }

    //获取用户信息
    public function Getmessage_count($uid)
    {
        $map['uid'] = $uid;
        $map['status'] = 0;
        return  $this->Message->where($map)->count();
    }

}