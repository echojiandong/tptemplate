<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use think\Cache;
use vendor\phpoffic;
use app\manage\model\WeeklyReport;

class WeeklyReportController extends author
{
	public function personStudyList(){
		return view('/weeklyReport/personStudyList',['title'=>"学生信息`"]);
	}

	public function getPersonstudyList(request $request){

		$param = $request->param();

        $page=$param['page'];
        $limit=$param['limit'];
        $where = array();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where['p.nickName|p.phone']=['like',"%".$param['keyword']."%"];
        }
        $data_arr = getWeek_SdateAndEdate(time());   //获取当前周的开始和结束时间

        $timeStart = $data_arr['sdate'];   //开始时间

        $timeEnd = $data_arr['edate'];   //开始时间


        $data = Db::name('person')
        				->alias('p')
        				->field('(select sum(all_watch_time) from guard_video_watch_log where p.id = person_id and time BETWEEN '.$timeStart.' AND '.$timeEnd.') as studyTime,min(fraction) as minFraction,max(fraction) as maxFraction,p.nickName,p.phone,p.id as person_id,s.id as advise_id')
        				->join('question_log ql','p.id = ql.person_id','LEFT')
        				->join('study_advise s','p.id = s.person_id','LEFT')
        				->whereOr(('`ql`.`intime` BETWEEN '.$timeStart.' AND '.$timeEnd.'  OR (`s`.`weekly_report_time` BETWEEN '.$timeStart.' AND '.$timeEnd.')'))
        				->where($where)
        				->page($page,$limit)
        				->group('p.id')
        				->order('addtime desc')
        				->select();
        $count = Db::name('person')
        				->alias('p')
        				->field('(select sum(all_watch_time) from guard_video_watch_log where p.id = person_id and time BETWEEN '.$timeStart.' AND '.$timeEnd.') as studyTime,min(fraction) as minFraction,max(fraction) as maxFraction,p.nickName,p.phone,s.id as advise_id,p.id as person_id')
        				->join('question_log ql','p.id = ql.person_id','LEFT')
        				->join('study_advise s','p.id = s.person_id','LEFT')
        				->whereOr(('`ql`.`intime` BETWEEN '.$timeStart.' AND '.$timeEnd.'  OR (`s`.`weekly_report_time` BETWEEN '.$timeStart.' AND '.$timeEnd.')'))
        				->where($where)
        				->group('p.id')
        				->order('addtime desc')
        				->count();
        				
