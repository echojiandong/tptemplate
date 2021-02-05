<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use app\manage\model\Person;
class PersonSon extends Model
{
	/**
     * 获取用户列表数据
     * @author 韩春雷 2019.3.27
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    array       $where [筛选条件]
     * @param    int         $page  [页码]
     * @param    int         $limit [每页数量]
     * @return   [array]                  
     */
	public static function getPersonSonList($where,$page=null,$limit=null)
	{
		//获取子用户列表
		$num = $limit * ($page - 1);
		$personSonList = Db::query("select ps.*,p.nickName,p.gradeAuth,p.act_status,p.user_id from guard_person_son ps join guard_person p on p.id = ps.person_id ".$where." limit ".$num.",".$limit);
		$count = Db::query("select count(ps.id) as num from guard_person_son ps join guard_person p on p.id = ps.person_id ".$where);
		$personSonList = person::changPersonList($personSonList);
		
		$personSonList['count'] = $count[0]['num'];

		return $personSonList;
	}
}