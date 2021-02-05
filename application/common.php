<?php
use think\Session;
use think\Cookie;
use think\Db;
use think\Cache;
// use Redis;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
/**
 * [GetGlodalClassId 获取全局年级id]
 * @author 薛少鹏 xsp15135921754@163.com
 * @DateTime 2019-04-10T11:23:54+0800
 * @return [int]    $class_id  年级id 默认为7（初中）
 */
function GetGlodalClassId(){
    //获取全局class_id
    $class_id = Session::get('Global_grade_id');
    if(empty($class_id)){
        //判断是否登录
        $userinfo =Cookie::get("user");
        if($userinfo)
        {
            $userinfo=mydecrypt($userinfo,config('encrypt_key_common'));
            $userinfo=base64_decode($userinfo);
            $userinfo=json_decode($userinfo,true);//转换成json
            Session::set("user",$userinfo);
            $user_id = Session::get('user')['id'];
        }else{
            $user_id = '';
        }
            
        //登录后取个人信息里的grade_id
        if(!empty($user_id)){
            $class_id = Db::name('person') ->field('grade_id') ->where(['id' => $user_id]) ->find()['grade_id'];
            $class_id = $class_id == 0?null:$class_id;
        }
        //登录但是个人信息里没有grade_id
        if(empty($class_id) && !empty($user_id)){
            //查询登录后时是否买过课程
            $class_data = DB::name('video_log') ->field('grade_id') ->where(['person_id' => $user_id]) ->group('grade_id') ->select();
            if(!empty($class_data)){
                foreach($class_data as $val){
                    $class_arr[] = $val['grade_id'];
                }
                $class_id = array_pop($class_arr);
            }
            
        }
        //未买过 默认为7
        if(empty($class_id)){
            $class_id = 7;
        }
    }
    //课程不存在时
    $video_class = Db::name('video_class') ->field('id') ->where(['grade_id' =>$class_id]) ->find();
    if(empty($video_class)){
        $class_id = Db::name('video_class') ->field('id,grade_id')->find()['grade_id'];
    }
    return $class_id;
}
/**
 * [GetSubjectList 根据年级，科目，对应课程id组装课程列表]
 * @author 薛少鹏 xsp15135921754@163.com
 * @DateTime 2019-04-11T09:55:25+0800
 * @param    [array]                   $v [课程信息]
 */
function GetSubjectList($v){
    $subject_list = Db::name('video') ->field('id,kid,testclass,outline,link,classhour,pid,part,audi')
                                      ->where('kid='.$v['id'])
                                      ->order('sort')
                                      ->where(['display' => 1])
                                      ->select();
    $person_subject = Db::name('video_log') ->field('video_id,study_time,study_num,video_status')
                                            ->where(['person_id' => $v['person_id']])
                                            ->where(['type' => 0])
                                            ->select();
    //组装学习进度信息
    if(!empty($person_subject)){
        foreach($subject_list as $key =>$val){
            foreach($person_subject as $k =>$v){
                if($v['video_id'] == $val['id']){
                    //上次学习的时间
                    $subject_list[$key]['study_time'] = $v['study_time'];
                    //学习次数
                    $subject_list[$key]['study_num'] = $v['study_num'];
                    //总时长
                    $subject_list[$key]['study_video_num'] = $val['classhour'];
                    //学习状态
                    $subject_list[$key]['video_status'] = $v['video_status'];
                }
            }
        }
    }
    //取出并组装 每个视频的知识点
    $sid = [];
    foreach($subject_list as $k =>$v){
        if($v['part'] == 2){
            $sid[] = $v['id'];
        }
    }
    $knowledge = Db::name('knowledge') ->field('k_id,k_name,k_content,s_id,start_time,end_time,sort')
                                       ->where('s_id','in',$sid)
                                       ->order('sort')
                                       ->select();
    foreach($subject_list as $k =>$v){
        foreach($knowledge as $key =>$val){
            if($v['id'] == $val['s_id']){
                //知识点
                $subject_list[$k]['treelist'][] = $val;
                //便签
                $subject_list[$k]['treelist_1'][] = ['title' => $val['k_name'], 'content' => $val['k_content']];
            }
        }
    }
    //拼接数据列表
    $data=[];
    foreach($subject_list as $key =>$val){
        if($val['pid'] == 0){
            foreach($subject_list as $k =>$v){
                if($v['part'] == 1 && $v['pid'] == $val['id']){
                    foreach($subject_list as $k_1 =>$v_1){
                        if($v_1['part'] == 2 && $v_1['pid'] != 0 && $v_1['pid'] == $v['id']){
                            $v['treelist'][] = $v_1;
                        }
                    }
                    $val['treelist'][] = $v;
                }else{
                    if($v['pid'] != 0 && $v['pid'] == $val['id']){
                        $val['treelist'][] = $v;
                    }
                }
            }
            $data[] = $val;
        }
    }
    return $data;
}
/**
 * [ajaReturn 基于layui的数据返回格式]
 * @author 薛少鹏 xsp15135921754@163.com
 * @DateTime 2019-04-11T17:25:11+0800
 * @param    array                    $data  [返回数据]
 * @param    integer                  $code  [返回状态码]
 * @param    string                   $msg   [状态信息]
 * @param    integer                  $count [是否是表格返回]
 * @return   [json]                          [组装的数据]
 */
function ajaReturn($data = [], $code = 1, $msg = '请求成功',$count = -1){
    header('Content-type: application/json');
    header("Access-Control-Allow-Origin: *");
    $arr = ['code' => $code, 'msg' => $msg, 'data' => $data];
    if($count != -1){
        $arr['count'] = $count; 
    }
    die(json_encode($arr));
}
/**
 * [setVideoLog 激活卡中间操作]
 * @author 薛少鹏 xsp15135921754@163.com
 * @DateTime 2019-04-12T09:26:46+0800
 * @param    [int]                   $c_id 对应的课程id
 * @param    [int]                   $person_id 激活的用户id 默认当前登录的session['id']
 * @return    true  or  flase;
 */
function setVideoLog($c_id,$person_id){
    //获取 年级 科目 上下册
    $video_class_id = Db::name('video_class') ->field('id,grade_id,subject_id,Semester')
                                              ->where('id','in',$c_id)
                                              ->select();
    //获取对应的 视频信息
    $video_id = Db::name('video') ->where('kid','in',$c_id)
                                  ->where('pid','<>',0)
                                  ->where('part','=',2)
                                  ->where(['audi' => 1])
                                  ->where(['display' => 1])
                                  ->field('id as video_id,kid,classhour')
                                  ->select();
    //数据组装
    $data = [];
    foreach($video_class_id as $key =>$val){
        foreach($video_id as $k =>$v){
            if($v['kid'] == $val['id']){
                $v['person_id'] = $person_id;
                $v['grade_id'] = $val['grade_id'];
                $v['subject_id'] = $val['subject_id'];
                $v['semester'] = $val['Semester'];
                $v['video_time'] = $v['classhour'];
                $v['intime'] = time();
                $v['expireTime'] = time()+3600*24*190;
                $v['video_class_id'] = $v['kid'];
                unset($v['kid']);
                unset($v['classhour']);
                $data[] = $v;
            }
        }
    }
    //批量插入
    $log_msg = [];
    while(!empty($data)){
        $log_msg[] = array_shift($data);
        //批量插入 30条
        if(count($log_msg) == 100){
            $res = Db::transaction(function() use ($log_msg){
                Db::name('video_log') ->insertAll($log_msg);
            });
        }
    }
    //不够30条时，将剩余数据压入数据库
    if(!empty($log_msg)){
        $res = Db::transaction(function() use ($log_msg){
            Db::name('video_log') ->insertAll($log_msg);
        });
    }
    return true;
}
/**
 * [formlimit 表单提交限流]
 * @author 薛少鹏 xsp15135921754@163.com
 * @DateTime 2019-06-10T14:55:32+0800
 * @return   [type]                   [description]
 */
