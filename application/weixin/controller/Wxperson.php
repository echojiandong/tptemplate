<?php 
namespace app\weixin\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use app\index\model\PersonModel;
use app\weixin\model\PersonModel as WxM;
class Wxperson extends Controller{
	private $person;                //用户
	public function __construct(){
		 $this->person = new PersonModel();
	}	
	//小程序注册激活
    public function activationCode(){
        $param = input();
        $where['card'] = $param['title'];
        $where['show_password'] = $param['password'];
        $pid = $param['openid'];
        //获取personid
        // $personList = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
        if($pid){
            //验证卡密是否正确
	        $code_msg = $this ->person ->verifyTheRegistrationCard($where);
	        if(empty($code_msg)){
	            ajaReturn([],1001,'注册卡账号或密码错误');
	        }
	        //是否已被激活
	        if($code_msg['status'] == 2){
	            ajaReturn([],1001,'注册卡已被激活，不可重复使用');
	        }
	        //卡是否已被禁用
	        if($code_msg['is_forbidden'] == 2){
	            ajaReturn([],1001,'对不起，该类型卡已被禁用');
	        }
	        //是否激活相同课程
	        $idlist = explode(',',$code_msg['coursePackage_id']);
	        $listArr = Db::name('code') ->field('coursePackage_id') 
	                                    ->where(['person_id' => $pid])
	                                    ->select();
	        if(!empty($listArr)){
	            $video_arr = array_column($listArr, 'coursePackage_id');
	            $video_str = implode(',', $video_arr);
	            $vieo_arr1 = explode(',', $video_str);
	            foreach($idlist as &$val){
	                if(in_array($val,$vieo_arr1)){

	                    ajaReturn([],1001,'请勿重复激活相同课程，如有疑问请联系本地代理商!');
	                }
	            }

	        }
	        //查询订单是否存在、以及订单是否审核成功（需要时解开注释）
	        // $is_order = Db::name('order_person') ->field('id,orderCheck') 
	        //                                      ->where(['code_id' => $code_msg['id'], 'person_id' => $personList['pid']]) 
	        //                                      ->find();
	        // if(empty($is_order)){
	        //     ajaReturn([],1001,'未知错误，请联系本地代理商！');
	        // }else{
	        //     if($is_order['orderCheck'] != 2){
	        //         ajaReturn([],1001,'未知错误，请联系本地代理商！');
	        //     }
	        // }
	        //激活时将该卡所对应的视频写入video_log
	        $res = setVideoLog($code_msg['coursePackage_id'],$pid);
	        if(!$res){
	            ajaReturn([],1001,'激活失败，请稍后重试');
	        }
	        
	        $data['person_id'] = $pid;
	        $data['status'] = 2;
	        $data['update_time'] = time();
	        $person = new PersonModel;
	        $res=$person->updateRegistrationCardActivation($where,$data,$code_msg['user_id']);
	        if(!$res){
	            ajaReturn([],1001,'激活失败，请稍后重试');
	        }
	        ajaReturn([],1,'您的学习卡已激活成功!');
        }else{
            ajaReturn([],1001,'用户不存在！');
        }
    }
    //获取用户激活注册卡列表
    public function getPersonCodeList()
    {
        $param=input();
        //获取用户id
        $pid = $param['openid'];
        if($pid){
	        //获取personid
	        // $userinfo = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
	        $where['person_id']=$pid;
	        $where['status']=2;
	        // $countCode=DB::name('code')->where($where)->count();
	        $codeList=DB::name('code')->where($where)->order('update_time desc')->select();
	        foreach($codeList as $k=>$v)
	        {
	            $codeList[$k]['update_time']=date("Y/m/d H:i:m",$v['update_time']);
	        }
	        if($codeList){
	            return jsonMsg('success',1,$codeList);
	        }else{
	            return jsonMsg('暂时没有符合条件的数据！',0);
	        }
	    }else{
	    	jsonMsg('用户未登录，请先登录！',0);
	    }
    }
    //用户个人中心
    public function getPersonInfo(){
    	$param = input();
    	//获取用户详情
        $pid = $param['openid'];
        if($pid){
	        $personInfoList = Db::name('wx_person')->alias('wp')
	        					->join('guard_person p','p.id = wp.pid')
	        					->field('p.*')
	        					->where('p.id',$pid)
	        					->find();
	        //参数转换
	        $personInfoList['birthday'] = date('Y-m-d',$personInfoList['birthday']);
	        if($personInfoList){
	        	return jsonMsg('success',1,$personInfoList);
	        }else{
	        	return jsonMsg('fail',0);
	        }
    	}else{
    		jsonMsg('用户未登录，请先登录！',0);
    	}
    }
    //修改个人中心
    public function updatePersonInfo(){
    	$param = input();
    	//获取用户id
        $pid = $param['openid'];
        if($pid){
	        //获取personid
	        // $userinfo = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
	    	$data = array();
	    	$data['grade_id'] = !empty($param['grade_id'])?$param['grade_id']:'';
	    	$data['gender'] = !empty($param['gender'])?$param['gender']:'';
	    	$data['birthday'] = !empty($param['birthday'])?strtotime($param['birthday']):'';
	    	$data['nickName'] = !empty($param['username'])?$param['username']:'';
	    	$data['phone'] = !empty($param['phone'])?$param['phone']:'';
	    	$data['email'] = !empty($param['email'])?$param['email']:'';
	    	$data['wechat'] = !empty($param['wechat'])?$param['wechat']:'';
	    	$data['school'] = !empty($param['school'])?$param['school']:'';
	    	$data['province'] = !empty(explode(',',$param['region']))?explode(',',$param['region'])[0]:'';
	    	$data['city'] = !empty(explode(',',$param['region']))?explode(',',$param['region'])[1]:'';
	    	$data['country'] = !empty(explode(',',$param['region']))?explode(',',$param['region'])[2]:'';

	    	//更新数据
	    	$res = Db::name('person')->where('id',$pid)->update($data);
	    	if($res){
	    		jsonMsg('修改成功',1);
	    	}else{
	    		jsonMsg('修改失败',0);
	    	}
	    }else{
	    	jsonMsg('用户未登录，请先登录！',0);
	    }
    }
    //消息中心
    public function getMessageList(){
    	$param = input();
    	//获取用户id
        $pid = $param['openid'];
        if($pid){
	        //获取personid
	        // $userinfo = Db::name('wx_person')->where("app_openid='".$openid."'")->find();

	        //获取消息信息
	        $messageList = Db::name('message')->where('uid',$pid)->order('status')->select();
	        foreach ($messageList as $key => $value) {
                $messageList[$key]['create_time'] = date('Y-m-d',$value['create_time']); 
	        	$messageList[$key]['desc'] =strip_tags($value['desc']); 
	        }
	        if($messageList){
	        	return jsonMsg('success',0,$messageList);
	        }else{
	        	return jsonMsg('fail',1);
	        }
    	}else{
    		jsonMsg('用户未登录，请先登录！',0);
    	}
    }
    //删除消息
    public function delMessage(){
    	$param = input();
    	$res = Db::name('message')->where('id',$param['id'])->delete();
    	if($res){
    		return jsonMsg('success',1);
    	}
    }
    //消息中心 更新
    public function updateMessage(){
    	$param = input();
    	$data['status'] = 1;
    	$pid = $param['openid'];
    	//获取personid
	    // $userinfo = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
    	$messageList = Db::name('message')->where('id',$param['id'])->find();
    	if($messageList['status'] == 0){
    		$res = Db::name('message')->where('id',$param['id'])->update($data);
    		//获取全部数据
    		$list = Db::name('message')->where('uid',$pid)->order('status')->select();
    		foreach ($list as $key => $value) {
	        	$list[$key]['create_time'] = date('Y-m-d',$value['create_time']); 
                $list[$key]['desc'] =strip_tags($value['desc']); 
	        }
    		if($res){
    			return jsonMsg('success',1,$list);
    		}
    	}else{
    		$list = Db::name('message')->where('uid',$pid)->order('status')->select();
    		foreach ($list as $key => $value) {
	        	$list[$key]['create_time'] = date('Y-m-d',$value['create_time']); 
                $list[$key]['desc'] =strip_tags($value['desc']); 
	        }
    		return jsonMsg('success',1,$list);
    	}
    	
    }
    //获取微信版本信息
    public function getVersion(){
    	$versionList = Db::name('config')->where('cfg_field = "version"')->find();
    	jsonMsg('success',1,$versionList);
    }
    //获取用户收藏
    // public function getCollectList(){
    // 	$param = input();
    // 	//获取用户id
    //     $pid = $param['openid'];
    //     if($pid){
	//         // $userinfo = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
	//         $where['pc.uid'] = $pid;
    //         $where['pc.status']=1;
	//        	if(!empty($param['subject_id'])){
	//        		$where['pc.subject_id'] = (int)$param['subject_id'];
	//        	}
	//        	$page = $param['page'];
	//        	$pageNum = $param['pageNum'];
	//        	$limit = $pageNum*($page-1);
	//     	$colletList = Db::name('person_collect')->alias('pc')
	//     					->join('guard_subject s','s.id = pc.subject_id')
	//     					->join('guard_video v','v.id = pc.video_id','left')
	//     					->where($where)
	//     					->limit($limit,$pageNum)
	//     					->field('s.subject,pc.*,v.outline')
	//     					->select();
	//     	$count = Db::name('person_collect')->alias('pc')
	//     					->join('guard_subject s','s.id = pc.subject_id')
	//     					->join('guard_video v','v.id = pc.video_id','left')
	//     					->where($where)
	//     					->field('s.subject,pc.*,v.outline')
	//     					->count();
	//     	if($colletList){
	//     		return jsonMsg('success',1,$colletList,$count);
	//     	}else{
	//     		return jsonMsg('fail',0,$colletList,$count);
	//     	}
    // 	}else{
    // 		jsonMsg('用户未登录，请先登录！',0);
    // 	}
	// }
	
