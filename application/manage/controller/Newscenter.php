<?php 
namespace app\manage\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use think\Image;
use app\manage\model\Newscenter as Nc;

class Newscenter extends author{

	private $param;
	//  当前登录人信息（ 以后便于扩展）
	private $userinfo;	
	//  消息类型
	private $msgType = [1 => '全部', 2 => '已购买', 3 => '未购买'];			

	public function _initialize(){

		parent::_initialize();

		$this ->param = Request::instance() ->param(true);

		$this ->userinfo = Session::get("manageinfo");
	}

	public function shownews(){

		$this ->assign('msgType', $this ->msgType);

		return $this ->fetch();
	}

	public function newslistInterface(){

		$param = $this ->param;

		$msgtype = $this ->msgType;

		$where = '1=1';

		$where .= isset($param['title']) && !empty($param['title']) ? ' and title like "%'.$param['title'].'%"' : '' ;

		$where .= isset($param['type']) && !empty($param['type']) ? ' and sendobj = '.$param['type'] : '' ;

		$where .= isset($param['btime']) && !empty($param['btime']) ? ' and sendtime > '.strtotime($param['btime']) : '';

		$where .= isset($param['etime']) && !empty($param['etime']) ? ' and sendtime < '.strtotime($param['etime']) : '';

		$page = isset($param['page']) && !empty($param['page']) ? $param['page'] : 1;

		$limit = isset($param['limit']) && !empty($param['limit']) ? $param['limit'] : 10;

		$field = 'id,title,content,numbers,sender,sendobj,sendtime,sendobj_grade,sendobj_subject';

		$data = Db::name('newscenter') ->field($field) ->where($where) ->page($page, $limit) ->select();

		$count = (Db::name('newscenter') ->field('count(1) as counts') ->where($where) ->find())['counts'];

		$userlist = Db::name('user') ->field('username,uid') ->select();
		//  管理 列表
		$ulist = empty($userlist) ? [] : array_combine(array_column($userlist, 'uid'), array_column($userlist, 'username'));

		$subjectlist = Db::name('subject') ->field('id,subject') ->select();
		// 科目列表
		$sblist = empty($subjectlist) ? [] : array_combine(array_column($subjectlist, 'id'), array_column($subjectlist, 'subject'));

		$record = array_map(function($v) use ($ulist, $sblist, $msgtype){

				$v['sender'] = isset($ulist[$v['sender']]) ? $ulist[$v['sender']] : '匿名';

				$v['sendtime'] = date('y-m-d H:i:s',$v['sendtime']);

				$v['sendobj'] = isset($msgtype[$v['sendobj']]) ? $msgtype[$v['sendobj']] : '未识别的消息类型';

				$v['sendobj_grade'] = $v['sendobj_grade'] == 0 ? '---' : $v['sendobj_grade'].'年级';

				$v['sendobj_subject'] = !isset($sblist[$v['sendobj_subject']]) ? '---' : $sblist[$v['sendobj_subject']];

				return $v;

			},$data);

		ajaReturn($record, 0, 'success', $count);
	}

	public function newsoperation(){

		$grade_list = Db::name('video_class') ->field('grade_id') 
											  ->group('grade_id') 
											  ->select();

		$id_arr = array_column($grade_list, 'grade_id');

		$glist = Db::name('grade') ->field('id,grade') ->where('id', 'in', $id_arr) ->select();

		$sid = isset($this ->param['sid']) ? $this ->param['sid'] : 0;

		$this ->assign('sid', $sid);

		$this ->assign('glist', $glist);

		$this ->assign('msgType', $this ->msgType);

		return $this ->fetch();
	}
	// 年级选择
	public function gradechoice(){

		$grade = isset($this ->param['grade']) && !empty($this ->param['grade']) ? $this ->param['grade'] : 7 ;

		$subject_list = Db::name('video_class') ->field('subject_id') 
												->where(['grade_id' => $grade]) 
												->group('subject_id') 
												->select();

		$id_arr = array_column($subject_list, 'subject_id');

		$subjectlist = Db::name('subject') ->field('id,subject') ->where('id', 'in', $id_arr) ->select();
		// 科目列表
		$sblist = empty($subjectlist) ? [] : array_combine(array_column($subjectlist, 'id'), array_column($subjectlist, 'subject'));

		ajaReturn($sblist, 0, 'success');
	}

	// 添加修改
	public function operationinterface(){

		$param = $this ->param;

		$type = isset($this ->param['type']) ? $this ->param['type'] : 1;

		$sid = isset($this ->param['sid']) ? $this ->param['sid'] : 0;

		switch($type){
			case '1':
				# 添加全部
				$res = Nc::operationinterface_one($param);
				break;
			case '2':
				# 已购买
				$res = Nc::operationinterface_two($param);
				break;
			case '3':
				# 未购买
				$res = Nc::operationinterface_three($param);
				break;
		}

		$this ->returns($res);
	}

	public function setformval(){

		$sid = isset($this ->param['sid']) ? $this ->param['sid'] : '';

		if(!$sid){

			ajaReturn([],1001,'error');
		}

		$res = Db::name('newscenter') ->field('sendobj as type,title,content') ->where(['id' => $sid]) ->find();

		$this ->returns($res);
	}

	//  富文本图片上传接口
	public function uploadimg(){

		$file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload/layedit');

        if($info){
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            $url =  $info->getSaveName();
            
            if($url){
                ajaReturn(['src' => 'https://www.ydtkt.com/upload/layedit/'.$url],0,'成功');
            }else{
                ajaReturn([$file->getError()],1001,'失败');
            }
        }else{
        	// 上传失败获取错误信息
        	echo $file->getError();
        }

	}

	public function delmessage(){

		$sid = isset($this ->param['sid']) ? $this ->param['sid'] : '';

		$res = Db::name('newscenter') ->field('title,sendtime') ->where(['id' => $sid]) ->find();

		Db::transaction(function() use ($res,$sid){

			Db::name('message') ->where(['title' => $res['title'], 'create_time' => $res['sendtime']]) ->delete();

			Db::name('newscenter') ->delete([$sid]);
		});

		ajaReturn([], 0, 'success');
	}

	// 返回时判断
	private function returns($chapterval){

		$msg['data'] = $chapterval ? $chapterval : $chapterval;

		$msg['code'] = $chapterval ? 0 : 1001;

		$msg['msg'] = $chapterval ? 'success' : 'error';

		ajaReturn($msg['data'], $msg['code'], $msg['msg']);
	}


}