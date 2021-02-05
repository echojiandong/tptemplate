<?php
namespace app\manage\model;
use think\Model;
use think\Request;
use think\Session;
use think\Db;
use app\manage\model\QiniuModel;

class Videos extends Model{
	//参数列表
	public $parmes;											
	//区分小学、初中、高中
	public $a_grade = [[1,2,3,4,5,6],[7,8,9],[10,11,12]];

	public $qiniu;

	public static function newV($parme = []){
		$model = new self();
		$model ->qiniu = new QiniuModel();
		$model ->parmes = $parme;
		return $model;
	} 

	public function videotree($g_type, $s_type){
		//根据年级科目查询所有课程
		$video = Db::name('video_class')  ->field('id as vid,grade_id as name,img,audi,Semester as title,sname,price,Discount,edition_id') 
							 			  ->whereOr('grade_id','IN',$this ->a_grade[$g_type])
							 			  ->where(['subject_id' => $s_type])
							 			  ->order('sort','asc')
							 			  ->select();
		//容错
		if(empty($video)){
			ajaReturn('',1001,'error');
		}
		//教材版本
		$edition = Db::name('textbook') ->field('id,textbook') ->select();
		$edition_arr = array_combine(array_column($edition, 'id'),array_column($edition, 'textbook'));
		//取出所有课程的主键id
		$v_ids = array_column($video, 'vid');
		//查询视频下的所有视频列表
		$c_v_field = 'id as vid,kid,img,testclass as title,outline as name,audi,pid,part,teachername as sname';
		$c_video = Db::name('video') ->field($c_v_field) 			 
									 ->where('kid','IN',$v_ids)
									 ->order('sort','asc')
									 ->select();
		//合并课程与视频列表
		$c_video = array_merge($c_video, $video);
		//重新分配主键id、 根据新的主键id 取出 课程、视频列表（最小课时、以及章节）
		foreach($c_video as $key =>&$val){
			$c_video[$key]['id'] = $key + 1;
			$val['id'] = $key + 1;
			if(isset($val['part']) && $val['part'] == 2){
				//最小课时
				$c_video[$key]['title'] = $val['title'].$val['name'];
				$part_2_arr[$key] = $val;
			}else{
				if(!isset($val['part'])){
					//课程
					$c_video[$key]['pid'] = 0;
					if($val['title'] == 1 || $val['title'] == 2){
						$val['title'] = $val['title'] == 1?'上':'下'; 
					}else{
						$val['title'] = '全册';
					}
					$c_video[$key]['title'] = $val['name'].'年级'.$val['title'];
					$c_video[$key]['edition_id'] = $edition_arr[$val['edition_id']];
					$video_son[$key+1] = $val; 
				}else{
					//章节
					$c_video[$key]['title'] = $val['title'].$val['name'];
					$video_p_son[$key+1] = $val;
				}
			}
		}
		//取出最小课时的自增id
		$kl_ids = array_column($part_2_arr, 'vid');
		//查询知识点
		$kl_field = 'k_id as vid,k_name as title,k_content as name,s_id as pid';

		$k_l_arr = Db::name('knowledge') ->field($kl_field) ->where('s_id', 'IN', $kl_ids) ->order('sort','asc') ->select();
		//以最小课时的主键id为键、新id为值 处理成新数组
		$kl_parent = array_combine($kl_ids, array_column($part_2_arr, 'id'));
		//替换知识点的父id 为新的id，并排空
		$k_l_list = array_filter(array_map(function($v) use ($kl_parent){
					$v['pid'] = isset($kl_parent[$v['pid']])?$kl_parent[$v['pid']]:'';
					return $v['pid']?$v:'';
		},$k_l_arr));
		//以课程的主键id为键、新id为值 处理成新数组
		$v_parent = array_combine(array_column($video_son,'vid'), array_column($video_son,'id'));
		//以章节的主键id为键、新id为值 处理成新数组
		$v1_parent = array_combine(array_column($video_p_son,'vid'), array_column($video_p_son,'id'));
		//替换所有的父id 为新的id
		$list = array_map(function($v) use ($v_parent,$v1_parent){
					if(isset($v['part']) && $v['part'] == 0){
						$v['pid'] = $v_parent[$v['kid']];
					}else if(isset($v['part']) && $v['part'] == 1){
						$v['pid'] = $v1_parent[$v['pid']];
					}else if(isset($v['part']) && $v['part'] == 2){
						$v['pid'] = $v1_parent[$v['pid']];
					}
					return $v;
			}, $c_video);
		//将知识点 压入数组
		$list = array_merge($list, $k_l_list);
		//为知识点分配新的id
		foreach($list as $key =>&$val){
			if(!isset($val['id'])){
				$list[$key]['id'] = $key + 1;
			}
		}
		//tree 雏形
		return $list;

	}
	/**
	 * [addclassinterferce1 video添加]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:27:33+0800
	 */
	public function addclassinterferce1(){

		$where['testclass'] = $this ->parmes['testclass'];

		$where['outline'] = $this ->parmes['outline'];

		$where['part'] = isset($this ->parmes['part']) ? $this ->parmes['part'] : 1;

		$k_arr = Db::name('video') ->field('kid') ->where(['id' => $this ->parmes['pid']]) ->find();

		if(empty($k_arr)){
			ajaReturn('',1001,'error');
		}
		$where['kid'] = $k_arr['kid'];

		$where['time'] = time();

		$where['pid'] = $this ->parmes['pid'];

		$res = Db::name('video') ->insertGetId($where);

		$res = Db::name('video') ->where(['id' => $res]) ->update(['sort' => $res]);

		return $res;
	}

