<?php
/**
 * @马桂婵 
 * Date: 2019/3/10
 */
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\image;
use think\Session;
use app\manage\model\Review;
use app\manage\model\Teacher;
use app\manage\model\QiniuModel;
class ReviewContorller extends author{

    
    /*
    * 添加课程
    *@马桂婵   2019/3/14
    */
    // public function reviewAdd(Request $request){
    //     $param=$request->param();
    //     $info=new Review();
    //     //获取d对应章节
    //     $getvideo=$info->getvideo();
    //     $this->assign('getvideo',$getvideo);
    //     //获取对应课程
    //     $getvideo_class=$info->getvideo_class();
    //     $this->assign('getvideo_class',$getvideo_class);

    //     return $this->fetch("/review/reviewAdd");

    // }
   //  public function addReview(Request $request){
   //      $param = $request->param();
   //      $data['outline'] = $param['outline'];
   //      $data['testclass'] = $param['testclass'];
   //      $data['link'] = $param['link'];
   //      $data['classhour'] = $param['classhour'];
   //      $data['pid'] = $param['pid'];
   //      $data['kid'] = $param['kid'];
   //      $data['audi'] = $param['audi'];
   //      $data['img'] = $param['image'];//课程的主图s
   //      $data['likes'] = rand(000,100);//人气点赞人数
   //      $data['time'] = time();//添加的时间
   //   //   $video= new Review();
   // //     $res=$video->videoAdd($param);
   //      $res =  Db::name('video')->insertGetId($data);
   //      if ($res) {
   //          jsonMsg("success", 0, $res);
   //      } else {
   //          jsonMsg("添加失败", 1);
   //      }
   //  }
    //上传 图片 空间名：ydtvlitpic,域名：http://ydtvlitpic.ydtkt.com/
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