function formlimit(){
    $redis = new Redis();
    $redis ->connect('127.0.0.1',6379);
    //用户id
    $id = Session::get('user')['id'];
    $key = 'user'.$id;
    //获取队列中的条数 (限制次数三次)
    if($redis ->lLen($key) < 3){
        //入队
        $redis ->lPush($key, time());
        //设置过期时间
        $redis ->expire($key, 60);
    }else{
        //获取最后一次请求的时间
        $last_time = $redis ->lIndex($key, 0);
        if(time() - $last_time < 60 ){
            ajaReturn([],1001,'请求频繁,请稍后重试');
        }
    }
}
/**
 * [getImgCurl 获取微信头像 https]
 * @author 薛少鹏 xsp15135921754@163.com
 * @DateTime 2019-07-01T14:00:46+0800
 * @param    [string]                   $url [url 地址]
 */
function getImgCurl($url){
        $header = array(   
        'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',    
        'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',    
        'Accept-Encoding: gzip, deflate',);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($code == 200) {//把URL格式的图片转成base64_encode格式的！    
            $imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
        }else{
            return 0;
        }
        return $imgBase64Code;//图片内容
}
function ismobile()
{

    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备

    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))

        return true;



    //此条摘自TPM智能切换模板引擎，适合TPM开发

    if (isset ($_SERVER['HTTP_CLIENT']) && 'PhoneClient' == $_SERVER['HTTP_CLIENT'])

        return true;

    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息

    if (isset ($_SERVER['HTTP_VIA']))

        //找不到为flase,否则为true

        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;

    //判断手机发送的客户端标志,兼容性有待提高

    if (isset ($_SERVER['HTTP_USER_AGENT'])) {

        $clientkeywords = array(

            'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'

        );

        //从HTTP_USER_AGENT中查找手机浏览器的关键字

        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {

            return true;

        }

    }

    //协议法，因为有可能不准确，放到最后判断

    if (isset ($_SERVER['HTTP_ACCEPT'])) {

        // 如果只支持wml并且不支持html那一定是移动设备

        // 如果支持wml和html但是wml在html之前则是移动设备

        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {

            return true;

        }

    }

    return false;

}
/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string $key
 */
function user_md5($str, $key = '', $key2 = '') {
    return '' === $str ? '' : md5(sha1($key2 . $str) . $key);
}

function user_sha1($str, $key = '', $key2 = '', $key3 = '') {
    return '' === $str ? '' : md5(sha1($key . $str . $key3) . $key2);
}

/* 字段加密 */

function baseauthcode($str, $type = 1) {
    if ($type == 1) {
        return base64_encode(authcode($str, 'ENCODE', C('DATA_AUTH_KEY'), 0));
    } else {
        return base64_encode(authcode($str, 'DECODE', C('DATA_AUTH_KEY'), 0));
    }
}

/* 字段加密解密 */

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;
    // 密匙
    $key = md5($key ? $key : $_SERVER['HTTP_HOST']);
    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
//解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/* 微信字段校验 */

function jsapi_encode($data) {
    ksort($data);
    $str = '';
    foreach ($data as $k => $v) {
        $str .= '&' . $k . '=' . $v;
    }
    $str = ltrim($str, '&');
    return sha1($str);
}

function jsapi_md5($data) {
    ksort($data);
    $str = '';
    foreach ($data as $k => $v) {
        $str .= '&' . $k . '=' . $v;
    }
    $str = ltrim($str, '&');
    return md5($str);
}

/* 系统提示操作 */

function ErrMsg($str = '操作失败！') {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<script>";
    echo "alert('" . $str . "');history.back();";
    echo "</script>";
    exit();
}

function SucMsg($str = '操作成功！', $url) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<script>";
    echo "alert('" . $str . "');location.href='" . $url . "';";
    echo "</script>";
    exit();
}

function closeMsg($str = '操作失败！', $code = 0) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<script>";
    echo "var index = parent.layer.getFrameIndex(window.name) || parent.layer.index;";
    echo "parent.layer.msg('" . $str . "');";
    echo "parent.layer.close(index);";
    echo "console.log(index);";
    echo "</script>";
    exit();
}

function jsonMsg($msg = '操作失败', $error_code = 1, $data = NULL,$count=NULL) {
    header('Content-type: application/json');
    header("Access-Control-Allow-Origin: *");
    $arr = array("error_code" => $error_code, "msg" => $msg, "data" => $data,"count"=>$count);
    die(json_encode($arr));
}

function jsonList($msg = '操作失败', $error_code = 1, $list = array(), $count = 0, $data = NULL) {
    header('Content-type: application/json');
    header("Access-Control-Allow-Origin: *");
    $arr = array("error_code" => $error_code, "msg" => $msg, "data" => $list, 'count' => $count, 'content' => $data);
    die(json_encode($arr));
}

function jsonMsg2($msg = '操作失败', $error_code = 1, $data = NULL) {
    header('Content-type: application/json');
    header("Access-Control-Allow-Origin: *");
    $arr = array("code" => $error_code, "msg" => $msg, "data" => $data);
    die(json_encode($arr));
}

/* 生成随机码 */

function getKey($length = 10, $type = 0) {
    $chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'), array('!', '@', '$', '%', '^', '&', '*'));
    $type == 1 AND $chars = range(0, 9);
    shuffle($chars);
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $type == 1 AND shuffle($chars);
        $password .= $chars[$i];
    }
    return $password;
}

/* 校验手机号码 */

function checkphone($mobilephone) {
    if (preg_match("/^1[3456789]{1}\d{9}$/", $mobilephone)) {
        //验证通过
        return true;
    } else {
        //手机号码格式不对
        return false;
    }
}

//检测金钱
function CheckMoney($C_Money) {
    if ($C_Money == 0)
        return true;
    if (!ereg("^[1-9]{1}[0-9]{0,4}$|[0-9]{1,5}[.][0-9]{1,2}$", $C_Money))
        return false;
    return true;
}

function showMoney($money) {
    $money = ltrim($money, '0');
    $money = preg_replace("/([0-9]+)(\.00)$/i", '${1}', $money);
    return $money;
}

function checkdatetime($dateTime) {
    $unixTime = strtotime($dateTime);
    if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
        return false;
    }
    return TRUE;
}

function getTimeName($dateTime) {
    if (preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/s", $dateTime)) {
        return substr($dateTime, 11, 5);
    } else {
        return $dateTime;
    }
}

function checkemail($email) {
    return preg_match('/^([0-9A-Za-z\-_\.]+)@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,}([\.][a-z]{2,})*$/i', $email);
}

function checkIdCard($idcard) {
    $City = array(11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古", 21 => "辽宁", 22 => "吉林", 23 => "黑龙江", 31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东", 41 => "河南", 42 => "湖北", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南", 50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏", 61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆", 71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外");
    $iSum = 0;
    $idCardLength = strlen($idcard);
    //长度验证
    if (!preg_match('/^\d{17}(\d|x)$/i', $idcard) and ! preg_match('/^\d{15}$/i', $idcard)) {
        return false;
    }
    //地区验证
    if (!array_key_exists(intval(substr($idcard, 0, 2)), $City)) {
        return false;
    }
    // 15位身份证验证生日，转换为18位
    if ($idCardLength == 15) {
        $sBirthday = '19' . substr($idcard, 6, 2) . '-' . substr($idcard, 8, 2) . '-' . substr($idcard, 10, 2);
        $d = new DateTime($sBirthday);
        $dd = $d->format('Y-m-d');
        if ($sBirthday != $dd) {
            return false;
        }
        $idcard = substr($idcard, 0, 6) . "19" . substr($idcard, 6, 9); //15to18
        $Bit18 = getVerifyBit($idcard); //算出第18位校验码
        $idcard = $idcard . $Bit18;
    }
    // 判断是否大于2078年，小于1900年
    $year = substr($idcard, 6, 4);
    if ($year < 1900 || $year > 2078) {
        return false;
    }
    //18位身份证处理
    $sBirthday = substr($idcard, 6, 4) . '-' . substr($idcard, 10, 2) . '-' . substr($idcard, 12, 2);
    $d = new DateTime($sBirthday);
    $dd = $d->format('Y-m-d');
    if ($sBirthday != $dd) {
        return false;
    }
    //身份证编码规范验证
    $idcard_base = substr($idcard, 0, 17);
    if (strtoupper(substr($idcard, 17, 1)) != getVerifyBit($idcard_base)) {
        return false;
    }
    return true;
}

