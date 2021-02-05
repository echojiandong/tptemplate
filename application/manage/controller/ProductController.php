<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use think\Cache;
use vendor\phpoffic;
use app\manage\model\Product;

class ProductController extends author
{
	public function productList(){
        return view('/product/productList',['title'=>"产品列表"]);
    }

    public function getProductList(Request $request){
        $param = $request->param();

        $page=$param['page'];
        $limit=$param['limit'];
        $where = array();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where['p.name']=['like',"%".$param['keyword']."%"];
        }


        $data = Db::name('product')
                    ->field('p.*,g.grade,s.subject,l.learn,t.textbook')
                    ->alias('p')
                    ->join('guard_grade g','g.id = p.grade_id')
                    ->join('guard_subject s','s.id = p.subject_id')
                    ->join('guard_learn l','l.id=p.learn_id','left')
                    ->join('guard_textbook t','t.id=p.edition_id','left')
                    ->where($where)
                    ->select();
        $count = Db::name('product')
                    ->field('p.*,g.grade,s.subject,l.learn,t.textbook')
                    ->alias('p')
                    ->join('guard_grade g','g.id = p.grade_id')
                    ->join('guard_subject s','s.id = p.subject_id')
                    ->join('guard_learn l','l.id=p.learn_id','left')
                    ->join('guard_textbook t','t.id=p.edition_id','left')
                    ->where($where)
                    ->count();
        $Semester = [1=>'上学期',2=>'下学期',3=>'全册'];
        foreach ($data as $key => $value) {
            $data[$key]['addTime'] = date('Y-m-d H:i:s',$value['create_at']);
            $data[$key]['Semester'] = $Semester[$value['Semester']];

        }

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

    public function add(){
        //获取年级列表
        $gradeList = Db::name('grade')->select();

        //获取科目列表
        $subjectList = Db::name('subject')->select();

        //获取版本列表
        $textbookList = Db::name('textbook')->select();

        //获取阶段列表
        $learnList = Db::name('learn')->select();

        //获取教师列表
        $teacherList = Db::name('teacher')->field('id,name')->select();


        $this->assign('gradeList',$gradeList);
        $this->assign('subjectList',$subjectList);
        $this->assign('textbookList',$textbookList);
        $this->assign('learnList',$learnList);
        $this->assign('teacherList',$teacherList);
        return view('/product/add',['title'=>"添加产品"]);
    }

    //视频标签树
    public function videoTagTree(){

        $param = input();
        //获取列表树格式
        if(isset($param['id'])){
            $list = Product::videoTagTree($param['id']);
        }else{
            $list = Product::videoTagTree();
        }
        //递归处理树结构
        $data = $this ->tree($list);
        ajaReturn($data,0);
    }

