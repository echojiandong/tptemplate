<?php
namespace app\manage\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;
use think\image;
use think\Page;
use app\manage\model\QiniuModel;

class DatafileController extends author
{
    /**
     * 列表页面
     */
    public function index()
    {
        return view("datafile/index");
    }

    /**
     * 列表接口
     */
    public function getList(Request $request)
    {
        $where = [];
        $param = $request->param();

        if(array_key_exists('keyword', $param) && $param['keyword'] != ''){
            $where['a.name'] = ['like', "%".$param['keyword']."%"];
        }

        $data = Db::name('data_file')->alias('a')
                ->field("a.*, b.grade, FROM_UNIXTIME(a.create_time,'%Y-%m-%d %H:%i:%s') as addTime, c.name as video_className, (case a.semester
                    when 1 then '上学期'
                    when 2 then '下学期'
                    when 3 then '全册'
                    end
                    ) as stmesterText
                ")
                ->join('guard_grade b', 'a.grade_id = b.id')
                ->join('guard_video_class c', 'a.video_class_id = c.id')
                ->where($where)->select();
        $count = count($data);
        if($data){
        	$res = array(
					'code'=>0,
					'msg'=>'',
					'count'=>$count,
					'data'=>$data
				);
        	echo json_encode($res);
        }else{
        	jsonMsg("暂无数据",1);
        }
    }

    /**
     * 添加页面
     */
    public function add(Request $request)
    {
        // 年级
        $gradeList = Db::name('grade')->where(['status' => 1])->select();

        // 标签
        $tagList = Db::name('tag')->select();
        $this->assign('gradeList', !empty($gradeList) ? $gradeList : array());
        $this->assign('tagList', $tagList);
        return view("datafile/add");
        
    }

    /**
     * 添加执行
     */
    public function doAdd(Request $request)
    {
        $param = $request->param();

        $video_id = isset($param['tag_ids1']) ? $param['tag_ids1'] : ''; // 章节
        $tagid = isset($param['tag2']) ? $param['tag2'] : ''; // 标签

        $name = isset($param['name']) ? $param['name'] : '';
        $type = isset($param['type']) ? $param['type'] : '';
        if (!$type) {
            jsonMsg('请选择所属类型', 1);
        }

        $content = isset($param['editor']) ? $param['editor'] : '';
        $grade_id = isset($param['grade_id']) ? $param['grade_id'] : ''; // 年级
        $semester = isset($param['semester']) ? $param['semester'] : '';  // 学期
        $video_class_id = isset($param['video_class_id']) ? $param['video_class_id'] : ''; // 课程
       
        $link = isset($param['link']) ? $param['link'] : '';

        if (!$grade_id || !$semester || !$video_class_id || !$video_id || !$tagid) {
            jsonMsg('必填项不能为空', 1);
        }

        if (!$link) {
            jsonMsg('请选择上传文件', 1);
        }

        $link_url = explode(',', $link);

        $data = [
            'name' => $name,
            'type' => $type,
            'grade_id' => $grade_id,
            'semester' => $semester,
            'video_class_id' => $video_class_id,
            'video_id' => $video_id,
            'tag_id' => $tagid,
            'create_time' => time()
            
        ];

        $data_file_id = Db::name('data_file')->insertGetId($data);
        if ($data_file_id) {
            foreach ($link_url as $value) {
                $datas[] = [
                    'data_file_id' => $data_file_id,
                    'title' => $name,
                    'link' => $value
                ];
            }

            $res = Db::name('video_download_info')->insertAll($datas);
            if ($res) {
                jsonMsg('添加成功', 0);
            } else {
                Db::name('data_file')->where(['id' => $data_file_id])->delete();
                josnMsg('添加失败', 1);
            }
        } else {
            josnMsg('添加失败', 1);
        }
    }