	public function addclassinterferce2(){
		//自身id
		$selfid = isset($this ->parmes['selfid']) ? $this ->parmes['selfid'] : 0;
		//父id
		$pid = isset($this ->parmes['pid']) ? $this ->parmes['pid'] : 0;
		//章 、节 、课时
		$part = isset($this ->parmes['part']) ? $this ->parmes['part'] : 1;
		//教师名称 string（0-测试）
		$teachername = isset($this ->parmes['teachername']) ? $this ->parmes['teachername'] : '0-测试';

		$teacher = explode('-', $teachername);
		//课时
		$data['testclass'] = isset($this ->parmes['testclass']) ? $this ->parmes['testclass'] : '';
		//课程名称
		$data['outline'] = isset($this ->parmes['outline']) ? $this ->parmes['outline'] : '';
		//1080p 视频链接
		$data['link'] = isset($this ->parmes['video_1080']) ? $this ->parmes['video_1080'] : '';
		//720p 视频链接
		$data['link_720'] = isset($this ->parmes['video_720']) ? $this ->parmes['video_720'] : '';
		//480p 视频链接
		$data['link_480'] = isset($this ->parmes['video_480']) ? $this ->parmes['video_480'] : '';
		//封面图
		$data['img'] = isset($this ->parmes['img']) ? $this ->parmes['img'] : '';
		//试听状态
		$data['audi'] = isset($this ->parmes['audi']) ? $this ->parmes['audi'] : '';
		//教师id
		$data['teacherid'] = $teacher[0];
		//教师姓名
		$data['teachername'] = $teacher[1];
		//课程简介
		$data['skill'] = isset($this ->parmes['skill'])? $this ->parmes['skill'] : '';

		if($selfid != 0){
			//修改
			$res = Db::name('video') ->where(['id' => $selfid]) ->update($data);
		}

		if($selfid == 0 && $pid != 0){
			//添加
			$data['time'] = time();
			//课程id
			$data['kid'] = Db::name('video') ->field('kid') ->where(['id' => $pid]) ->find()['kid'];
			//分类状态
			$data['part'] = $part;
			//父id
			$data['pid'] = $pid;
			//课程分钟数
			$data['classhour'] = '';
			//取不为空的三个数值
			$link = array_filter(array_map(function($v){ 

				return $v?$v:'';
				},[$data['link'],$data['link_720'],$data['link_480']]));
			//获取视频时长
			if(!empty($link)){
				//自动获取课时时长
		        $videoInfoList = file_get_contents(array_shift($link).'?avinfo');

		        $videoInfoList = json_decode($videoInfoList,true);

		        $classhour = intval($videoInfoList['format']['duration']);
		        //拼接时长
		        $hour = str_pad(intval($classhour/3600),2,"0",STR_PAD_LEFT);
		        //拼接分钟数
		        $minute = str_pad(intval($classhour%3600/60),2,"0",STR_PAD_LEFT);
		        //拼接秒数
		        $second = str_pad(intval($classhour%60%60),2,"0",STR_PAD_LEFT);

		        $data['classhour'] =  $hour.':'.$minute.':'.$second;
			}
			$res = Db::name('video') ->insertGetId($data);
			$res = Db::name('video') ->where(['id' => $res]) ->update(['sort' => $res]);
		}
		return $res;
	}
	/**
	 * [curriculumedit 课程添加、编辑]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:29:25+0800
	 */
	public function curriculumedit(){
		unset($this ->parmes['file']);

		$id = isset($this ->parmes['selfid']) ? $this ->parmes['selfid'] : 0;
		// 教师
		$this ->parmes['sname'] = isset($this ->parmes['sname']) ? $this ->parmes['sname'] : '0-测试';

		$teacher = explode('-', $this ->parmes['sname']);

		$this ->parmes['teacherId'] = $teacher[0];

		$this ->parmes['sname'] = $teacher[1];

		$res = '';
		// 添加
		if(empty($id)){

			unset($this ->parmes['selfid']);

			$res = Db::name('video_class') ->insertGetId($this ->parmes);

			$res = Db::name('video_class') ->where(['id' => $res]) ->update(['sort' => $res]);
		}
		// 编辑
		if(!empty($id)){
			$this ->parmes['id'] =  $this ->parmes['selfid'];

			unset($this ->parmes['selfid']);

			$this ->parmes['uptime'] = time();

			$this ->parmes['admin_id'] = Session::get("manageinfo")['uid'];

			$res = Db::name('video_class') ->update($this ->parmes);
		}

		return $res;
	}
	/**
	 * [deleteVCdata video_class删除]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:32:28+0800
	 * @param    [int]                   $id [要删除的id]
	 */
	public function deleteVCdata($id){
		// 查询 父节点下所有数据
		$data = Db::name('video') ->field('id,kid,img,link,link_720,link_480') 
								  ->where(['kid' => $id]) 
								  ->select();
		if(empty($data)){

			$res = Db::name('video_class') ->where(['id' => $id]) ->delete();

			return $res;
		}
		// 删除对应七牛云 资源
		$this ->delqiniudata($data);
		// 获取所有知识点id
		$all_id = array_column($data, 'id');
		// 事务处理
		Db::transaction(function() use ($all_id,$id){
			Db::name('video') ->where(['kid' => $id]) ->delete();
			Db::name('knowledge') ->where('s_id','IN',$all_id) ->delete();
			Db::name('video_class') ->where(['id' => $id]) ->delete();
		});

		return 1;

	}
	/**
	 * [deleteVdata 要删除video 列表的id]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:37:10+0800
	 * @param    [array]                   $id 
	 */
	public function deleteVdata($id){
		// 查询数据
		$data = Db::name('video') ->field('id,kid,img,link,link_720,link_480') 
										->where('id','IN',$id) 
										->select();
		// 删除对应七牛云 资源
		$res = $this ->delqiniudata($data);
		// 事务处理
		Db::transaction(function() use ($id){
			Db::name('video') ->where('id','IN',$id) ->delete();
			Db::name('knowledge') ->where('s_id','IN',$id) ->delete();
		});
		//查询是否有孩子
		$p_data = Db::name('video') ->field('id,kid,img,link,link_720,link_480') 
										  ->where('pid','IN',$id) 
										  ->select();
		// 递归处理
		if(!empty($p_data)){
			$ids = array_column($p_data, 'id');

			return $this ->deleteVdata($ids);
		}else{

			return 1;
		}
	}
	/**
	 * [deleteKLdata 知识点删除]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:39:46+0800
	 * @param    [int]                   $id
	 */
	public function deleteKLdata($id){

		$res = Db::name('knowledge') ->where(['k_id' => $id]) ->delete();

		return $res;
	}