    private  function tree($data,$pid=0){
        $tree = [];
        foreach($data as $k => $v)
        {
            // $v['level']=$level;
            if($v['tId'] == $pid)
            {        
                $v['children'] = $this ->tree($data, $v['id']);
                if(empty($v['children'])){
                    unset($v['children']);
                }else{
                    unset($v['checked']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    public function addProduct(Request $request){
        $param = $request->param();
        $data = array();
        $data['name'] = isset($param['name']) ? $param['name'] : '';
        $data['title'] = isset($param['title']) ? $param['title'] : '';
        $data['price'] = isset($param['price']) ? $param['price'] : '';
        $data['purchase'] = isset($param['purchase']) ? $param['purchase'] : 0;
        $data['grade_id'] = isset($param['gradeId']) ? $param['gradeId'] : '';
        $data['subject_id'] = isset($param['subject_id']) ? $param['subject_id'] : '';
        $data['edition_id'] = isset($param['edition_id']) ? $param['edition_id'] : '';
        $data['learn_id'] = isset($param['learn_id']) ? $param['learn_id'] : '';
        $data['content'] = isset($param['content']) ? $param['content'] : '';
        $data['productUrl'] = isset($param['litpic']) ? $param['litpic'] : '';
        $data['imgNo'] = isset($param['imgNo']) ? $param['imgNo'] : '';
        $data['tagId'] = isset($param['ids']) ? trim($param['ids'],',') : '';
        $data['create_at'] = time();
        $data['Semester'] = isset($param['Semester']) ? $param['Semester'] : '';
        $data['audi'] = isset($param['audi']) ? $param['audi'] : 0;
        $data['audition_price'] = isset($param['audition_price']) ? $param['audition_price'] : 0;
        $data['courseware'] = isset($param['courseware']) ? $param['courseware'] : '';
        $data['courseware1'] = isset($param['courseware1']) ? $param['courseware1'] : '';
        $data['courseware2'] = isset($param['courseware2']) ? $param['courseware2'] : '';
        $teacher = explode('-',$param['teacher']);
        $data['teacherId'] = isset($teacher[0]) ? $teacher[0] : 0;
        $data['sname'] = isset($teacher[1]) ? $teacher[1] : '';
        //根据 ids 获取 video 视频
        $videoList = [];
        if(!empty($data['tagId'])){
            $idList = explode(',',trim($data['tagId'],','));

            for($i=0; $i < count($idList); $i++){
                $videoIdList = Db::name('video')
                    ->alias('v')
                    ->field('v.id')
                    ->where('v.part = 2 and find_in_set('.$idList[$i].',v.videoTag)')
                    ->select();
                //把二维数组变成一维的
                $list = array();
                if($videoIdList){
                    foreach ($videoIdList as $key => $value) {
                        $list[] = $value['id'];
                    }
                    $videoList = array_merge($videoList,$list);
                }
            }
            $videoList = array_unique($videoList);

            //添加
            $productID = Db::name('product')->insertGetId($data);
            if(!empty($productID)){
                $productInfo = array();
                foreach($videoList as $key => $val) {
                    $productInfo[$key]['product_id'] = $productID;
                    $productInfo[$key]['video_id'] = $val;
                    $pid = Db::name('video')->field('pid')->where('id',$val)->find();
                    $productInfo[$key]['video_pid'] = $pid['pid'];
                }
                //添加详情
                $res = DB::name('product_info')->insertAll($productInfo);
                if($res){
                    jsonMsg('添加成功',0);
                }else{
                    jsonMsg('添加失败',1);
                }
            }
        }else{
            //添加
            $productID = Db::name('product')->insertGetId($data);
            if($productID){
                jsonMsg('添加成功',0);
            }else{
                jsonMsg('添加失败',1);
            }
        }

    }

    public function edit(){
        $param = input();
        //获取年级列表
        $gradeList = Db::name('grade')->select();

        //获取科目列表
        $subjectList = Db::name('subject')->select();

        //获取版本列表
        $textbookList = Db::name('textbook')->select();

        //获取阶段列表
        $learnList = Db::name('learn')->select();

        //获取教师列表
        $teacherList = Db::name('teacher')->field('id,name')->select();

        //获取数据
        $info = Db::name('product')->where('id='.$param['id'])->find();

        $this->assign('gradeList',$gradeList);
        $this->assign('subjectList',$subjectList);
        $this->assign('textbookList',$textbookList);
        $this->assign('learnList',$learnList);
        $this->assign('teacherList',$teacherList);
        $this->assign('info',$info);
        $this->assign('id',$param['id']);
        return view('/product/edit',['title'=>"编辑产品"]);
    }

    public function editProduct(Request $request){
        $param = $request->param();
        $data = array();
        $data['name'] = isset($param['name']) ? $param['name'] : '';
        $data['title'] = isset($param['title']) ? $param['title'] : '';
        $data['price'] = isset($param['price']) ? $param['price'] : '';
        $data['purchase'] = isset($param['purchase']) ? $param['purchase'] : 0;
        $data['grade_id'] = isset($param['gradeId']) ? $param['gradeId'] : '';
        $data['subject_id'] = isset($param['subject_id']) ? $param['subject_id'] : '';
        $data['edition_id'] = isset($param['edition_id']) ? $param['edition_id'] : '';
        $data['learn_id'] = isset($param['learn_id']) ? $param['learn_id'] : '';
        $data['content'] = isset($param['content']) ? $param['content'] : '';
        $data['productUrl'] = isset($param['litpic']) ? $param['litpic'] : '';
        $data['imgNo'] = isset($param['imgNo']) ? $param['imgNo'] : '';
        $data['tagId'] = isset($param['ids']) ? trim($param['ids'],',') : '';
        $data['Semester'] = isset($param['Semester']) ? $param['Semester'] : '';
        $data['audi'] = isset($param['audi']) ? $param['audi'] : 0;
        $data['audition_price'] = isset($param['audition_price']) ? $param['audition_price'] : 0;
        $data['courseware'] = isset($param['courseware']) ? $param['courseware'] : '';
        $data['courseware1'] = isset($param['courseware1']) ? $param['courseware1'] : '';
        $data['courseware2'] = isset($param['courseware2']) ? $param['courseware2'] : '';
        $teacher = explode('-',$param['teacher']);
        $data['teacherId'] = isset($teacher[0]) ? $teacher[0] : 0;
        $data['sname'] = isset($teacher[1]) ? $teacher[1] : '';

        $result = Db::name('product')->where('id='.$param['id'])->update($data);

        if($data['tagId'] != $param['lastids'] && !empty($result)){
            //删除之前的详情
            Db::name('product_info')->where('product_id = '.$param['id'])->delete();
            if(!empty($data['tagId'])){
                //根据 ids 获取 video 视频
                $idList = explode(',',trim($data['tagId'],','));
                $videoList = [];
                for($i=0; $i < count($idList); $i++){
                    $videoIdList = Db::name('video')
                        ->alias('v')
                        ->field('v.id')
                        ->where('v.part = 2 and find_in_set('.$idList[$i].',v.videoTag)')
                        ->select();
                    //把二维数组变成一维的
                    $list = array();
                    if($videoIdList){
                        foreach ($videoIdList as $key => $value) {
                            $list[] = $value['id'];
                        }
                        $videoList = array_merge($videoList,$list);
                    }
                }
                $videoList = array_unique($videoList);

                $productInfo = array();
                foreach($videoList as $key => $val) {
                    $productInfo[$key]['product_id'] = $param['id'];
                    $productInfo[$key]['video_id'] = $val;
                    $pid = Db::name('video')->field('pid')->where('id',$val)->find();
                    $productInfo[$key]['video_pid'] = $pid['pid'];
                }
                //添加详情
                $res = DB::name('product_info')->insertAll($productInfo);
                if($res){
                    jsonMsg('修改成功',0);
                }else{
                    jsonMsg('修改失败',1);
                }
            }else{
                if($result){
                    jsonMsg('修改成功',0);
                }else{
                    jsonMsg('修改失败',1);
                }
            }

        }else{
            if($result){
                jsonMsg('修改成功',0);
            }else{
                jsonMsg('修改失败',1);
            }
        }
    }

    public function del(){
        $param = input();

        if(!empty($param['id'])){
            //删除详情
            $res = Db::name('product_info')->where('product_id = '.$param['id'])->delete();

            //删除产品
            $result = Db::name('product')->where('id = '.$param['id'])->delete();

            if($res && $result){
                jsonMsg('删除成功',0);
            }else{
                jsonMsg('删除失败',1);
            }
        }
    }
}