	/**
	 * 小程序收藏列表
	 */
	public function getCollectList()
	{
		$param  = input();
		$person_id = $param['openid'];
		// $person_id = 47;
		$page = !empty($param['page']) ? $param['page'] : 1;
		$pagesize = !empty($param['pagesize']) ? $param['pagesize'] : 10;

		if (!$person_id) {
			jsonMsg('请先登录', 0);
		}
		// 年级列表
				
		// 默认七年级
		$grade_id = $param['gid'] ? $param['gid'] : 7 ;
	
		// 默认语文
		$subject_id = $param['sid'] ? $param['sid'] : 1 ;

		if($subject_id>9){
            $collectList = Db::name('person_collect')
                ->alias('a')
                ->field('a.*, b.outline, c.grade_id, c.subject_id')
                ->join('guard_video b', 'a.video_id = b.id')
                ->join('guard_product c', 'a.product_id = c.id')
                ->where(['b.display' => 1])
                ->where([
                    'a.uid' => $person_id,
                    'a.status'  => 1,
                    'c.grade_id' => $grade_id,
                    'a.subject_id' => $subject_id
                ])->order('a.id desc')->page($page, $pagesize)->select();
            $totalCount = Db::name('person_collect')
                ->alias('a')
                ->field('a.*, b.outline, c.grade_id, c.subject_id')
                ->join('guard_video b', 'a.video_id = b.id')
                ->join('guard_product c', 'a.product_id = c.id')
                ->where(['b.display' => 1])
                ->where([
                    'a.uid' => $person_id,
                    'a.status'  => 1,
                    'c.grade_id' => $grade_id,
                    'a.subject_id' => $subject_id
                ])->count();
        }else{
            $collectList = Db::name('person_collect')
                ->alias('a')
                ->field('a.*, b.outline, c.grade_id, c.subject_id')
                ->join('guard_video b', 'a.video_id = b.id')
                ->join('guard_video_class c', 'a.video_class_id = c.id')
                ->where(['b.display' => 1])
                ->where([
                    'a.uid' => $person_id,
                    'a.status'  => 1,
                    'c.grade_id' => $grade_id,
                    'a.subject_id' => $subject_id
                ])->order('a.id desc')->page($page, $pagesize)->select();
            $totalCount = Db::name('person_collect')
                ->alias('a')
                ->field('a.*, b.outline, c.grade_id, c.subject_id')
                ->join('guard_video b', 'a.video_id = b.id')
                ->join('guard_video_class c', 'a.video_class_id = c.id')
                ->where(['b.display' => 1])
                ->where([
                    'a.uid' => $person_id,
                    'a.status'  => 1,
                    'c.grade_id' => $grade_id,
                    'a.subject_id' => $subject_id
                ])->count();
        }

		foreach ($collectList as $key => $value) {
			$collectList[$key]['startTime'] = $value['startTime'];
			$collectList[$key]['start_time'] = $this->ChangePlayTime($value['startTime']);
		}
		if($collectList){
			return jsonMsg('success',1,$collectList, $totalCount);
		}else{
			return jsonMsg('fail', 0, [], 0);
		}
	}
	//视频播放时间数据转换
    public function ChangePlayTime($allTime){
        $classAlltime = array();
        $classAlltime[0] = str_pad(floor($allTime/3600),2,0,STR_PAD_LEFT);
        $classAlltime[1] = str_pad(floor(($allTime%3600)/60),2,0,STR_PAD_LEFT);
        $classAlltime[2] = str_pad($allTime - $classAlltime[0] * 3600 - $classAlltime[1] * 60,2,0,STR_PAD_LEFT);
        if($classAlltime[0] == '00'){
            unset($classAlltime[0]);
        }
        $currentTime = implode(':',$classAlltime);
        return $currentTime;
    }
    //删除用户收藏视频
    public function delMyCollect(){
    	$param = input();
    	$res = Db::name('person_collect')->where('id',$param['id'])->delete();
    	if($res){
    		return jsonMsg('success',1);
    	}
	}
	