	public function sortingVC($id, $status){
		$data = Db::name('video_class') ->field('id,grade_id,subject_id,sort') 
										->where(['id' => $id]) 
										->find();
		if(empty($data)){

			return 0;
		}
		$s_num = $data['sort'];

		$grade = 1;

		// 判断 学段所在区间
		foreach($this ->a_grade as $key => &$val){

			if(in_array($data['grade_id'],$val)){

				$grade = $key;
			}
		}

		$one_data = Db::name('video_class') ->field('id,grade_id,subject_id,sort')
							   				->where(['subject_id' => $data['subject_id']])
							   				->where('grade_id', 'IN', $this ->a_grade[$grade])
							   				->order('sort','asc')
							   				->select();
		if(empty($one_data)){
			return 1;
		}

		$k_id_arr = array_column($one_data,'id');

		$sort_arr = array_combine($k_id_arr, array_column($one_data,'sort'));

		$key = array_search($id,$k_id_arr);

		$id_key = $status == 1 ? $key - 1 : $key + 1;

		if(!isset($k_id_arr[$id_key])){

			return 1;
		}

		$soct_id = $k_id_arr[$id_key];

		Db::transaction(function() use ($id, $soct_id, $sort_arr, $s_num){

			Db::name('video_class') ->where(['id' => $id]) ->update(['sort' => $sort_arr[$soct_id]]);

			Db::name('video_class') ->where(['id' => $soct_id]) ->update(['sort' => $s_num]);
		});

		$res = 1;

		return $res;

	}

