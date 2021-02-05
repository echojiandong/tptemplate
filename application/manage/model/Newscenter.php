<?php
namespace app\manage\model;
use think\Model;
use think\Request;
use think\Session;
use think\Db;

class Newscenter extends Model{

	public static function operationinterface_one($param){

		$where['title'] = isset($param['title']) && !empty($param['title']) ? $param['title'] : '通知';

		$where['content'] = isset($param['content']) && !empty($param['content']) ? $param['content'] : '';

		$sid = isset($param['sid']) && !empty($param['sid']) ? $param['sid'] : 0;

		$user = Db::name('person') ->field('id') ->select();

		$user_ids = array_column($user, 'id');
		// 添加
		if($sid == 0){

			$res = self::addmessage($user_ids, $where, $param['type']);
		}else{

			$where['sid'] = $sid;

			$res = self::updmessage($user_ids, $where);
		}

		return $res;
	}

	public static function operationinterface_two($param){

		$where['title'] = isset($param['title']) && !empty($param['title']) ? $param['title'] : '通知';

		$where['content'] = isset($param['content']) && !empty($param['content']) ? $param['content'] : '';

		$where['grade'] = isset($param['grade']) && !empty($param['grade']) ? $param['grade'] : 0;

		$where['subject'] = isset($param['subject']) && !empty($param['subject']) ? $param['subject'] : 0;

		$sid = isset($param['sid']) && !empty($param['sid']) ? $param['sid'] : 0;

		$user_where = 'type = 0 ';

		$user_where .= $where['grade'] ? ' and grade_id = '.$where['grade'] : '';

		$user_where .= $where['subject'] ? ' and subject_id = '.$where['subject'] : '';

		$user = Db::name('video_log') ->field('person_id as id') 
									  ->where($user_where) 
									  ->group('person_id') 
									  ->select();

		$user_ids = array_column($user, 'id');
		// 添加
		if($sid == 0){

			$res = self::addmessage($user_ids, $where, $param['type']);

		}else{

			$where['sid'] = $sid;

			$res = self::updmessage($user_ids, $where);
		}

		return $res;

	}

	public static function operationinterface_three($param){

		$where['title'] = isset($param['title']) && !empty($param['title']) ? $param['title'] : '通知';

		$where['content'] = isset($param['content']) && !empty($param['content']) ? $param['content'] : '';

		$sid = isset($param['sid']) && !empty($param['sid']) ? $param['sid'] : 0;

		$user = Db::name('person') ->field('id') ->select();

		$buy_user = Db::name('video_log') ->field('person_id as id') 
									  	  ->group('person_id') 
									  	  ->select();

		$user = !empty($user) ? $user : ['id' => 0] ;

		$buy_user = !empty($buy_user) ? $buy_user : ['id' => 0] ;

		$user_ids = array_diff(array_column($user, 'id'),array_column($buy_user, 'id'));
		// 添加
		if($sid == 0){

			$res = self::addmessage($user_ids, $where, $param['type']);

		}else{

			$where['sid'] = $sid;

			$res = self::updmessage($user_ids, $where);
		}

		return $res;
		
	}

	public static function addmessage($uids, $where, $type){

		if(empty($uids)){

			return 1;
		}

		$uids = array_unique($uids);

		$time = time();

		$data = [];

		foreach($uids as $key =>&$val){

			$val != 0 && $data[] = ['uid' => $val
							, 'title' => $where['title']
							, 'desc' => $where['content']
							, 'create_time' => $time
							, 'status' => 0
							, 'type' => 0
						  ];
		}

		$newsdata = ['title' => $where['title']
					 ,'content' => $where['content']
					 ,'sender' => Session::get("manageinfo")['uid']
					 ,'numbers' => count($data)
					 ,'sendobj' => $type
					 ,'sendtime' => $time
					 ,'updtime' => $time
					 ,'sendobj_grade' => isset($where['grade']) && $where['grade'] ? $where['grade'] : 0
					 ,'sendobj_subject' => isset($where['subject']) && $where['subject'] ? $where['subject'] : 0
						];

		Db::transaction(function() use ($data, $newsdata){

			if(!empty($data)){

				Db::name('message') ->insertAll($data);
			}

			Db::name('newscenter') ->insert($newsdata);
		});

		return 1;
	}

	public static function updmessage($uids, $where){

		$stime = (Db::name('newscenter') ->field('sendtime') 
										 ->where(['id' => $where['sid']]) 
										 ->find())['sendtime'];

		Db::transaction(function() use ($uids, $where, $stime){

			Db::name('message') ->where('uid', 'in', $uids) 
								->where('create_time', '=', $stime) 
								->update(['title' => $where['title'], 'desc' => $where['content']]);

			Db::name('newscenter') ->update(['title' => $where['title']
											, 'content' => $where['content']
											, 'id' => $where['sid']
											, 'updtime' => time()]);
		});

		return 1;
	}
}