    //上传视频 空间名：ydtvideo1080,域名：http://ydtvideo1080.ydtkt.com/
    public function uploadvideo_1080(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file');
        $info = $file ->getinfo();
        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            // //获取上传凭证
            $qiniuSpace = 'ydtvideo1080';
            $uploadToken=$qiniu->getQnToken($qiniuSpace);
            $filePath=$info['tmp_name'];
            // $fileName=$qiniu->getNewfilename('index',$info['name']);
            //视频上传不改变 原名字
            $fileName = $info['name'];
            // 上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://ydtvideo1080.ydtkt.com/';
            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
            if($result){
                jsonMsg("成功",0,$result);
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
    //上传视频 空间名：ydtvideo720,域名：http://ydtvideo720.ydtkt.com/
    public function uploadvideo_720(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file');
        $info = $file ->getinfo();
        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            // //获取上传凭证
            $qiniuSpace = 'ydtvideo720';
            $uploadToken=$qiniu->getQnToken($qiniuSpace);
            $filePath=$info['tmp_name'];
            // $fileName=$qiniu->getNewfilename('index',$info['name']);
            //视频上传不改变 原名字
            $fileName = $info['name'];
            // 上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://ydtvideo720.ydtkt.com/';
            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
            if($result){
                jsonMsg("成功",0,$result);
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
    //上传视频 空间名：ydtvideo480,域名：http://ydtvideo480.ydtkt.com/
    public function uploadvideo_480(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file');
        $info = $file ->getinfo();
        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            // //获取上传凭证
            $qiniuSpace = 'ydtvideo480';
            $uploadToken=$qiniu->getQnToken($qiniuSpace);
            $filePath=$info['tmp_name'];
            // $fileName=$qiniu->getNewfilename('index',$info['name']);
            //视频上传不改变 原名字
            $fileName = $info['name'];
            // 上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://ydtvideo480.ydtkt.com/';
            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
            if($result){
                jsonMsg("成功",0,$result);
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
    /*
       * 章节管理列表
       * */
      public function getReviewlist(Request $request)
      {
          
          $param=$request->param();
          $this->assign('id',$param['id']);
          return view('/review/reviewList',['title'=>"章节列表"]);
      }
  
  /**
   * 章
   */
  public function getReview(Request $request){
    $param=$request->param();
    
    if(array_key_exists('keyword',$param) && $param['keyword']!=''){
        $where['v.testclass']=['like',"%".$param['keyword']."%"];
    }else{
        $where['v.kid']=$param['pid'];
    }
    // $where['v.pid'] = 0;
    $page=$param['page'];
    $limit=$param['limit'];

    $info=new Review();

    $data =  $info->getReviewlist($where,$page,$limit);
    $count= $info->getReviewlist($where);
    //获取对应课程信息
    $classList = Db::name('video_class')->alias('vc')
                    ->join('guard_subject s','s.id = vc.subject_id')
                    ->join('guard_grade g','g.id = vc.grade_id')
                    ->field('s.subject,g.grade,vc.*')
                    ->where('vc.id',$data[0]['kid'])
                    ->find();
    if($classList['Semester'] == 1){
        $classList['seme'] = '上学期';
    }else{
        $classList['seme'] = '下学期';
    }
    foreach($data as $k=>$v){
        // $video = Db::name('video')->where(['id'=>$v['kid']])->field('testclass')->find();
        // $data[$k]['testclass'] = $video['testclass'];
        $data[$k]['time']=date('Y-m-d H-s-m',$v['time']);
        if($v['audi']==1){
            $data[$k]['audi']='试听';
        }else{
            $data[$k]['audi']='非试听';
        }
        if($v['kid'] == $classList['id']){
            $data[$k]['className'] = $classList['grade'].$classList['seme'].$classList['subject'];
        }
    }
    if($data){
        $arr=json_encode(
            array(
                'code'=>0,
                'msg'=>'',
                'count'=>$count,
                'data'=>$data
            )
        );
        echo $arr;
    }else{
        return jsonMsg("暂无数据",1);
    }

  }

    /**
     * 章编辑
     */
    public function updateChapter(Request $request)
    {
        $param=$request->param();
        $res = Db::name('video')->where('id' , $param['id'])->find();
        $this->assign('res' , $res);
        $this->assign('id' , $param['id']);
        return view('/review/updatechapter',['title'=>"编辑"]);
    }
    public function upchapter(Request $request)
    {
        $param=$request->param();        
        $chap_id = $param['chap_id'];

        $data["testclass"] = $param["testclass"];//章节名称
        $data["audi"] = $param["audi"];//是否试听
        $data["img"] = $param["image"];//是否试听
        //删除七牛课程主图
        $imgPath = Db::name('video')->where('id',$chap_id)->select();
        $qiniu = new QiniuModel();
        if(!empty($imgPath[0]['img']) && $imgPath[0]['img'] != $param['image']){
            $remarkVideoPath = str_replace("http://ydtvlitpic.ydtkt.com/","",$imgPath[0]['img']);
            $qiniuSpace = 'ydtvlitpic';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
        $res = Db::name('video')->where('id' , $chap_id)->update($data);
        if ($res) {
            jsonMsg("success", 0, $res);
        } else {
            jsonMsg("添加失败", 1);
        }
    }

    /**
     * 章添加
     */
    public function addChapter(Request $request)
    {
        $param=$request->param();
        $this->assign('kid' , $param['id']);
        return view('/review/addChapter',['title'=>"添加章"]);
    }

    public function achapter(Request $request)
    {
        $param = $request->param();
        $data['testclass'] = $param['testclass'];//章标题
        $data['kid'] = $param['kid'];//是否试听
        $data['time'] = time();//添加时间
        $data['pid'] = 0; //添加章 pid为0
        $data['img'] = $param['image'];
        $res = Db::name('video')->insert($data);
        if ($res) {
            jsonMsg("success", 0, $res);
        } else {
            jsonMsg("添加失败", 1);
        }
    }

    /**
     * 章删除
     */
    public function delchapter(Request $request)
    {
        $param=$request->param();
        
        $res = Db::name('video')->where('id' , $param['id'])->delete(); 
        if ($res) {
            //删除所属此章的所有课时
            $part = Db::name('video')->where('pid' , $param['id'])->select();//查询课时块下的内容
            $all_section = Db::name('video')->where('pid' , $param['id'])->delete();
            if(!empty($part)){
                foreach($part as $k=>$v){
                    if($part[$k]['part'] == 2){ //如果为课时
                        //删除知识点
                        Db::name('knowledge')->where('s_id' , $part[$k]['id'])->delete();
                    }elseif($part[$k]['part'] == 1){ //如果为课时块
                        $sectionList = Db::name('video')->where('pid' , $part[$k]['id'])->select();
                        if(!empty($sectionList)){
                            foreach ($variable as $key => $value) {
                                //删除知识点
                                 Db::name('knowledge')->where('s_id' , $sectionList[$key]['id'])->delete();
                            }
                        }
                    }
                    $r = Db::name('video')->where('pid' , $part[$k]['id'])->delete();
                }
            }
            jsonMsg("success", 0, $res);
        } else {
            jsonMsg("添加失败", 1);
        }
    }



    /**
     * 课时管理列表
     * @马桂婵  2019/3/18
     */
    public function getsectionlist(Request $request)
    {
        $param=$request->param();
        $this->assign('id' , $param['id']);
        return view('/review/sectionList',['title'=>"课时列表"]);
    }

    /**
     * 课时
     */
    public function sectionlist(Request $request)
    {
        $param=$request->param();
        $id = $param['pid'];
        $info=new Review();

        $page=$param['page'];
        $limit=$param['limit'];

        $res = $info->getlist($id , $page , $limit);
        $count = $info->getlist($id);
        //获取章节信息
        $reviewList = DB::name('video')->where('id',$res[0]['pid'])->find();
        foreach($res as $k=>$v){
            $res[$k]['time'] = date('Y-m-d H:i:s' , $v['time']);
            $res[$k]['reviewName'] = $reviewList['testclass'];
            if($v['audi']==1){
                $res[$k]['audi']='试听';
            }else{
                $res[$k]['audi']='非试听';
            }

            if($v['part']==1){
                $res[$k]['part']='课时块';
            }else{
                $res[$k]['part']='课时';
            }
            
        }
        if($res){
            $arr=json_encode(
                array(
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$count,
                    'data'=>$res
                )
            );
            echo $arr;
            
        }else{
            return jsonMsg("暂无数据",1);
        }
    }

    /**
     * 课时添加
     */
    public function addsection(Request $request)
    {
        $param=$request->param();
        $c_id = $param['id'];
        $this->assign('c_id' , $c_id);

        // 讲师
        $teacher = Db::name('teacher')->select();
        $this->assign('teacher' , $teacher);

        return view('/review/sectionAdd');
    }

    public function asection(Request $request)
    {
        $param=$request->param();

        $t_name = Db::name('teacher')->where('id' , $param['teacherId'])->find();
        $teacher_name = $t_name['name'];

        $kid = Db::name('video')->where('id' , $param['c_id'])->find();
        
        $data['kid'] = $kid['kid'];  //课程ID
        $data['pid'] = $param['c_id'];  //父ID
        $data['testclass'] = $param['testclass']; 
        $data['outline'] = $param['outline'];  
        $data['teacherid'] = $param['teacherId'];  //老师ID
        $data['audi'] = $param['audi'];  //是否试听
        $data['img'] = $param['image'];  //图片
        $data['teachername'] = $teacher_name;  //老师名称
        $data['time'] = time();
        $data['likes'] = mt_rand(100,1000);  // 点赞
        $data['link'] = $param['video'];  //课程链接
        $data['link_720'] = $param['video_720'];  //课程链接
        $data['link_480'] = $param['video_480'];  //课程链接
        // if(str_replace("http://ydtvideo1080.ydtkt.com/","",$data['link']) != str_replace("http://ydtvideo720.ydtkt.com/","",$data['link_720']) || str_replace("http://ydtvideo1080.ydtkt.com/","",$data['link']) != str_replace("http://ydtvideo480.ydtkt.com/","",$data['link_480'])){
        //     jsonMsg("视频名称不一样,请上传统一视频", 1);
        // }
        //自动获取课时时长
        if(!empty($data['link'])){
            $videoInfoList = $this->getVideoHour($data['link']);
            $videoInfoList = json_decode($videoInfoList,true);
            $classhour = intval($videoInfoList['format']['duration']);
            //拼接时长
            $hour = str_pad(intval($classhour/3600),2,"0",STR_PAD_LEFT);
            $minute = str_pad(intval($classhour%3600/60),2,"0",STR_PAD_LEFT);
            $second = str_pad(intval($classhour%60%60),2,"0",STR_PAD_LEFT);
            $data['classhour'] =  $hour.':'.$minute.':'.$second;
        }
        // $data['classhour'] = $param['classhour'];  //课程时长
        $data['skill'] = empty($param['editor'])?"":$param['editor'];  //课程技巧
        $data['part'] = 0;

        $res = Db::name('video')->insert($data);
        if ($res) {
            jsonMsg("success", 0, $res);
        } else {
            jsonMsg("添加失败", 1);
        }
    }
    //获取课时时长
    public function getVideoHour($videoPath){
        $videoInfoList = file_get_contents($videoPath.'?avinfo');
        return $videoInfoList;
    }
    /**
     * 课时编辑 课时
     */
    public function updatesection(Request $request)
    {
        $param=$request->param();
        $res = Db::name('video')->where('id' , $param['id'])->find();
        $this->assign('res' , $res);
        $this->assign('id' , $param['id']);

        // 讲师
        $teacher = Db::name('teacher')->select();
        $this->assign('teacher' , $teacher);

        return view('/review/sectionUpdate');
    }
    /**
     * 课时编辑 课时块
     */
    public function updatepart(Request $request)
    {
        $param=$request->param();
        $res = Db::name('video')->where('id' , $param['id'])->find();
        $this->assign('res' , $res);
        $this->assign('id' , $param['id']);

        // 讲师
        $teacher = Db::name('teacher')->select();
        $this->assign('teacher' , $teacher);

        return view('/review/updatePart');
    }

    public function upsection(Request $request)
    {
        $param = $request->param();

        $t_name = Db::name('teacher')->where('id' , $param['teacherId'])->find();
        $teacher_name = $t_name['name'];

        $data['testclass'] = empty($param['testclass'])?"":$param['testclass'];//节数
        $data['outline'] = $param['outline'];//标题
        $data['teacherid'] = empty($param['teacherId'])?"":$param['teacherId'];//老师ID
        $data['teachername'] = empty($teacher_name)?"":$teacher_name;//老师名称
        $data['audi'] = $param['audi'];//是否试听
        $data['img'] = empty($param['image'])?"":$param['image'];//图片
        $data['skill'] = empty($param['editor'])?"":$param['editor'];  //课程技巧
        $data['link'] = empty($param['video'])?"":$param['video'];  //课时链接
        $data['link_720'] = empty($param['video_720'])?"":$param['video_720'];  //课时链接
        $data['link_480'] = empty($param['video_480'])?"":$param['video_480'];  //课时链接
        // if(str_replace("http://ydtvideo1080.ydtkt.com/","",$data['link']) != str_replace("http://ydtvideo720.ydtkt.com/","",$data['link_720']) || str_replace("http://ydtvideo1080.ydtkt.com/","",$data['link']) != str_replace("http://ydtvideo480.ydtkt.com/","",$data['link_480'])){
        //     jsonMsg("视频名称不一样,请上传统一视频", 1);
        // }
        //自动获取课时时长
        if(!empty($data['link'])){
            $videoInfoList = $this->getVideoHour($data['link']);
            $videoInfoList = json_decode($videoInfoList,true);
            $classhour = intval($videoInfoList['format']['duration']);
            //拼接时长
            $hour = str_pad(intval($classhour/3600),2,"0",STR_PAD_LEFT);
            $minute = str_pad(intval($classhour%3600/60),2,"0",STR_PAD_LEFT);
            $second = str_pad(intval($classhour%60%60),2,"0",STR_PAD_LEFT);
            $data['classhour'] =  $hour.':'.$minute.':'.$second;
        }
        //删除七牛课程主图
        $imgPath = Db::name('video')->where('id',$param['section_id'])->select();
        $qiniu = new QiniuModel();
        if(!empty($imgPath[0]['img']) && $imgPath[0]['img'] != $param['image']){
            $remarkVideoPath = str_replace("http://ydtvlitpic.ydtkt.com/","",$imgPath[0]['img']);
            $qiniuSpace = 'ydtvlitpic';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['link']) && $imgPath[0]['link'] != $param['video']){
            $remarkVideoPath = str_replace("http://ydtvideo1080.ydtkt.com/","",$imgPath[0]['link']);
            $qiniuSpace = 'ydtvideo1080';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['link_720']) && $imgPath[0]['link_720'] != $param['video_720']){
            $remarkVideoPath = str_replace("http://ydtvideo720.ydtkt.com/","",$imgPath[0]['link_720']);
            $qiniuSpace = 'ydtvideo720';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['link_480']) && $imgPath[0]['link_480'] != $param['video_480']){
            $remarkVideoPath = str_replace("http://ydtvideo480.ydtkt.com/","",$imgPath[0]['link_480']);
            $qiniuSpace = 'ydtvideo480';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
        $res = Db::name('video')->where('id' , $param['section_id'])->update($data);
        if ($res) {
            jsonMsg("success", 0, $res);
        } else {
            jsonMsg("更新失败", 1);
        }
    }

    /**
     * 课时删除
     */
    public function delsection(Request $request)
    {
        $param = $request->param();
        //删除七牛课程主图 视频
        $imgPath = Db::name('video')->where('id',$param['id'])->select();
        $qiniu = new QiniuModel();
        if(!empty($imgPath[0]['img'])){
            $remarkVideoPath = str_replace("http://ydtvlitpic.ydtkt.com/","",$imgPath[0]['img']);
            $qiniuSpace = 'ydtvlitpic';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['link'])){
            $remarkVideoPath = str_replace("http://ydtvideo1080.ydtkt.com/","",$imgPath[0]['link']);
            $qiniuSpace = 'ydtvideo1080';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['link_720'])){
            $remarkVideoPath = str_replace("http://ydtvideo720.ydtkt.com/","",$imgPath[0]['link_720']);
            $qiniuSpace = 'ydtvideo720';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['link_480'])){
            $remarkVideoPath = str_replace("http://ydtvideo480.ydtkt.com/","",$imgPath[0]['link_480']);
            $qiniuSpace = 'ydtvideo480';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
        $res = Db::name('video')->where('id' , $param['id'])->delete();
        //删除知识点
        Db::name('knowledge')->where('s_id' , $param['id'])->delete();
        if ($res) {
            $imgPathList = Db::name('video')->where('pid',$param['id'])->select();
            if(!empty($imgPathList)){
                foreach ($imgPathList as $key => $value) {
                    //删除知识点
                    Db::name('knowledge')->where('s_id' ,$imgPathList[$key]['id'])->delete();
                    if(!empty($imgPathList[$key]['img'])){
                        $remarkVideoPath = str_replace("http://ydtvlitpic.ydtkt.com/","",$imgPathList[$key]['img']);
                        $qiniuSpace = 'ydtvlitpic';
                        $qiniu->delFile($remarkVideoPath,$qiniuSpace);
                    }
                    if(!empty($imgPathList[$key]['link'])){
                        $remarkVideoPath = str_replace("http://ydtvideo1080.ydtkt.com/","",$imgPathList[$key]['link']);
                        $qiniuSpace = 'ydtvideo1080';
                        $qiniu->delFile($remarkVideoPath,$qiniuSpace);
                    }
                    if(!empty($imgPathList[$key]['link_720'])){
                        $remarkVideoPath = str_replace("http://ydtvideo720.ydtkt.com/","",$imgPathList[$key]['link_720']);
                        $qiniuSpace = 'ydtvideo720';
                        $qiniu->delFile($remarkVideoPath,$qiniuSpace);
                    }
                    if(!empty($imgPathList[$key]['link_480'])){
                        $remarkVideoPath = str_replace("http://ydtvideo480.ydtkt.com/","",$imgPathList[$key]['link_480']);
                        $qiniuSpace = 'ydtvideo480';
                        $qiniu->delFile($remarkVideoPath,$qiniuSpace);
                    }
                }
            }
            $res = Db::name('video')->where('pid' , $param['id'])->delete();
            jsonMsg("success", 0, $res);
        } else {
            jsonMsg("删除失败", 1);
        }
    }

    // 级数顺序以语文为例：课程列表1级 ->  单元2级 -> 写作/阅读3级 -> 阅读下的课时4级
    // 三级页面
    
    /**
    *@马桂婵
    *2019/3/21
    *知识点管理
    */
        
    // 知识点列表
    public function secknowledge(Request $request)
    {
        $param = $request->param();

        $this->assign('id' , $param['id']); //获取课时的ID
        return view('/review/knowList');
    }
    
    public function getknowlist(Request $request)
    {
        $param = $request->param();
        $section_id = $param['pid'];
        $info = new Review();
        $res = $info->knowlist($section_id);
        foreach($res as $k=>$v){
            $res[$k]['created_time'] = date('Y-m-d H:i:s' , $v['created_time']);
            $res[$k]['update_time'] = date('Y-m-d H:i:s' , $v['update_time']);
        }

        if($res){
            $arr=json_encode(
                array(
                    'code'=>0,
                    'msg'=>'',
                    // 'count'=>$count,
                    'data'=>$res
                )
            );
            echo $arr;
            
        }else{
            return jsonMsg("暂无数据",1);
        }
    }

    /**
     * 知识点添加
     */
    public function addknowledge(Request $request)
    {
        $param = $request->param();
        $this->assign('s_id' , $param['id']);

        // $list = Db::name('video')->where('pid' , $param['id'])->select();
        // $this->assign('list' , $list);

        return view('/review/addknowledge');
    }

    public function addknow(Request $request)
    {
        $param = $request->param();
        $data['s_id'] = $param['sid'];
        $data['k_name'] = $param['knowname'];//知识点标题
        $data['k_content'] = $param['editor']; //知识点内容
        $data['start_time'] = $param['starttime']; //知识点开始时间
        $data['end_time'] = $param['endtime']; //知识点结束时间
        $data['created_time'] = time();
        // if(!empty($param['section_id'])){
            $res = Db::name('knowledge')->insert($data);
            if ($res) {
                jsonMsg("success", 0, $res);
            } else {
                jsonMsg("添加失败", 1);
            }
        // }else{
            // jsonMsg("所选课程不可为空", 1);
        // }
        
    }

    /**
     * 知识点编辑
     * @马桂婵 2019/3/22
     */
    public function editknowledge(Request $request)
    {
        $param = $request->param();
        $res = Db::name('knowledge')->where('k_id' , $param['id'])->find();
        $this->assign('res' , $res);

        $this->assign('k_id' , $param['id']);
        return view('/review/editknowledge');
    }

    public function editknow(Request $request)
    {
        $param = $request->param();
        $data['k_name'] = $param['knowname'];
        $data['k_content'] = $param['editor']; //知识点内容
        $data['start_time'] = $param['starttime'];   //知识点开始时间
        $data['end_time'] = $param['endtime'];     //知识点结束时间
        $data['update_time'] = time();
        $res = Db::name('knowledge')->where('k_id' , $param['id'])->update($data);
        if($res){
            jsonMsg("success", 0, $res);
        }else{
            jsonMsg("更新失败", 1);
        }
    }

    /**
     * 知识点删除
     */
    public function delknowdege(Request $request)
    {
        $param = $request->param();
        $res = Db::name('knowledge')->where('k_id' , $param['id'])->delete();
        if ($res) {
            jsonMsg("success", 0, $res);
        } else {
            jsonMsg("删除失败", 1);
        }
    }

    /**
     * 知识点详情
     */
    public function getOneKnow(Request $request)
    {
        $param = $request->param();
        $res = Db::name('knowledge')->where('k_id' , $param['id'])->find();
        $this->assign('res' , $res);
        return view('/review/getoneknow');
    }

    /**
     * 马桂婵
     * 2019/3/28
     *注：课时块是课时是同一级，编辑、添加、
     */


    //课时块添加
    public function addpart(Request $request)
    {
        $param = $request->param();
        $this->assign('id' , $param['id']);

        // 讲师
        $teacher = Db::name('teacher')->select();
        $this->assign('teacher' , $teacher);

        return view('/review/addPart');
    }

    public function apart(Request $request)
    {
        $param = $request->param();
        
        $kid = Db::name('video')->where('id' , $param['pid'])->find();

        $data['kid'] = $kid['kid'];
        $data['img'] = $param['image'];
        $data['testclass'] = $param['testclass'];//节数
        $data['outline'] = $param['outline'];//标题
        $data['teacherid'] = empty($param['teacherId'])?"":$param['teacherId'];//老师ID

        $t_name = Db::name('teacher')->where('id' , $param['teacherId'])->find();

        $data['teachername'] = empty($t_name['name'])?"":$t_name['name'];//老师名称
        $data['audi'] = $param['audi'];//是否试听
        $data['skill'] = empty($param['editor'])?"":$param['editor'];//技巧
        $data['pid'] = $param['pid'];
        $data['time'] = time();
        $data['part'] = 1;

        $res = Db::name('video')->insert($data);
        if ($res) {
            jsonMsg("success", 0, $res);
        } else {
            jsonMsg("课时小块添加失败", 1);
        }

    }

    // 块课时列表
    public function partlist(Request $request)
    {
        $param = $request->param();
        $this->assign('id' , $param['id']); //获取课时的ID

        $res = Db::name('video')->where('id' , $param['id'])->find();
        if($res['part'] == 1){  //1为课时块
            
            return view('/review/partList');
        }else{
            // $this->assign('id' , $param['id']);
            return view('/review/knowList');
        }

        
    }
    
    public function getpartlist(Request $request)
    {
        $param = $request->param();
        $section_id = $param['pid'];

        $res = Db::name('video')->where('pid' , $section_id)->select();
        foreach ($res as $key => $value) {
            $res[$key]['time'] = date('Y-m-d H:i:s' , $value['time']);
            if($value['audi']==1){
                $data[$key]['audi']='试听';
            }else{
                $data[$key]['audi']='非试听';
            }
        }

        if($res){
            $arr=json_encode(
                array(
                    'code'=>0,
                    'msg'=>'',
                    // 'count'=>$count,
                    'data'=>$res
                )
            );
            echo $arr;
            
        }else{
            return jsonMsg("暂无数据",1);
        }

    }

    

}