	public function sortingV($id, $status){
		$field = 'id,kid,pid,part,sort';

		$data = Db::name('video') ->field($field) 
								  ->where(['id' => $id]) 
								  ->find();

		if(empty($data)){
			return 0;
		}

		$s_num = $data['sort'];

		$one_data = Db::name('video') ->field($field)
							   		  ->where(['kid' => $data['kid'],'pid' => $data['pid'],'part' => $data['part']])
							   		  ->order('sort','asc')
							   		  ->select();
		if(empty($one_data)){

			return 1;
		}

		$k_id_arr = array_column($one_data,'id');

		$sort_arr = array_combine($k_id_arr, array_column($one_data,'sort'));

		$key = array_search($id,$k_id_arr);

		$id_key = $status == 1 ? $key - 1 : $key + 1;

		if(!isset($k_id_arr[$id_key])){

			return 1;
		}

		$soct_id = $k_id_arr[$id_key];

		Db::transaction(function() use ($id, $soct_id, $sort_arr, $s_num){

			Db::name('video') ->where(['id' => $id]) ->update(['sort' => $sort_arr[$soct_id]]);

			Db::name('video') ->where(['id' => $soct_id]) ->update(['sort' => $s_num]);
		});

		$res = 1;

		return $res;

	}

	public function sortingKL($id, $status){
		$field = 'k_id,s_id,sort';

		$data = Db::name('knowledge') ->field($field) 
								  	  ->where(['k_id' => $id]) 
								  	  ->find();

		if(empty($data)){
			return 0;
		}

		$s_num = $data['sort'];

		$one_data = Db::name('knowledge') ->field($field)
							   			  ->where(['s_id' => $data['s_id']])
							   			  ->order('sort','asc')
							   			  ->select();
		if(empty($one_data)){
			
			return 1;
		}

		$k_id_arr = array_column($one_data,'k_id');

		$sort_arr = array_combine($k_id_arr, array_column($one_data,'sort'));

		$key = array_search($id,$k_id_arr);

		$id_key = $status == 1 ? $key - 1 : $key + 1;

		if(!isset($k_id_arr[$id_key])){

			return 1;
		}

		$soct_id = $k_id_arr[$id_key];

		Db::transaction(function() use ($id, $soct_id, $sort_arr, $s_num){

			Db::name('knowledge') ->where(['k_id' => $id]) ->update(['sort' => $sort_arr[$soct_id]]);

			Db::name('knowledge') ->where(['k_id' => $soct_id]) ->update(['sort' => $s_num]);
		});

		$res = 1;

		return $res;

	}
	// 批量删除对应七牛云资源
	private function delqiniudata($data){
		$delink = [];

		foreach($data as &$val){
			if(!empty($val['img'])){
				$delink[] = ['spnce' => 'ydtvlitpic', 'name' => substr($val['img'],strripos($val['img'],'/')+1)];
			}
			if(!empty($val['link'])){
				$delink[] = ['spnce' => 'ydtvideo'.'1080', 'name' => substr($val['link'],strripos($val['link'],'/')+1)];
			}
			if(!empty($val['link_720'])){
				$delink[] = ['spnce' => 'ydtvideo'.'720', 'name' => substr($val['link_720'],strripos($val['link_720'],'/')+1)];
			}
			if(!empty($val['link_480'])){
				$delink[] = ['spnce' => 'ydtvideo'.'480', 'name' => substr($val['link_480'],strripos($val['link_480'],'/')+1)];
			}
		}
		if(!empty($delink)){

			foreach($delink as &$val){

				$this ->qiniu->delFile($val['spnce'],$val['name']);
			}
		}
		return 1;
	}
}	