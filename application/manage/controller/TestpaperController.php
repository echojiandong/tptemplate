<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\image;
use think\Page;
use app\manage\model\TestPaper;
use app\manage\model\Question;
class TestpaperController extends author{
	//试卷列表列表
    public function testPaperList(Request $request)
    {
        return view('/testPaper/testPaperList',['title'=>"试卷列表`"]);
    }
    //js获取试卷列表
    public function getTestPaperList(Request $request)
    {
        $param = $request->param();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where['title']=['like',"%".$param['keyword']."%"];
        }
        if(array_key_exists('grade_id',$param) && !empty($param['grade_id'])){
            $where['grade_id']=$param['grade_id'];
        }else{
        	$where['grade_id']=['GT',0];
        }
        if(array_key_exists('subject_id',$param) && !empty($param['subject_id'])){
            $where['subject_id']=$param['subject_id'];
        }else{
        	$where['subject_id']=['GT',0];
        }
        if(array_key_exists('semester',$param) && !empty($param['semester'])){
            $where['semester']=$param['semester'];
        }else{
        	$where['semester']=['GT',0];
        }
        if(array_key_exists('type',$param) && !empty($param['type'])){
            $where['type']=$param['type'];
        }else{
        	$where['type']=['GT',0];
        }
        $where['pid'] = 0;
        $where['status'] = 1;
        $page=$param['page'];
        $limit=$param['limit'];
        $data =  TestPaper::getTestPaperList($where,$page,$limit);
        $count = $data['count'];
        unset($data['count']);
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
    public function makePaper(){
        //获取年列表
        $gradeList = Db::name('grade')->select();
        $this->assign("gradeList",$gradeList);
        //获取科目列表
        $subjectList = Db::name('subject')->select();
        $this->assign("subjectList",$subjectList);
        //获取题型列表
        $questionTypeList = Db::name('questype')->select();
        $this->assign("questionTypeList",$questionTypeList);

    	return view('/testPaper/makePaper',['title'=>"添加手工试卷"]);
    }
    public function randMakePaper(){
        //获取年列表
        $gradeList = Db::name('grade')->select();
        $this->assign("gradeList",$gradeList);
        //获取科目列表
        $subjectList = Db::name('subject')->select();
        $this->assign("subjectList",$subjectList);
        //获取题型列表
        $questionTypeList = Db::name('questype')->select();
        $this->assign("questionTypeList",$questionTypeList);

        return view('/testPaper/randMakePaper',['title'=>"添加随机试卷"]);
    }
    //执行添加随机试卷
    public function doRandMakePaper(Request $request){
        $param = $request->param();
        //获取题型列表
        $typeList = Db::name('questype')->select();
        $questionList = array();
        $k = 0;
        for ($i=1; $i <= count($typeList); $i++) {
            $where['q_gradeid'] = $param['grade_id'];
            $where['q_subjectid'] = $param['subject_id'];
            $where['q_seme'] = $param['semester'];
            $where['q_type'] = $i;
            if(!empty($param["num_".$i]) && !empty($param["everyScore_".$i]) && !empty($param["sort_".$i])){
                if(!empty($param["easy_".$i])){
                    $where['q_level'] = 1;
                    $easydata = Db::name('question')
                                    ->where($where)
                                    ->select();
                    $easyQuesList = array();
                    foreach ($easydata as $key => $value) {
                        $easyQuesList[$key]['grade_id'] = $param['grade_id'];
                        $easyQuesList[$key]['subject_id'] = $param['subject_id'];
                        $easyQuesList[$key]['semester'] = $param['semester'];
                        $easyQuesList[$key]['score'] = $param["everyScore_".$i];
                        $easyQuesList[$key]['addTime'] = time();
                        $easyQuesList[$key]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
                        $easyQuesList[$key]['qid'] = $value['q_id'];
                        $easyQuesList[$key]['q_type'] = $i;
                        $easyQuesList[$key]['sort'] = $param["sort_".$i];
                        $easyQuesList[$key]['title'] = $param["title"];
                        $easyQuesList[$key]['type'] = 2;
                    }
                    if($param["easy_".$i] <= count($easydata)){
                        $easyList = array_rand($easyQuesList,$param["easy_".$i]);
                        if($param["easy_".$i] > 1){
                            for($j=0; $j< $param["easy_".$i]; $j++){
                                $questionList[$k] = $easyQuesList[$easyList[$j]];
                                $k++;
                            }
                        }else{
                            $questionList[$k] = $easyQuesList[$easyList];
                            $k++;
                        }
                    }else{
                        return jsonMsg("你所选的".$typeList[$i-1]['questype']."简单类型题目超出数据库数量",1);
                    }
                }
                if(!empty($param["mid_".$i])){
                    $where['q_level'] = 2;
                    $middata = Db::name('question')
                                    ->where($where)
                                    ->select();
                    $midQuesList = array();
                    foreach ($middata as $key => $value) {
                        $midQuesList[$key]['grade_id'] = $param['grade_id'];
                        $midQuesList[$key]['subject_id'] = $param['subject_id'];
                        $midQuesList[$key]['semester'] = $param['semester'];
                        $midQuesList[$key]['score'] = $param["everyScore_".$i];
                        $midQuesList[$key]['addTime'] = time();
                        $midQuesList[$key]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
                        $midQuesList[$key]['qid'] = $value['q_id'];
                        $midQuesList[$key]['q_type'] = $i;
                        $midQuesList[$key]['sort'] = $param["sort_".$i];
                        $midQuesList[$key]['title'] = $param["title"];
                        $midQuesList[$key]['type'] = 2;
                    }
                    if($param["mid_".$i] <= count($middata)){
                        $midList = array_rand($midQuesList,$param["mid_".$i]);
                        if($param["mid_".$i] > 1){
                            for($j=0; $j< $param["mid_".$i]; $j++){
                                $questionList[$k] = $midQuesList[$midList[$j]];
                                $k++;
                            }
                        }else{
                            $questionList[$k] = $midQuesList[$midList];
                            $k++;
                        }
                    }else{
                        return jsonMsg("你所选的".$typeList[$i-1]['questype']."中等类型题目超出数据库数量",1);
                    }
                }
                if(!empty($param["difficulty_".$i])){
                    $where['q_level'] = 3;
                    $difficultydata = Db::name('question')
                                    ->where($where)
                                    ->select();
                    $difficultyQuesList = array();
                    foreach ($difficultydata as $key => $value) {
                        $difficultyQuesList[$key]['grade_id'] = $param['grade_id'];
                        $difficultyQuesList[$key]['subject_id'] = $param['subject_id'];
                        $difficultyQuesList[$key]['semester'] = $param['semester'];
                        $difficultyQuesList[$key]['score'] = $param["everyScore_".$i];
                        $difficultyQuesList[$key]['addTime'] = time();
                        $difficultyQuesList[$key]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
                        $difficultyQuesList[$key]['qid'] = $value['q_id'];
                        $difficultyQuesList[$key]['q_type'] = $i;
                        $difficultyQuesList[$key]['sort'] = $param["sort_".$i];
                        $difficultyQuesList[$key]['title'] = $param["title"];
                        $difficultyQuesList[$key]['type'] = 2;
                    }
                    if($param["difficulty_".$i] <= count($difficultydata)){
                        $difficultyList = array_rand($difficultyQuesList,$param["difficulty_".$i]);
                        if($param["difficulty_".$i] > 1){
                            for($j=0; $j< $param["difficulty_".$i]; $j++){
                                $questionList[$k] = $difficultyQuesList[$difficultyList[$j]];
                                $k++;
                            }
                        }else{
                            $questionList[$k] = $difficultyQuesList[$difficultyList];
                            $k++;
                        }
                    }else{
                        return jsonMsg("你所选的".$typeList[$i-1]['questype']."困难类型题目超出数据库数量",1);
                    }
                }
            }
        }
        $data['title'] = $param['title']; 
        $data['grade_id'] = $param['grade_id'];
        $data['subject_id'] = $param['subject_id'];
        $data['semester'] = $param['semester'];
        $data['addTime'] = time();
        $data['testTime'] = $param['testTime'];
        $data['user_id'] = $_SESSION['think']['manageinfo']['uid'];
        $data['score'] = $param['score'];
        $data['type'] = 2;
        $res = Db::name('test_paper')->insertGetId($data);
        if($res){
            foreach ($questionList as $key => $value) {
                $questionList[$key]['pid'] = $res;
            }
            $testpaper = new TestPaper;
            $result = $testpaper->saveAll($questionList);
            if($result){
                return jsonMsg("添加成功",0);
            }else{
                return jsonMsg("题目添加失败",1);
            }
        }else{
            return jsonMsg("试卷添加失败",1);
        }
    }
    //试卷展示
    public function showpaper(Request $request){
        $param = $request->param();
        $questionList = Db::name('test_paper')
                            ->alias('tp')
                            ->field('tp.score,tp.title,q.*,qt.questype,tp.sort')
                            ->join('guard_question q','tp.qid = q.q_id')
                            ->join('guard_questype qt','qt.quest_id = q.q_type')
                            ->where('tp.pid = '.$param['id'].' and tp.q_type < 7')
                            ->order('tp.sort')
                            ->group('q.q_id')
                            ->select();
        //获取题型列表
        $questionTypeList = Db::name('questype')
                            ->alias('q')
                            ->join('guard_test_paper tp','tp.q_type = q.quest_id','LEFT')
                            ->field('q.*,tp.sort')
                            ->where('tp.pid = '.$param['id'])
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
                    $q_stem = preg_replace('/<p>/','',$value['q_stem']);
                    $q_stem1  = preg_replace('/<\/p>/','',$q_stem);
                    $newList[$i]['son'][$k]['q_stem'] = $q_stem1;
                    $newList[$i]['son'][$k]['q_select'] = htmlspecialchars_decode($value['q_select']);
                    $newList[$i]['son'][$k]['q_describe'] = $value['q_describe'];
                    $newList[$i]['son'][$k]['q_answer'] = $value['q_answer'];
                    $newList[$i]['son'][$k]['question_sort'] = "第".$bit[$k]."题";
                    $k++;
                }
            }
        }
        //获取题帽题
        $questionrowsList = Db::name('test_paper')
                                ->alias('tp')
                                ->join('guard_questionrows q','q.qr_id = tp.qid')
                                ->join('guard_question qt','qt.q_parent = q.qr_id','RIGHT')
                                ->where('tp.pid = '.$param['id'].' and tp.q_type >= 7')
                                ->field('q.qr_question,qt.*')
                                ->group('qt.q_id')
                                ->select();
        $selectQuestList = Db::name('test_paper')
                            ->where('pid = '.$param['id'].' and q_type >= 7')
                            ->field('qid')
                            ->select();
        $newQuestionrowsList = array();
        for($i=0; $i<count($selectQuestList); $i++ ){
            $m=0;
            foreach ($questionrowsList as $key => $value) {
                if($value['q_parent'] == $selectQuestList[$i]['qid']){
                    $newQuestionrowsList[$i]['qr_question'] = $value['qr_question'];
                    $newQuestionrowsList[$i]['question_sort'] = $bit[$i+$k];
                    $newQuestionrowsList[$i]['son'][$m]['q_stem'] = $value['q_stem'];
                    $newQuestionrowsList[$i]['son'][$m]['q_describe'] = $value['q_describe'];
                    $newQuestionrowsList[$i]['son'][$m]['q_answer'] = $value['q_answer'];
                    $newQuestionrowsList[$i]['son'][$m]['q_select'] = htmlspecialchars_decode($value['q_select']);
                    $m++;
                }
            }
        }
        $testPaperList = Db::name('test_paper')->where('id='.$param['id'])->select();
        $this->assign('testPaperList', $testPaperList);
        $this->assign('newQuestionrowsList', $newQuestionrowsList);
        $this->assign('newList', $newList);
        $this->assign('bit', $bit);
        return view('/testPaper/showpaper',['title'=>"试卷查看"]);
    }

    //随机试卷修改页面
    public function updateRandTestPaper(Request $request){
        $param = $request->param();

        //获取年列表
        $gradeList = Db::name('grade')->select();
        $this->assign("gradeList",$gradeList);
        //获取科目列表
        $subjectList = Db::name('subject')->select();
        $this->assign("subjectList",$subjectList);
        //获取题型列表
        $questionTypeList = Db::name('questype')->select();
        //获取试卷信息
        $testPaperList = Db::name('test_paper')->where('id='.$param['id'])->find();
        $this->assign("testPaperList",$testPaperList);

        //获取试卷添加题目信息
        $list = array();
        $questionList = Db::name("test_paper")
                            ->alias('tp')
                            ->join('guard_question q',"q.q_id = tp.qid")
                            ->where("tp.pid = ".$param['id'])
                            ->field("tp.*,q.q_level")
                            ->select();
        for ($i=0; $i <count($questionTypeList) ; $i++) {
            $m = 0;
            $k = 0;
            $y = 0;
            $n = 0;
            foreach ($questionList as $key => $value) {
                if($value['q_type'] == $questionTypeList[$i]['quest_id']){
                    $questionTypeList[$i]['score'] =$value['score'];
                    $questionTypeList[$i]['sort'] =$value['sort'];
                    $m++;
                    $questionTypeList[$i]['count'] = $m;
                    if($value['q_level'] == 1){
                        $k++;
                        $questionTypeList[$i]['easy_num'] = $k;
                    }
                    if($value['q_level'] == 2){
                        $y++;
                        $questionTypeList[$i]['mid_num'] = $y;
                    } 
                    if($value['q_level'] == 3){
                        $n++;
                        $questionTypeList[$i]['difficulty_num'] =$n;
                    }
                }
            }
        }
        $this->assign("questionTypeList",$questionTypeList);
        return view('/testPaper/updateRandTestPaper',['title'=>"修改随机试卷"]);
    }
    //执行修改随机试卷
    public function doUpdateRandTestPaper(Request $request){
        $param = $request->param();
        $data['title'] = $param['title']; 
        $data['grade_id'] = $param['grade_id'];
        $data['subject_id'] = $param['subject_id'];
        $data['semester'] = $param['semester'];
        $data['updateTime'] = time();
        $data['user_id'] = $_SESSION['think']['manageinfo']['uid'];
        $data['score'] = $param['score'];
        $data['type'] = 2;
        $data['testTime'] = $param['testTime'];
        $res = Db::name('test_paper')->where('id='.$param['id'])->update($data);
        if($res){
            $typeList = Db::name('questype')->select();
            $questionList = array();
            $k = 0;
            for ($i=1; $i <= count($typeList); $i++) {
                $where['q_gradeid'] = $param['grade_id'];
                $where['q_subjectid'] = $param['subject_id'];
                $where['q_seme'] = $param['semester'];
                $where['q_type'] = $i;
                if(!empty($param["num_".$i]) && !empty($param["everyScore_".$i]) && !empty($param["sort_".$i])){
                    if(!empty($param["easy_".$i])){
                        $where['q_level'] = 1;
                        $easydata = Db::name('question')
                                        ->where($where)
                                        ->select();
                        $easyQuesList = array();
                        foreach ($easydata as $key => $value) {
                            $easyQuesList[$key]['grade_id'] = $param['grade_id'];
                            $easyQuesList[$key]['subject_id'] = $param['subject_id'];
                            $easyQuesList[$key]['semester'] = $param['semester'];
                            $easyQuesList[$key]['score'] = $param["everyScore_".$i];
                            $easyQuesList[$key]['addTime'] = time();
                            $easyQuesList[$key]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
                            $easyQuesList[$key]['qid'] = $value['q_id'];
                            $easyQuesList[$key]['q_type'] = $i;
                            $easyQuesList[$key]['sort'] = $param["sort_".$i];
                            $easyQuesList[$key]['title'] = $param["title"];
                            $easyQuesList[$key]['type'] = 2;
                            $easyQuesList[$key]['pid'] = $param['id'];
                            $easyQuesList[$key]['updateTime'] = time();
                        }
                        if($param["easy_".$i] <= count($easydata)){
                            $easyList = array_rand($easyQuesList,$param["easy_".$i]);
                            if($param["easy_".$i] > 1){
                                for($j=0; $j< $param["easy_".$i]; $j++){
                                    $questionList[$k] = $easyQuesList[$easyList[$j]];
                                    $k++;
                                }
                            }else{
                                $questionList[$k] = $easyQuesList[$easyList];
                                $k++;
                            }
                        }else{
                            return jsonMsg("你所选的".$typeList[$i-1]['questype']."简单类型题目超出数据库数量",1);
                        }
                    }
                    if(!empty($param["mid_".$i])){
                        $where['q_level'] = 2;
                        $middata = Db::name('question')
                                        ->where($where)
                                        ->select();
                        $midQuesList = array();
                        foreach ($middata as $key => $value) {
                            $midQuesList[$key]['grade_id'] = $param['grade_id'];
                            $midQuesList[$key]['subject_id'] = $param['subject_id'];
                            $midQuesList[$key]['semester'] = $param['semester'];
                            $midQuesList[$key]['score'] = $param["everyScore_".$i];
                            $midQuesList[$key]['addTime'] = time();
                            $midQuesList[$key]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
                            $midQuesList[$key]['qid'] = $value['q_id'];
                            $midQuesList[$key]['q_type'] = $i;
                            $midQuesList[$key]['sort'] = $param["sort_".$i];
                            $midQuesList[$key]['title'] = $param["title"];
                            $midQuesList[$key]['type'] = 2;
                            $midQuesList[$key]['pid'] = $param['id'];
                            $midQuesList[$key]['updateTime'] = time();
                        }
                        if($param["mid_".$i] <= count($middata)){
                            $midList = array_rand($midQuesList,$param["mid_".$i]);
                            if($param["mid_".$i] > 1){
                                for($j=0; $j< $param["mid_".$i]; $j++){
                                    $questionList[$k] = $midQuesList[$midList[$j]];
                                    $k++;
                                }
                            }else{
                                $questionList[$k] = $midQuesList[$midList];
                                $k++;
                            }
                        }else{
                            return jsonMsg("你所选的".$typeList[$i-1]['questype']."中等类型题目超出数据库数量",1);
                        }
                    }
                    if(!empty($param["difficulty_".$i])){
                        $where['q_level'] = 3;
                        $difficultydata = Db::name('question')
                                        ->where($where)
                                        ->select();
                        $difficultyQuesList = array();
                        foreach ($difficultydata as $key => $value) {
                            $difficultyQuesList[$key]['grade_id'] = $param['grade_id'];
                            $difficultyQuesList[$key]['subject_id'] = $param['subject_id'];
                            $difficultyQuesList[$key]['semester'] = $param['semester'];
                            $difficultyQuesList[$key]['score'] = $param["everyScore_".$i];
                            $difficultyQuesList[$key]['addTime'] = time();
                            $difficultyQuesList[$key]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
                            $difficultyQuesList[$key]['qid'] = $value['q_id'];
                            $difficultyQuesList[$key]['q_type'] = $i;
                            $difficultyQuesList[$key]['sort'] = $param["sort_".$i];
                            $difficultyQuesList[$key]['title'] = $param["title"];
                            $difficultyQuesList[$key]['type'] = 2;
                            $difficultyQuesList[$key]['pid'] = $param['id'];
                            $difficultyQuesList[$key]['updateTime'] = time();
                        }
                        if($param["difficulty_".$i] <= count($difficultydata)){
                            $difficultyList = array_rand($difficultyQuesList,$param["difficulty_".$i]);
                             if($param["difficulty_".$i] > 1){
                                for($j=0; $j< $param["difficulty_".$i]; $j++){
                                    $questionList[$k] = $difficultyQuesList[$difficultyList[$j]];
                                    $k++;
                                }
                            }else{
                                $questionList[$k] = $difficultyQuesList[$difficultyList];
                                $k++;
                            }
                        }else{
                            return jsonMsg("你所选的".$typeList[$i-1]['questype']."困难类型题目超出数据库数量",1);
                        }
                    }
                }
            }
            $idList = Db::name('test_paper')->where('pid='.$param['id'])->field('id')->select();
            $questionIdList = array();
            foreach ($idList as $key => $value) {
                $questionIdList[] = $value['id'];
            }
            $questionIdList = implode(',',$questionIdList);
            $testpaper = new TestPaper;
            $result = $testpaper->saveAll($questionList);
            if($result){
                Db::name('test_paper')->where('id in ('.$questionIdList.')')->delete();
                return jsonMsg("修改成功",0);
            }else{
                return jsonMsg("修改失败",1);
            }
        }else{
            return jsonMsg("修改失败",1);
        }
    }
    //添加手工试卷 选择题目
    public function selQuestion(Request $request){
        $param = $request->param();
        //获取科目
        $where['grade_id'] = $param['grade_id'];
        $where['subject_id'] = $param['subject_id'];
        $where['semester'] = $param['semester'];
        $videoClassid = Db::name('video_class')->where($where)->field('id,name')->find();
        if($videoClassid){
            $videoList = Db::name('video')
                            ->where('kid ='.$videoClassid['id'].' and pid=0')
                            ->field('id,testclass')
                            ->select();

            $this->assign('videoList',$videoList);
        }
        $this->assign('typeId',$param['typeId']);
        $this->assign('grade_id',$param['grade_id']);
        $this->assign('subject_id',$param['subject_id']);
        $this->assign('semester',$param['semester']);
        return view('/testPaper/selQuestion',['title'=>"选择题目"]);
    }
    //选择题目页面
    public function getSelQuestion(Request $request){
        $param = $request->param();
        $limit = $param['limit'];
        $page = $param['page'];
        //获取默认题目
        $questionWhere = array();
        if(array_key_exists('grade_id',$param) && !empty($param['grade_id'])){
            $questionWhere['q.q_gradeid'] = $param['grade_id'];
            $questionrowsWhere['q.qr_grade'] = $param['grade_id'];
        }
        if(array_key_exists('subject_id',$param) && !empty($param['subject_id'])){
            $questionWhere['q.q_subjectid'] = $param['subject_id'];
            $questionrowsWhere['q.qr_subject'] = $param['subject_id'];
        }
        if(array_key_exists('semester',$param) && !empty($param['semester'])){
            $questionWhere['q.q_seme'] = $param['semester'];
            $questionrowsWhere['q.qr_semester'] = $param['semester'];
        }
        //if(array_key_exists('typeId',$param) && !empty($param['typeId']) && $param['typeId'] < 7){
            $questionWhere['q.q_type'] = $param['typeId'];
            $questionrowsWhere['q.qr_type'] = $param['typeId'];
        //}
        if(array_key_exists('keyword',$param) && !empty($param['keyword'])){
            $questionWhere['q.q_stem'] = $param['keyword'];
            $questionrowsWhere['q.qr_question'] = $param['keyword'];
        }
        if(array_key_exists('kid',$param) && !empty($param['kid'])){
            $questionWhere['q.chapter_id'] = $param['kid'];
            $questionrowsWhere['q.qr_chapter'] = $param['kid'];
        }
        if(array_key_exists('chapter_id',$param) && !empty($param['chapter_id'])){
            $questionWhere['q.video_id'] = $param['chapter_id'];
            $questionrowsWhere['q.qr_video'] = $param['chapter_id'];
        }
        $question = new Question;
        if($param['typeId'] < 7){
            $questionWhere['q.q_parent'] = 0;
            $questionList =  $question->alias('q')
                                ->join('guard_questype qt','q.q_type = qt.quest_id')
                                ->field('q.*,qt.questype')
                                ->where($questionWhere)
                                ->page($page,$limit)
                                ->select();
            $count =  Db::name('question')
                        ->alias('q')
                        ->join('guard_questype qt','q.q_type = qt.quest_id')
                        ->field('q.*,qt.questype')
                        ->where($questionWhere)
                        ->count();
            foreach ($questionList as $key => $value) {
                if($value['q_level'] == 1){
                    $questionList[$key]['level'] = '容易';
                }else if($value['q_level'] == 2){
                    $questionList[$key]['level'] = '中等';
                }else{
                    $questionList[$key]['level'] = '困难';
                }
            }
        }else{
            $questionList = Db::name('questionrows')->alias('q')
                                ->join('guard_questype qt','q.qr_type = qt.quest_id')
                                ->field('q.*,qt.questype')
                                ->where($questionrowsWhere)
                                ->page($page,$limit)
                                ->select();
            foreach ($questionList as $key => $value) {
                if($value['qr_level'] == 1){
                    $questionList[$key]['level'] = '容易';
                }else if($value['qr_level'] == 2){
                    $questionList[$key]['level'] = '中等';
                }else{
                    $questionList[$key]['level'] = '困难';
                }
                $questionList[$key]['q_stem'] = $value['qr_question'];
                $questionList[$key]['q_id'] = $value['qr_id'];
            }
            $count = Db::name('questionrows')
                        ->alias('q')
                        ->join('guard_questype qt','q.qr_type = qt.quest_id')
                        ->field('q.*,qt.questype')
                        ->where($questionrowsWhere)
                        ->page($page,$limit)
                        ->count();
        }
        
        if($questionList){
            $res = array(
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$count,
                    'data'=>$questionList
                );
            echo json_encode($res);
        }else{
            jsonMsg("暂无数据",1);
        }
    }
    //二级联动 选择章节
    public function selVideoInfo(Request $request){
        $param = $request->param();
        //根据video_id 获取章节信息
        $sectionList = Db::name('video')->where('pid='.$param['video_id'])->field('id,testclass,part')->select();
        if(!empty($sectionList) && $sectionList[0]['part'] == 2){
            return jsonMsg('获取成功',0,$sectionList);
        }else if(!empty($sectionList) && $sectionList[0]['part'] == 1){
             $videoClassid= array();
            foreach ($sectionList as $key => $value) {
                $videoClassid[] = $value['id'];
            }
            $videoList = Db::name('video')->where('pid','in',implode(',',$videoClassid))->select();
            if($videoList){
                return jsonMsg('获取成功',0,$videoList);
            }
        }else{
            return jsonMsg('获取失败',1);
        }
    }
    //获取选择题目的id
    public function getCheckedQuestion(Request $request){
        $param = $request ->param();
        if(!empty($param['checkedId'])){
            $questionList = $param['checkedId'];
            $idList = array();
            foreach ($questionList as $key => $value) {
                $idList[$key] = $value['q_id'];
            }
            $count = count($idList);
            return jsonMsg('选择成功',0,$idList,$count);
        }else{
            return jsonMsg('选择不能为空，请重新选择',1);
        }
    }
    //查看选择的题目
    public function showSelectedQuestion(Request $request){
        $param = $request->param();
        $bit = array("一", "二", "三", "四", "五", "六", "七", "八", "九", "十");
        if($param['typeId'] < 7){
            $questionList = Db::name('question')
                                ->alias('q')
                                ->join('guard_questype qt','qt.quest_id = q.q_type')
                                ->where("q.q_id",'in',$param['selectQuestList'])
                                ->field('q.*,qt.questype')
                                ->select();
            $questionTypeList = Db::name('questype')->select();

            $newList = array();
            for ($i=0; $i < count($questionTypeList); $i++) { 
                $k = 0;
                foreach ($questionList as $key => $value) {
                    if($value['q_type'] ==  $questionTypeList[$i]['quest_id']){
                        $newList[$k]['q_stem'] = $value['q_stem'];
                        $newList[$k]['q_select'] = htmlspecialchars_decode($value['q_select']);
                        $newList[$k]['q_describe'] = $value['q_describe'];
                        $newList[$k]['q_answer'] = $value['q_answer'];
                        $newList[$k]['question_sort'] = "第".$bit[$k]."题";
                        $k++;
                    }
                }
            }
        }else{
            // $questionList = Db::name('question')
            //                     ->alias('qt')
            //                     ->join('guard_questionrows q','qt.q_parent = q.qr_id','LEFT')
            //                     ->where("qt.q_parent",'in',$param['selectQuestList'])
            //                     ->field('q.qr_question,qt.*')
            //                     ->select();
            // $newList = array();
            // $idList = explode(',',$param['selectQuestList']);
            // for($i=0; $i<count($idList); $i++ ){
            //     $m=0;
            //     foreach ($questionList as $key => $value) {
            //         if($value['q_parent'] == $idList[$i]){
            //             $newList[$i]['qr_question'] = $value['qr_question'];
            //             $newList[$i]['question_sort'] = $bit[$i];
            //             $newList[$i]['son'][$m]['q_stem'] = $value['q_stem'];
            //             $newList[$i]['son'][$m]['q_describe'] = $value['q_describe'];
            //             $newList[$i]['son'][$m]['q_answer'] = $value['q_answer'];
            //             $newList[$i]['son'][$m]['q_select'] = htmlspecialchars_decode($value['q_select']);
            //             $m++;
            //         }
            //     }
            // }
            $questionrows = Db::name('questionrows')->field('qr_question')->where('qr_id ='.$param['selectQuestList'])->select();
            $questionList = Db::name('question')
                                ->where("q_parent",'in',$param['selectQuestList'])
                                ->select();
            $newList = $questionrows;
            $idList = explode(',',$param['selectQuestList']);
            for($i=0; $i<count($idList); $i++ ){
                foreach ($questionList as $key => $value) {
                    if($value['q_parent'] == $idList[$i]){
                        $newList[0]['son'][$key]['question_sort'] = $bit[$i];
                        $newList[0]['son'][$key]['q_stem'] = htmlspecialchars_decode($value['q_stem']);
                        $newList[0]['son'][$key]['q_describe'] = $value['q_describe'];
                        $newList[0]['son'][$key]['q_answer'] = $value['q_answer'];
                        $newList[0]['son'][$key]['q_select'] = htmlspecialchars_decode($value['q_select']);
                    }
                }
            }
        }
        $this->assign('newList', $newList);
        $this->assign('typeId', $param['typeId']);
        $this->assign('bit', $bit);
        return view('/testPaper/showSelectedQuestions',['title'=>"题目查看"]);
        
    }
    //添加手工试卷
    public function doMakePaper(Request $request){
        $param = $request->param();
        //获取题型列表
        $typeList = Db::name('questype')->select();
        $questionList = array();
        $k = 0;
        $question = new Question;
        for ($i=1; $i <= count($typeList); $i++) {
            if(!empty($param["num_".$i]) && !empty($param["everyScore_".$i]) && !empty($param["sort_".$i]) && !empty($param["select_".$i]) && $i < 7){
                //获取当前类型 所选择的题目
                $selectedQuestionLis = array();
                $selectedQuestionList = $question ->alias('q')
                                            ->join('guard_questype qt','qt.quest_id = q.q_type')
                                            ->where("q.q_id",'in',$param['selectQuest_'.$i])
                                            ->field('q.*,qt.questype')
                                            ->select();
                foreach ($selectedQuestionList as $key => $value) {
                    $questionList[$k]['grade_id'] = $param['grade_id'];
                    $questionList[$k]['subject_id'] = $param['subject_id'];
                    $questionList[$k]['semester'] = $param['semester'];
                    $questionList[$k]['score'] = $param["everyScore_".$i];
                    $questionList[$k]['addTime'] = time();
                    $questionList[$k]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
                    $questionList[$k]['qid'] = $value['q_id'];
                    $questionList[$k]['q_type'] = $i;
                    $questionList[$k]['sort'] = $param["sort_".$i];
                    $questionList[$k]['title'] = $param["title"];
                    $questionList[$k]['type'] = 1;
                    $k++;
                }
            }else{
                //添加题冒题
                //获取当前类型 所选择的题目
                $selectedQuestionrows = array();
                $selectedQuestionrows = Db::name('questionrows')->where('qr_id','in',$param['selectQuest_'.$i])->select();
                foreach ($selectedQuestionrows as $key => $value) {
                    $questionList[$k]['grade_id'] = $param['grade_id'];
                    $questionList[$k]['subject_id'] = $param['subject_id'];
                    $questionList[$k]['semester'] = $param['semester'];
                    $questionList[$k]['score'] = $param["everyScore_".$i];
                    $questionList[$k]['addTime'] = time();
                    $questionList[$k]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
                    $questionList[$k]['qid'] = $value['qr_id'];
                    $questionList[$k]['q_type'] = $value['qr_type'];
                    $questionList[$k]['sort'] = $param["sort_".$i];
                    $questionList[$k]['title'] = $param["title"];
                    $questionList[$k]['type'] = 1;
                    // $questionList[$k]['pid'] = $param["id"];
                    $k++;
                }
            }
        }
        //添加题帽题
        // if(!empty($param["num"]) && !empty($param["everyScore"]) && !empty($param["sort"]) && !empty($param["select"])){
        //     //获取当前类型 所选择的题目
        //     $selectedQuestionrows = array();
        //     $selectedQuestionrows = Db::name('questionrows')->where('qr_id','in',$param['selectQuest'])->select();
        //     foreach ($selectedQuestionrows as $key => $value) {
        //         $questionList[$k]['grade_id'] = $param['grade_id'];
        //         $questionList[$k]['subject_id'] = $param['subject_id'];
        //         $questionList[$k]['semester'] = $param['semester'];
        //         $questionList[$k]['score'] = $param["everyScore"];
        //         $questionList[$k]['addTime'] = time();
        //         $questionList[$k]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
        //         $questionList[$k]['qid'] = $value['qr_id'];
        //         $questionList[$k]['q_type'] = 0;
        //         $questionList[$k]['sort'] = $param["sort"];
        //         $questionList[$k]['title'] = $param["title"];
        //         $questionList[$k]['type'] = 1;
        //         $k++;
        //     }
        // }
        $data['title'] = $param['title']; 
        $data['grade_id'] = $param['grade_id'];
        $data['subject_id'] = $param['subject_id'];
        $data['semester'] = $param['semester'];
        if(!empty($param['students'])){
            $data['students'] = $param['students'];
            $data['is_all'] = 2;
        }else{
            $data['is_all'] = 1;
        }
        $data['addTime'] = time();
        $data['user_id'] = $_SESSION['think']['manageinfo']['uid'];
        $data['score'] = $param['score'];
        $data['testTime'] = $param['testTime'];
        $data['type'] = 1;
        $res = Db::name('test_paper')->insertGetId($data);
        if($res){
            foreach ($questionList as $key => $value) {
                $questionList[$key]['pid'] = $res;
            }
            $testpaper = new TestPaper;
            $result = $testpaper->saveAll($questionList);
            if($result){
                return jsonMsg("添加成功",0);
            }else{
                return jsonMsg("试卷添加失败",1);
            }
        }else{
            return jsonMsg("试卷添加失败",1);
        }
    }
    //手工试卷修改页面
    public function updateMakeTestPaper(Request $request){
        $param = $request->param();

        //获取年列表
        $gradeList = Db::name('grade')->select();
        $this->assign("gradeList",$gradeList);
        //获取科目列表
        $subjectList = Db::name('subject')->select();
        $this->assign("subjectList",$subjectList);
        //获取题型列表
        $questionTypeList = Db::name('questype')->select();
        //获取试卷信息
        $testPaperList = Db::name('test_paper')->where('id='.$param['id'])->find();
        $this->assign("testPaperList",$testPaperList);

        //获取试卷添加题目信息
        $list = array();
        $questionList = Db::name("test_paper")
                            ->alias('tp')
                            ->join('guard_question q',"q.q_id = tp.qid")
                            ->where("tp.pid = ".$param['id'])
                            ->field("tp.*,q.q_level")
                            ->select();
        for ($i=0; $i < count($questionTypeList) ; $i++){
            $m = 0;
            $k = 0; 
            $idList = array();
            $questionTypeList[$i]['score'] =0;
            $questionTypeList[$i]['sort'] =0;
            $questionTypeList[$i]['count'] = 0;
            $questionTypeList[$i]['idList']=0;
            foreach ($questionList as $key => $value) {
                if($value['q_type'] == $questionTypeList[$i]['quest_id']){
                    $questionTypeList[$i]['score'] =$value['score'];
                    $questionTypeList[$i]['sort'] =$value['sort'];
                    $m++;
                    $questionTypeList[$i]['count'] = $m;
                    $idList[] = $value['qid'];
                    $questionTypeList[$i]['idList'] = implode(',',$idList);
                }
            }
        }
        //题帽题信息
        $questionrowsList = array();
        $questionrowsIdList = array();
        foreach ($questionList as $key => $value) {
            if($value['q_type'] == 0){
                $questionrowsList['score']= $value['score'];
                $questionrowsList['sort']= $value['sort'];
                $questionrowsIdList[] = $value['qid'];
                $questionrowsList['count'] = count($questionrowsIdList);
                $questionrowsList['idList'] = implode(',',$questionrowsIdList);
            }
        }
        $this->assign("questionTypeList",$questionTypeList);
        $this->assign("questionrowsList",$questionrowsList);
        return view('/testPaper/updateMakeTestPaper',['title'=>"修改手工试卷"]);
    }
    //执行修改手工试卷
    public function doUpdateMakeTestPaper(Request $request){
        $param = $request->param();
        //获取题型列表
        $typeList = Db::name('questype')->select();
        $questionList = array();
        $k = 0;
        $question = new Question;
        for ($i=1; $i <= count($typeList); $i++) {
            if(!empty($param["num_".$i]) && !empty($param["everyScore_".$i]) && !empty($param["sort_".$i]) && !empty($param["select_".$i]) && $i < 7){
                //获取当前类型 所选择的题目
                $selectedQuestionLis = array();
                $selectedQuestionList = $question ->alias('q')
                                            ->join('guard_questype qt','qt.quest_id = q.q_type')
                                            ->where("q.q_id",'in',$param['selectQuest_'.$i])
                                            ->field('q.*,qt.questype')
                                            ->select();
                foreach ($selectedQuestionList as $key => $value) {
                    $questionList[$k]['grade_id'] = $param['grade_id'];
                    $questionList[$k]['subject_id'] = $param['subject_id'];
                    $questionList[$k]['semester'] = $param['semester'];
                    $questionList[$k]['score'] = $param["everyScore_".$i];
                    $questionList[$k]['addTime'] = time();
                    $questionList[$k]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
                    $questionList[$k]['qid'] = $value['q_id'];
                    $questionList[$k]['q_type'] = $i;
                    $questionList[$k]['sort'] = $param["sort_".$i];
                    $questionList[$k]['title'] = $param["title"];
                    $questionList[$k]['type'] = 1;
                    $questionList[$k]['pid'] = $param["id"];
                    $k++;
                }
            }elseif(!empty($param["num_".$i]) && !empty($param["everyScore_".$i]) && !empty($param["sort_".$i]) && !empty($param["select_".$i]) && $i >= 7){
                //添加题冒题
                //获取当前类型 所选择的题目
                $selectedQuestionrows = array();
                $selectedQuestionrows = Db::name('questionrows')->where('qr_id','in',$param['selectQuest_'.$i])->select();
                foreach ($selectedQuestionrows as $key => $value) {
                    $questionList[$k]['grade_id'] = $param['grade_id'];
                    $questionList[$k]['subject_id'] = $param['subject_id'];
                    $questionList[$k]['semester'] = $param['semester'];
                    $questionList[$k]['score'] = $param["everyScore_".$i];
                    $questionList[$k]['addTime'] = time();
                    $questionList[$k]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
                    $questionList[$k]['qid'] = $value['qr_id'];
                    $questionList[$k]['q_type'] = $value['qr_type'];
                    $questionList[$k]['sort'] = $param["sort_".$i];
                    $questionList[$k]['title'] = $param["title"];
                    $questionList[$k]['type'] = 1;
                    $questionList[$k]['pid'] = $param["id"];
                    $k++;
                }
            }
        }
        //添加题帽题
        // if(!empty($param["num"]) && !empty($param["everyScore"]) && !empty($param["sort"]) && !empty($param["select"])){
        //     //获取当前类型 所选择的题目
        //     $selectedQuestionrows = array();
        //     $selectedQuestionrows = Db::name('questionrows')->where('qr_id','in',$param['selectQuest'])->select();
        //     foreach ($selectedQuestionrows as $key => $value) {
        //         $questionList[$k]['grade_id'] = $param['grade_id'];
        //         $questionList[$k]['subject_id'] = $param['subject_id'];
        //         $questionList[$k]['semester'] = $param['semester'];
        //         $questionList[$k]['score'] = $param["everyScore"];
        //         $questionList[$k]['addTime'] = time();
        //         $questionList[$k]['user_id'] = $_SESSION['think']['manageinfo']['uid'];
        //         $questionList[$k]['qid'] = $value['qr_id'];
        //         $questionList[$k]['q_type'] = $value['qr_type'];
        //         $questionList[$k]['sort'] = $param["sort"];
        //         $questionList[$k]['title'] = $param["title"];
        //         $questionList[$k]['type'] = 1;
        //         $questionList[$k]['pid'] = $param["id"];
        //         $k++;
        //     }
        // }
        $data['title'] = $param['title']; 
        $data['grade_id'] = $param['grade_id'];
        $data['subject_id'] = $param['subject_id'];
        $data['semester'] = $param['semester'];
        $data['testTime'] = $param['testTime'];
        $data['updateTime'] = time();
        if(!empty($param['students'])){
            $data['students'] = $param['students'];
            $data['is_all'] = 2;
        }else{
            $data['is_all'] = 1;
        }
        $data['user_id'] = $_SESSION['think']['manageinfo']['uid'];
        $data['score'] = $param['score'];
        $data['type'] = 1;
        $res = Db::name('test_paper')->where('id='.$param['id'])->update($data);
        if($res){
            $idList = Db::name('test_paper')->where('pid='.$param['id'])->field('id')->select();
            $questionIdList = array();
            foreach ($idList as $key => $value) {
                $questionIdList[] = $value['id'];
            }
            $questionIdList = implode(',',$questionIdList);
            $testpaper = new TestPaper;
            $result = $testpaper->saveAll($questionList);
            if($result){
                Db::name('test_paper')->where('id in ('.$questionIdList.')')->delete();
                return jsonMsg("修改成功",0);
            }else{
                return jsonMsg("试卷修改失败",1);
            }
        }else{
            return jsonMsg("试卷修改失败",1);
        }
    }
    /**
    *@author  韩春雷 2019/5/24
    *试卷禁用
    */
    public function forbidden(Request $request)
    {
        $param=$request->param();
        $where['id'] = $param['id'];
        $data['status'] = 2;
        $info=new testPaper();
        $res=$info->where($where)->update($data);
        if($res)
        {
            return jsonMsg('修改成功',0);
        }
        else
        {
            return jsonMsg('修改失败',1);
        }
    }
    //学员分配
    public function studentAssign(Request $request){
        $param = input();
        $students = isset($param['students'])?$param['students']:'';
        $is_all = isset($param['is_all'])?$param['is_all']:'';

        $this->assign('students',$students);
        $this->assign('is_all',$is_all);
        return view('/testPaper/studentsAssign',['title'=>"学员分配"]);
    }
    //获取学员信息
    public function getStudentsList(){
        $param = input();
        $students = isset($param['students'])?$param['students']:'';
        if(!empty($students)){
            $count = count(explode(',',$students));
            $studentsArr = explode(',',$students);
            //获取学员信息
            $studentsList = Db::name('person')->alias('p')
                                              ->field('p.id,nickName,g.grade,phone')
                                              ->join('grade g','g.id = p.grade_id','left')
                                              ->where('p.id','in',$studentsArr)
                                              ->select();
            if($studentsList){
                $res = array(
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$count,
                    'data'=>$studentsList
                );
                echo json_encode($res);
            }else{
                jsonMsg("暂无数据",1);
            }
        }else{
            jsonMsg("暂无数据,请添加学员",1);
        }
    }
    //搜索学员
    public function getStudentInfo(){
        $param = input();
        $students = isset($param['students'])?$param['students']:'';
        $phone = isset($param['phone'])?$param['phone']:'';
        if(!empty($phone)){
            $studentInfo = Db::name('person')->where(['phone'=>$phone])->find();
            //判断学员是否重复
            if(!empty($students)){
                $studentsArr = explode(',',$students);
                if(in_array($studentInfo['id'],$studentsArr)){
                    jsonMsg("该学员已存在",1);
                }
            }
            if($studentInfo){
                $newStudents = $students.','.$studentInfo['id'];
                jsonMsg("success",0,$newStudents);
            }else{
                jsonMsg("该手机号不存在",2);
            }
        }
    }
    //删除学员
    public function delStudent(){
        $param = input();
        $id = isset($param['id'])?$param['id']:'';
        $students = isset($param['students'])?$param['students']:'';

        if(!empty($students)){
            $studentsArr = explode(',',$students);
            foreach ($studentsArr as $key => $value) {
                if($id == $value){
                    unset($studentsArr[$key]);
                }
            }
            $studentStr = implode(',', $studentsArr);
        }
        return jsonMsg('success',0,$studentStr);
    }
}