// 计算身份证校验码，根据国家标准GB 11643-1999
function getVerifyBit($idcard_base) {
    if (strlen($idcard_base) != 17) {
        return false;
    }
    //加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    //校验码对应值
    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    $checksum = 0;
    for ($i = 0; $i < strlen($idcard_base); $i++) {
        $checksum += substr($idcard_base, $i, 1) * $factor[$i];
    }
    $mod = $checksum % 11;
    $verify_number = $verify_number_list[$mod];
    return $verify_number;
}

//手机号码部分显示
function subphone($phone) {
    return str_replace(substr($phone, 3, 5), '*****', $phone);
}

//身份证号码部分显示
function subidcard($idcard) {
    return str_replace(substr($idcard, 6, 8), '********', $idcard);
}

//计算指定时间到当前时间的时间差
function diff_time($time1, $time2 = '', $type = 's') {
    $endtime = strtotime($time1);
    if (empty($time2)) {
        $starttime = strtotime(date('Y-m-d H:i:s'));
    } else {
        $starttime = strtotime($time2);
    }
    $diff = $endtime - $starttime;
    switch ($type) {
        case 'm':
            $diff = round($diff / 60);
            break;
        case 'h':
            $diff = round($diff / 3600);
            break;
        case 'd':
            $diff = round($diff / 86400);
            break;
        default:
            $diff = $diff;
    }
    return $diff;
}

function ntobr($str) {
    $order = array("\r\n", "\n", "\r");
    $replace = '<br/>';
    return str_replace($order, $replace, $str);
}

//多维数组转换为一维数组
function array_multi2single($array) {
    static $result_array = array();
    foreach ($array as $value) {
        if (is_array($value)) {
            array_multi2single($value);
        } else
            $result_array[] = $value;
    }
    return $result_array;
}

//urlcode转义
function url_encode($str) {
    if (is_array($str)) {
        foreach ($str as $key => $value) {
            unset($str[$key]);
            $str[urlencode($key)] = url_encode($value);
        }
    } else {
        $str = urlencode($str);
    }
    return $str;
}

//二维数组变为一维数组
function array_toOne($arr, $data = array()) {
    if (is_array($arr)) {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $data = array_toOne($v, $data);
            } else {
                $data[$k] = $arr;
            }
        }
    } else {
        $data[] = $arr;
    }
    return $data;
}

function arr_foreach($arr, $tmp = array()) {
    if (!is_array($arr)) {
        return false;
    }
    foreach ($arr as $key => $val) {
        if (is_array($val)) {
            $tmp = arr_foreach($val, $tmp);
        } else {
            $tmp[$key] = $val;
        }
    }
    return $tmp;
}

//二维数组排序
function array_sort($arr, $keys, $type = 'desc') {
    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v) {
        $keysvalue[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array[] = $arr[$k];
    }
    return $new_array;
}

function is_mobile() {
    $user_agent = (!isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
    //Mobile
    if ((preg_match("/(iphone|ipod|android)/i", strtolower($user_agent))) AND strstr(strtolower($user_agent), 'webkit')) {
        return true;
    } else if (trim($user_agent) == '' OR preg_match("/(nokia|sony|ericsson|mot|htc|samsung|sgh|lg|philips|lenovo|ucweb|opera mobi|windows mobile|blackberry)/i", strtolower($user_agent))) {
        return true;
    } else {//PC
        return false;
    }
}

function is_weixin() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

function html_postdata($url,$data=""){//服务器请求微信接口
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tmpInfo = curl_exec($ch);
    if (curl_errno($ch)) {
        echo curl_error($ch);
    }

    curl_close($ch);

    return $tmpInfo;
}

function html_post($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    $header = array("content-type: application/json;charset=UTF-8");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);  //设置头信息的地方
    // post数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // post的变量
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

//xml转换为数组
function xmlToArray($xml) {
    libxml_disable_entity_loader(true);
    $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);
    return $data;
}

//计算时间差
function getTimeDiff($time1, $time2) {
    $arr1 = explode(':', $time1);
    $arr2 = explode(':', $time2);
    $hour1 = $arr1[0];
    $mins1 = $arr1[1];
    $hour2 = $arr2[0];
    $mins2 = $arr2[1];
    if ($time1 > $time2) {
        $difftime = 60 * $hour2 + $mins2 + 24 * 60 - 60 * $hour1 - $mins1;
    } else {
        $difftime = 60 * $hour2 + $mins2 - 60 * $hour1 - $mins1;
    }
    return $difftime;
}

//数字转化为时间段
function numToTime($num, $separator = '-') {
    $start = sprintf("%02d", $num);
    $end = sprintf("%02d", $num + 1);
    return $start . ":00" . $separator . $end . ":00";
}

/**
 * 计算上一个月的今天，如果上个月没有今天，则返回上一个月的最后一天
 * @param type $time
 * @return type
 */
function last_month_today($time) {
    if (empty($time))
        $time = time();
    $last_month_time = mktime(date("G", $time), date("i", $time), date("s", $time), date("n", $time), 0, date("Y", $time));
    $last_month_t = date("t", $last_month_time);
    if ($last_month_t < date("j", $time)) {
        return date("Y-m-t H:i:s", $last_month_time);
    }
    return date(date("Y-m", $last_month_time) . "-d", $time);
}

/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后)
 * @param string $day1
 * @param string $day2
 * @return number
 */
function diffBetweenTwoDays($day1, $day2) {
    $second1 = strtotime($day1);
    $second2 = strtotime($day2);
    /*
      if ($second1 < $second2) {
      $tmp = $second2;
      $second2 = $second1;
      $second1 = $tmp;
      } */
    return abs($second1 - $second2) / 86400;
}

function checkWxSign($arr, $signstr) {
    ksort($arr);
    $string1 = '';
    foreach ($arr as $k => $v) {
        if ($v != '' && $k != 'sign') {
            $string1 .= "{$k}={$v}&";
        }
    }
    $sign = strtoupper(md5($string1 . "key=" . C('WX_KEY')));
    if ($signstr != $sign)
        return false;
    return true;
}

/* 从身份证号码中获取生日性别等信息 */

function getIDCardInfo($IDCard) {
    $result = array('error' => 1, 'birthday' => '', 'gender' => '');
    if (!checkIdCard($IDCard)) {
        return $result;
    }
    $tyear = intval(substr($IDCard, 6, 4));
    $tmonth = intval(substr($IDCard, 10, 2));
    $tday = intval(substr($IDCard, 12, 2));
    $result['error'] = 0;
    $result['birthday'] = $tyear . '-' . $tmonth . '-' . $tday;
    $sexint = intval(substr($IDCard, 16, 1));
    $result['gender'] = $sexint % 2;
    return $result;
}

/* 根据出生年月计算年龄 */

function getAge($birthday, $startdate) {
    if (!$startdate) {
        $startdate = date('Y-m-d');
    }
    if (strlen($startdate) == 4) {//只计算年份
        $tyear = date('Y', strtotime($birthday));
        return intval($startdate - $tyear);
    } else {
        list($y1, $m1, $d1) = explode("-", $birthday);
        list($y2, $m2, $d2) = explode("-", $startdate);
        $age = $y2 - $y1;
        if (intval($m2 . $d2) > intval($m1 . $d1)) {
            $age--;
        }
        return $age;
    }
}

//替换
function replacequot($str) {
    return str_replace(",", '"', $str);
}

//空值默认
function mydefault($value, $default) {
    if (empty($value)) {
        return $default;
    } else {
        return $value;
    }
}

function Array_Month($month) {
    /* 方法一 */
    //$month_str='一,二,三,四,五,六,七,八,九,十,十一,十二';
    //$arr=explode(',',$month_str);
    /* 方法二 */
    $arr = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '十一', '十二');
    return $arr[$month - 1];
}

