<?php

namespace app\weixin\model;
use think\Model;
use think\Request;
use think\Session;
use think\Db;

class PersonModel
{
    // 账号绑定个数
    const BINDINGNUM = 3;
    /**
     * 获取关注微信公众号用户
     */
    public function getWeChatUser()
    {
        $where = [
            'openid' => ['neq', '']
        ];

        $user = Db::name('wx_person')->where($where)->select();
        return !empty($user) ? $user : array();
    }

    
    /**
     * 获取用户周报学习信息
     */
    public function getStudyInfo($uid, $startTime = null, $endTiem = null)
    {
        if (!isset($uid) || empty($uid)) {
            return false;
        }

        if ($startTime && $endTiem) {
            $where['a.time'] = ['between time', [$startTime, $endTiem]];

        }

        $where['a.person_id'] = $uid;
        $field = 'a.id, a.person_id, a.grade_id, a.subject_id, a.video_id, sum(a.study_time) as totalTime, a.semester, a.study_num, a.video_status, concat(a.grade_id, a.subject_id, a.semester) as gss, a.time,
                  b.name as coursename, c.grade, d.nickName, d.school,
                  (case b.Semester
                        when 1 then "上学期"
                        when 2 then "下学期"
                        when 3 then "全册"
                        else ""
                    end
                    ) as semester
                    ';
        $res = Db::name('video_watch_log')->alias('a')
                ->field($field)
                ->join('video_class b', 'a.video_class_id = b.id')
                ->join('grade c', 'b.grade_id = c.id')
                ->join('person d', 'a.person_id = d.id')
                ->where($where)
                ->group('gss')
                ->select();
        return empty($res) ? array() : $res;
    }

    public function videolist($where, $pid,$productStatus=0){
        if($productStatus==1){
            $video = Db::name('product') ->where($where) ->find();
            if(empty($video)){
                return [[],0,0];
            }
            $video_infos = Db::name('product_info')->where('product_id',$video['id']) ->select();
            $video_info = array_column($video_infos,'video_id');
            $video_pid = array_column($video_infos,'video_pid');
            $video_info = array_merge($video_info,$video_pid);
            $list = Db::name('video') ->field('id,testclass,outline,pid,part,kid,classhour,audi')
                ->whereIn('id',$video_info)
                ->where(['display' => 1])
                ->order('sort', 'asc')
                ->select();
            foreach ($list as $k=>$v){
                $list[$k]['kid'] = $video['id'];
                $list[$k]['productStatus'] = 1;
            }

            $count = Db::name('video') ->field('count(1) as counts')
                ->whereIn('id',$video_info)
                ->where(['display' => 1])
                ->where(['part' => 2])
                ->find()['counts'];
            if(empty($list)){
                return [[],0,0];
            }

            if(isset($where['id'])){

                $where = ['product_id' => $where['id']];
            }
        }else{
            $video = Db::name('video_class') ->where($where) ->find();
            if(empty($video)){
                return [[],0,0];
            }

            $list = Db::name('video') ->field('id,testclass,outline,pid,part,kid,classhour,audi')
                ->where(['kid' => $video['id']])
                ->where(['display' => 1])
                ->order('sort', 'asc')
                ->select();
            foreach ($list as $k=>$v){
                $list[$k]['productStatus'] = 0;
            }
            $count = Db::name('video') ->field('count(1) as counts')
                ->where(['kid' => $video['id']])
                ->where(['display' => 1])
                ->where(['part' => 2])
                ->find()['counts'];

            if(empty($list)){
                return [[],0,0];
            }

            if(isset($where['id'])){

                $where = ['video_class_id' => $where['id']];
            }
        }
    
        $video_log = Db::name('video_log') ->field('id,video_id,video_status') 
                                           ->where($where) 
                                           ->where(['type' => 0])
                                           ->where('expireTime','>',time()) 
                                           ->where(['person_id' => $pid])
                                           ->select();

        $count_log = Db::name('video_log') ->field('count(1) as counts') 
                                           ->where($where) 
                                           ->where(['type' => 0])
                                           ->where('expireTime','>',time())
                                           ->where(['person_id' => $pid])
                                           ->where('video_status', '<>', 1)
                                           ->find()['counts'];
        if(empty($video_log)){
            $video_log = ['video_id' => 0, 'video_status' => 1];
        }

        $logs = array_combine(array_column($video_log, 'video_id'), array_column($video_log, 'video_status'));
        $treelist = array_map(function($v) use ($logs){
                            $v['is_buy'] = 0;

                            $v['is_study'] = 1;

                            if($v['part'] == 2 && isset($logs[$v['id']])){

                                $v['is_buy'] = 1;

                                $v['is_study'] = $logs[$v['id']];
                            }

                            return $v;
                        }, $list);
        return [$treelist, $count, $count_log];

    }

    public function getUserInfoByOpenid($data, $uid)
    { 
        $data = json_decode($data, true);
        //  验证数据有效性
        if($data['watermark']['appid'] != config('WX_small_appId')){

            jsonMsg('error',0);
        }

        $unionId = $data['unionId'];
        
        $user_msg = Db::name('wx_person') ->field('id,pid,app_openid,unionid') 
                                          ->where(['unionid' => $unionId, 'pid' => $uid])
                                          ->find();
        $res = 1;
        // 判断当前登录用户是否存在
        if(!empty($user_msg)){
            // 判断小程序的的openid是否存在
            $user_msg['app_openid'] !== $data['openId'] && $res = Db::name('wx_person') 
                                                                ->where(['unionid' => $unionId]) 
                                                                ->update(['app_openid' => $data['openId']]);
        }
        // 判断当前账号绑定账户的个数
        $num = Db::name('wx_person') ->field('count(1) as counts')
                                     ->where(['pid' => $uid])
                                     ->find();
        if($num['counts'] > self::BINDINGNUM){

            jsonMsg('绑定用户过多！',0);
        }

        if(empty($user_msg)){

            $info['pid'] = $uid;

            $info['nickName'] = $data['nickName'];

            $info['app_openid'] = $data['openId'];

            $info['addtime'] = time();

            $info['unionid'] = $data['unionId'];

            $info['province'] = $data['province'];

            $info['city'] = $data['city'];

            $info['area'] = $data['country'];

            $info['sex'] = $data['gender'];

            $res = Db::name('wx_person') ->insert($info);  
        }

        return $res;
    }
}