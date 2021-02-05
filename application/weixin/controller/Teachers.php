<?php
namespace app\weixin\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use app\weixin\model\PersonModel;
use app\manage\model\Wechatnav;

class Teachers extends Controller
{
    protected $courseArr = [];
    protected $subjArr = [1,2,3,10]; // 屏蔽刁这几个课程
    public function _initialize()
    {
        parent::_initialize();
        $this->courseArr = Db::name('subject')->column('subject');

    }
    // public function getTeacherList()
    // {
    //     $type = (int) input('type', 1);
    //     $page = (int) input('page', 1);
    //     $pageSize = (int) input('pageSize');

    //     $teacherList = Db::name('teacher')->alias('a')
    //                     ->field('a.*, b.subject as subjectname')
    //                     ->join('subject b', 'a.subject_id = b.id')
    //                     ->order('a.id asc')
    //                     ->where('a.type', $type)
    //                     ->limit(($page -1) *$pageSize, $pageSize)
    //                     ->select();
    //     $teacherCount = Db::name('teacher')->alias('a')
    //                     ->field('a.*, b.subject as subjectname')
    //                     ->join('subject b', 'a.subject_id = b.id')
    //                     ->where('a.type', $type)
    //                     ->count();
     
    //     $data = [
    //         'teacherList' => $teacherList,
    //         'totalNum'    => $teacherCount
    //     ];
    //     return json_encode($data);
    // }

    public function getTeacherList(){

        $type = (int) input('type', 1);

        //教研老师
        $teacherList = Db::name('teacher') ->field('t.*') 
                                            ->alias('t')
                                            ->join('guard_subject s','s.id = t.subject_id')
                                            ->order('s.id,t.grade_id')
                                            ->where('t.type' , $type)
                                            ->select();

        $data = $teacherList;

        $subject = Db::name('subject') ->field('id,subject') ->select();

        $grade = Db::name('grade') ->field('id,grade') ->select();

        if(!empty($subject)){

            $subject_arr = array_combine(array_column($subject, 'id'), array_column($subject, 'subject'));
        }

        if(!empty($grade)){

            $grade_arr = array_combine(array_column($grade, 'id'),array_column($grade, 'grade'));
        }

        if($type == 1){

            $data = [];

            $school_1 = array_unique(array_column($teacherList, 'schoolid'));

            $school_arr = Db::name('school') ->field('id,s_name') ->where('id', 'in', $school_1) ->order('id desc') ->select();

            foreach($school_arr as $key =>&$val){

                foreach($teacherList as &$v){

                    if(isset($subject_arr)){

                        $v['subjectname'] = $subject_arr[$v['subject_id']];
                    }

                    if(isset($grade_arr)){

                        $v['grade_ids'] = $grade_arr[$v['grade_id']];
                    }

                    if($val['id'] == $v['schoolid']){

                        $val['son'][] = $v;
                    }
                }
                $val['p']=$key;
                $data[] = $val;
            }
        }else{

            $data = [];

            foreach($teacherList as &$v){

                if(isset($subject_arr)){

                    $v['subjectname'] = $subject_arr[$v['subject_id']];
                }

                if(isset($grade_arr)){

                        $v['grade_ids'] = $grade_arr[$v['grade_id']];
                }

                $data[] = $v;
            }
        }

        jsonMsg('success', 1, $data);
    }

    public function teacherscontain(){

        $id = (int) input('id', 1);

        
        $teacherList = Db::name('teacher') ->field('id,name,litpic,content,title,subject_id,grade_id') 
                                           ->where(['id' => $id])
                                           ->find();
        $subject = Db::name('subject') ->field('id,subject') ->select();

        $grade = Db::name('grade') ->field('grade') ->where(['id' => $teacherList['grade_id']])->find()['grade'];

        if(!empty($subject)){

            $subject_arr = array_combine(array_column($subject, 'id'), array_column($subject, 'subject'));
        }
        $teacherList['subjectname'] = isset($subject_arr) ? $subject_arr[$teacherList['subject_id']] : '';

        $teacherList['grade_id'] = $grade;

        jsonMsg('success', 1, $teacherList);

    }

    /**
     * 课程全局搜索修改
     */

