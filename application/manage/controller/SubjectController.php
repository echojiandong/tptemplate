<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use app\manage\model\Subject;
class SubjectController extends author{
	public $subject;
	public function _initialize(){
		parent::_initialize();
		$this->subject = new Subject();
	}
	public function index()
	{

		return $this->fetch('/subject/index');
	}
	public function getSubjectList()
	{
		$param = input();
		$page=$param['page'];
        $limit=$param['limit'];
		$list = $this->subject->getSubjectList($page,$limit);
		ajaReturn($list['list'], 0, 'success',$list['count']);
	}
	public function add()
	{
		$param = input();

		$id = isset($param['id']) ? $param['id'] : '';

		$data['subject'] = isset($param['subject']) ? $param['subject'] : '';
		$data['bgimg'] = isset($param['bgimg']) ? $param['bgimg'] : '';
		$list = Subject::get(['subject' => $data['subject'],'bgimg'=>$param['bgimg']]);

		if(!empty($list)){
			ajaReturn('', 1001, '科目已存在');
		}
		$Subject = new Subject();
		$data=[
		    'subject'  =>  $param['subject'],
		    'color' => '#636DDD',
		    'bgimg' =>  $param['bgimg']
		];
		if($id == 0){
			$res = $Subject->save($data);
		}

		if($id != 0){

			$res = $Subject->save($data,['id'=>$id]);
		}

		$msg = $res ? ['msg' => 'success', 'code' => 0] : ['msg' => 'error', 'code' => 1001] ;

		ajaReturn('', $msg['code'], $msg['msg']);
	}
	/*
	 * 表单初始赋值
	 */
	public function doadd()
	{
		$param = input();

		$id = isset($param['id']) ? $param['id'] : '';

		if($id == ''){

			ajaReturn('error', 1001, []);
		}
		$data = Subject::get(['id' => $id]);
		ajaReturn($data, 0, 'success');
	}
	/*
	 * 页面跳转
	 */
	public function addpage(){

		$param = input();

		$id = isset($param['id']) ? $param['id'] : 0;

		$this ->assign('id', $id);

		return $this ->fetch('/subject/add');
	}
	/*
	 * 删除科目
	 */
	public function delete(){

		$param = input();
		
		$id = isset($param['id']) ? $param['id'] : '';

		if($id == ''){
			ajaReturn('error', 1001, []);
		}

		$res = Subject::destroy($id);;

		$msg = $res ? ['msg' => 'success', 'code' => 0] : ['msg' => 'error', 'code' => 1001] ;

		ajaReturn('', $msg['code'], $msg['msg']);

	}
	public function upload(){
        $file = request()->file('file');
        if($file){
            $info = $file->move(ROOT_PATH . 'public/upload/subject');
            if($info){
                jsonMsg("成功",0,"/upload/subject/".$info->getSaveName());
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
}