function time2string($second) {
    $day = floor($second / (3600 * 24));
    $second = $second % (3600 * 24);
    $hour = floor($second / 3600);
    $second = $second % 3600;
    $minute = floor($second / 60);
    $second = $second % 60;
    $day = $day ? $day . '天' : '';
    $hour = $hour ? $hour . '时' : ($day && ($hour || $minute || $second) ? '0时' : '');
    $minute = $minute ? $minute . '分' : ($hour && $second ? '0分' : '');
    $second = $second ? $second . '秒' : '';
    return $day . $hour . $minute . $second;
}

function create_guid($namespace = '') {
    static $guid = '';
    $uid = uniqid("", true);
    $data = $namespace;
    $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
    $guid = substr($hash, 0, 8) .
        '-' .
        substr($hash, 8, 24) .
        '-' . substr(strtoupper(md5($namespace)), 0, 30);
    return $guid;
}

function y2f($rmbs) {
    $rmb = floor($rmbs * 10 * 10);
    return $rmb;
}

// $round: float round ceil floor
function f2y($rmbs, $round = 'float') {
    $rmb = floor($rmbs * 100) / 10000;
    if ($round == 'float') {
        $rmb = number_format($rmb, 2, '.', '');
    } elseif ($round == 'round') {
        $rmb = round($rmb);
    } elseif ($round == 'ceil') {
        $rmb = ceil($rmb);
    } elseif ($round == 'floor') {
        $rmb = floor($rmb);
    }
    return floatval($rmb);
}

function getpercent($a, $b) {
    if ($b <= 0)
        return 0;
    $per = round($a / $b, 2);
    return $per * 100;
}

/**
 * 计算两点地理坐标之间的距离
 * @param  Decimal $longitude1 起点经度
 * @param  Decimal $latitude1  起点纬度
 * @param  Decimal $longitude2 终点经度
 * @param  Decimal $latitude2  终点纬度
 * @param  Int     $unit       单位 1:米 2:公里
 * @param  Int     $decimal    精度 保留小数位数
 * @return Decimal
 */
function getdistance($longitude1, $latitude1, $longitude2, $latitude2, $unit = 2, $decimal = 2) {

    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI = 3.1415926;

    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI / 180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $distance = $distance * $EARTH_RADIUS * 1000;
    if ($unit == 2) {
        $distance = $distance / 1000;
    }
    return round($distance, $decimal);
}

if (!function_exists('array_column')) {

    function array_column($input, $columnKey, $indexKey = NULL) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
        $indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
        $result = array();

        foreach ((array) $input AS $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : NULL;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }

            $result[$key] = $tmp;
        }
        return $result;
    }

}

function bd_decrypt($arr) {
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $arr['longitude'] - 0.0065;
    $y = $arr['latitude'] - 0.006;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $data= array();
    $data[] = $z * cos($theta);
    $data[] = $z * sin($theta);
    return $data;
}
function bd_decrypt_list($arr) {
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $arr['longitude'] - 0.0065;
    $y = $arr['latitude'] - 0.006;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $arr['longitude'] = $z * cos($theta);
    $arr['latitude'] = $z * sin($theta);
    return $arr;
}
function trimall($str)//删除空格
{
    $qian=array(" ","　","\t","\n","\r");
    $hou=array("","","","","");
    return str_replace($qian,$hou,$str);
}


function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr"))
        return mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}
 function tree($data,$pid=0,$level=1){
    $tree = [];
    foreach($data as $k => $v)
    {
        $v['level']=$level;
        if($v['parentid'] == $pid)
        {        //父亲找到儿子

            $v['children'] = tree($data, $v['id']);
            $tree[] = $v;
        }
    }
    return $tree;
}

