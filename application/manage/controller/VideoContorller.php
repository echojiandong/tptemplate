<?php
/**
 * Created by PhpStorm.
 * User: 73938
 * Date: 2018/4/5
 * Time: 21:48
 */
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\image;
use think\Session;
use app\manage\model\Video;
use app\manage\model\Teacher;
use app\manage\model\QiniuModel;
class VideoContorller extends author{
    /*
     * 添加课程
     * */
     public function videoAdd(Request $request){
        $param=$request->param();
        $info=new teacher();
        //获取学段
        $getLearn=$info->getlearn();
        $this->assign('getLearn',$getLearn);
        //获取年级
        $getgrade=$info->getgrade();
        $this->assign('getgrade',$getgrade);
        //获取科目
        $getSubject=$info->getsubject();
        $this->assign('getSubject',$getSubject);
        //获取教材版本
        $res=Db::name('textbook')->select();
        $this->assign('textBook_res',$res);

        //获取教师
        $teacher=Db::name('teacher')->select();
        $this->assign('teacher',$teacher);

        $this->assign('id',$param['id']);
        return $this->fetch("/video/videoAdd");

    }

    // 课程添加 
    public function addVideo(Request $request){
        $param = $request->param();

            // 判断学段
            if($param['grade_id'] <= 6){
                $grade_name = "小学";
            }else if($param['grade_id'] >= 6 || $param['grade_id'] <= 9){
                $grade_name = "初中";
            }else{
                $grade_name = "高中";
            }

            //判断学科
            if($param['subject_id'] == 1){
                $sub_name = "语文";
            }else if($param['subject_id'] == 2){
                $sub_name = "数学";
            }else if($param['subject_id'] == 3){
                $sub_name = "英语";
            }else if($param['subject_id'] == 4){
                $sub_name = "物理";
            }else if($param['subject_id'] == 5){
                $sub_name = "化学";
            }else if($param['subject_id'] == 6){
                $sub_name = "政治";
            }else if($param['subject_id'] == 7){
                $sub_name = "历史";
            }else if($param['subject_id'] == 8){
                $sub_name = "地理";
            }else if($param['subject_id'] == 9){
                $sub_name = "生物";
            }else{
                $sub_name = "专项";
            }

            $key = $grade_name .= $sub_name;    //拼接字段获取pid
            $pid = Db::name('menu')->where('title', 'like' , "%{$key}%")->order('id desc')->select();//模糊查询获取ID

            $data['name'] = $param['vname'];//课程名称
            $data['title'] = $param['titleName'];//课程副标题
            $data['learn_id'] = $param['learn_id'];//学段
            $data['grade_id']=$param['grade_id'];//所属年级
            $data['subject_id']=$param['subject_id'];//所属科目
            $data['edition_id'] = $param['textbook_id'];//教材版本
            $data['Semester'] = $param['Semester'];//1上学期2下学期
            $teacher_name=explode(',',$param['teacherId']);
            $data['teacherId'] = $teacher_name[0];//老师的ID
            $data['sname'] = $teacher_name[1];//老师的名字
            $data['price'] = $param['price'];//价格
            $data['Discount'] = $param['Discount'];//折扣
            $data['audi'] = $param['audi'];//是否可以试听 【1. 没试听 2.有试听】
            $data['img'] = $param['image'];//课程的主图
            $data['imgNo'] = $param['imageNo'];//课程的主图
            $data['popularity'] = rand(000,100);//人气点赞人数
            if(isset($param['editor'])){
                $data['content'] = $param['editor'];//课程简介
            }else{
                $data['content'] ='';
            }
            $data['time'] = time();//添加的时间

            // $data['pid'] = $param['vpid'];//父id
            $data['pid'] = $pid[0]['id'];//父id

        $video= new video();
        $res=$video->videoAdd($data);    
        if ($res) {
            jsonMsg("success", 0, $res);
        } else {
            jsonMsg("添加失败", 1);
        }
    }
    /*
    * 审核课程
    * id 课程id
    * check_id 审核人id
    * statue 审核状态 2通过，3拒绝
    * */
    public function saveVideoStatue(Request $request){
        if($request->isAjax()) {
            $param=$request->param();
            $where['statue']=$param['val'];
            $where['id']=$param['id'];
            $where['check_id']=$_SESSION['think']['manageinfo']['uid'];
            $data =  Video::saveVideoStatue($where);
            if($data){jsonMsg("success",0,$data);}else{jsonMsg("修改失败",1);}
        }else{
            jsonMsg("非法提交",1);
        }
    }

    // 课程列表
    public function Videolist(Request $request)
    {
        $param=$request->param();
        // var_dump($param['type']);
        $res = Db::name('video_class')->where('pid' , $param['type'])->select();
        $this->assign('id',$param['type']);
        $this->assign('res',$res);
        return view('/video/videolist',['title'=>"课程列表"]);
    }

