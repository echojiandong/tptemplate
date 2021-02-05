<?php
namespace app\index\Controller;
use app\index\model\PersonModel;
use app\index\model\courseModel;
use app\index\model\MessageModel;
use app\index\model\CollectModel;
use app\manage\model\QiniuModel;
use app\index\model\IndexModel;
use app\index\controller\Auth;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;
use think\Image;
class Person extends auth
{
    private $person;                //用户
    private $message;               //消息
    private $collect;               //收藏
    private $userinfo;              //用户信息           
    public function _initialize() {
        parent::_initialize();
        //头部用户信息
        $this->person = new PersonModel($this ->_info);
        $this ->userinfo =  $this->person->GetPerson();
        $this->assign('personInfo',$this ->userinfo);
        //头部未读消息数量
        $this ->message  = new MessageModel();
        $message_count = $this ->message->Getmessage_count($this ->userinfo['id']);
        $this->assign('message_count',$message_count);
        //头部收藏数量
        $this ->collect  = new CollectModel();
        $collect_count = $this ->collect->Getcollect_count($this ->userinfo['id']);
        // 点击铃铛时
        $param = input();
        $type = isset($param['type']) ? $param['type'] : 0 ;
        
        $this ->assign('_type', $type);
        $this ->assign('collect_count',$collect_count);
    }
    /**
     * [personal_center 个人中心跳转]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-09T11:56:03+0800
     */
    public function person()
    {
        $param = input();
        $userGrade = $this->person->getUserGrade();//获取用户年级

        $class_id = isset($param['gid']) ? $param['gid'] : $userGrade;

        $list = $this ->person ->GetUserClass($class_id);                //课程列表
        $subject = $this ->person ->getSubjectUser($class_id);           //科目名称
        $seme_status = $this ->person ->getSemeStatus($class_id);        //上下册高亮显示
        $courseList = $this ->person ->getMystudyClass($class_id);          //我的课程（新版
        $recommendClass = $this ->person ->recommendClass($class_id);          //推荐课程（新版
        $classList = $this->person->getClassList($class_id);//获取用户是否存在已经激活的课程
        $user = $this ->_info;
        if($user){
            //判断用户是否存在未激活课程
            $notActiveCourseList = Db::name('order_person_son')->alias('ps')
                            ->join('guard_video_class vc','vc.id = ps.video_class_id')
                            ->field("vc.*")
                            ->where(['vc.grade_id'=>$class_id,'ps.type'=> ['neq', 1], 'ps.is_audition'=>0,'ps.person_id'=>$user['id'],'ps.orderCheck'=>2])
                            ->select();
            $notActiveCourseLists = Db::name('order_person_son')->alias('ps')
                ->join('guard_product vc','vc.id = ps.product_id')
                ->field("vc.*")
                ->where(['vc.grade_id'=>$class_id,'ps.type'=> ['neq', 1], 'ps.is_audition'=>0,'ps.person_id'=>$user['id'],'ps.orderCheck'=>2])
                ->select();
            $notActiveCourseList = array_merge($notActiveCourseList,$notActiveCourseLists);
        }
        //查询年级列表
        $grade = Db::name('grade')->select();
        $this ->assign('subject', $subject);
        $this ->assign('courseList', $courseList);
        $this ->assign('classList', $classList);
        $this ->assign('recommendClass', $recommendClass);
        $this ->assign('list', $list);
        $this ->assign('s_status', $seme_status);
        $this->assign('grade', $grade);
        $this->assign('userGrade',$userGrade);
        $this->assign('notActiveCourseList',$notActiveCourseList);
        return $this->fetch("index/person/person");
    }
    /**
     * [myClassList 我的课程列表接口]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-15T14:21:50+0800
     * @return   [type]                   [description]
     */
    public function myClassList(){
        $param = input();
        $subject_id = isset($param['subject_id'])?$param['subject_id']:1;       //科目id
        $seme_id = isset($param['seme_id'])?$param['seme_id']:1;                //上下册
        // $class_id = GetGlodalClassId();
        $class_id = isset($param['gid']) ? $param['gid'] : 7 ;
        //根据年级判断科目是否是全册
        $whereSemester['grade_id']=$class_id;
        $whereSemester['subject_id']=$subject_id;
        $res=Db::name('video_class')->field('Semester')->where($whereSemester)->find();
        if($res['Semester']==3){
          $seme_id=3;
        }
        //获取课程列表
        $list = $this ->person ->GetUserClass($class_id, $subject_id, $seme_id);
        if(!empty($list['data_arr'])){
            //上下册显示状态
            $seme_status = $this ->person ->getSemeStatus($class_id, $subject_id, $seme_id);
        }
        if(!isset($seme_status)){
            $seme_status = [0,0];
        }
        $list['seme_status'] = $seme_status;
        ajaReturn($list);
    }

    /**
     * [myNewClassList 新的我的课程列表接口]
     */
    public function myNewClassList()
    {
        $param=input();
        $courseList = $this ->person ->getMystudyClass($param['class_id']); 
        $recommendClass = $this ->person ->recommendClass($param['class_id']);          //推荐课程（新版)
        $classList = $this->person->getClassList($param['class_id']);//获取用户是否存在已经激活的课程
        $user = $this ->_info;
        if($user){
            //判断用户是否存在未激活课程
            $notActiveCourseList = Db::name('order_person_son')->alias('ps')
                            ->join('guard_video_class vc','vc.id = ps.video_class_id')
                            ->field("vc.*")
                            ->where(['vc.grade_id'=>$param['class_id'],'ps.type'=> ['neq', 1],'ps.is_audition'=>0,'ps.person_id'=>$user['id']])
                            ->select();
        }else{
            $notActiveCourseList = '';
        }
        $ajax['courseList'] = $courseList;
        $ajax['classList'] = $classList;
        $ajax['notActiveCourseList'] = $notActiveCourseList;
        return jsonMsg('success',1,$ajax,$recommendClass);
    }

    public function noteSubject(){

        $param = input();

        $gid = isset($param['gid']) ? $param['gid'] : 7;

        $data = Db::name('person_collect') ->field('video_class_id,product_id')
                                           ->where(['uid' => $this ->userinfo['id']]) 
                                           ->where(['status' => 2])
                                           ->select();

        if(empty($data)){

            ajaReturn([], 0, 'success');
        }

        $video_class_id = array_unique(array_column($data, 'video_class_id'));
        $product_id = array_unique(array_column($data, 'product_id'));
        $s_list = Db::name('video_class') ->field('subject_id')
                                          ->where('id', 'in', $video_class_id)
                                          ->where(['grade_id' => $gid])
                                          ->select();
        if(!empty($product_id)){
            $s_list_p = Db::name('product') ->field('subject_id')
                ->where('id', 'in', $product_id)
                ->where(['grade_id' => $gid])
                ->select();
        }
        if(empty($s_list) && empty($s_list_p)){

            ajaReturn([], 0, 'success');
        }

        $s_list = array_merge($s_list,$s_list_p);
        $subject_list = Db::name('subject') ->field('id,subject') 
                                            ->where('id', 'in', array_column($s_list, 'subject_id' ))
                                            ->select();
        $subject_volumn = Db::name('video_class') ->field('Semester') 
                                                  ->where(['grade_id' => $gid, 'subject_id' =>$subject_list[0]['id']])
                                                  ->select();

        $volumn_id = array_column($subject_volumn, 'Semester');

        ajaReturn(['slist' => $subject_list, 'volumn' => $volumn_id], 0, 'success');

    }