function doc_to_pdf($srcfilename,$destfilename){
    try {
        $word = new \COM("word.application") or die("Can't start Word!");
        $word->Visible=0;
        $word->Documents->Open($srcfilename, false, false, false, "1", "1", true);

        $word->ActiveDocument->final = false;
        $word->ActiveDocument->Saved = true;
        $word->ActiveDocument->ExportAsFixedFormat(
            $destfilename,
            17,                         // wdExportFormatPDF
            false,                      // open file after export
            0,                          // wdExportOptimizeForPrint
            3,                          // wdExportFromTo
            1,                          // begin page
            5000,                       // end page
            7,                          // wdExportDocumentWithMarkup
            true,                       // IncludeDocProps
            true,                       // KeepIRM
            1                           // WdExportCreateBookmarks
        );
        $word->ActiveDocument->Close();
        $word->Quit();
        $rs = str_replace('D:/phpStudy/PHPTutorial/WWW/trade',$_SERVER['HTTP_HOST'],$destfilename);
        jsonMsg("成功",0,$rs);
    } catch (\Exception $e) {
       jsonMsg("转换失败");
    }

}
// 发送短信
function VerifyCode($tel)
{
    //实例化配置里面的变量
    $url=Config('sendUrl');
    $sendUrl=$url["smsurl"];
    $smsConf=Config('smsConf');
    //随机生成六位随机的验证码
    $CheckCode=rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    //存入session可全局调用
    // Session::set($tel,$CheckCode);//压入缓存
    // Cache::set($tel,$CheckCode);
    $smsConf['mobile'] = $tel;
    $smsConf['tpl_value'] = "#code#=".$CheckCode."&#company#=鹦鹉岛";
    $content = juhecurl($sendUrl,$smsConf,1); //请求发送短信
    $aa = Sms($content);
    //将获取的验证码存入数据库
    $data = [
        'phone'=>$tel,
        'code'=>$CheckCode,
        'expire_time'=>time()+300,
    ];
    Db::name('check_code')->insert($data);
    return $aa;
}
function juhecurl($url,$params=false,$ispost=0){
    $httpInfo = array();
    $ch = curl_init();
 
    curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
    curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
    curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
    // curl_setopt( $ch, CURLOPT_REFERER, 'https://www.ydtkt.com/');//模拟来路
    if( $ispost )
    {
        curl_setopt( $ch , CURLOPT_POST , true );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
        curl_setopt( $ch , CURLOPT_URL , $url );
    }
    else
    {
        if($params){
            curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
        }else{
            curl_setopt( $ch , CURLOPT_URL , $url);
        }
    }
    $response = curl_exec( $ch );
    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
    $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
    curl_close( $ch );
    return $response;
}
function Sms($content){
    if($content){
        $result = json_decode($content,true);
        $error_code = $result['error_code'];
        if($error_code == 0){
            //状态为0，说明短信发送成功
            //echo "短信发送成功,短信ID：".$result['result']['sid'];
            return true;
        }else{
            //状态非0，说明失败
            $msg = $result['reason'];
            //echo "短信发送失败(".$error_code.")：".$msg;
            return false;
        }
    }else{
        //返回内容异常，以下可根据业务逻辑自行修改
        //echo "请求发送短信失败";
        return false;
    }
}
//自定义分页公共方法
function Fpage($pagenow,$count,$pagesize)
{
        //计算总页数
        $countPage=ceil($count/$pagesize);
        //首页
        $firstPage="<li class='common-pages-first common-pages-style' onclick='pagebody(1)'>首页</li>";
        //上一页
        if($pagenow==1){
            $pagenev="<li class='common-pages-prev common-pages-style'>上一页</li>";
        }else if($pagenow>1){
            $p=$pagenow-1;
            $pagenev="<li class='common-pages-prev common-pages-style' onclick='pagebody(".$p.")'>上一页</li>";
        }
        //页码
        $pagebody='';
        if($pagenow==5 || $pagenow<5){
            $first=1;
        }elseif($pagenow>5){
            $first=$pagenow-4;
        }
        if(($countPage-$pagenow)>5){
            $end=$first+9;
            if($end>$countPage){
                $end=$countPage;
            }
        }else{
            $end=$countPage;
        }
        for($i=$first;$i<=$end;$i++){
            if($i==$pagenow){
                //当前页面
                $pagebody.="<li class='common-pages-nums common-pages-nums-click' style='color:#fff'>$i</li>";
            }else{
                $pagebody.="<li class='common-pages-nums' onclick='pagebody(".$i.")'>$i</li>";
            }
        }
        //下一页
        if($pagenow==$countPage || $countPage == 0){
            $pagenex="<li class='page-words'>下一页</li>";
        }else if($pagenow<=$countPage){
            $p=$pagenow+1;
            $pagenex="<li class='common-pages-next common-pages-style' onclick='pagebody(".$p.")'>下一页</li>";
        }
        //尾页
        $lastPage="<li class='common-pages-last common-pages-style' onclick='pagebody(".$countPage.")'>尾页</li>";
        if($countPage >=1){
            $page=$firstPage.$pagenev.$pagebody.$pagenex.$lastPage;
        }else{
            $page='';
        }
        return $page;
}
//我的收藏分页方法
function collectFpage($pagenow,$count,$pagesize,$subject)
{
        //计算总页数
        $countPage=ceil($count/$pagesize);
        //首页
        $firstPage="<li class='common-pages-first common-pages-style' onclick='collectPageBody(1,".$subject.")'>首页</li>";
        //上一页
        if($pagenow==1){
            $pagenev="<li class='common-pages-prev common-pages-style'>上一页</li>";
        }else if($pagenow>1){
            $p=$pagenow-1;
            $pagenev="<li class='common-pages-prev common-pages-style' onclick='collectPageBody(".$p.",".$subject.")'>上一页</li>";
        }
        //页码
        $pagebody='';
        if($pagenow==5 || $pagenow<5){
            $first=1;
        }elseif($pagenow>5){
            $first=$pagenow-4;
        }
        if(($countPage-$pagenow)>5){
            $end=$first+9;
            if($end>$countPage){
                $end=$countPage;
            }
        }else{
            $end=$countPage;
        }
        for($i=$first;$i<=$end;$i++){
            if($i==$pagenow){
                //当前页面
                $pagebody.="<li class='common-pages-nums common-pages-nums-click' style='color:#fff'>$i</li>";
            }else{
                $pagebody.="<li class='common-pages-nums' onclick='collectPageBody(".$i.",".$subject.")'>$i</li>";
            }
        }
        //下一页
        if($pagenow==$countPage || $countPage == 0){
            $pagenex="<li class='page-words'>下一页</li>";
        }else if($pagenow<=$countPage){
            $p=$pagenow+1;
            $pagenex="<li class='common-pages-next common-pages-style' onclick='collectPageBody(".$p.",".$subject.")'>下一页</li>";
        }
        //尾页
        $lastPage="<li class='common-pages-last common-pages-style' onclick='collectPageBody(".$countPage.",".$subject.")'>尾页</li>";
        if($countPage >=1){
            $page=$firstPage.$pagenev.$pagebody.$pagenex.$lastPage;
        }else{
            $page='';
        }
        return $page;
}
//消息中心分页
function messageFpage($pagenow,$count,$pagesize,$status)
{
        //计算总页数
        $countPage=ceil($count/$pagesize);
        //首页
        $firstPage="<li class='common-pages-first common-pages-style' onclick='messagePageBody(1,".$status.")'>首页</li>";
        //上一页
        if($pagenow==1){
            $pagenev="<li class='common-pages-prev common-pages-style'>上一页</li>";
        }else if($pagenow>1){
            $p=$pagenow-1;
            $pagenev="<li class='common-pages-prev common-pages-style' onclick='messagePageBody(".$p.",".$status.")'>上一页</li>";
        }
        //页码
        $pagebody='';
        if($pagenow==5 || $pagenow<5){
            $first=1;
        }elseif($pagenow>5){
            $first=$pagenow-4;
        }
        if(($countPage-$pagenow)>5){
            $end=$first+9;
            if($end>$countPage){
                $end=$countPage;
            }
        }else{
            $end=$countPage;
        }
        for($i=$first;$i<=$end;$i++){
            if($i==$pagenow){
                //当前页面
                $pagebody.="<li class='common-pages-nums common-pages-nums-click' style='color:#fff'>$i</li>";
            }else{
                $pagebody.="<li class='common-pages-nums' onclick='messagePageBody(".$i.",".$status.")'>$i</li>";
            }
        }
        //下一页
        if($pagenow==$countPage || $countPage == 0){
            $pagenex="<li class='page-words'>下一页</li>";
        }else if($pagenow<=$countPage){
            $p=$pagenow+1;
            $pagenex="<li class='common-pages-next common-pages-style' onclick='messagePageBody(".$p.",".$status.")'>下一页</li>";
        }
        //尾页
        $lastPage="<li class='common-pages-last common-pages-style' onclick='messagePageBody(".$countPage.",".$status.")'>尾页</li>";
        if($countPage >=1){
            $page=$firstPage.$pagenev.$pagebody.$pagenex.$lastPage;
        }else{
            $page='';
        }
        return $page;
}

//   我的笔记分页
function noteFpage($pagenow,$count,$pagesize)
{
        //计算总页数
        $countPage=ceil($count/$pagesize);
        //首页
        $firstPage="<li class='common-pages-first common-pages-style' onclick='courseNote(1)'>首页</li>";
        //上一页
        if($pagenow==1){
            $pagenev="<li class='common-pages-prev common-pages-style'>上一页</li>";
        }else if($pagenow>1){
            $p=$pagenow-1;
            $pagenev="<li class='common-pages-prev common-pages-style' onclick='courseNote(".$p.")'>上一页</li>";
        }
        //页码
        $pagebody='';
        if($pagenow==5 || $pagenow<5){
            $first=1;
        }elseif($pagenow>5){
            $first=$pagenow-4;
        }
        if(($countPage-$pagenow)>5){
            $end=$first+9;
            if($end>$countPage){
                $end=$countPage;
            }
        }else{
            $end=$countPage;
        }
        for($i=$first;$i<=$end;$i++){
            if($i==$pagenow){
                //当前页面
                $pagebody.="<li class='common-pages-nums common-pages-nums-click' style='color:#fff'>$i</li>";
            }else{
                $pagebody.="<li class='common-pages-nums' onclick='courseNote(".$i.")'>$i</li>";
            }
        }
        //下一页
        if($pagenow==$countPage || $countPage == 0){
            $pagenex="<li class='page-words'>下一页</li>";
        }else if($pagenow<=$countPage){
            $p=$pagenow+1;
            $pagenex="<li class='common-pages-next common-pages-style' onclick='courseNote(".$p.")'>下一页</li>";
        }
        //尾页
        $lastPage="<li class='common-pages-last common-pages-style' onclick='courseNote(".$countPage.")'>尾页</li>";
        if($countPage >=1){
            $page=$firstPage.$pagenev.$pagebody.$pagenex.$lastPage;
        }else{
            $page='';
        }
        return $page;
}