    public function getVideo(Request $request){
        $param=$request->param();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where['v.name']=['like',"%".$param['keyword']."%"];
        }
        $where['v.pid']=$param['id'];
        $page=$param['page'];
        $limit=$param['limit'];
        $data =  Video::getVideolist($where,$page,$limit);
        $count= Video::getVideolist($where);
        foreach($data as $k=>$v){
            $data[$k]['time']=date('Y-m-d H-s-m',$v['time']);
            $data[$k]['DiscountPrice']=$v['Discount']*$v['price'];
            if($v['Semester']==1){
                $data[$k]['Semester']='上学期';
            }else{
                $data[$k]['Semester']='下学期';
            }
        }
        if($data){jsonMsg2("success",0,$data,$count);}else{jsonMsg("暂无数据",1);}
   }

    public function getOneVideo(Request $request){
        $param=$request->param();
        $data =  Video::getOneVideo($param['id']);
        if($data['Semester']==1){
            $data['Semester']='上学期';
        }else{
            $data['Semester']='下学期';
        }
        $this->assign("data",$data);
        return $this->fetch("/video/videoshow");
    }

    // 更新课程内容
    public function updateVideo(Request $request){
        $param=$request->param();
        $info=new teacher();
        //获取学段
        $getLearn=$info->getlearn();
        $this->assign('getLearn',$getLearn);
        //获取年级
        $getgrade=$info->getgrade();
        $this->assign('getgrade',$getgrade);
        //获取科目
        $getSubject=$info->getsubject();
        $this->assign('getSubject',$getSubject);
        //获取教材版本
        $res=Db::name('textbook')->select();
        $this->assign('textBook_res',$res);

        //获取教师
        $teacher=Db::name('teacher')->select();
        $this->assign('teacher',$teacher);

        $data =  Video::getOneUpdateVideo($param['id']);
        $this->assign("data",$data);
        $this->assign("id",$param['id']);
        return $this->fetch("/video/videoUpdate");
    }

    // 更新课程操作
    public function video_update_handle(Request $request){
        $param = $request->param();

        // 判断学段
        if($param['grade_id'] <= 6){
            $grade_name = "小学";
        }else if($param['grade_id'] >= 6 || $param['grade_id'] <= 9){
            $grade_name = "初中";
        }else{
            $grade_name = "高中";
        }

        //判断学科
        if($param['subject_id'] == 1){
            $sub_name = "语文";
        }else if($param['subject_id'] == 2){
            $sub_name = "数学";
        }else if($param['subject_id'] == 3){
            $sub_name = "英语";
        }else if($param['subject_id'] == 4){
            $sub_name = "物理";
        }else if($param['subject_id'] == 5){
            $sub_name = "化学";
        }else if($param['subject_id'] == 6){
            $sub_name = "政治";
        }else if($param['subject_id'] == 7){
            $sub_name = "历史";
        }else if($param['subject_id'] == 8){
            $sub_name = "地理";
        }else if($param['subject_id'] == 9){
            $sub_name = "生物";
        }else{
            $sub_name = "专项";
        }

        $key = $grade_name .= $sub_name;    //拼接字段获取pid
        $pid = Db::name('menu')->where('title', 'like' , "%{$key}%")->order('id desc')->select();//模糊查询获取ID

        $data['name'] = $param['vname'];//课程名称
        $data['title'] = $param['titleName'];//课程副标题
        $data['learn_id'] = $param['learn_id'];//学段
        $data['grade_id']=$param['grade_id'];//所属年级
        $data['subject_id']=$param['subject_id'];//所属科目
        $data['edition_id'] = $param['edition_id'];//教材版本
        $data['Semester'] = $param['Semester'];//1上学期2下学期
        $teacher_name=explode(',',$param['teacherId']);
        $data['teacherId'] = $teacher_name[0];//老师的ID
        $data['sname'] = $teacher_name[0];//老师的名字
        $data['price'] = $param['price'];//价格
        $data['Discount'] = $param['Discount'];//折扣
        $data['audi'] = $param['audi'];//是否可以试听 【1. 没试听 2.有试听】
        $data['img'] = $param['image'];//课程的主图
        $data['imgNo'] = $param['imageNo'];//课程的主图
        $data['popularity'] = rand(000,100);//人气点赞人数
        if(isset($param['editor'])){
            $data['content'] = $param['editor'];//课程简介
        }else{
            $data['content'] ='';
        }
        $data['uptime'] = time();//更新时间
        $data['pid'] = $pid[0]['id'];//pid

        $manageinfo=Session::get('manageinfo');
        $data['admin_id'] = $manageinfo['uid'];//记录更新人的id

        $where['id'] = $param['id'];//被修改的id
        //删除七牛课程主图
        $imgPath = Db::name('video_class')->where('id='.intval($param['id']))->select();
        $qiniu = new QiniuModel();
        if(!empty($imgPath[0]['img']) && $imgPath[0]['img'] != $param['image']){
            $remarkVideoPath = str_replace("http://ydtvlitpic.ydtkt.com/","",$imgPath[0]['img']);
            $qiniuSpace = 'ydtvlitpic';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
        $video=new video();
        $rs=$video->updateVido($data,$where);
        if($rs){jsonMsg("修改成功",0);}else{jsonMsg("修改失败",1);}
    }
    //上传课程图片 空间名：ydtvlitpic
    public function upload(){
        $file = request()->file('file');
        $info = $file->getinfo();
        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            //获取上传凭证
            $qiniuSpace = 'ydtvlitpic';
            $uploadToken=$qiniu->getQnToken($qiniuSpace);
            $filePath=$info['tmp_name'];
            $fileName=$qiniu->getNewfilename('index',$info['name']);
            //上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://ydtvlitpic.ydtkt.com/';
            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
            if($result){
                jsonMsg("成功",0,$result);
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
    // 删除视频
    public function delvideo(Request $request)
    {
        $param=$request->param();
        $where['id']=$param['id'];
        $video=new video();
        $res=$video->delVideo($where);
        if($res){jsonMsg("删除成功",0);}else{jsonMsg("删除失败",1);}
    }
}