        foreach ($data as $key => $value) {
        	if(!empty($value['advise_id'])){
        		$data[$key]['is_have'] = '老师建议已提交';
        	}else{
        		$data[$key]['is_have'] = '老师建议暂未提交';
        	}
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

	//学习详情页面
	public function studyInfo(){
		$param  = input();

        $time = isset($param['time']) ? $param['time'] : time();

		$data_arr = getWeek_SdateAndEdate($time);   //获取当前周的开始和结束时间

        $timeStart = $data_arr['sdate'];   //开始时间

        $timeEnd = $data_arr['edate'];   //开始时间

        //学生最高分 最低分 平均分 学习总时长
		$data = Db::name('person')
        				->alias('p')
        				->field('(select sum(all_watch_time) from guard_video_watch_log where p.id = person_id and time BETWEEN '.$timeStart.' AND '.$timeEnd.') as studyTime,min(fraction) as minFraction,max(fraction) as maxFraction,avg(fraction) as avgFraction,p.nickName,p.phone,p.id as person_id,s.id as advise_id')
        				->join('question_log ql','p.id = ql.person_id','LEFT')
        				->join('study_advise s','p.id = s.person_id','LEFT')
        				->whereOr(('`ql`.`intime` BETWEEN '.$timeStart.' AND '.$timeEnd.'  OR `s`.`weekly_report_time` BETWEEN '.$timeStart.' AND '.$timeEnd))
        				->where(['p.id'=>$param['id']])
        				->group('p.id')
        				->order('addtime desc')
        				->find();
        //学生观看时长
        $subject_id = isset($param['subject_id']) ? $where['subject_id'] = $param['subject_id'] : $where['subject_id'] = 1;

        $watchVideoList = Db::name('video_watch_log')
        					->field('sum(all_watch_time) as studyTime,from_unixtime(time,"%w") as time')
        					->where('time BETWEEN '.$timeStart.' AND '.$timeEnd.' and person_id='.$param['id'])
        					->where($where)
        					->group('from_unixtime(time,"%w")')
        					->select();

        //学生测试平均分
        $testList = Db::name('question_log')
        					->alias('ql')
        					->field('avg(fraction) as fraction,from_unixtime(intime,"%w") as time')
        					->join('guard_test_paper t','t.id = ql.t_id')
        					->where('intime BETWEEN '.$timeStart.' AND '.$timeEnd.' and person_id='.$param['id'])
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
        $personInfo = Db::name('person')->where(['id'=>$param['id']])->find();
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
        					->where(['grade_id'=>$personInfo['grade_id'],'type'=>2,'person_id'=>$param['id'],'weekly_report_time'=>array('between',''.$timeStart.','.$timeEnd.'')])
        					->find();
        $datalist['myChart'] = json_encode($myChart); 
        $datalist['testDataList'] = json_encode($testDataList);
        $this->assign('data',$data);
        $this->assign('id',$param['id']);
        $this->assign('time',$time);
        $this->assign('datalist',$datalist);
        // $this->assign('testDataList',json_encode($testDataList));
        $this->assign('defaultAdvise',$defaultAdvise);
        $this->assign('teacherAdvise',$teacherAdvise);
        return view('/weeklyReport/studyInfo',['title'=>"学习详情`"]);
	}

	//折线图数据
	public function myChartDate(){
		$param  = input();

		$data_arr = getWeek_SdateAndEdate($param['time']);   //获取当前周的开始和结束时间

        $timeStart = $data_arr['sdate'];   //开始时间

        $timeEnd = $data_arr['edate'];   //开始时间
		 //学生观看时长
        $subject_id = isset($param['subject_id']) ? $where['subject_id'] = $param['subject_id'] : $where['subject_id'] = 1;

        $watchVideoList = Db::name('video_watch_log')
        					->field('sum(all_watch_time) as studyTime,from_unixtime(time,"%w") as time')
        					->where('time BETWEEN '.$timeStart.' AND '.$timeEnd.' and person_id='.$param['id'])
        					->where($where)
        					->group('from_unixtime(time,"%w")')
        					->select();

        //学生测试平均分
        $testList = Db::name('question_log')
        					->alias('ql')
        					->field('avg(fraction) as fraction,from_unixtime(intime,"%w") as time')
        					->join('guard_test_paper t','t.id = ql.t_id')
        					->where('intime BETWEEN '.$timeStart.' AND '.$timeEnd.' and person_id='.$param['id'])
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
        for($j=0; $j < 8; $j++){
        	$testDataList[$j] = 0;
        	foreach ($testList as $key => $value) {
        		if((int)$value['time'] - 1 == $j){
        			$testDataList[$j] = (int)ceil($value['fraction']);
        		}
        	}
        }
        $datalist = array();
        $myChart = $myChart;
        $testDataList = $testDataList;

        jsonMsg('success',1,$myChart,$testDataList);
	}


    //老师建议提交
	public function submitTeacherAdvise(){
		$param = input();
		$data = array();
		if(!empty($param['defaultAdviseId'])){

			$data['content'] = $param['teacherAdvise'];
			$res = Db::name('study_advise')->where(['id'=>$param['defaultAdviseId']])->update($data);
		}else{
			//获取学生信息
			$personInfo = Db::name('person')->where(['id'=>$param['person_id']])->find();
			$data['content'] = $param['teacherAdvise'];
			$data['person_id'] = $param['person_id'];
			$data['weekly_report_time'] = time();
			$data['grade_id'] = $personInfo['grade_id'];
			$data['create_at'] = time();
			$data['type'] = 2;

			$res = Db::name('study_advise')->insert($data);
		}

		if($res){
			jsonMsg('success',1);
		}
	}

    //默认建议页面
    public function defaultStudyAdvise(){
        return view('/weeklyReport/defaultAdviseList',['title'=>"默认建议"]);
    }
    //获取默认建议数据
    public function getDefaultAdviseList(){
        $param = input();
        $page=$param['page'];
        $limit=$param['limit'];

        $adviseList = Db::name('study_advise')
                        ->field('sa.*,from_unixtime(create_at,"%Y-%m-%d %H:%i:%s") as createTime,g.grade')
                        ->alias('sa')
                        ->join('guard_grade g','g.id = sa.grade_id')
                        ->where(['sa.type'=>1])
                        ->page($page,$limit)
                        ->select();

        $count = Db::name('study_advise')->where(['type'=>1])->count();
        if(!empty($adviseList)){
            foreach ($adviseList as $key => $value) {
                switch ($value['time_sole']) {
                    case '1':
                        $adviseList[$key]['timeSole'] = '0-1';
                        break;
                    case '2':
                        $adviseList[$key]['timeSole'] = '1-3';
                        break;
                    case '3':
                        $adviseList[$key]['timeSole'] = '3小时以上';
                        break;
                    case '4':
                        $adviseList[$key]['timeSole'] = '本周没有学习';
                        break;
                }
                switch ($value['score_sole']) {
                    case '1':
                        $adviseList[$key]['scoreSole'] = '0-60';
                        break;
                    case '2':
                        $adviseList[$key]['scoreSole'] = '60-70';
                        break;
                    case '3':
                        $adviseList[$key]['scoreSole'] = '70-90';
                        break;
                    case '4':
                        $adviseList[$key]['scoreSole'] = '90-100';
                        break;
                    case '5':
                        $adviseList[$key]['scoreSole'] = '本周没有考试';
                        break;
                }
            }
        }

        if($adviseList){
            $res = array(
                    'code'=>0,
                    'msg'=>'',
                    'count'=>$count,
                    'data'=>$adviseList
                );
            echo json_encode($res);
        }
    }

    //添加默认建议页面
    public function add(){
        $gradeList = Db::name('grade')->select();

        $this->assign('gradeList',$gradeList);
        return view('/weeklyReport/addDefaultAdvise',['title'=>"添加默认建议"]);
    }
    public function addDefaultAdvise(){
        $param = input();

        $time_sole = $param['data']['time_sole'];
        $score_sole = $param['data']['score_sole'];
        $gradeId = $param['data']['gradeId'];

        $res = Db::name('study_advise')
                 ->where(['time_sole'=>$time_sole,'score_sole'=>$score_sole,'type'=>1,'grade_id'=>$gradeId])
                 ->find();

        if(!empty($res)){
            jsonMsg('该年级的成绩段和时间段的默认建议已经添加',2);
        }

        $result = Db::name('study_advise')->insert(['time_sole'=>$time_sole,'score_sole'=>$score_sole,'type'=>1,'create_at'=>time(),'content'=>$param['data']['content'],'grade_id'=>$gradeId]);

        if($result){
            jsonMsg('success',1);
        }else{
            jsonMsg('添加失败',2);
        }
    }

    public function update()
    {
        $param = input();

        $id = $param['id'];

        $res = Db::name('study_advise')
                 ->where(['id'=>$id])
                 ->find();

        $gradeList = Db::name('grade')->select();
        $this->assign('gradeList',$gradeList);
        $this->assign('res',$res);
        return view('/weeklyReport/updateDefaultAdvise',['title'=>"添加默认建议"]);
    }

    public function updateDefaultAdvise()
    {
        $param = input();

        $time_sole = $param['data']['time_sole'];
        $score_sole = $param['data']['score_sole'];
        $gradeId = $param['data']['gradeId'];

        $res = Db::name('study_advise')
                 ->where(['time_sole'=>$time_sole,'score_sole'=>$score_sole,'type'=>1,'grade_id'=>$gradeId,'id'=>array('neq',$param['data']['id'])])
                 ->find();

        if(!empty($res)){
            jsonMsg('该年级的成绩段和时间段的默认建议已经拥有',2);
        }

        $result = Db::name('study_advise')->where('id',$param['data']['id'])->update(['time_sole'=>$time_sole,'score_sole'=>$score_sole,'content'=>$param['data']['content'],'grade_id'=>$gradeId]);

        if($result){
            jsonMsg('success',1);
        }else{
            jsonMsg('修改失败',2);
        }
    }

    public function del(){
        $param = input();
        $id = $param['id'];
        if(!empty($id)){
            $res = Db::name('study_advise')->where(['id'=>$id])->delete();
            if($res){
                jsonMsg('success',1);
            }
        }
    }

    public function teacherStudyAdvise(){
        return view('/weeklyReport/teacherStudyAdvise',['title'=>"添加默认建议"]);
    }

    public function getTeacherAdviseList(Request $request)
    {
        $param = $request->param();

        $page=$param['page'];
        $limit=$param['limit'];
        $where = array();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where['p.nickName|p.phone']=['like',"%".$param['keyword']."%"];
        }
        $where['sa.type'] = 2;
        // $data = Db::name('study_advise')
        //             ->alias('sa')
        //             ->field('(select sum(all_watch_time) from guard_video_watch_log  as vl where sa.person_id = vl.person_id and vl.time BETWEEN  UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day)) AND UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day))+604799) as studyTime,min(ql.fraction) as minFraction,max(ql.fraction) as maxFraction,p.nickName,p.phone,p.id as person_id,sa.id as advise_id,sa.content,weekly_report_time')
        //             ->join('person p','p.id = sa.person_id')
        //             ->join('question_log ql','p.id = ql.person_id','LEFT')
        //             ->where($where)
        //             ->whereOr(('ql.intime BETWEEN  UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day)) AND UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day))+604799'))
        //             ->page($page,$limit)
        //             ->group('sa.id')
        //             ->order('sa.create_at desc')
        //             ->select();

        $data = Db::name('study_advise')
                    ->alias('sa')
                    ->field('sum(all_watch_time) as studyTime,min(ql.minFraction) as minFraction,max(ql.maxFraction) as maxFraction,p.nickName,p.phone,p.id as person_id,sa.id as advise_id,sa.content,weekly_report_time')
                    ->join('person p','p.id = sa.person_id')
                    ->join('(select person_id,max(fraction) as maxFraction ,min(fraction) as  minFraction,intime as intime from  guard_question_log) ql','p.id = ql.person_id','LEFT')
                    ->join('video_watch_log vl','p.id = vl.person_id','LEFT')
                   // ->join('question_log ql','p.id = ql.person_id','LEFT')
                    ->where($where)
                    ->where(('ql.intime BETWEEN  UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day)) AND UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day))+604799 or vl.time BETWEEN  UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day)) AND UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day))+604799'))
                    ->page($page,$limit)
                    ->group('sa.id')
                    ->order('sa.create_at desc')
                    ->select();
        $count = Db::name('study_advise')
                    ->alias('sa')
                    ->field('sum(distinct(all_watch_time)) as studyTime,min(ql.minFraction) as minFraction,max(ql.maxFraction) as maxFraction,p.nickName,p.phone,p.id as person_id,sa.id as advise_id,sa.content,weekly_report_time')
                    ->join('person p','p.id = sa.person_id')
                    ->join('(select distinct person_id,max(fraction) as maxFraction ,min(fraction) as  minFraction,intime from  guard_question_log) ql','p.id = ql.person_id','LEFT')
                    ->join('video_watch_log vl','p.id = vl.person_id','LEFT')
                    ->where($where)
                    ->where(('ql.intime BETWEEN  UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day)) AND UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day))+604799 or vl.time BETWEEN  UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day)) AND UNIX_TIMESTAMP(DATE_SUB(from_unixtime(weekly_report_time,"%Y-%m-%d"),INTERVAL WEEKDAY(from_unixtime(weekly_report_time)) day))+604799'))
                    ->page($page,$limit)
                    ->group('sa.id')
                    ->order('sa.create_at desc')
                    ->count();
        if($data){
            $res = array(
                    'code'=>0,
                    'msg'=>'success',
                    'count'=>$count,
                    'data'=>$data,
                );
            echo json_encode($res);
        }else{
            jsonMsg("暂无数据",1);
        }
    }
}