    public function mynoteList(){

        $pagesize = 3;

        $param = input();

        $gid = isset($param['gid']) ? $param['gid'] : 7;

        $subject_id = isset($param['sid']) ? $param['sid'] : '';

        $seme_id = isset($param['vid']) ? $param['vid'] : 1;

        $page = isset($param['page']) ? $param['page'] : 1;
        if($subject_id>9){
            $video_class_id = Db::name('product') ->field('id')
                ->where(['grade_id' => $gid, 'subject_id' => $subject_id, 'Semester' => $seme_id])
                ->find();
        }else{
            $video_class_id = Db::name('video_class') ->field('id')
                ->where(['grade_id' => $gid, 'subject_id' => $subject_id, 'Semester' => $seme_id])
                ->find();
        }


        if(empty($video_class_id)){

            ajaReturn([], 0, 'success');
        }
        if($subject_id>9){
            $product_video_id = Db::name('product p')
                ->join('product_info pi','p.id=pi.product_id','inner')
                ->field('pi.video_id')
                ->where(['p.grade_id' => $gid, 'p.subject_id' => $subject_id, 'p.Semester' => $seme_id])
                ->select();
            $video_arr_ids = array_column($product_video_id,'video_id');
            $video_id = Db::name('video') ->field('id,outline') ->whereIn('id', $video_arr_ids) ->where(['display' => 1])->select();
        }else{
            $video_id = Db::name('video') ->field('id,outline') ->where(['kid' => $video_class_id['id']]) ->where(['display' => 1]) ->select();
        }

        $video_arr = array_column($video_id, 'id');
        $nodelist_count = Db::name('person_collect') ->field('count(1) as counts') 
                                               ->where(['uid' => $this ->userinfo['id'],'status' => 2]) 
                                               ->where('video_id', 'in', $video_arr)
                                               ->find();

        if(ceil($nodelist_count['counts']/$pagesize) < $page && $page != 1){

            --$page;
        }

        $nodelist = Db::name('person_collect') ->field('id,video_id,intime,startTime,noteText as content,video_class_id,product_id')
                                               ->where(['uid' => $this ->userinfo['id'],'status' => 2]) 
                                               ->where('video_id', 'in', $video_arr)
                                               ->page($page,$pagesize)
                                               ->select();
        if(empty($nodelist)){

            ajaReturn([], 0, 'success');
        }
        //   课程标题id
        $outline = array_combine(array_column($video_id, 'id'), array_column($video_id, 'outline'));

        foreach($nodelist as $key =>&$val){

            $nodelist[$key]['intime'] = date('Y-m-d H:i:s',$val['intime']);

            $classStudytime[0] = str_pad(floor($val['startTime']/3600),2,0,STR_PAD_LEFT);
            $classStudytime[1] = str_pad(floor(($val['startTime']%3600)/60),2,0,STR_PAD_LEFT);
            $classStudytime[2] = str_pad(($val['startTime']-$classStudytime[0] * 3600 - $classStudytime[1] * 60),2,0,STR_PAD_LEFT);
            if($classStudytime[0] == '00'){
                unset($classStudytime[0]);
            }
            $nodelist[$key]['startTime'] = implode(':',$classStudytime);

            $nodelist[$key]['videoname'] = isset($outline[$val['video_id']]) ? $outline[$val['video_id']] : '不存在的视频标题';
            if(empty($nodelist[$key]['video_class_id'])){
                $nodelist[$key]['video_class_id'] = $nodelist[$key]['product_id'];
                $nodelist[$key]['productStatus'] = 1;
            }else{
                $nodelist[$key]['productStatus'] = 0;
            }
        }
        $page_html = noteFpage($page,$nodelist_count['counts'],$pagesize);

        ajaReturn(['list' => $nodelist, 'page' => $page_html],0,'success');

    }

    public function deletevolumn(){

        $pagesize = 3;

        $param = input();

        $gid = isset($param['gid']) ? $param['gid'] : 7;

        $subject_id = isset($param['sid']) ? $param['sid'] : '';

        $seme_id = isset($param['vid']) ? $param['vid'] : 1;

        $id = isset($param['id']) ? $param['id'] : 0;

        $res = Db::name('person_collect') ->delete([$id]);

        if($res){
            ajaReturn('', 0, 'success');
        }else{
            ajaReturn('', 1001, 'error');
        }

    }

    public function getsubjectlist(){
        $param = input();
        
        $class_id = isset($param['gid']) ? $param['gid'] : 7 ;
        //  科目
        $subject = $this ->person ->getSubjectUser($class_id);

        ajaReturn($subject);
    }
    /**
     * [setPersonImg 修改头像]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-28T16:18:19+0800
     */
    // public function setPersonImg(){
    //     if(Request::instance()->isAjax()){

    //         $file = request()->file('file');

    //         // 移动到框架应用根目录/public/uploads/ 目录下
    //         $info = $file->move(ROOT_PATH . 'public' . DS . 'upload/uploads');

    //         if($info){
    //             // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
    //             $url =  $info->getSaveName();
    //             $image = Image::open($info);
    //             $image->thumb(411,411)->save(ROOT_PATH . 'public' . DS . 'upload/uploads/'.$url);
    //             $_path = ROOT_PATH . 'public' . DS . 'upload/uploads/'.$this ->userinfo['litpic'];
    //             if(file_exists($_path) && is_file($_path) && $this ->userinfo['litpic']){
    //                 unlink($_path);
    //             }
                
    //             $res = Db::name('person') ->where(['id' => $this ->userinfo['id']]) ->update(['litpic' => $url]);
    //             if($res){
    //                 ajaReturn('',0,'成功');
    //             }else{
    //                 ajaReturn([$file->getError()],1001,'失败');
    //             }
    //         }else{
    //         // 上传失败获取错误信息
    //         echo $file->getError();
    //         }
    //     }
    //     return $this ->fetch('index/person/personal-headPortrait');
    // }

    #   修改头像 （上传至服务器）
    public function setPersonImg(){

        if(Request::instance()->isAjax()){
            $file = request()->file('file');
            $info = $file->getinfo();

            $result = '';

            if($file){
                //实例化七牛类
                $qiniu = new QiniuModel();
                //获取上传凭证
                $qiniuSpace = 'ydtvlitpic';
                // 删除七牛云
                $qiniu ->delFile($qiniuSpace,$info['name']);

                $uploadToken = $qiniu->getQnToken($qiniuSpace);

                $filePath = $info['tmp_name'];

                $fileName = $qiniu->getNewfilename('index',$info['name']);
                //上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
                $qiniuSpaceHost = 'http://ydtvlitpic.ydtkt.com/';

                $result = $qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);

                $res = Db::name('person') ->where(['id' => $this ->userinfo['id']]) 
                                        ->update(['litpic' => substr($result,28)]);

                if($res){
                    ajaReturn('',0,'成功');
                }else{
                    ajaReturn($result,1001,'失败');
                }
            }
        }

