<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
class Live extends Model
{
	// 获取直播课程列表数据
	public function getLiveList()
	{
		$param=input();
		$res=Db::name('live')
		     ->alias('l')
		     ->join('guard_teacher t',"t.id=l.teacher_id",'LEFT')
		     ->field('l.id,l.subject_live,l.teacher_id,l.grade,l.start_time,l.end_time,l.remark,l.litpic_live,l.title_live,t.name')
		     ->page($param['page'],$param['limit'])
		     ->select();
		foreach($res as $k=>$v)
		{
			//直播开始时间戳转换成年月日时分秒时间格式
			if($v['start_time'])
			{
				$res[$k]['start_time']=date('Y-m-d H-i-s',$v['start_time']);
			}
			else
			{
				$res[$k]['start_time']='-- -- --';
			}
			// 直播结束时间戳转换成年月日时分秒时间格式
			if($v['end_time'])
			{
				$res[$k]['end_time']=date('Y-m-d H-i-s',$v['end_time']);
			}
			else
			{
				$res[$k]['end_time']='-- -- --';
			}
			if($v['grade']==1)
			{
				$res[$k]['grade']='小学一年级';
			}
			elseif($v['grade']==2)
			{
				$res[$k]['grade']='小学二年级';
			}
			elseif($v['grade']==3)
			{
				$res[$k]['grade']='小学三年级';
			}
			elseif($v['grade']==4)
			{
				$res[$k]['grade']='小学四年级';
			}
			elseif($v['grade']==5)
			{
				$res[$k]['grade']='小学五年级';
			}
			elseif($v['grade']==6)
			{
				$res[$k]['grade']='小学六年级';
			}
			elseif($v['grade']==7)
			{
				$res[$k]['grade']='七年级';
			}
			elseif($v['grade']==8)
			{
				$res[$k]['grade']='八年级';
			}
			elseif($v['grade']==9)
			{
				$res[$k]['grade']='九年级';
			}
			if($v['subject_live']==1)
			{
				$res[$k]['subject_live']='数学';
			}
			elseif($v['subject_live']==2)
			{
				$res[$k]['subject_live']='英语';
			}
			elseif($v['subject_live']==3)
			{
				$res[$k]['subject_live']='语文';
			}
		}
		return $res;
	}
	// 添加直播课程
	public function addLive()
	{
		$param=input();
		$data['start_time']=strtotime(substr($param['liveTime'],0,19));//直播开始的时间
		$data['end_time']=strtotime(substr($param['liveTime'],22,41));//直播结束的时间
		$data['subject_live']=$param['subject'];//课程科目
		$data['litpic_live']=$param['litpic'];//直播课程图片
		$data['title_live']=$param['title'];//直播课程标题
		$data['remark']=$param['editor'];//直播课程简介
		$data['grade']=$param['grade'];//课程年级
		$data['teacher_id']=$param['teacher_id'];//关联直播讲师id
		$res=Db::name('live')->insert($data);
		return $res;
	}
	// 查找直播课程数据
	public function editlive()
	{
		$param= input();
		$where['id']=$param['id'];
		$res=Db::name('live')->where($where)->find();
		$res['time_end']=date('Y-m-d H:i:s',$res['start_time']).'-'.date('Y-m-d H:i:s',$res['end_time']);
		return $res;
	}
	// 执行编辑的方法
	public function liveEdit()
	{
		$param=input();
		$data['start_time']=strtotime(substr($param['liveTime'],0,19));//直播开始的时间
		$data['end_time']=strtotime(substr($param['liveTime'],22,41));//直播结束的时间
		$data['subject_live']=$param['subject'];//课程科目
		$data['litpic_live']=$param['litpic'];//直播课程图片
		$data['title_live']=$param['title'];//直播课程标题
		$data['remark']=$param['editor'];//直播课程简介
		$data['grade']=$param['grade'];//课程年级
		$data['teacher_id']=$param['teacher_id'];//关联直播讲师id
		$where['id']=$param['id'];
		$res=Db::name('live')->where($where)->update($data);
		return $res;
	}
	// 直播课程详细信息
	public function showlive()
	{
		$param=input();
		$where['l.id']=$param['id'];
		$res=Db::name('live')
		     ->alias('l')
		     ->join('guard_teacher t',"t.id=l.teacher_id",'LEFT')
		     ->field('l.id,l.subject_live,l.teacher_id,l.grade,l.start_time,l.end_time,l.remark,l.litpic_live,l.title_live,t.name,t.content,t.title')
		     ->where($where)
		     ->find();
		$res['start_time']=date('Y-m-d H-i-s',$res['start_time']);
		$res['end_time']=date('Y-m-d H-i-s',$res['end_time']);
		if($res['grade']==1)
		{
			$res['grade']='小学一年级';
		}
		elseif($res['grade']==2)
		{
			$res['grade']='小学二年级';
		}
		elseif($res['grade']==3)
		{
			$res['grade']='小学三年级';
		}
		elseif($res['grade']==4)
		{
			$res['grade']='小学四年级';
		}
		elseif($res['grade']==5)
		{
			$res['grade']='小学五年级';
		}
		elseif($res['grade']==6)
		{
			$res['grade']='小学六年级';
		}
		elseif($res['grade']==7)
		{
			$res['grade']='七年级';
		}
		elseif($res['grade']==8)
		{
			$res['grade']='八年级';
		}
		elseif($res['grade']==9)
		{
			$res['grade']='九年级';
		}
		if($res['subject_live']==1)
		{
			$res['subject_live']='数学';
		}
		elseif($res['subject_live']==2)
		{
			$res['subject_live']='英语';
		}
		elseif($res['subject_live']==3)
		{
			$res['subject_live']='语文';
		}
		return $res;
	}
}