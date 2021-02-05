<?php
namespace app\manage\model;
use think\Model;
use think\Request;
use think\Db;
class Wechatnav extends Model
{

    public function insertWechatnav($data)
    {
        if (empty($data)) {
            return false;
        }
        return Db::name('wechat_nav')->insert($data);
    }

    public function wechatnavInfo($id)
    {
        if (!$id) {
            return false;
        }

        return Db::name('wechat_nav')->where(['id' => $id])->find();
    }
    
    public function updateWechatnav($id, $data)
    {
        if (!$id || !$data) {
            return false;
        }
        return Db::name('wechat_nav')->where(['id' => $id])->update($data);
    }

    public function deleteNav($id)
    {
        if (!$id) {
            return false;
        }
        return Db::name('wechat_nav')->where(['id' => $id])->update(['del_status' => 0, 'updatetime' => date('Y-m-d H:i:s', time())]);
    }

    public function navSort($id, $sort)
    {
        if (!$id) {
            return false;
        }

        return Db::name('wechat_nav')->where(['id' => $id])->update(['sort' => $sort]);
    }

    public function getNavList($idArr)
    {
        if (!$idArr) {
            return false;
        }

        return Db::name('wechat_nav')->where(['id' => ['in', $idArr]])->order('sort desc')->select();
        
    }
    
}