<?php
namespace app\index\model;
use think\Db;
use think\Session;
class CollectModel
{
    private $Collect;

    public function __construct()
    {
        $this->Collect = Db::name('person_collect');
    }

    //获取用户信息
    public function Getcollect_count($uid)
    {
        $map['uid'] = $uid;
        return  $this->Collect->where($map)->count();
    }

}