function getTree($array){ 
    //第一步 构造数据
    $items = array();
    foreach($array as $value){
        $items[$value['id']] = $value;
    }
    //第二部 遍历数据 生成树状结构
    $tree = array();
    foreach($items as $key => $value){
        if(isset($items[$value['pid']])){
            $items[$value['pid']]['son'][] = &$items[$key];
        }else{
            $tree[] = &$items[$key];
        }
    }
    return $tree;
}
//urlsafe_base64_encode函数
function urlsafe_base64_encode($data) {
    $data = base64_encode($data);
    $data = str_replace(array('+','/'),array('-','_'),$data);
    return $data;
}
//获取 七牛云当前帧的截图并上传
function getNewImgPath($imgName,$imgSecond,$videoPath){

    $accessKey = 'PEe4FPr5SCILVbQ3T8mKw4TL4mk5PolFFzGuV4E0';
    $secretKey = 'XTt-clIWF8uDPaV9oRoYZiwFeeOjY1MGqZX_p-tf';

    //生成EncodedEntryURI的值
    $entry='litpic:'.$imgName;//<Key>为生成缩略图的文件名
    $encodedEntryURI=urlsafe_base64_encode($entry);//生成的值:eHp5cmVzOnRodW0tVHVsaXBzLmpwZw==
    //使用SecretKey对新的下载URL进行HMAC1-SHA1签名
    $videoPath= str_replace("http://","",$videoPath);
    // $newurl_1 = "pic.myxzy.com/Tulips.jpg?imageView/2/w/200/h/200|saveas/".$encodedEntryURI;
    $newurl_2 = $videoPath."?vframe/jpg/offset/".$imgSecond."/w/480/h/360|saveas/".$encodedEntryURI;
    // $sign_1 = hash_hmac("sha1", $newurl_1,$secretKey, true);
    $sign_2 = hash_hmac("sha1", $newurl_2,$secretKey, true);
    //对签名进行URL安全的Base64编码
    // $encodedSign_1 = urlsafe_base64_encode($sign_1);
    $encodedSign_2 = urlsafe_base64_encode($sign_2);

    //最终得到的完整下载URL
    // $finalURL_1 = "http://".$newurl_1."/sign/".$accessKey.":".$encodedSign_1;
    $finalURL_2 = "http://".$newurl_2."/sign/".$accessKey.":".$encodedSign_2;

    $res = curlGetContents($finalURL_2); 
    $res = json_decode($res,true);
    if(!empty($res['key']) && $res['key'] == $imgName){
        return "http://litpic.ydtkt.com/".$imgName;
    }
}
function curlGetContents($durl){
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$durl);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
    $data=curl_exec($ch);
    curl_close($ch);
    return $data;
} 

/**
 * 获取客户端真实的IP地址
 */
function getClientIP()  
{  
    $ip = isset($_SERVER['HTTP_CDN_SRC_IP'])?$_SERVER['HTTP_CDN_SRC_IP']:'';
    if (!$ip) {
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknow";
        }
    }
    if(strpos($ip,',') >= 0) {
        $arr = explode(',',$ip);
        $ip = $arr[0];
    }
    return $ip;
} 

/**
 * 插入消息
 */

 function insertMessage($uid, $title, $desc, $type = 0)
 {
     if (!$desc || !$uid) {
         return false;
     }

     $data = [
        'uid' => $uid,
        'title' => $title,
        'desc'  => $desc,
        'type'  => $type,
        'create_time' => time()
     ];
     Db::name('message')->insert($data);
 }

/**
 * 登录储存客户端的IP地址
 * @param $uid 登录用户id
 */
function ipLog($uid)
{
    if (!$uid) {
        return false;
    }
    $ip = getClientIP();
    $userIpLog = Db::name('ip_log')->where(['uid' => $uid, 'ip' => $ip])->find();
    
    if ($userIpLog) {
        $data['num'] = $userIpLog['num'] + 1;
        $data['create_time'] = date('Y-m-d H:i:s', time());
        $data['update_time'] = date('Y-m-d H:i:s', time());
        Db::name('ip_log')->where(['uid' => $uid, 'ip' => $ip])->update($data);
    } else {
        $data['ip']  =$ip;
        $data['uid'] = $uid;
        $data['num'] = 1;
        $data['create_time'] = date('Y-m-d H:i:s', time());
        Db::name('ip_log')->insert($data);
        // 48小时之内IP有5次不同的则危险
        $riskCount = Db::name('ip_log')->where(['uid' => $uid])->whereTime('create_time', '-2 day')->count('id');
        if ($riskCount >= 5) {
            Db::name('person')->where(['id' => $uid])->update(['risk' => 1]);
            // $msg = [
            //     'uid'           => $uid,
            //     'title'         => '账号异常提示',
            //     'desc'          => '您的账号在48小时之内更换了5次登陆IP，是否是您本人的操作，如是请点击确认取消提示！',
            //     'create_time'   => time(),
            //     'type'          => 1
            // ];
            // Db::name('message')->insert($msg);
        }
    }
    return true;
}

/**
 * 时间戳转换格式
 */
function timeTostring($second)
{
    if ($second < 60) {
        return 0;
    }
    $day = floor($second/(3600*24));
    $second = $second%(3600*24);//除去整天之后剩余的时间
    $hour = floor($second/3600);
    $second = $second%3600;//除去整小时之后剩余的时间 
    $minute = floor($second/60);
    $second = $second%60;//除去整分钟之后剩余的时间 
    //返回字符串
    return $day.'天'.$hour.'小时'.$minute.'分';
}

/**
 *  给某个数组值作为键
 */
function convert_arr_key($arr, $key_name) 
{ 
    $arr2 = []; 
    foreach($arr as $key => $val){ 
        $arr2[$val[$key_name]] = $val;         
    } 
    return $arr2; 
} 

/**
 * 代理商的无限极分类
 */
function resort($data,$parentid=0,$level=0){
    static $ret=array();
    foreach($data as $k=>$v){
        if($v['parent_id']==$parentid){
            $v['level']=$level;
            $ret[]=$v;
            resort($data,$v['uid'],$level+1);
        }
    }
    return $ret;
}

function dump($arr)
{
    echo '<pre>';
    var_dump($arr);
}

function GetIps(){  
      $realip = '';  
      $unknown = 'unknown';  
      if (isset($_SERVER)){  
          if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)){  
              $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);  
              foreach($arr as $ip){  
                  $ip = trim($ip);  
                  if ($ip != 'unknown'){  
                      $realip = $ip;  
                      break;  
                  }  
              }  
          }else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)){  
              $realip = $_SERVER['HTTP_CLIENT_IP'];  
          }else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)){  
              $realip = $_SERVER['REMOTE_ADDR'];  
          }else{  
              $realip = $unknown;  
          }  
      }else{  
          if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)){  
              $realip = getenv("HTTP_X_FORWARDED_FOR");  
          }else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)){  
              $realip = getenv("HTTP_CLIENT_IP");  
          }else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)){  
              $realip = getenv("REMOTE_ADDR");  
          }else{  
              $realip = $unknown;  
          }  
      }  
      $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;  
      return $realip;  
  }  
    
  function GetIpFrom($ip = ''){  
      if(empty($ip)){  
          $ip = GetIps();  
      }
 
     #  http://ip.fengtalk.com/ip/?ip=
     #  http://ip.taobao.com/service/getIpInfo.php?ip=
     $res = @file_get_contents('http://ip.fengtalk.com/ip/?ip='.$ip);
     
      if($res){
          $json = json_decode($res,true);
      }else{
          $json = '';
      }
 
      //var_dump($json);
      #   http://ip.taobao.com/service/getIpInfo.php?ip=
      // if(!empty($json)){

      //   $address[0] = $json['data']['country'].$json['data']['region'].$json['data']['city'].$json['data']['isp'];

      //   $address[1] = $ip;
      // }
      
      
      #  http://ip.fengtalk.com/ip/?ip=
      if(!empty($json)){

        $json['cityname'] = $json['cityname'] == '' ? '内网ip' : $json['cityname'];

        $address = [$json['cityname'], $ip];
      }

      return isset($address) ? $address : [0 => '未知',1 => '未知'];
  }

  //获取浏览器信息