    public function searchCourse() 
    {
        $param = input();
        $page     = isset($param['page']) ? (int) $param['page'] : 1;  // 页码
        $pageSize = isset($param['pageSize']) ? (int) $param['pageSize'] : 10;  // 页显示数
        $keywords = preg_replace('# #', '', trim($param['keywords']));  // 搜索关键词
        $type     = (int) $param['type'] ? (int) $param['type'] : 1; // 类型 ， 1全部， 2课程， 3知识点
        $openid   = isset($param['openid']) ? $param['openid'] : ''; // 小程序登录用户id
        if (!isset($keywords) || empty($keywords)) {
            $data = [
                'msg' => '输入搜索关键词',
                'code' => 1
            ];
            return json_encode($data);
        }


        // 判断是否是科目, 存在查询课程
        if (in_array($keywords, $this->courseArr)) {
            $subject = $this->getSubject($keywords);

        }

        $where = array();
        $where1 = array();

        // 收藏条件
        $where2['status'] = 1; 
        // 课程章节中存在关键词
        if (isset($keywords) && !empty($keywords)) {
            $where['outline'] = ['like', '%'.$keywords.'%'];
            $where['part'] = 2;
            // 知识点
            $where1['k_name|k_content'] = ['like', '%'.$keywords.'%'];
            $where2['a.ctitle'] = ['like', '%'.$keywords.'%'];
        }
        if (isset($openid) && !empty($openid)) {
            $where2['a.uid'] = $openid;
        }
        $subjectCount = isset($subject) ? count($subject) : 0;
        $chapterCount = count($this->getChapter($where));
        $chapterCount = $subjectCount + $chapterCount;
        $knowledgeCount = count($this->getKnowledge($where1));
        $collectCount = count($this->getCollect($where2));
        
        $totalCount = $chapterCount + $knowledgeCount + $collectCount;
      
        switch ($type) {
            case '1':
                $subject = $this->getSubject($keywords);
                $videoChapter = $this->getChapter($where);
                $knowledge = $this->getKnowledge($where1);
                $collect = $this->getCollect($where2);
                $list = array_merge($subject, $videoChapter, $knowledge, $collect);
                break;
            case '2':
                // 章节
                $subject = $this->getSubject($keywords);
                $videoChapter = $this->getChapter($where);
                $list = array_merge($subject, $videoChapter);
                
                break;
            case '3':
                // 知识点
                $knowledge = $this->getKnowledge($where1);
                $list = $knowledge;
                break;
            case '4':
                // 收藏
                $collect = $this->getCollect($where2);
                $list = $collect;
                break;
            default:
                break;
        }

        if ($totalCount <= 0) {
            $data = [
                'msg'      => '没有数据',
                'code'     => 0,
                'totalNum' => 0,
                'chapterNum' => 0,
                'knowledgeNum' => 0,
                'collectNum' => 0,
                'list'     => []
            ];
            return json_encode($data);
        }

        $list = array_slice($list, ($page-1)*$pageSize, $pageSize);
        $data = [
            'totalNum' => $totalCount,
            'chapterNum' => $chapterCount,
            'knowledgeNum' => $knowledgeCount,
            'collectNum' => $collectCount,
            'list'     => $list,
            'msg'      => 'success',
            'code'     => 0
        ];

        
        return json_encode($data);

    }