    public function upload()
    {
    	set_time_limit(0);
        $file = request()->file('file');

        $info = $file->getinfo();

        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            //获取上传凭证
            $qiniuSpace = 'ydttlitpic';
            $uploadToken=$qiniu->getQnToken($qiniuSpace);
            $filePath=$info['tmp_name'];
            $fileName=$qiniu->getNewfilename('index',$info['name']);
            //上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://ydttlitpic.ydtkt.com/';
            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
            if($result){
                jsonMsg("成功",0,$result);
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }


    /**
     * 编辑页面
     */
    public function edit(Request $request)
    {
        $id = $request->param('id');
        // 年级
        $gradeList = Db::name('grade')->where(['status' => 1])->select();
        // 标签
        $tagList = Db::name('tag')->select();

        // 资料文件
        $info = Db::name('data_file')->where(['id' => $id])->find();

        $courselist = Db::name('video_class')->field('id, name, grade_id, Semester')->where(['grade_id' => $info['grade_id'], 'Semester' => $info['semester']])->select();

        $videolist = Db::name('video')->field('id, outline as name')->where(['kid' => $info['video_class_id'], 'part' => 2])->select();

        $this->assign('gradeList', !empty($gradeList) ? $gradeList : array()); // 年级列表
        $this->assign('tagList', $tagList); // 标签信息
        $this->assign('info', $info); // 资料信息
        $this->assign('courseList', $courselist); // 课程列表
        $this->assign('videoList', $videolist); // 章节列表

        return view("datafile/edit");
        
    }

    /**
     * 编辑执行
     */
    public function update(Request $request)
    {
        $param = $request->param();

        $id = $param['id'];
        if (!$id) {
            jsonMsg('该数据不存在', 1);
        }
        $video_id = isset($param['tag_ids1']) ? $param['tag_ids1'] : ''; // 章节
        $tagid = isset($param['tag2']) ? $param['tag2'] : ''; // 标签

        $name = isset($param['name']) ? $param['name'] : '';
        $type = isset($param['type']) ? $param['type'] : '';
        if (!$type) {
            jsonMsg('请选择所属类型', 1);
        }

        $content = isset($param['editor']) ? $param['editor'] : '';
        $grade_id = isset($param['grade_id']) ? $param['grade_id'] : ''; // 年级
        $semester = isset($param['semester']) ? $param['semester'] : '';  // 学期
        $video_class_id = isset($param['video_class_id']) ? $param['video_class_id'] : ''; // 课程
       
        $link = isset($param['link']) ? $param['link'] : '';

        if (!$grade_id || !$semester || !$video_class_id || !$video_id || !$tagid) {
            jsonMsg('必填项不能为空', 1);
        }

        if (!$link) {
            jsonMsg('请选择上传文件', 1);
        }

        $link_url = explode(',', $link);

        $data = [
            'name' => $name,
            'type' => $type,
            'grade_id' => $grade_id,
            'semester' => $semester,
            'video_class_id' => $video_class_id,
            'video_id' => $video_id,
            'tag_id' => $tagid,
            'update_time' => time()
        ];

        $res = Db::name('data_file')->where(['id' => $id])->update($data);
        
        if ($res) {
            Db::name('video_download_info')->where(['data_file_id' => $id])->delete();
            foreach ($link_url as $value) {
                $datas[] = [
                    'data_file_id' => $id,
                    'title' => $name,
                    'link' => $value
                ];
            }

            $row = Db::name('video_download_info')->insertAll($datas);
            if ($row) {
                jsonMsg('修改成功', 0);
            } else {
                josnMsg('修改失败', 1);
            }
        } else {
            josnMsg('修改失败', 1);
        }

    }

    /**
     * 详情
     */
    public function info(Request $request) 
    {
        $id = $request->param('id');
        if (!$id) {
            jsonMsg('该数据不存在', 1);
        }

        // 年级
        $gradeList = Db::name('grade')->where(['status' => 1])->select();
        // 标签
        $tagList = Db::name('tag')->select();

        // 资料文件
        $info = Db::name('data_file')->where(['id' => $id])->find();

        $courselist = Db::name('video_class')->field('id, name, grade_id, Semester')->where(['grade_id' => $info['grade_id'], 'Semester' => $info['semester']])->select();

        $videolist = Db::name('video')->field('id, outline as name')->where(['kid' => $info['video_class_id'], 'part' => 2])->select();

        $this->assign('gradeList', !empty($gradeList) ? $gradeList : array()); // 年级列表
        $this->assign('tagList', $tagList); // 标签信息
        $this->assign('info', $info); // 资料信息
        $this->assign('courseList', $courselist); // 课程列表
        $this->assign('videoList', $videolist); // 章节列表
        return $this->fetch('datafile/info');
    }

    /**
     * 删除
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');
        if (!$id) {
            jsonMsg('数据不存在', 1);
        }

        $res = Db::name('data_file')->where(['id' => $id])->delete();
        if ($res) {
            Db::name('video_download_info')->where(['data_file_id' => $id])->delete();
            jsonMsg('删除成功', 0);
        } else {
            jsonMsg('删除失败', 1);
        }
    }

    /**
     * 获取标签
     */
    public function getTagList(Request $request)
    {
        $tagList = Db::name('tag')->field('id, name as title, pid')->select();
        // $tagList = $this->getTree($tagList);
        return $tagList;
    }

    // 标签tree结构
    public function getTree($array){ 
        //第一步 构造数据
        $items = array();
        foreach($array as $value){
            $items[$value['id']] = $value;
        }
        //第二部 遍历数据 生成树状结构
        $tree = array();
        foreach($items as $key => $value){
            if(isset($items[$value['pid']])){
                $items[$value['pid']]['children'][] = &$items[$key];
            }else{
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }

    /**
     * 获取课程
     */
    public function getVideoClassList(Request $request)
    {
        $param = $request->param();
        $grade_id = isset($param['grade_id']) && !empty($param['grade_id']) ? $param['grade_id'] : '';
        $semester = isset($param['semester']) && !empty($param['semester']) ? $param['semester'] : '';
        if (!$grade_id || !$semester) {
            jsonMsg('请选择年级和学期', 1);
        }
        $list = Db::name('video_class')->field('id, name, grade_id, Semester')->where(['grade_id' => $grade_id, 'Semester' => $semester])->select();

        jsonMsg('success', 0, $list);
    }

    /**
     * 获取章节
     */
    public function getVideoList(Request $request)
    {
        $param = $request->param();
        $video_class_id = isset($param['video_class']) && !empty($param['video_class']) ? $param['video_class'] : '';

        if (!$video_class_id) {
            return false;
        }
        $list = Db::name('video')->field('id, outline as name')->where(['kid' => $video_class_id, 'part' => 2])->select();
        jsonMsg('success', 0, $list);
    }


}