function get_broswer(){
    $sys = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串
    if (stripos($sys, "Firefox/") > 0) {
        preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
        $exp[0] = "Firefox";
        $exp[1] = $b[1];  //获取火狐浏览器的版本号
    } elseif (stripos($sys, "Maxthon") > 0) {
        preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
        $exp[0] = "傲游";
        $exp[1] = $aoyou[1];
    } elseif (stripos($sys, "Baiduspider") > 0) {
        $exp[0] = "百度";
        $exp[1] = '蜘蛛';
    }elseif (stripos($sys, "YisouSpider") > 0) {
        $exp[0] = "一搜";
        $exp[1] = '蜘蛛';
    }elseif (stripos($sys, "Googlebot") > 0) {
        $exp[0] = "谷歌";
        $exp[1] = '蜘蛛';
    }elseif (stripos($sys, "Android 4.3") > 0) {
        $exp[0] = "安卓";
        $exp[1] = '4.3';
    }
    elseif (stripos($sys, "MSIE") > 0) {
        preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
        $exp[0] = "IE";
        $exp[1] = $ie[1];  //获取IE的版本号
    } elseif (stripos($sys, "OPR") > 0) {
        preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
        $exp[0] = "Opera";
        $exp[1] = $opera[1];
    } elseif(stripos($sys, "Edge") > 0) {
        //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
        preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
        $exp[0] = "Edge";
        $exp[1] = $Edge[1];
    } elseif (stripos($sys, "Chrome") > 0) {
        preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
        $exp[0] = "Chrome";
        $exp[1] = $google[1];  //获取google chrome的版本号
    } elseif(stripos($sys,'rv:')>0 && stripos($sys,'Gecko')>0){
        preg_match("/rv:([\d\.]+)/", $sys, $IE);
        $exp[0] = "IE";
        $exp[1] = $IE[1];
    }else if(stripos($sys,'AhrefsBot')>0){
        $exp[0] = "AhrefsBot";
        $exp[1] = '蜘蛛';
    }else if(stripos($sys,'Safari')>0){
        preg_match("/([\d\.]+)/", $sys, $safari);
        $exp[0] = "Safari";
        $exp[1] = $safari[1];
    }else if(stripos($sys,'bingbot')>0){
        $exp[0] = "必应";
        $exp[1] = '蜘蛛';
    }else if(stripos($sys,'WinHttp')>0){
        $exp[0] = "windows";
        $exp[1] = 'WinHttp 请求接口工具';
    }else if(stripos($sys,'iPhone OS 10')>0){
        $exp[0] = "iPhone";
        $exp[1] = 'OS 10';
    }else if(stripos($sys,'Sogou')>0){
        $exp[0] = "搜狗";
        $exp[1] = '蜘蛛';
    }else if(stripos($sys,'HUAWEIM')>0){
        $exp[0] = "华为";
        $exp[1] = '手机端';
    }else if(stripos($sys,'Dalvik')>0){
        $exp[0] = "安卓";
        $exp[1] = 'Dalvik虚拟机';
    }else if(stripos($sys,'Mac OS X 10')>0){
        $exp[0] = "MAC";
        $exp[1] = 'OS X10';
    }else if(stripos($sys,'Opera/9.8')>0){
        $exp[0] = "Opera";
        $exp[1] = '9.8';
    }else if(stripos($sys,'JikeSpider')>0){
        $exp[0] = "即刻";
        $exp[1] = '蜘蛛';
    }else if(stripos($sys,'Baiduspider')>0){
        $exp[0] = "百度";
        $exp[1] = '蜘蛛';
    }
    else {
        $exp[0] = $sys;
        $exp[1] = "";
    }
    return $exp[0].' '.$exp[1];
}

  //获取操作系统信息
function get_os(){
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os = false;
 
    if (preg_match('/win/i', $agent) && strpos($agent, '95'))
    {
        $os = 'Windows 95';
    }
    else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90'))
    {
        $os = 'Windows ME';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent))
    {
        $os = 'Windows 98';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))
    {
        $os = 'Windows Vista';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))
    {
        $os = 'Windows 7';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))
    {
        $os = 'Windows 8';
    }else if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))
    {
        $os = 'Windows 10';#添加win10判断
    }else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))
    {
        $os = 'Windows XP';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))
    {
        $os = 'Windows 2000';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))
    {
        $os = 'Windows NT';
    }
    else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent))
    {
        $os = 'Windows 32';
    }
    else if (preg_match('/linux/i', $agent))
    {
        $os = 'Linux';
    }
    else if (preg_match('/unix/i', $agent))
    {
        $os = 'Unix';
    }
    else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))
    {
        $os = 'SunOS';
    }
    else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))
    {
        $os = 'IBM OS/2';
    }
    else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))
    {
        $os = 'Macintosh';
    }
    else if (preg_match('/PowerPC/i', $agent))
    {
        $os = 'PowerPC';
    }
    else if (preg_match('/AIX/i', $agent))
    {
        $os = 'AIX';
    }
    else if (preg_match('/HPUX/i', $agent))
    {
        $os = 'HPUX';
    }
    else if (preg_match('/NetBSD/i', $agent))
    {
        $os = 'NetBSD';
    }
    else if (preg_match('/BSD/i', $agent))
    {
        $os = 'BSD';
    }
    else if (preg_match('/OSF1/i', $agent))
    {
        $os = 'OSF1';
    }
    else if (preg_match('/IRIX/i', $agent))
    {
        $os = 'IRIX';
    }
    else if (preg_match('/FreeBSD/i', $agent))
    {
        $os = 'FreeBSD';
    }
    else if (preg_match('/teleport/i', $agent))
    {
        $os = 'teleport';
    }
    else if (preg_match('/flashget/i', $agent))
    {
        $os = 'flashget';
    }
    else if (preg_match('/webzip/i', $agent))
    {
        $os = 'webzip';
    }
    else if (preg_match('/offline/i', $agent))
    {
        $os = 'offline';
    }else if (preg_match('/iPhone OS 8/i', $agent))
    {
        $os = 'iOS 8';
    }else if (preg_match('/YisouSpider/i', $agent))
    {
        $os = '一搜引擎';
    }else if (preg_match('/Yahoo! Slurp/i', $agent))
    {
        $os = '雅虎引擎';
    }else if (preg_match('/iPhone OS 6/i', $agent))
    {
        $os = 'iOS 6';
    }
    else if (preg_match('/Baiduspider/i', $agent))
    {
        $os = '百度引擎';
    }else if (preg_match('/iPhone OS 10/i', $agent))
    {
        $os = 'iOS 10';
    }else if (preg_match('/Mac OS X 10/i', $agent))
    {
        $os = 'Mac OS 10';
    }
    else if (preg_match('/Ahrefs/i', $agent))
    {
        $os = 'Ahrefs SEO 引擎';
    }
    else if (preg_match('/JikeSpider/i', $agent))
    {
        $os = '即刻引擎';
    }else if (preg_match('/Googlebot/i', $agent))
    {
        $os = '谷歌引擎';
    }else if(preg_match('/bingbot/i',$agent)){
        $os = '必应引擎';
    }else if(preg_match('/iPhone OS 7/i',$agent)){
        $os = 'iOS 7';
    }else if(preg_match('/Sogou web spider/i',$agent)){
        $os = '搜狗引擎';
    }else if(preg_match('/IP-Guide.com Crawler/i',$agent)){
        $os = 'IP-Guide Crawler 引擎';
    }else if(preg_match('/VenusCrawler/i',$agent)){
        $os = 'VenusCrawler 引擎';
    }
    else{
        $os = $agent;
    }
    return $os;
}



//将数值数组还原成字符串,$v为str2long返回的数组,$w为bool值(数组中是否包函原str长度)

function long2str($v, $w) {
    $len = count($v);
    $n = ($len - 1) << 2;
    if ($w) {
        $m = $v[$len - 1];
        if (($m < $n - 3) || ($m > $n)) return false;
        $n = $m;
    }
    $s = array();
    for ($i = 0; $i < $len; $i++) {
        $s[$i] = pack("V", $v[$i]);
    }
    if ($w) {
        return substr(join('', $s), 0, $n);
    }
    else {
        return join('', $s);
    }
 }
//将字符串转换成数值数组,$s要转换的字符串,$w为bool值(数组中是否包函原str长度)

function str2long($s, $w) {
    $v = unpack("V*", $s. str_repeat("\0", (4 - strlen($s) % 4) & 3));
    $v = array_values($v);
    if ($w) {
        $v[count($v)] = strlen($s);
    }
    return $v;
 }
function int32($n) {
    while ($n >= 2147483648) $n -= 4294967296;
    while ($n <= -2147483649) $n += 4294967296; 
    return (int)$n;
 }
//核心加密函数