        return $this ->fetch('index/person/personal-headPortrait');
    }
    //我的收藏科目列表
    public function myCollectionSubjectList()
    {
        $res=Db::name('subject')->select();
        jsonMsg($res,1);
    }
    //我的收藏
    public function collect()
    {
        $s_array = [1 => '上', 2 => '下', 3 => '全册'];
        $param=input();
        $info = $this ->_info;
        $gid = isset($param['gid']) ? $param['gid'] : 7;
        $where['c.uid']=$info['id'];
        $where['c.status']=1;
        if($param['subject']>=1){
            $where['v.subject_id']=$param['subject'];
        }
        $pagesize=12;
        if($param['pagenow']>1){
            $pagestart=($param['pagenow']-1)*$pagesize;
        }else{
            $pagestart=0;
        }
        $collect = Db::name('person_collect')
            ->alias('c')
            ->field('c.*,v.grade_id,v.subject_id,v.Semester,v.img,v.name,v.title,s.subject,vs.outline')
            ->join('guard_video_class v','v.id=c.video_class_id','left')
            ->join('guard_video vs','vs.id=c.video_id')
            ->join('guard_subject s','s.id = v.subject_id','left')
            ->where(['vs.display' => 1])
            ->where($where)
            ->where(['v.grade_id' => $gid])
            ->order('c.intime desc')
            ->limit($pagestart,$pagesize)->select();
        foreach ($collect as $k =>$v) {
            if(!empty($v['countTime'])){
               $collect[$k]['minute_count'] = intval(floor($v['countTime']/60));//向下取整数
               $collect[$k]['second_count']=   $v['countTime']%60;//取余
            }
            $collect[$k]['Semester'] = $s_array[$v['Semester']];
        }
        $collect_count = Db::name('person_collect')
            ->alias('c')
            ->field('c.*,v.grade_id,v.subject_id,v.Semester,v.img,v.name,v.title,s.subject,vs.outline')
            ->join('guard_video_class v','v.id=c.video_class_id','left')
            ->join('guard_video vs','vs.id=c.video_id')
            ->join('guard_subject s','s.id = v.subject_id','left')
            ->where(['vs.display' => 1])
            ->where($where)
            ->where(['v.grade_id' => $gid])
            ->count();
        $page=collectFpage($param['pagenow'],$collect_count,$pagesize,$param['subject']);
        if($collect){
            return jsonMsg($page,1,$collect,$collect_count);
        }else{
            return jsonMsg('暂时没有符合条件的数据！',0,$collect,$collect_count);
        }
    }
    //删除我的收藏
    public function delMyCollection()
    {
        $param=input();
        $where=$param['id'];
        $qiniu = new QiniuModel();
        //删除我的收藏 同时删除七牛云的图片
        //获取收藏图片
        $imgPath = Db::name('person_collect')->where('id','in',$where)->select();
        foreach ($imgPath as $key => $value) {
            if(!empty($imgPath[$key]['collectImg'])){
                $collectImgPath = str_replace("http://litpic.ydtkt.com/","",$imgPath[$key]['collectImg']);
                $qiniuSpace = 'litpic';
                $qiniu->delFile($collectImgPath,$qiniuSpace);
            }
        }
        $res=Db::name('person_collect')->where('id','in',$where)->delete();
        if($res){
            jsonMsg('删除成功！',1);
        }else{
            jsonMsg('删除失败！',0);
        }
    }
    //个人中心
    public function infor()
    {
        $usermsg = $this->person->GetPerson();
        $msg_count1 = !empty($usermsg['nickName'])?10:0;
        $msg_count2 = !empty($usermsg['phone'])?15:0;
        $msg_count3 = !empty($usermsg['gender'])?10:0;
        $msg_count4 = !empty($usermsg['city']) || !empty($usermsg['province']) || !empty($usermsg['country'])?10:0;
        $msg_count5 = !empty($usermsg['birthday'])?10:0;
        $msg_count6 = !empty($usermsg['grade_id'])?10:0;
        $msg_count7 = !empty($usermsg['email'])?10:0;
        $msg_count8 = !empty($usermsg['wechat'])?15:0;
        $msg_count9 = !empty($usermsg['school'])?10:0;
        $msg_count = round($msg_count1+$msg_count2+$msg_count3+$msg_count4+$msg_count5+$msg_count6+$msg_count7+$msg_count8+$msg_count9)."%";
        jsonMsg($msg_count);
    }
    public function upload_img(){

        return $this->fetch("index/person/uploadImg");
    }
    //更新资料
    public function updatePerson(){
        // formlimit();
        $uid = $this ->userinfo['id'];
        $data = input('post.');

        $data['birthday'] = strtotime($data['birthday']);

        $data['up_time'] = time();
        $re = Db::name('person')->where('id',$uid)->update($data);
        if($re){
           jsonMsg('修改成功!',1);
        }else{
            jsonMsg('修改失败!',0);
        }
    }
    //消息中心
    public function message()
    {
        $param = input();
        $info = $this ->_info;
        $status = input('status');
        $pagesize=10;
        if($param['pagenow']>1){
            $pagestart=($param['pagenow']-1)*$pagesize;
        }else{
            $pagestart=0;
        }
        $where['uid'] = $info['id'];
        $where['status'] = $status;
        if($status == 2){  //全部
            $where['status'] = array('in','0,1');
        }
        $msg_list = Db::name('message')->where($where)->order('create_time desc')->limit($pagestart,$pagesize)->select();
        foreach($msg_list as $k=>$v)
        {
            $msg_list[$k]['create_time']=date('Y/m/d H:i:s',$v['create_time']);
        }
        $msg_count = Db::name('message')->where($where)->count();
        $page=messageFpage($param['pagenow'],$msg_count,$pagesize,$status);
        if($msg_list){
            return jsonMsg($page,1,$msg_list,$msg_count);
        }else{
            return jsonMsg('暂时没有符合条件的数据！',0,$msg_list,$msg_count);
        }
    }
    //消息中心-删除已读
    public function del_msg(){
        $info = $this ->_info;
        $re = Db::name('message')->where(array('status'=>1,'uid'=>$info['id']))->delete();
        if($res){
            jsonMsg('删除成功',1);
        }else{
            jsonMsg('删除失败',0);
        }
    }
    //消息中心-消息详情
    public function msg_info(){
        $id = input('id');
        $msg = Db::name('message')->where('id',$id)->find();
        //未读变已读
        if($msg['status'] == 0){
           $re =  Db::name('message')->where('id',$id)->setField(array('status'=>1));
        }
        $msg['create_time']=date('Y/m/d H:i:s',$msg['create_time']);
        return jsonMsg($msg,1);
    }
    //消息详情中删除消息
    public function delMssage()
    {
        $param=input();
        $msg = Db::name('message')->where('id',$param['id'])->find();
        // 账号异常消息
        if ($msg['type'] == 1) {
            Db::name('ip_log')->where(['uid' => $msg['uid']])->delete();
            Db::name('person')->where(['id' => $msg['uid']])->update(['risk' => 0]);
        }
        $res=Db::name('message')->where('id','in',$param['id'])->delete();
        if($res){
            jsonMsg('删除成功',1);
        }else{
            jsonMsg('删除失败',0);
        } 
    }
    /**
     * [activationCode 注册卡激活]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-11T17:49:54+0800
     * @return   [json]
     */
    public function activationCode(){
        $param = input();
        $where['card'] = $param['title'];
        $where['show_password'] = $param['password'];
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
                                    ->where(['person_id' => $this ->userinfo['id']]) 
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
        //                                      ->where(['code_id' => $code_msg['id'], 'person_id' => $this ->userinfo['id']]) 
        //                                      ->find();
        // if(empty($is_order)){
        //     ajaReturn([],1001,'未知错误，请联系本地代理商！');
        // }else{
        //     if($is_order['orderCheck'] != 2){
        //         ajaReturn([],1001,'未知错误，请联系本地代理商！');
        //     }
        // }
        //激活时将该卡所对应的视频写入video_log
        $res = setVideoLog($code_msg['coursePackage_id'],$this ->userinfo['id']);
        if(!$res){
            ajaReturn([],1001,'激活失败，请稍后重试');
        }
        
        $data['person_id'] = $this ->userinfo['id'];
        $data['status'] = 2;
        $data['update_time'] = time();
        $res=$this->person->updateRegistrationCardActivation($where,$data,$code_msg['user_id']);
        if(!$res){
            ajaReturn([],1001,'激活失败，请稍后重试');
        }
        ajaReturn([],1,'您的学习卡已激活成功!');
    }
    //获取用户激活注册卡列表
    public function getPersonCodeList()
    {
        $param=input();
        //获取用户id
        $userinfo=$this->person->GetPerson();
        $where['person_id']=$userinfo['id'];
        $pagesize=20;
        $pagenow=$param['pagenow'];
        $pagestart=($pagenow-1)*$pagesize;
        $countCode=DB::name('code')->where($where)->count();
        $codeList=DB::name('code')->where($where)->order('update_time desc')->limit($pagestart,$pagesize)->select();
        foreach($codeList as $k=>$v)
        {
            $codeList[$k]['update_time']=date("Y 年 m 月 d 日 H:i:m",$v['update_time']);
        }
        //获取分页
        $page=Fpage($pagenow,$countCode,$pagesize);
        if($codeList){
            return jsonMsg($page,1,$codeList,$countCode);
        }else{
            return jsonMsg('暂时没有符合条件的数据！',0,$codeList,$countCode);
        }
    }
    public function upload(){
        $file = request()->file('file');
        $info = $file->getinfo();
        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            //获取上传凭证
            $uploadToken=$qiniu->getQnToken();
            $filePath=$info['tmp_name'];
            $fileName=$qiniu->getNewfilename('index',$info['name']);
            //上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $data['litpic']=$qiniu->uploadFile($uploadToken,$filePath,$fileName);
            $info = $this ->_info;
            $data['up_time'] = time();
            $re = Db::name('person')->where('id',$info['id'])->update($data);
            if($re){
                $this->redirect("index/person/infor");
            }else{
                $this->redirect("index/person/upload_img");
            }
        }
    }
    /**
     * [myCourse 手机端个人中心课程列表+接口]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-24T16:15:03+0800
     * @return   [type]                   [description]
     */
    public function myCourse(){
        if (!isMobile()) {
            //如果是pc
            return $this->redirect('/index/person/person');
        }
       $param = input();
        $subject_id = isset($param['subject_id'])?$param['subject_id']:1;       //科目id
        $seme_id = isset($param['seme_id'])?$param['seme_id']:1;                //上下册
        $class_id = GetGlodalClassId();
        //获取课程列表
        $list = $this ->person ->GetUserClass($class_id, $subject_id, $seme_id);
        //判断是否是ajax请求
        if(Request::instance()->isAjax()){
            ajaReturn($list);
        }
        $subject = $this ->person ->getSubjectUser($class_id);           //科目名称
        $this ->assign('subject', $subject);
        $this ->assign('list', $list);
        return $this ->fetch('index/person/my-course');
    }
    /**
     * [myMessage 手机端消息中心]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-24T18:53:35+0800
     * @return   [type]                   [description]
     */
    public function myMessage(){
        if (!isMobile()) {
            //如果是pc
            return $this->redirect('/index/person/person');
        }
        if(Request::instance()->isAjax()){
            $param = input();
            $info = $this ->_info;
            $status = input('status');

            $msg_list = Db::name('message')->where(array('status'=>$status,'uid'=>$info['id']))->order('create_time')->select();
            foreach($msg_list as $k=>$v)
            {
                $msg_list[$k]['create_time']=date('Y/m/d H:i:s',$v['create_time']);
            }
            $msg_count = Db::name('message')->where(array('status'=>$status,'uid'=>$info['id']))->count();
            if($msg_list){
                return jsonMsg('',1,$msg_list,$msg_count);
            }else{
                return jsonMsg('暂时没有符合条件的数据！',0,$msg_list,$msg_count);
            }
        }
        return $this ->fetch('index/person/my-message');
    }
    /*
    * 消息详情页 手机端
     */
    public function msgContent(){
        if (!isMobile()) {
            //如果是pc
            return $this->redirect('/index/person/person');
        }
        $id = input('id');
        $msg = Db::name('message')->where('id',$id)->find();
        //未读变已读
        if($msg['status'] == 0){
           $re =  Db::name('message')->where('id',$id)->setField(array('status'=>1));
        }
        $msg['create_time']=date('Y/m/d H:i:s',$msg['create_time']);
        return $this ->fetch('/index/person/message-content',['msg' =>$msg]);
    }
    /*
    *个人信息（手机端）
     */
    public function personMsg(){
        if (!isMobile()) {
            //如果是pc
            return $this->redirect('/index/person/person');
        }
        return $this ->fetch('index/person/info');
    }
    /**
     * [mySelect 手机端收藏列表]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-25T15:44:32+0800
     * @return   [type]                   [description]
     */
    public function mySelect(){
        if (!isMobile()) {
            //如果是pc
            return $this->redirect('/index/person/person');
        }
        if(Request::instance()->isAjax()){
            $param=input();
            $info = $this ->_info;
            $where['c.uid']=$info['id'];
            if($param['subject']>=1){
                $where['v.subject_id']=$param['subject'];
            }
            $collect = Db::name('person_collect')
                ->alias('c')
                ->field('c.*,v.grade_id,v.subject_id,v.img,v.name,v.title,s.subject,v1.testclass,v1.outline')
                ->join('guard_video_class v','v.id=c.video_class_id','left')
                ->join('guard_subject s','s.id = v.subject_id','left')
                ->join('guard_video v1','v1.id = c.video_id','left')
                ->where(['v1.display' => 1])
                ->where($where)
                ->order('c.intime desc')
                ->select();
            foreach ($collect as $k =>$v) {
                if(!empty($v['countTime'])){
                   $collect[$k]['minute_count'] = intval(floor(  $v['countTime']/60));//向下取整数
                   $collect[$k]['second_count']=   $v['countTime']%60;//取余
                }
            }
            if($collect){
                return jsonMsg('',1,$collect,count($collect));
            }else{
                return jsonMsg('暂时没有符合条件的数据！',0,$collect,count($collect));
            }
        }
        $res=Db::name('subject')->select();
        return $this ->fetch('index/person/my-select',['res' => $res]);
    }
    /*
    *卡号列表（手机端）
     */
    public function myCard(){
        if (!isMobile()) {
            //如果是pc
            return $this->redirect('/index/person/person');
        }
        if(Request::instance()->isAjax()){
            $userinfo= $this->person->GetPerson();
            $where['person_id']=$userinfo['id'];
            $countCode=DB::name('code')->where($where)->count();
            $codeList=DB::name('code')->where($where)->order('update_time desc')->select();
            foreach($codeList as $k=>$v)
            {
                $codeList[$k]['update_time']=date("Y 年 m 月 d 日 H:i:m",$v['update_time']);
            }
            if($codeList){
                return jsonMsg('',1,$codeList,$countCode);
            }else{
                return jsonMsg('暂时没有符合条件的数据！',0,$codeList,$countCode);
            }
        }
        return $this ->fetch('/index/person/my-card');
    }
    /*
     *我的收藏 修改收藏名称
     */
    public function updCollectName(){
        $param = Request::instance() ->param();
        $id = isset($param['id'])?$param['id']:'';
        $name = isset($param['name'])?$param['name']:'';
        if(empty($id) && empty($name)){
            ajaReturn('',1001,'error');
        }
        $res = Db::name('person_collect') ->where(['id' => $id]) ->update(['ctitle' => $name]);
        if(!$res){
            ajaReturn('',1001,'error');
        }
    }

    public function cardactivation(){
        
        $param = Request::instance() ->param();
        // 年级
        $grade = isset($param['gid']) ? $param['gid'] : 7;
        // 当前登录用户id
        $uid = $this ->userinfo['id'];

        if($grade == 0){

            $data = Db::name('order_person_son') ->field('video_class_id,person_id') 
                                                 ->where(['orderCheck' => 2, 'is_audition' => 0,'person_id' => $uid,]) 
                                                 ->select();
            empty($data) && $grade = 7;

            if(!empty($data)){

                $video_id = array_unique(array_column($data, 'video_class_id'));

                $grade_arr = Db::name('video_class') ->field('grade_id') 
                                                     ->where('id', 'in', $video_id) 
                                                     ->group('grade_id') 
                                                     ->order('grade_id') 
                                                     ->find();
                !empty($grade_arr) && $grade = $grade_arr['grade_id'];
            }
        }

        $data = $this->person ->cardactivation($grade, $uid);

        if(empty($data)){
            ajaReturn('', 1001, '系统繁忙，请稍后再试');
        }

        ajaReturn($data, 0, 'success',$grade);

    }

    public function activeCourses(){

        $param = Request::instance() ->param();
        // video_class     id
        $cid = isset($param['cid']) ? $param['cid'] : 0;
        if($cid!=0){
            $cidArr = explode('+',$cid);
            $cid = $cidArr[0];
            $productStatus = $cidArr[1];
        }
        // 当前登录用户id
        $uid = $this ->userinfo['id'];

        $res = $this->person ->activeCourses($cid, $uid,$productStatus);

        if($res){

            $res = Db::name('video_class') ->field('grade_id') ->where(['id' => $cid]) ->find();
            $grade=$res['grade_id'];
            // $data = $this->person ->cardactivation($grade, $uid);

            ajaReturn($grade, 0, '激活成功');
        }

        ajaReturn('', 1001, '激活失败');

    }

    //测评信息
    public function getTestPaperList(){

        $param = input();
        $userInfo =  $this ->_info;
        $where = [];

        $pagesize=8;
        if($param['pagenow']>1){
            $pagestart=($param['pagenow']-1)*$pagesize;
        }else{
            $pagestart=0;
        }

        //获取试卷列表信息
        $testPaperList = DB::name('test_paper')
                            ->field('tp.*,from_unixtime(addTime,"%Y-%m-%d") as create_at,log.review')
                            ->alias('tp')
                            ->join('guard_question_log log','tp.id = log.t_id','LEFT')
                            ->where('(grade_id ='.$param['gid'].' and semester='.$param['semester'].' and subject_id='.$param['subject_id'].'
                                 and is_all = 1 and pid = 0) 
                                 or (grade_id ='.$param['gid'].' and semester='.$param['semester'].' and subject_id='.$param['subject_id'].' 
                                 and is_all = 2 and pid = 0 
                                 and find_in_set('.$userInfo['id'].',students))')
                            ->limit($pagestart,$pagesize)
                            ->order('addTime desc')
                            ->group('tp.id')
                            ->select();
                            // ->getLastSql();
        $count = DB::name('test_paper')
                            ->field('tp.*,from_unixtime(addTime,"%Y-%m-%d") as create_at,log.review')
                            ->alias('tp')
                            ->join('guard_question_log log','tp.id = log.t_id','LEFT')
                            ->where('(grade_id ='.$param['gid'].' and semester='.$param['semester'].' and subject_id='.$param['subject_id'].'       and  is_all = 1 and pid = 0)
                                       or (grade_id ='.$param['gid'].' and semester='.$param['semester'].' 
                                       and subject_id='.$param['subject_id'].' 
                                       and is_all = 2 and pid = 0 
                                       and find_in_set('.$userInfo['id'].',students))')
                            ->group('tp.id')
                            ->count();
        $page=TestPaperFpage($param['pagenow'],$count,$pagesize,$param['gid'],$param['semester'],$param['subject_id']);
        if(!empty($testPaperList)){
            return jsonMsg($page,1,$testPaperList);
        }else{
            return jsonMsg('暂无数据',0);
        }
    }
    //试卷详情
    public function testPaperInfo(){
        $param = input();
        $id = isset($param['id']) ? $param['id'] : '';
        if(!empty($id)){
            //判断question_log表中是否有数据
            $logInfo = Db::name('question_log')->where(['person_id'=>$this ->userinfo['id'],'t_id'=>$id])->select();
            if(!empty($logInfo)){
                $questionList = Db::name('test_paper')
                            ->alias('tp')
                            ->field('tp.score,tp.title,q.*,qt.questype,tp.sort,log.status,log.status_log,log.fraction,log.answer')
                            ->join('guard_question q','tp.qid = q.q_id')
                            ->join('guard_questype qt','qt.quest_id = q.q_type')
                            ->join('guard_question_log log','log.t_id = tp.pid and log.q_id=q.q_id','LEFT')
                            ->where("tp.pid = ".$param['id']." and tp.q_type < 7 and log.id in (select id from guard_question_log as log where log.person_id = ".$this ->userinfo['id']." and log.q_id = q.q_id and  log.t_id = tp.pid)")
                            ->order('tp.sort')
                            ->group('q.q_id')
                            ->select();
            }else{
               $questionList = Db::name('test_paper')
                            ->alias('tp')
                            ->field('tp.score,tp.title,q.*,qt.questype,tp.sort')
                            ->join('guard_question q','tp.qid = q.q_id')
                            ->join('guard_questype qt','qt.quest_id = q.q_type')
                            // ->join('guard_question_log log','log.t_id = tp.pid and log.q_id=q.q_id','LEFT')
                            ->where("tp.pid = ".$param['id']." and tp.q_type < 7")
                            ->order('tp.sort')
                            ->group('q.q_id')
                            ->select(); 
            }
            
            //获取题型列表
            $questionTypeList = Db::name('questype')
                                ->alias('q')
                                ->join('guard_test_paper tp','tp.q_type = q.quest_id','LEFT')
                                ->field('q.*,tp.sort')
                                ->where('tp.pid = '.$param['id']." and tp.q_type < 7")
                                ->group('q.quest_id')
                                ->order('tp.sort')
                                ->select();

            $bit = array("一", "二", "三", "四", "五", "六", "七", "八", "九", "十");
            $newList = array();
            for ($i=0; $i < count($questionTypeList); $i++) {
                $k = 0;
                foreach ($questionList as $key => $value) {
                    if($value['q_type'] ==  $questionTypeList[$i]['quest_id']){
                        $newList[$i]['questype'] = $value['questype'];
                        $newList[$i]['q_type'] = $value['q_type'];
                        $newList[$i]['son'][$k]['q_stem'] = $value['q_stem'];
                        $newList[$i]['son'][$k]['q_id'] = $value['q_id'];
                        $newList[$i]['son'][$k]['q_select'] = htmlspecialchars_decode($value['q_select']);
                        $newList[$i]['son'][$k]['q_describe'] = $value['q_describe'];
                        $newList[$i]['son'][$k]['q_answer'] = $value['q_answer'];
                        $newList[$i]['son'][$k]['q_stem_img'] = $value['q_stem_img'];
                        if(!empty($logInfo)){
                            $newList[$i]['son'][$k]['log_status'] = $value['status'];
                            $newList[$i]['son'][$k]['log_status_log'] = $value['status_log'];
                            $newList[$i]['son'][$k]['log_fraction'] = $value['fraction'];
                            $newList[$i]['son'][$k]['log_answer'] = $value['answer'];
                        }
                        $newList[$i]['son'][$k]['question_sort'] = "第".$bit[$k]."题";
                        $k++;
                    }
                }
            }
            if(!empty($logInfo)){
                //获取题帽题
                $questionrowsList = Db::name('test_paper')
                                        ->alias('tp')
                                        ->join('guard_questionrows q','q.qr_id = tp.qid')
                                        ->join('guard_question qt','qt.q_parent = q.qr_id','RIGHT')
                                        ->join('guard_question_log log','log.t_id = tp.pid and log.q_id=qt.q_id','LEFT')
                                        ->where('tp.pid = '.$id.' and tp.q_type >= 7  and log.id in (select id from guard_question_log as log where log.person_id = '.$this ->userinfo['id'].' and log.q_id = qt.q_id and  log.t_id = tp.pid)')
                                        ->field('q.qr_question,q.qr_type,q.qr_id,qt.*,log.status,log.status_log,log.fraction,log.answer')
                                        ->group('qt.q_id')
                                        ->select();
            }else{
                //获取题帽题
                $questionrowsList = Db::name('test_paper')
                                        ->alias('tp')
                                        ->join('guard_questionrows q','q.qr_id = tp.qid')
                                        ->join('guard_question qt','qt.q_parent = q.qr_id','RIGHT')
                                        // ->join('guard_question_log log','log.t_id = tp.pid and log.q_id=qt.q_id','LEFT')
                                        ->where('tp.pid = '.$id.' and tp.q_type >= 7')
                                        ->field('q.qr_question,q.qr_type,q.qr_id,qt.*')
                                        ->group('qt.q_id')
                                        ->select();
            }
            $selectQuestList = Db::name('test_paper')
                                ->where('pid = '.$id.' and q_type >= 7')
                                ->field('qid')
                                ->select();
            $newQuestionrowsList = array();
            // var_dump($questionrowsList);die;
            for($i=0; $i<count($selectQuestList); $i++ ){
                $m=0;
                foreach ($questionrowsList as $key => $value) {
                    if($value['q_parent'] == $selectQuestList[$i]['qid']){
                        $newQuestionrowsList[$i]['qr_question'] = $value['qr_question'];
                        $newQuestionrowsList[$i]['question_sort'] = $bit[$i+$k];
                        $newQuestionrowsList[$i]['qr_type'] = $value['qr_type'];
                        $newQuestionrowsList[$i]['qr_id'] = $value['qr_id'];
                        $newQuestionrowsList[$i]['son'][$m]['q_id'] = $value['q_id'];
                        $newQuestionrowsList[$i]['son'][$m]['q_stem'] = $value['q_stem'];
                        $newQuestionrowsList[$i]['son'][$m]['q_describe'] = $value['q_describe'];
                        $newQuestionrowsList[$i]['son'][$m]['q_answer'] = $value['q_answer'];
                        $newQuestionrowsList[$i]['son'][$m]['q_select'] = htmlspecialchars_decode($value['q_select']);
                        if(!empty($logInfo)){
                            $newQuestionrowsList[$i]['son'][$m]['log_status'] = $value['status'];
                            $newQuestionrowsList[$i]['son'][$m]['log_status_log'] = $value['status_log'];
                            $newQuestionrowsList[$i]['son'][$m]['log_fraction'] = $value['fraction'];
                            $newQuestionrowsList[$i]['son'][$m]['log_answer'] = $value['answer'];
                        }
                        $m++;
                    }
                }
            }
            $testPaperList = Db::name('test_paper')->where('id='.$id)->select();
            $data = array();
            $data['newList'] = $newList;    //普通试题列表
            $data['id'] = $id;    //试卷id
            $data['testPaperList'] = $testPaperList;    //试卷信息
            $data['newQuestionrowsList'] = $newQuestionrowsList;   //题帽题
            if(!empty($logInfo)){
                $data['log_fraction'] = $questionList ? $questionList[0]['fraction'] : '';//分数大于0就认为用户做过试卷 否则判定为测试过
            }
            if(!empty($questionList) || !empty($questionrowsList)){
                return jsonMsg('success',1,$data,$bit);
            }else{
                return jsonMsg('暂无数据',0);
            }
        }
    }

    //上传试题答案 空间名：ydtvlitpic
    public function uploadQuestAnswer(){
        $file = request()->file('file');
        $info = $file->getinfo();
        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            //获取上传凭证
            // $qiniuSpace = 'ydtvlitpic';
            $qiniuSpace = 'litpic';
            $uploadToken=$qiniu->getQnToken($qiniuSpace);
            $filePath=$info['tmp_name'];
            $fileName=$qiniu->getNewfilename('questionAnswer-',$info['name']);
            //上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://litpic.ydtkt.com/';
            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
            $data = array();
            $data['src'] = $result;
            $data['title'] = '试题答案';
            if($result){
                ajaReturn($data,0,'成功');
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }

    //试卷提交
    public function questionSubmit(){
        $param = input();
        $data = json_decode($param['data'],true);
        $id = isset($data['id']) ? $data['id'] : '';
        if(!empty($id)){
            //普通试题
            $questionList = Db::name('test_paper')
                            ->alias('tp')
                            ->field('tp.score,tp.title,q.*,qt.questype,tp.sort')
                            ->join('guard_question q','tp.qid = q.q_id')
                            ->join('guard_questype qt','qt.quest_id = q.q_type')
                            ->where("tp.pid = ".$data['id']." and tp.q_type < 7")
                            ->order('tp.sort')
                            ->group('q.q_id')
                            ->select();
            // var_dump($questionList);die;
            //普通试题检测
            unset($data['id']);
            unset($data['file']);
            $questionLog = array();
            $i = 0;
            $testScore = 0;     //试题分数

            foreach ($questionList as $k => $val) {
                //根据题目id 获取大题记录
                $answerLog = Db::name('question_log')->where(['q_id'=>$val['q_id'],'person_id'=>$this ->userinfo['id']])->order('num desc')->select();
                $num = $answerLog ? $answerLog['0']['num'] : 1;
                $num  = $num+1;
                $error_num = $answerLog ? $answerLog['0']['error_num'] : 0;

                if(!empty($answerLog)){     //更新大题次数
                    Db::name('question_log')->where(['q_id'=>$val['q_id'],'person_id'=>$this ->userinfo['id']])->update(['num'=>$num]);
                }
                //答题记录
                $questionLog[$i]['person_id'] = $this ->userinfo['id'];
                $questionLog[$i]['t_id'] = $id;
                $questionLog[$i]['q_id'] = $val['q_id'];
                $questionLog[$i]['status'] = 0;       //默认未作答
                $questionLog[$i]['status_log'] = 1;       //默认未作答
                $questionLog[$i]['intime'] = time();      
                $questionLog[$i]['num'] = $num;   
                $questionLog[$i]['error_num'] = $error_num;     
                $questionLog[$i]['type'] = $val['q_type'];  
                $questionLog[$i]['review'] = 2;
                $answers = '';     //试题答案
                foreach ($data as $key => $value) {
                    //截取$key
                    $keyArr = [];
                    $keyArr = explode('_',$key);
                    if($keyArr[1] == 1 && $val['q_type'] == 1){   //检测选择题
                        
                        if($val['q_id'] == $keyArr[2]){
                            //开始检测
                            if(strstr($value,':'))
                            {
                                $answer = explode(':',$value);   //处理传过来的数据
                            }elseif(strstr($value,'：')){
                                $answer = explode('：', $value);   //处理传过来的数据
                            }elseif(strstr($value,'.')){
                                $answer = explode('.', $value);   //处理传过来的数据
                            }elseif(strstr($value,'。')){
                                $answer = explode('。', $value);   //处理传过来的数据
                            }elseif(strstr($value,',')){
                                $answer = explode(',', $value);   //处理传过来的数据
                            }elseif(strstr($value,'，')){
                                $answer = explode('，', $value);   //处理传过来的数据
                            }
                            $answers = strtoupper($answer['0']);   //试题答案
                        }
                    }elseif($val['q_type'] == 2 && $keyArr[1] == 2){        //检测多选题
                        if($val['q_id'] == $keyArr[2]){
                            //开始检测
                            if(strstr($value,':'))
                            {
                                $answer = explode(':',$value);   //处理传过来的数据
                            }elseif(strstr($value,'：')){
                                $answer = explode('：', $value);   //处理传过来的数据
                            }elseif(strstr($value,'.')){
                                $answer = explode('.', $value);   //处理传过来的数据
                            }elseif(strstr($value,'。')){
                                $answer = explode('。', $value);   //处理传过来的数据
                            }elseif(strstr($value,',')){
                                $answer = explode(',', $value);   //处理传过来的数据
                            }elseif(strstr($value,'，')){
                                $answer = explode('，', $value);   //处理传过来的数据
                            }
                            // $questionLog[$i]['answer'] = $answers;
                            // //获取多选题答案
                            $answers .= strtoupper($answer[0]);   //试题答案
                        }
                    }elseif($val['q_type'] == 3 && $keyArr[1] == 3){     //判断题
                        if($val['q_id'] == $keyArr[2]){
                            //开始检测
                            if($value == '否'){
                                $answers = 'B';
                            }else if($value == '是'){
                                $answers = 'A';
                            }
                        }
                    }elseif($val['q_type'] == 5 && $keyArr[1] == 5){    //填空
                        if($val['q_id'] == $keyArr[2]){
                            //答案处理
                            $answers .= ','.$value;
                            $answers = trim($answers,',');
                        }
                    }elseif($val['q_type'] == 6 && $keyArr[1] == 6){    //问答题
                        //答案处理
                        $answers = $value;
                    }
                }
                $questionLog[$i]['answer'] = $answers;
                if($val['q_type'] == 1 || $val['q_type'] == 2 || $val['q_type'] == 3){       //自动检测只检测判断题和选择、多选
                    if(!empty($answers)){
                        if($answers == strtoupper($val['q_answer'])){
                            //答对了
                            $questionLog[$i]['status'] = 1;
                            $testScore = $testScore + $val['score'];
                        }else{
                            //答错了
                            $questionLog[$i]['status'] = 2;
                            $questionLog[$i]['error_num'] = $error_num + 1;
                            $questionLog[$i]['status_log'] = 2;       //默认未作答

                            //更新大题打错次数
                            if(!empty($answerLog)){
                                Db::name('question_log')->where(['q_id'=>$val['q_id'],'person_id'=>$this ->userinfo['id']])->update(['error_num'=>$error_num + 1]);
                            }
                        }
                    }
                }else{
                    if(!empty($answers)){
                        $questionLog[$i]['status'] = 3;       //作答但为检测
                    }
                }
                $i++;
            }
            //获取题帽题
            $questionrowsList = Db::name('test_paper')
                                ->alias('tp')
                                ->join('guard_questionrows q','q.qr_id = tp.qid')
                                ->join('guard_question qt','qt.q_parent = q.qr_id','RIGHT')
                                ->where('tp.pid = '.$id.' and tp.q_type >= 7')
                                ->field('q.qr_question,q.qr_type,q.qr_id,qt.*,tp.score')
                                ->group('qt.q_id')
                                ->select();
            // var_dump($questionrowsList);die;
            //题帽题处理
            foreach ($questionrowsList as $k => $val) {
                //根据题目id 获取大题记录
                $answerLog = Db::name('question_log')->where(['q_id'=>$val['q_id'],'person_id'=>$this ->userinfo['id']])->select();
                $num = $answerLog ? $answerLog['0']['num']: 1;
                $num  = $num+1;
                $error_num = $answerLog ? $answerLog['0']['error_num'] : 0;

                if(!empty($answerLog)){     //更新大题次数
                    Db::name('question_log')->where(['q_id'=>$val['q_id'],'person_id'=>$this ->userinfo['id']])->update(['num'=>$num]);
                }
                //答题记录
                $questionLog[$i]['person_id'] = $this ->userinfo['id'];
                $questionLog[$i]['t_id'] = $id;
                $questionLog[$i]['q_id'] = $val['q_id'];
                $questionLog[$i]['status'] = 0;       //默认未作答
                $questionLog[$i]['status_log'] = 1;       //默认未作答
                $questionLog[$i]['intime'] = time();      
                $questionLog[$i]['num'] = $num;   
                $questionLog[$i]['error_num'] = $error_num;     
                $questionLog[$i]['type'] = $val['qr_type'];
                $questionLog[$i]['review'] = 2;
                $answers = '';     //试题答案
                foreach ($data as $key => $value) {
                    //截取$key
                    $keyArr = [];
                    $keyArr = explode('_',$key);
                    if($keyArr[1] == 7 && $val['qr_type'] == 7){   //检测完型填空
                        if($keyArr[2] == $val['qr_id']){
                            if($val['q_id'] == $keyArr[3]){
                                //开始检测
                                if(strstr($value,':'))
                                {
                                    $answer = explode(':',$value);   //处理传过来的数据
                                }elseif(strstr($value,'：')){
                                    $answer = explode('：', $value);   //处理传过来的数据
                                }elseif(strstr($value,'.')){
                                    $answer = explode('.', $value);   //处理传过来的数据
                                }elseif(strstr($value,'。')){
                                    $answer = explode('。', $value);   //处理传过来的数据
                                }elseif(strstr($value,',')){
                                    $answer = explode(',', $value);   //处理传过来的数据
                                }elseif(strstr($value,'，')){
                                    $answer = explode('，', $value);   //处理传过来的数据
                                }
                                $answers = strtoupper($answer['0']);   //试题答案
                            }
                        }
                    }elseif($keyArr[1] == 8 && $val['qr_type'] == 8){    //语文阅读理解
                        if($keyArr[2] == $val['qr_id']){
                            if($val['q_id'] == $keyArr[3]){
                                //答案处理
                                $answers = $value;
                            }
                        }
                    }elseif($keyArr[1] == 9 && $val['qr_type'] == 9){    //英语阅读理解
                        if($keyArr[2] == $val['qr_id']){
                            if($val['q_id'] == $keyArr[3]){
                                //开始检测
                                if(strstr($value,':'))
                                {
                                    $answer = explode(':',$value);   //处理传过来的数据
                                }elseif(strstr($value,'：')){
                                    $answer = explode('：', $value);   //处理传过来的数据
                                }elseif(strstr($value,'.')){
                                    $answer = explode('.', $value);   //处理传过来的数据
                                }elseif(strstr($value,'。')){
                                    $answer = explode('。', $value);   //处理传过来的数据
                                }elseif(strstr($value,',')){
                                    $answer = explode(',', $value);   //处理传过来的数据
                                }elseif(strstr($value,'，')){
                                    $answer = explode('，', $value);   //处理传过来的数据
                                }
                                $answers = strtoupper($answer['0']);   //试题答案
                            }
                        }
                    }
                }
                $questionLog[$i]['answer'] = $answers;
                if($val['qr_type'] == 7 || $val['qr_type'] == 8){       //自动检测只检测英语完型填空和英语阅读理解
                    if(!empty($answers)){
                        if($answers == strtoupper($val['q_answer'])){
                            //答对了
                            $questionLog[$i]['status'] = 1;
                            $testScore = $testScore + $val['score'];
                        }else{
                            //答错了
                            $questionLog[$i]['status'] = 2;
                            $questionLog[$i]['error_num'] = $error_num + 1;
                            $questionLog[$i]['status_log'] = 2;       //默认未作答
                            //更新大题打错次数
                            if(!empty($answerLog)){
                                Db::name('question_log')->where(['q_id'=>$val['q_id'],'person_id'=>$this ->userinfo['id']])->update(['error_num'=>$error_num + 1]);
                            }
                        }
                    }
                }else{
                    if(!empty($answers)){
                        $questionLog[$i]['status'] = 3;       //作答但为检测
                    }
                }
                $i++;
            }
        }
        for ($k=0; $k <count($questionLog); $k++) { 
            $questionLog[$k]['fraction'] = $testScore;
        }
        //添加做题记录
        $res = Db::name('question_log')->insertAll($questionLog);
        if($res){
            jsonMsg('提交成功',1);
        }else{
            jsonMsg('提交失败',0);
        }
    }
    //错题本列表信息
    public function wrongQuestion(){
        $param = input();
        $userInfo =  $this ->_info;
        $where = [];

        $pagesize=3;
        if($param['pagenow']>1){
            $pagestart=($param['pagenow']-1)*$pagesize;
        }else{
            $pagestart=0;
        }

        //获取错题本列表(错题列表只获取单选、多选和判断)
        $wrongQuestionList = Db::name('question_log')
                             ->field('q.*')
                             ->alias('ql')
                             ->join('guard_question q','q.q_id = ql.q_id')
                             ->where('q.q_gradeid ='.$param['gid'].' and q.q_seme='.$param['semester'].' and q.q_subjectid='.$param['subject_id'].'
                                 and status = 2 and person_id ='.$userInfo['id'].' and  q.q_type < 4')
                             ->order('intime desc,q.q_type')
                             ->limit($pagestart,$pagesize)
                             ->select();
        $count = Db::name('question_log')
                             ->field('q.*')
                             ->alias('ql')
                             ->join('guard_question q','q.q_id = ql.q_id')
                             ->where('q.q_gradeid ='.$param['gid'].' and q.q_seme='.$param['semester'].' and q.q_subjectid='.$param['subject_id'].'
                                 and status = 2 and person_id ='.$userInfo['id'].' and  q.q_type < 4')
                             ->order('intime desc,q.q_type')
                             ->count();

        $page=wrongQuestionFpage($param['pagenow'],$count,$pagesize,$param['gid'],$param['semester'],$param['subject_id']);
        if(!empty($wrongQuestionList)){
            return jsonMsg($page,1,$wrongQuestionList);
        }else{
            return jsonMsg('暂无数据',0);
        }

    }

    //错题本做题列表
    public function doOneQuestion(){
        $param = input();
        $userInfo =  $this ->_info;

        //获取错题本列表(错题列表只获取单选、多选和判断)
        $wrongQuestionList = Db::name('question_log')
                             ->field('q.*')
                             ->alias('ql')
                             ->join('guard_question q','q.q_id = ql.q_id')
                             ->where('q.q_gradeid ='.$param['gid'].' and q.q_seme='.$param['semester'].' and q.q_subjectid='.$param['subject_id'].'
                                 and status = 2 and person_id ='.$userInfo['id'].' and  q.q_type < 4')
                             ->order('intime desc,q.q_type')
                             ->select();
        $count = Db::name('question_log')
                             ->field('q.*')
                             ->alias('ql')
                             ->join('guard_question q','q.q_id = ql.q_id')
                             ->where('q.q_gradeid ='.$param['gid'].' and q.q_seme='.$param['semester'].' and q.q_subjectid='.$param['subject_id'].'
                                 and status = 2 and person_id ='.$userInfo['id'].' and  q.q_type < 4')
                             ->order('intime desc,q.q_type')
                             ->count();
                            
        $questionInfo = array();
        foreach ($wrongQuestionList as $key => $value) {
            if($value['q_id'] == $param['id']){
                $questionKeyId = $key; 
            }
        }
        //习题信息
        $questionInfo = $wrongQuestionList[$questionKeyId];
        $nextKeyId = $questionKeyId + 1;     //下一题
        // $lastId = $questionKeyId - 1;     //上一题

        if($nextKeyId <=  $count-1){
            $nextId = $wrongQuestionList[$questionKeyId+1]['q_id'];
        }else{
            $nextId = '';
        }
        // if($lastId >= 0){
        //     $lastId = $wrongQuestionList[$questionKeyId-1]['q_id'];
        // }

        if(!empty($questionInfo)){
            return jsonMsg('success',1,$questionInfo,$nextId);         
        }
    }
    //错题本提交
    public function errorQuestionSubmit(){
        $param = input();
        $data = json_decode($param['data'],true);
        $id = isset($data['q_id']) ? $data['q_id'] : '';
        //普通试题
        $questionList = Db::name('question')
                        ->field('*')
                        ->where("q_id = ".$data['q_id'])
                        ->find();
        //根据题目id 获取大题记录
        $answerLog = Db::name('question_log')->where(['q_id'=>$data['q_id'],'person_id'=>$this ->userinfo['id']])->select();
        $num = $answerLog ? $answerLog['0']['num'] : 1;
        $num  = $num+1;
        $error_num = $answerLog ? $answerLog['0']['error_num'] : 0;

        if(!empty($answerLog)){     //更新大题次数
            Db::name('question_log')->where(['q_id'=>$data['q_id'],'person_id'=>$this ->userinfo['id']])->update(['num'=>$num]);
        }
        $answers = '';     //试题答案
        $questionLog = array();
        unset($data['q_id']);
        foreach ($data as $key => $value) {
            //截取$key
            $keyArr = [];
            $keyArr = explode('_',$key);
            if($keyArr[1] == 1 && $questionList['q_type'] == 1){   //检测选择题
                if($questionList['q_id'] == $keyArr[2]){
                    //开始检测
                    if(strstr($value,':'))
                    {
                        $answer = explode(':',$value);   //处理传过来的数据
                    }elseif(strstr($value,'：')){
                        $answer = explode('：', $value);   //处理传过来的数据
                    }elseif(strstr($value,'.')){
                        $answer = explode('.', $value);   //处理传过来的数据
                    }elseif(strstr($value,'。')){
                        $answer = explode('。', $value);   //处理传过来的数据
                    }elseif(strstr($value,',')){
                        $answer = explode(',', $value);   //处理传过来的数据
                    }elseif(strstr($value,'，')){
                        $answer = explode('，', $value);   //处理传过来的数据
                    }
                    $answers = strtoupper($answer['0']);   //试题答案
                }
            }elseif($questionList['q_type'] == 2 && $keyArr[1] == 2){       //检测多选题
                if($questionList['q_id'] == $keyArr[2]){
                    //开始检测
                    if(strstr($value,':'))
                    {
                        $answer = explode(':',$value);   //处理传过来的数据
                    }elseif(strstr($value,'：')){
                        $answer = explode('：', $value);   //处理传过来的数据
                    }elseif(strstr($value,'.')){
                        $answer = explode('.', $value);   //处理传过来的数据
                    }elseif(strstr($value,'。')){
                        $answer = explode('。', $value);   //处理传过来的数据
                    }elseif(strstr($value,',')){
                        $answer = explode(',', $value);   //处理传过来的数据
                    }elseif(strstr($value,'，')){
                        $answer = explode('，', $value);   //处理传过来的数据
                    }
                    // //获取多选题答案
                    $answers .= strtoupper($answer[0]);   //试题答案
                }
            }elseif($questionList['q_type'] == 3 && $keyArr[1] == 3){     //判断题
                if($questionList['q_id'] == $keyArr[2]){
                    //开始检测
                    if($value == '否'){
                        $answers = 'B';
                    }else if($value == '是'){
                        $answers = 'A';
                    }
                }
            }
        }
        if(!empty($answers)){
            if($answers == strtoupper($questionList['q_answer'])){
                //答对了
                $questionLog['status_log'] = 1;
                //更新做题记录
                Db::name('question_log')->where(['q_id'=>$id,'person_id'=>$this ->userinfo['id']])->update($questionLog);
            }else{
                //答错了
                $questionLog['status_log'] = 2;
                //更新大题打错次数
                if(!empty($answerLog)){
                    Db::name('question_log')->where(['q_id'=>$id,'person_id'=>$this ->userinfo['id']])->update(['error_num'=>$error_num + 1]);
                }
            }
        } 
        if($questionList['q_type'] == 3){
            if($questionList['q_answer'] == 'A'){
                $trueQuestion = '是';
            }else if($questionList['q_answer'] == 'B'){
                $trueQuestion = '否';
            }
        }else{
            $trueQuestion = $questionList['q_answer'];
        }    
        if($answers){
            if($questionLog['status_log'] == 1){
                jsonMsg('恭喜你答对了',1);
            }elseif($questionLog['status_log'] == 2){
                jsonMsg('很遗憾，您答错了,正确答案:'.$trueQuestion,2);
            }
        }else{
            jsonMsg('提交失败',0);
        }
    }

    //学习周报
    public function studyWeeklyResport(){
        $param = input();
        $person_id = $this ->userinfo['id'];

        $data_arr = getWeek_SdateAndEdate(time());   //获取当前周的开始和结束时间

        $timeStart = !empty($param['timeStart']) ? strtotime($param['timeStart']) : $data_arr['sdate'];   //开始时间

        $timeEnd = !empty($param['timeEnd']) ? strtotime($param['timeEnd']) : $data_arr['edate'];   //开始时间

        //学生最高分 最低分 平均分 学习总时长
        $data = Db::name('person')
                        ->alias('p')
                        ->field('(select sum(all_watch_time) from guard_video_watch_log where p.id = person_id and time BETWEEN '.$timeStart.' AND '.$timeEnd.') as studyTime,min(fraction) as minFraction,max(fraction) as maxFraction,avg(fraction) as avgFraction,p.nickName,p.phone,p.id as person_id,s.id as advise_id')
                        ->join('question_log ql','p.id = ql.person_id','LEFT')
                        ->join('study_advise s','p.id = s.person_id','LEFT')
                        ->whereOr(('`ql`.`intime` BETWEEN '.$timeStart.' AND '.$timeEnd.'  OR `s`.`weekly_report_time` BETWEEN '.$timeStart.' AND '.$timeEnd))
                        ->where(['p.id'=>$person_id])
                        ->group('p.id')
                        ->order('addtime desc')
                        ->find();
        //学生观看时长折线图数据
        $subject_id = isset($param['subject_id']) ? $where['subject_id'] = $param['subject_id'] : $where['subject_id'] = 1;

        $watchVideoList = Db::name('video_watch_log')
                            ->field('sum(all_watch_time) as studyTime,from_unixtime(time,"%w") as time')
                            ->where('time BETWEEN '.$timeStart.' AND '.$timeEnd.' and person_id='.$person_id)
                            ->where($where)
                            ->group('from_unixtime(time,"%w")')
                            ->select();

        //学生测试平均分折线图数据
        $testList = Db::name('question_log')
                            ->alias('ql')
                            ->field('avg(fraction) as fraction,from_unixtime(intime,"%w") as time')
                            ->join('guard_test_paper t','t.id = ql.t_id')
                            ->where('intime BETWEEN '.$timeStart.' AND '.$timeEnd.' and person_id='.$person_id)
                            ->where($where)
                            ->group('from_unixtime(intime,"%w")')
                            ->select();
        $myChart = array();
        $testDataList = array();
        for($i=0; $i < 8; $i++){
            $myChart[$i] = 0;
            foreach ($watchVideoList as $key => $value) {
                if((int)$value['time'] - 1 == $i){
                    $myChart[$i] = (int)ceil($value['studyTime']/60);
                }
            }
        }
        for($i=0; $i < 8; $i++){
            $testDataList[$i] = 0;
            foreach ($testList as $key => $value) {
                if((int)$value['time'] - 1 == $i){
                    $testDataList[$i] = (int)ceil($value['fraction']);
                }
            }
        }
        //获取该用户的信息
        $personInfo = Db::name('person')->where(['id'=>$person_id])->find();
        //获取该学生分数段  分数段:1：0-60 不及格 2：60-70及格3：70-90良好   4：90-100-优秀  5：本周没有考试
        if(isset($data['avgFraction'])){
            if((int)$data['avgFraction'] == 0){
                $score_sole = 5;
            }else if((int)$data['avgFraction'] > 0 && (int)$data['avgFraction']< 60){
                $score_sole = 1;
            }else if((int)$data['avgFraction'] >= 60 && (int)$data['avgFraction']< 70){
                $score_sole = 2;
            }else if((int)$data['avgFraction'] >= 70 && (int)$data['avgFraction']< 90){
                $score_sole = 3;
            }else if((int)$data['avgFraction'] >= 90 && (int)$data['avgFraction'] <= 100){
                $score_sole = 4;
            }else{
                $score_sole = 5;
            } 
        }else{
            $score_sole = 5;
        }
                  
        //获取该学生的学习时间段   学习时间段:0-1小时:1   1-3小时 : 2   三小时以上：3  本周没有学习： 4
        if(isset($data['studyTime'])){
            $studyTime = ceil($data['studyTime'] / 7 / 60);
            if($studyTime == 0){
                $time_sole = 4;
            }else if($studyTime> 0 && $studyTime < 60){  //1小时
                $time_sole = 1;
            }else if($studyTime >= 60 && $studyTime < 180){  // 1-3小时
                $time_sole = 2;
            }else if($studyTime >= 180){
                $time_sole = 3;
            }
        }else{
            $time_sole = 4;
        }
        
        //学生默认建议
        $defaultAdvise = Db::name('study_advise')
                            ->where(['grade_id'=>$personInfo['grade_id'],'type'=>1,'time_sole'=>$time_sole,'score_sole'=>$score_sole])
                            ->find();

        //获取老师建议
        $teacherAdvise = Db::name('study_advise')
                            ->where(['grade_id'=>$personInfo['grade_id'],'type'=>2,'person_id'=>$person_id,'weekly_report_time'=>array('between',''.$timeStart.','.$timeEnd.'')])
                            ->find();
        $datalist = array();

        $datalist['myChart'] = $myChart;     //观看时长折线图数据
        $datalist['testDataList'] = $testDataList;   //测试分数折线图数据
        $datalist['data'] = $data;  //学生最高分 最低分 平均分 学习总时长
        if(!empty($teacherAdvise)){
            $datalist['teacherAdvise'] = $teacherAdvise;  //学生建议
        }else{
            $datalist['teacherAdvise'] = $defaultAdvise;  //学生建议
        }
        return jsonMsg('success',1,$datalist);
    }

}