    // 科目
    private function getSubject($keywords)
    {
        $subjects = ['语文','数学','英语'];
        if(in_array($keywords,$subjects)){
            $videoClass = Db::name('video_class')
                ->field("
                    id as video_class_id, img, name, title,
                    case Semester
                        when 1 then '上学期'
                        when 2 then '下学期'
                        when 3 then '全册'
                    end as semester,
                    concat(name, '(', 
                        case Semester
                            when 1 then '上学期'
                            when 2 then '下学期'
                            when 3 then '全册'
                        end
                        , ')') as Titles
                ")
                ->where(['title' => $keywords])
                ->where(['subject_id' => ['in', $this->subjArr]])
                ->where('id != 23')
                ->select();
            if (!$videoClass) {
                return array();
            }

            foreach ($videoClass as $key => $value) {
                $videoClass[$key]['type'] = 1; // 课程
                $courseNum = Db::name('video')->where(['kid' => $value['video_class_id']]) ->where(['display' => 1])->count('id');
                $videoClass[$key]['courseNum'] = $courseNum;
            }
            return $videoClass;
        }else{
            $videoClass = Db::name('product')
                ->field("
                    id as video_class_id, productUrl as img, name, title,productStatus,
                    case Semester
                        when 1 then '上学期'
                        when 2 then '下学期'
                        when 3 then '全册'
                    end as semester,
                    concat(name, '(', 
                        case Semester
                            when 1 then '上学期'
                            when 2 then '下学期'
                            when 3 then '全册'
                        end
                        , ')') as Titles
                ")
                ->where(['title' => $keywords])
                ->where(['subject_id' => ['in', $this->subjArr]])
                ->select();
            if (!$videoClass) {
                return array();
            }

            foreach ($videoClass as $key => $value) {
                $videoClass[$key]['type'] = 1; // 课程
                $courseNum = Db::name('product_info')->where(['product_id' => $value['video_class_id']]) ->where(['forbiden' => 1])->count('id');
                $videoClass[$key]['courseNum'] = $courseNum;
            }
            return $videoClass;
        }
    }


    // 章节
    private function getChapter($where)
    {
        $videoChapter = Db::name('video')->alias('a')
                        ->field("a.id as video_id, a.kid as video_class_id, a.img, a.testclass, a.outline, a.link, a.link_720, a.link_480,b.productStatus,
                            concat(b.name, '(', 
                                case b.Semester
                                    when 1 then '上学期'
                                    when 2 then '下学期'
                                    when 3 then '全册'
                                end
                            , ')') as Titles
                        ")
                        ->join('video_class b', 'a.kid = b.id')
                        ->where(['b.subject_id' => ['in', $this->subjArr]])
                        ->where($where)->select();
        $product_info = Db::name('product')
            ->alias('a')
            ->field("a.id,b.video_id,a.productStatus,
                            concat(a.name,case a.Semester
                                    when 1 then '上学期'
                                    when 2 then '下学期'
                                    when 3 then '全册'
                                end
                            )as Titles")
            ->join('product_info b','a.id=b.product_id')
            ->where(['a.subject_id' => ['in', $this->subjArr]])
            ->select();

        $video_id_info = array_column($product_info,'video_id');
        $videoChapter2 = Db::name('video')->alias('a')
            ->field("a.id as video_id, a.audi, a.kid, a.img, a.testclass, a.outline, a.link, a.link_720, a.link_480")
            ->where(['a.id' => ['in', $video_id_info]])
            ->where($where)->select();

        foreach($product_info as $k=>$v){
            foreach ($videoChapter2 as $key => $value) {
                if($v['video_id']==$value['video_id']){
                    $videoChapter2[$key]['Titles'] = $v['Titles'];
                    $videoChapter2[$key]['productStatus'] = $v['productStatus'];
                    $videoChapter2[$key]['video_class_id'] = $v['id'];
                }
            }
        }
        if (!$videoChapter && $videoChapter2) {
            return array();
        }
        $videoChapter = array_merge($videoChapter,$videoChapter2);
        foreach ($videoChapter as $key => $value) {
            $videoChapter[$key]['type'] = 2; // 章节
        }
        return $videoChapter;
    }

    // 知识点
    private function getKnowledge($where)
    {
        $knowledge = Db::name('knowledge')->alias('a')
                    ->field("a.k_id, a.s_id as video_id, a.k_name, a.start_time as startTime, b.id, b.img, b.testclass, b.outline, b.link, b.link_720, b.link_480,c.productStatus,
                            concat(c.name, '(', 
                                case c.Semester
                                    when 1 then '上学期'
                                    when 2 then '下学期'
                                    when 3 then '全册'
                                end
                            , ')') as Titles, b.kid as video_class_id
                    
                    ")
                    ->where($where)
                    ->where(['b.part' => 2])
                    ->where(['b.display' => 1])
                    ->join('video b', 'a.s_id = b.id')
                    ->join('video_class c', 'b.kid = c.id')
                    ->where(['c.subject_id' => ['in', $this->subjArr]])
                    ->select();
        $product_info = Db::name('product')
            ->alias('a')
            ->field("a.id,b.video_id,a.productStatus,
                            concat(a.name,case a.Semester
                                    when 1 then '上学期'
                                    when 2 then '下学期'
                                    when 3 then '全册'
                                end
                            )as Titles")
            ->join('product_info b','a.id=b.product_id')
            ->where(['a.subject_id' => ['in', $this->subjArr]])
            ->select();
        $video_id_info = array_column($product_info,'video_id');
        array_push($video_id_info,1651);
        $knowledge2 = Db::name('knowledge')->alias('a')
            ->field("a.k_id, a.s_id as video_id, a.k_name, a.start_time as startTime, b.id, b.img, b.testclass,  b.audi, b.outline, b.link, b.link_720, b.link_480, b.kid
                    ")
            ->join('video b', 'a.s_id = b.id')
            ->where($where)
            ->where(['b.part' => 2,'b.display' => 1])
            ->whereIn('b.id',$video_id_info)
            ->order('a.sort asc,a.start_time asc')
            ->select();
        foreach($product_info as $k=>$v){
            foreach ($knowledge2 as $key => $value) {
                if($v['video_id']==$value['video_id']){
                    $knowledge2[$key]['Titles'] = $v['Titles'];
                    $knowledge2[$key]['productStatus'] = $v['productStatus'];
                    $knowledge2[$key]['video_class_id'] = $v['id'];
                }
            }
        }
        if (!$knowledge && !$knowledge2) {
            return array();
        }
        $knowledge = array_merge($knowledge,$knowledge2);
        foreach ($knowledge as $key => $value) {
            $knowledge[$key]['type'] = 3;
        }

        return $knowledge;
    }

    // 收藏
    public function getCollect($where)
    {
        $collect = Db::name('person_collect')->alias('a')
                ->field("a.video_class_id, a.uid, a.video_id, a.startTime, a.ctitle, a.noteText, a.collectImg as img, a.intime, b.outline as Titles")
                ->where($where)
                ->where(['b.display' => 1])
                ->join('guard_video b', 'a.video_id = b.id')
                ->select();
        if (!$collect) {
            return array();
        }
        foreach ($collect as $key => $value) {
            $collect[$key]['intime'] = date('Y-m-d', $value['intime']);
            $collect[$key]['type'] = 4;
        }
        
        return $collect;
    }

}