function xxtea_encrypt($str, $key) {
    if ($str == "") {
        return "";
    }
    $v = str2long($str, true);
    $k = str2long($key, false);
    if (count($k) < 4) {
        for ($i = count($k); $i < 4; $i++) {
            $k[$i] = 0;
        }
    }
    $n = count($v) - 1;
 
    $z = $v[$n];
    $y = $v[0];
    $delta = 0x9E3779B9;
    $q = floor(6 + 52 / ($n + 1));
    $sum = 0;
    while (0 < $q--) {
        $sum = int32($sum + $delta);
        $e = $sum >> 2 & 3;
        for ($p = 0; $p < $n; $p++) {
            $y = $v[$p + 1];
            $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
            $z = $v[$p] = int32($v[$p] + $mx);
        }
        $y = $v[0];
        $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
        $z = $v[$n] = int32($v[$n] + $mx);
    }
    return long2str($v, false);
 }
//核心解密函数

function xxtea_decrypt($str, $key) {
    if ($str == "") {
        return "";
    }
    $v = str2long($str, false);
    $k = str2long($key, false);
    if (count($k) < 4) {
        for ($i = count($k); $i < 4; $i++) {
            $k[$i] = 0;
        }
    }
    $n = count($v) - 1;
 
    $z = $v[$n];
    $y = $v[0];
    $delta = 0x9E3779B9;
    $q = floor(6 + 52 / ($n + 1));
    $sum = int32($q * $delta);
    while ($sum != 0) {
        $e = $sum >> 2 & 3;
        for ($p = $n; $p > 0; $p--) {
            $z = $v[$p - 1];
            $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
            $y = $v[$p] = int32($v[$p] - $mx);
        }
        $z = $v[$n];
        $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
        $y = $v[0] = int32($v[0] - $mx);
        $sum = int32($sum - $delta);
    }
    return long2str($v, true);
 }

//包装修正后的加密函数,加密的文本越长返回值越长
function myencrypt($txt, $key = 'abcd9667676effff') {
    $s = urlencode(base64_encode(xxtea_encrypt($txt, $key)));
    $s = str_replace('%2F', '%252F', $s);    // fix nginx %2F 导致 rewrite 失效的问题
    return $s;
 }
//包装修正后的解密函数,如果密钥不正确则返回值为空

function mydecrypt($txt, $key = 'abcd9667676effff') {
    $txt = str_replace('%252F', '%2F', $txt);    // fix nginx %2F 导致 rewrite 失效的问题
    return xxtea_decrypt(base64_decode(urldecode($txt)), $key);    
 }

//
/**
 * 测评信息分页
 * @param   pagenow   int     页码 
 * @param   count     int     总数
 * @param   pagesize  int     每页条数
 * @param   subject   int     年级
 * @param   semester  int     学期
 * @param   subjectId int     科目id
 */
function TestPaperFpage($pagenow,$count,$pagesize,$subject,$semester,$subjectId)
{
        //计算总页数
        $countPage=ceil($count/$pagesize);
        //首页
        $firstPage="<li class='common-pages-first common-pages-style' onclick='TestPaperPageBody(1,".$subject.",".$semester.",".$subjectId.")'>首页</li>";
        //上一页
        if($pagenow==1){
            $pagenev="<li class='common-pages-prev common-pages-style'>上一页</li>";
        }else if($pagenow>1){
            $p=$pagenow-1;
            $pagenev="<li class='common-pages-prev common-pages-style' onclick='TestPaperPageBody(".$p.",".$subject.",".$semester.",".$subjectId.")'>上一页</li>";
        }
        //页码￥
        $pagebody='';
        if($pagenow==5 || $pagenow<5){
            $first=1;
        }elseif($pagenow>5){
            $first=$pagenow-4;
        }
        if(($countPage-$pagenow)>5){
            $end=$first+9;
            if($end>$countPage){
                $end=$countPage;
            }
        }else{
            $end=$countPage;
        }
        for($i=$first;$i<=$end;$i++){
            if($i==$pagenow){
                //当前页面
                $pagebody.="<li class='common-pages-nums common-pages-nums-click' style='color:#fff'>$i</li>";
            }else{
                $pagebody.="<li class='common-pages-nums' onclick='TestPaperPageBody(".$i.",".$subject.",".$semester.",".$subjectId.")'>$i</li>";
            }
        }
        //下一页
        if($pagenow==$countPage || $countPage == 0){
            $pagenex="<li class='page-words'>下一页</li>";
        }else if($pagenow<=$countPage){
            $p=$pagenow+1;
            $pagenex="<li class='common-pages-next common-pages-style' onclick='TestPaperPageBody(".$p.",".$subject.",".$semester.",".$subjectId.")'>下一页</li>";
        }
        //尾页
        $lastPage="<li class='common-pages-last common-pages-style' onclick='TestPaperPageBody(".$countPage.",".$subject.",".$semester.",".$subjectId.")'>尾页</li>";
        if($countPage >=1){
            $page=$firstPage.$pagenev.$pagebody.$pagenex.$lastPage;
        }else{
            $page='';
        }
        return $page;
}
/**
 * 错题本分页
 * @param   pagenow   int     页码 
 * @param   count     int     总数
 * @param   pagesize  int     每页条数
 * @param   subject   int     年级
 * @param   semester  int     学期
 * @param   subjectId int     科目id
 */
function wrongQuestionFpage($pagenow,$count,$pagesize,$subject,$semester,$subjectId)
{
        //计算总页数
        $countPage=ceil($count/$pagesize);
        //首页
        $firstPage="<li class='common-pages-first common-pages-style' onclick='wrongQuestionFpage(1,".$subject.",".$semester.",".$subjectId.")'>首页</li>";
        //上一页
        if($pagenow==1){
            $pagenev="<li class='common-pages-prev common-pages-style'>上一页</li>";
        }else if($pagenow>1){
            $p=$pagenow-1;
            $pagenev="<li class='common-pages-prev common-pages-style' onclick='wrongQuestionFpage(".$p.",".$subject.",".$semester.",".$subjectId.")'>上一页</li>";
        }
        //页码￥
        $pagebody='';
        if($pagenow==5 || $pagenow<5){
            $first=1;
        }elseif($pagenow>5){
            $first=$pagenow-4;
        }
        if(($countPage-$pagenow)>5){
            $end=$first+9;
            if($end>$countPage){
                $end=$countPage;
            }
        }else{
            $end=$countPage;
        }
        for($i=$first;$i<=$end;$i++){
            if($i==$pagenow){
                //当前页面
                $pagebody.="<li class='common-pages-nums common-pages-nums-click' style='color:#fff'>$i</li>";
            }else{
                $pagebody.="<li class='common-pages-nums' onclick='wrongQuestionFpage(".$i.",".$subject.",".$semester.",".$subjectId.")'>$i</li>";
            }
        }
        //下一页
        if($pagenow==$countPage || $countPage == 0){
            $pagenex="<li class='page-words'>下一页</li>";
        }else if($pagenow<=$countPage){
            $p=$pagenow+1;
            $pagenex="<li class='common-pages-next common-pages-style' onclick='wrongQuestionFpage(".$p.",".$subject.",".$semester.",".$subjectId.")'>下一页</li>";
        }
        //尾页
        $lastPage="<li class='common-pages-last common-pages-style' onclick='wrongQuestionFpage(".$countPage.",".$subject.",".$semester.",".$subjectId.")'>尾页</li>";
        if($countPage >=1){
            $page=$firstPage.$pagenev.$pagebody.$pagenex.$lastPage;
        }else{
            $page='';
        }
        return $page;
}

/** 
 * 获取本周的开始时间和结束时间 
 */ 
function getWeek_SdateAndEdate($current_time){ 
    $current_time = strtotime(date('Y-m-d',$current_time)); 
    $return_arr['sdate'] = $current_time-86400*(date('N',$current_time) - 1); 
    $return_arr['edate'] = $current_time+86400*(7- date('N',$current_time));

    return $return_arr; 
}

/**
 * 二维数组去重（去除重复的视频id）
 */
function assoc_unique($arr,$key){
    $tmp_arr = array();
    foreach ($arr as $k=>$v){
        if(in_array($v[$key],$tmp_arr)){
            unset($arr[$k]);
        }else{
            $tmp_arr[] = $v[$key];
        }
    }
    return $arr;
}

function a($arr){
    echo '<pre>';
    var_dump($arr);die;
}