	public function cardactivation(){

		$param = input();

		// 年级
        $grade = isset($param['gid']) ? $param['gid'] : 7;
        // 当前登录用户id
		$uid = $param['openid'];

		if($grade == 0){

            $data = Db::name('order_person_son') ->field('video_class_id,person_id') 
                                                 ->where(['orderCheck' => 2, 'is_audition' => 0, 'person_id' => $uid]) 
                                                 ->select();
            if(empty($data)){
                $data = Db::name('order_person_son') ->field('product_id,person_id')
                    ->where(['orderCheck' => 2, 'is_audition' => 0, 'person_id' => $uid])
                    ->select();
                $productStatus = 1;
            }

            empty($data) && $grade = 7;

            if(!empty($data)){
                if(isset($productStatus)){
                    $video_id = array_unique(array_column($data, 'product_id'));
                    $grade_arr = Db::name('product') ->field('grade_id')
                        ->where('id', 'in', $video_id)
                        ->group('grade_id')
                        ->order('grade_id')
                        ->find();
                }else{
                    $video_id = array_unique(array_column($data, 'video_class_id'));
                    $grade_arr = Db::name('video_class') ->field('grade_id')
                        ->where('id', 'in', $video_id)
                        ->group('grade_id')
                        ->order('grade_id')
                        ->find();
                }

                !empty($grade_arr) && $grade = $grade_arr['grade_id'];
            }
        }

        $data = $this->person ->cardactivation($grade, $uid);

        if(empty($data)){
            ajaReturn('', 1001, '系统繁忙，请稍后再试');
        }

        ajaReturn($data, 0, 'success');

	}
    //视频激活
	public function activeCourses(){

        $param = Request::instance() ->param();
        // video_class     id
        $cid = isset($param['cid']) ? $param['cid'] : 0;
        // 当前登录用户id
        $uid = $param['openid'];

        $res = $this->person ->activeCourses($cid, $uid);

        if($res){

            $gradelist = Db::name('video_class') ->field('grade_id') ->where(['id' => $cid]) ->find();
            $grade = $gradelist['grade_id'];
            // $data = $this->person ->cardactivation($grade, $uid);

            ajaReturn($grade, 0, '激活成功');
        }

        ajaReturn('', 1001, '激活失败');

    }
    //视频章节列表
    public function videolist(){
    	$parma = input();
    	// openid  (后期可以删除)
    	$pid = $parma['openid'];

    	// $pid = Db::name('wx_person') ->field('pid') ->where(['app_openid' => $openid]) ->find()['pid'];
    	// 年级列表
        
        $video_class_grade = Db::name('video_class') ->field('grade_id') ->group('grade_id') ->select();

        $grade_arr = array_column($video_class_grade, 'grade_id'); 

    	$grade_list = Db::name('grade') ->field('id,grade') 
    									->where('id', 'in', $grade_arr) 
    									->select();
        // 默认七年级
        $grade_id = isset($parma['gid']) ? $parma['gid'] : $grade_arr[0] ;

        $video_class_subject = Db::name('video_class') ->field('subject_id') 
                                                     ->where(['grade_id' => $grade_id])
                                                     ->order('subject_id')
                                                     ->group('subject_id') 
                                                     ->select();
        $video_product_subject = Db::name('product') ->field('subject_id')
            ->where(['grade_id' => $grade_id])
            ->order('subject_id')
            ->group('subject_id')
            ->select();
        $video_class_subject = array_merge($video_class_subject,$video_product_subject);
        $subject_arr = array_column($video_class_subject, 'subject_id');
    	// 科目列表
    	$subject_list = Db::name('subject') ->field('id,subject') 
    										->where('id', 'in', $subject_arr)
    										->select();
    	// 默认语文
    	$subject_id = isset($parma['sid']) ? $parma['sid'] : $subject_arr[0] ;
        $productStatus=0;
        if($subject_id>9){
            $productStatus=1;
        }
        $term = Db::name('video_class') ->field('Semester') 
                                        ->where(['grade_id' => $grade_id, 'subject_id' => $subject_id])
                                        ->order('Semester') 
                                        ->select();
        $term2 = Db::name('product') ->field('Semester')
            ->where(['grade_id' => $grade_id, 'subject_id' => $subject_id])
            ->order('Semester')
            ->select();

        $term_arr = array_column($term, 'Semester');
        $term_arr2 = array_column($term2, 'Semester');
        $term_arr = array_unique(array_merge($term_arr,$term_arr2));
        foreach($term_arr as &$val){
            $where = ['grade_id' => $grade_id, 'subject_id' => $subject_id, 'Semester' => $val];
            // 组装数据
            $res = (new WxM()) ->videolist($where, $pid,$productStatus);

            $videolist = getTree($res[0]);
            foreach($videolist as $key => &$v){
                if(!isset($v['son']))
                {

                    $videolist[$key]['son']=1;
                }

                $videolist[$key]['shows'] = $key == 0 ? true : false;
            }

            if($val == 1){

                $term_name = '上学期';
            }

            if($val == 2){

                $term_name = '下学期';
            }

            if($val == 3){

                $term_name = '全册';
            }

            $data[] = ['list' => $videolist, 'count' => $res[1], 'count_log' => $res[2], 'term_name' => $term_name];

        }

        jsonMsg('success', 0, ['glist' => $grade_list, 'slist' => $subject_list, 'data' => $data]);

    }
    //数组转换
    private  function tree($data,$pid=0){
	    $tree = [];
	    foreach($data as $k => $v)
	    {
	        if($v['pid'] == $pid)
	        {    
	            $v['son'] = $this ->tree($data, $v['id']);
	            if(empty($v['son'])){

	            	unset($v['son']);
	            }
	            $tree[] = $v;
	        }
	    }
    	return $tree;
	}
}