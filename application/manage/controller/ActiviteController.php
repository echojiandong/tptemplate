<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
//活动管理
class ActiviteController extends author
{
    /**
	 * 活动管理首页列表
	 * @author 韩春雷
	 * @DateTime 2019年8月26日
	 */
    public function index(){
        return $this->view("/manage/activite/index");
    }

    /**
	 * 获取活动列表数据
	 * @author 韩春雷
	 * @DateTime 2019年8月26日
	 */
    public function getIndexList(){

    }
}