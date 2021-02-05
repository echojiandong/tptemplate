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
class StudentpaperController extends author{
	//试卷列表列表
    public function studentPaperList(Request $request)
    {
        $getAllTestPaperList = TestPaper::getAllTestPaperList();
        $this->assign('AllTestPaperList',$getAllTestPaperList);
        return view('/studentPaper/studentPaperList',['title'=>"学员提交试卷列表`"]);
    }
    //js根据select框联动获取select选项框中试卷列表与本页面table学员提交试卷列表
    public function getstudentTestPaperList(Request $request)
    {
        $param=$request->param();
        if(!empty($param['grade_id']))
        {
            $where['grade_id'] = $param['grade_id'];
        }
        if(!empty($param['subject_id']))
        {
            $where['subject_id'] = $param['subject_id'];
        }
        if(!empty($param['semester']))
        {
            $where['semester'] = $param['semester'];
        }
        if(!empty($param['type']))
        {
            $where['type'] = $param['type'];
        }
        if(empty($where))
        {
            $getTestPaperList = TestPaper::getAllTestPaperList();//条件联动获取所有试卷列表
        }else{
            $getTestPaperList = TestPaper::getAllTestPaperList($where);//条件联动获取所有试卷列表
        }
        $data['getTestPaperList'] = $getTestPaperList;
        jsonMsg('1','success',$data);
    }
    //js获取试卷列表
    public function getTestPaperList(Request $request)
    {
        $param=$request->param();
        if(!empty($param['grade_id']))
        {
            $whereTp['tp.grade_id'] = $param['grade_id'];
        }
        if(!empty($param['subject_id']))
        {
            $whereTp['tp.subject_id'] = $param['subject_id'];
        }
        if(!empty($param['semester']))
        {
            $whereTp['tp.semester'] = $param['semester'];
        }
        if(!empty($param['type']))
        {
            $whereTp['tp.type'] = $param['type'];
        }
        if(!empty($param['reviewState']) && $param['reviewState'] !=0)
        {
            $whereTp['log.review'] = $param['reviewState'];
        }
        if(!empty($param['title']))
        {
            $whereTp['tp.title'] = array('like','%'.$param['title'].'%');
        }
        if(!empty($param['phone']))
        {
            $whereTp['p.phone'] = array('like','%'.$param['phone'].'%');
        }
        if(!empty($param['t_id']) && $param['t_id'] !=0){
            $whereTp['tp.id'] = (int)$param['t_id'];
        }else{
            $whereTp['tp.pid'] =0;//获取全部试卷列表
        }
        
        $page=$param['page'];
        $limit=$param['limit'];
        if(empty($whereTp))
        {
            $whereTp = '';
        }
            $res = TestPaper::getAllStudentTaperList($whereTp,$page,$limit);

        $getAllStudentTaperList = $res['getAllStudentTaperList'];
        $count = $res['count'];
        if($res){
        	$res = array(
					'code'=>0,
					'msg'=>'',
					'count'=>$count,
					'data'=>$getAllStudentTaperList
				);
        	echo json_encode($res);
        }else{
        	jsonMsg("暂无数据",1);
        }
    }
    //评阅试卷
    public function reviewState(Request $request)
    {
        $param = $request->param();
        $this->assign('res',$param);
        return view('studentPaper/reviewStudentPaper',['title'=>'评阅学员试卷']);
    }
    //获取学员提交的试卷信息进行评阅试卷
    public function getReviewStudentPaperInfo(Request $request)
    {
        $param = $request->param();
        //查询普通试题
        $questionList = Db::name('test_paper')
                            ->alias('tp')
                            ->field('tp.score,tp.title,q.*,qt.questype,tp.sort,log.status,log.status_log,log.fraction,log.answer')
                            ->join('guard_question q','tp.qid = q.q_id')
                            ->join('guard_questype qt','qt.quest_id = q.q_type')
                            ->join('guard_question_log log','log.t_id = tp.pid and log.q_id=q.q_id','LEFT')
                            ->where("tp.pid = ".$param['t_id']." and tp.q_type < 7 and log.id in (select id from guard_question_log as log where log.person_id = ".$param['person_id']." and log.q_id = q.q_id and  log.t_id = tp.pid)")
                            ->order('tp.sort')
                            ->group('q.q_id')
                            ->select();
        //获取题型列表
        $questionTypeList = Db::name('questype')
                            ->alias('q')
                            ->join('guard_test_paper tp','tp.q_type = q.quest_id','LEFT')
                            ->field('q.*,tp.sort')
                            ->where('tp.pid = '.$param['t_id'])
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
                    $newList[$i]['son'][$k]['q_select'] = htmlspecialchars_decode($value['q_select']);
                    $newList[$i]['son'][$k]['q_describe'] = $value['q_describe'];
                    $newList[$i]['son'][$k]['q_answer'] = $value['q_answer'];
                    $newList[$i]['son'][$k]['q_stem_img'] = $value['q_stem_img'];
                    $newList[$i]['son'][$k]['log_status'] = $value['status'];
                    $newList[$i]['son'][$k]['log_status_log'] = $value['status_log'];
                    $newList[$i]['son'][$k]['log_fraction'] = $value['fraction'];
                    $newList[$i]['son'][$k]['log_answer'] = $value['answer'];
                    $newList[$i]['son'][$k]['id'] = $value['q_id'];
                    $newList[$i]['son'][$k]['question_sort'] = "第".$bit[$k]."题";
                    $k++;
                }
            }
        }
        //获取题冒题列表
        $questionrowsList = Db::name('test_paper')
                                        ->alias('tp')
                                        ->join('guard_questionrows q','q.qr_id = tp.qid')
                                        ->join('guard_question qt','qt.q_parent = q.qr_id','RIGHT')
                                        ->join('guard_question_log log','log.t_id = tp.pid and log.q_id=qt.q_id','LEFT')
                                        ->where('tp.pid = '.$param['t_id'].' and tp.q_type >= 7  and log.id in (select id from guard_question_log as log where log.person_id = '.$param['person_id'].' and log.q_id = qt.q_id and  log.t_id = tp.pid)')
                                        ->field('q.qr_question,q.qr_type,q.qr_id,qt.*,log.status,log.status_log,log.fraction,log.answer')
                                        ->group('qt.q_id')
                                        ->select();
        
        $selectQuestList = Db::name('question_log')->alias('log')
                            ->join('guard_question qt','qt.q_id = log.q_id')
                            ->where('log.t_id = '.$param['t_id'].' and log.person_id ='.$param['person_id'].' and  log.type >= 7')
                            ->field('qt.q_parent as q_id')
                            ->group('qt.q_parent')
                            ->select();
        $newQuestionrowsList = array();
        for($i=0; $i<count($selectQuestList); $i++ ){
            $m=0;
            foreach ($questionrowsList as $key => $value) {
                if($value['q_parent'] == $selectQuestList[$i]['q_id']){
                    $newQuestionrowsList[$i]['qr_question'] = $value['qr_question'];
                    $newQuestionrowsList[$i]['question_sort'] = $bit[$i+$k];
                    $newQuestionrowsList[$i]['qr_type'] = $value['qr_type'];
                    $newQuestionrowsList[$i]['son'][$m]['q_stem'] = $value['q_stem'];
                    $newQuestionrowsList[$i]['son'][$m]['q_describe'] = $value['q_describe'];
                    $newQuestionrowsList[$i]['son'][$m]['q_answer'] = $value['q_answer'];
                    $newQuestionrowsList[$i]['son'][$m]['q_select'] = htmlspecialchars_decode($value['q_select']);
                    $newQuestionrowsList[$i]['son'][$m]['log_status'] = $value['status'];
                    $newQuestionrowsList[$i]['son'][$m]['log_status_log'] = $value['status_log'];
                    $newQuestionrowsList[$i]['son'][$m]['log_fraction'] = $value['fraction'];
                    $newQuestionrowsList[$i]['son'][$m]['log_answer'] = $value['answer'];
                    $newQuestionrowsList[$i]['son'][$m]['id'] = $value['q_id'];
                    $m++;
                }
            }
        }
        $data = array();
        $data['newList'] = $newList;    //普通试题列表
        $data['newQuestionrowsList'] = $newQuestionrowsList;   //题帽题
        $data['log_fraction'] = $newList[0]['son'][0]['log_fraction'];//分数
        if(!empty($data)){
            return jsonMsg('success',1,$data,$bit);
        }else{
            return jsonMsg('暂无数据',0);
        }
    }
    public function updateReviewStudentPaperInfo(Request $request)
    {
        $param = $request->param();
        $sort = $param['sort'];
        $where['person_id'] = $param['person_id'];
        $where['t_id'] = $param['t_id'];
        for($i=0;$i<$sort;$i++)
        {
            $q_id = substr($param['data']['judge'.$i],strpos($param['data']['judge'.$i],'-')+1);//对应题目id
            $status = substr($param['data']['judge'.$i],0,strpos($param['data']['judge'.$i],'-'));//true表示答对 false 表示答错
            $res = Db::name('question_log')->alias('log')
                        ->field('log.status,log.fraction,log.review,tp.score,tp.title')
                        ->join('guard_test_paper tp','tp.pid  = log.t_id')
                        ->where('log.person_id ='.$param['person_id'].' and log.t_id ='.$param['t_id'].' and log.q_id='.$q_id)->find();
            if($res['status'] == 0){ //用户第一次答题标记
                if($status){
                    $data['status'] =1;
                    $data['status_log'] =1;
                }else{
                    $data['status'] =2;
                    $data['status_log'] =2;
                }
            }else{
                if($status){
                    $data['status'] =1;
                }else{
                    $data['status'] =2;
                }
            }
            if($res['review'] == 2){
                $fraction['review'] = 1;//1已经评阅  2 未评阅
                $fraction['fraction'] = $res['fraction'] + $res['score'];//用户卷面分增加
                $res = Db::name('question_log')->where($where)->update($fraction);
            }
            $res = Db::name('question_log')->where('q_id ='.$q_id.' and person_id ='.$param['person_id'].' and t_id ='.$param['t_id'])->update($data);
            
        }
        $res = Db::name('test_paper')->field('title')->where('id = '.$param['t_id'])->find();
        $msg = "您参与测试的 ".$res['title']." 教师已经评阅, 可以前往个人中心进行查看结果";
        insertMessage($param['person_id'], '试卷评阅消息', $msg, 2);
        if($res){
            return jsonMsg('success',1,'','');
        }else{
            return jsonMsg('error',0,'','');
        }
    }
}