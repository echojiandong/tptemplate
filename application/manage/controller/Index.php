<?php
namespace app\manage\controller;
use app\manage\model\Indexdata;
use app\manage\model\Menu;
use app\manage\model\Banner;
use app\manage\model\News;
use app\manage\model\Other;
use app\manage\model\Partner;
use app\manage\model\Profile;
use app\manage\model\User;
use app\manage\model\OrderPerson;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;
use think\image;
use think\Page;
class Index extends author
{
    public function index()
    {
        return view("index/index");
    }

    public function main(){
        #  获取当前登录人的代理id
        $userinfo = Session::get('manageinfo');

        $id = $userinfo['uid'];

        if($userinfo['user_type'] == 2){

            $id = $userinfo['agent_id'];
        }

        $agent_where = $person_where = $no_buy_where = $visotors_where = $buy_where = '1=1';

        if(!in_array($userinfo['group_id'], array(1,2))){

            $agent_where .= ' and agent_id='.$id.' and user_type = 2 or uid = '.$id;
        }
        #  查询代理下所有员工id
        $agent =  Db::name('user') ->field('uid') 
                                       ->where($agent_where) 
                                       ->select();
                                      
        if(!in_array($userinfo['group_id'], array(1,2))){

            $agent_arr = array_column($agent, 'uid');

            $person_where .= ' and user_id in ('.implode(',', $agent_arr).')';

            $no_buy_where .= ' and person_id in ('.implode(',', $agent_arr).')';
        }
        #  学员情况
        #  代理下总学员
        $person_arr = Db::name('person') ->field('id') ->where($person_where) ->select();

        if(!in_array($userinfo['group_id'],array(1,2))){

            !empty($person_arr) && $visotors_where .= ' and uid in ('.implode(',', array_column($person_arr, 'id')).')';
        }
        #  已购买学员
        $buy_person = Db::name('order_person') ->field('person_id') 
                                        ->where($person_where) 
                                        ->group('person_id') 
                                        ->select();
        #  未购买已试听学员
        $no_buy_audition_person = Db::name('audition_log') ->field('person_id')
                                            ->where($no_buy_where)
                                            ->group('person_id')
                                            ->select();

        if(!empty($buy_person) && !empty($no_buy_audition_person)){

            $no_buy_audition_person = array_diff(array_column($no_buy_audition_person, 'person_id'), array_column($buy_person, 'person_id'));
        }

        if(!empty($buy_person)){

            $buy_where .= ' and uid in ('.implode(',', array_column($buy_person, 'person_id')).')';
        }
        #   访问量统计
        #   总访问量
        $visitors = Db::name('visitors') ->field('id,add_time')
                                         ->select();
        #  今日访问量
        $today_visitor = 0;

        $day_time = date('Y-m-d', time());

        foreach($visitors as &$val){    

            date('Y-m-d', $val['add_time']) == $day_time && ++$today_visitor;
        }
        #  今日活跃用户
        !empty($buy_person) && $no_die_person = Db::name('ip_log') ->field('count(1) as count') 
                                            ->where('create_time', 'like', '%'.$day_time.'%') 
                                            ->where($buy_where) 
                                            ->group('uid')
                                            ->find();
        #  总收入
        $all_money = Db::name('order_person') ->field('sum(payMoney) as countmoney') 
                                             ->where($person_where) 
                                             ->where(['orderCheck' => 2, 'state' => 2])
                                             ->find();
        #  今日收入
        $day_money = Db::name('order_person') ->field('sum(payMoney) as countmoney')
                                              ->where($person_where)
                                              ->where('TO_DAYS(endtime) = TO_DAYS(NOW())')
                                              ->where(['orderCheck' => 2, 'state' => 2])
                                              ->find();
        #  总学员
        $this ->assign('person_arr', count($person_arr));
        #  已购买学员
        $this ->assign('buy_person', count($buy_person));
        #  未购买已试听学员
        $this ->assign('no_buy_audition_person', count($no_buy_audition_person));
        #  总访问量
        $this ->assign('visitors', count($visitors));
        #  今日访问量
        $this ->assign('today_visitor',$today_visitor);
        #  今日活跃用户
        $this ->assign('no_die_person',isset($no_die_person['count']) ? $no_die_person['count'] : 0);
        #  总收入
        $this ->assign('all_money',isset($all_money['countmoney']) ? $all_money['countmoney'] : 0);
        #  今日收入
        $this ->assign('day_money', isset($day_money['countmoney']) ? $day_money['countmoney'] : 0);

        $this ->assign('_timer',date('Y-m-d'));

        return $this->fetch("index/main");
    }

    public function incomefuc(Request $request){
        $param = $request ->param();

        $index = isset($param['index']) ? $param['index'] : 0;

        $userinfo = Session::get('manageinfo');//获取当前登陆人信息

        $id = $userinfo['uid'];

        if($userinfo['user_type']== 2){

            $id = $userinfo['agent_id'];
        }

        $agent_where = $person_where = $no_buy_where = $visotors_where = '1=1';

        if(!in_array($userinfo['group_id'], array(1,2))){

            $agent_where ='agent_id='.$id.' and user_type = 2 or uid = '.$id;
        }
        #  查询代理下所有员工id
        $agent =  Db::name('user')->where($agent_where) ->column('uid');
        if($index == 0)
        {//今天
            $_time = 'd';
            $timers = '%Y-%m-%d %H:%m';
            $data = $this ->getHours();
        }
        if($index == 1){
            $_time = 'w';

            $timers = '%Y-%m-%d';

            $data = $this ->getDay();
        }

        if($index == 2){
            // 月
            $_time = 'm';

            $timers = '%Y-%m-%d';

            $data = $this ->getMonth();
        }

        if($index == 3){

            $_time = 'y';

            $timers = '%Y-%m';

            $data = $this ->getYear();
        }

        $_res = Db::name('order_person') ->field('id,date_format(endtime,"'.$timers.'") as timers,endtime')
                                  ->where(['orderCheck' => 2, 'is_forbidden' => 0, 'state' => 2]) 
                                  ->where('user_id', 'in', $agent)
                                  ->whereTime('endtime', $_time)
                                  ->select();
        foreach($data as $key =>&$val){
            $res[] = Db::name('order_person')
                                  ->where(['orderCheck' => 2, 'is_forbidden' => 0, 'state' => 2]) 
                                  ->where('user_id', 'in', $agent)
                                  ->where('endtime', 'like', $val.'%')
                                  ->sum('payMoney');
        }
        $max = max($res);

        $max = $this ->getMax($max);

        $max = $max == 1 ? 10 : $max;
        
        ajaReturn($res,0,'success',$max);
        
    }

    public function visitorsfuc(Request $request){

        $param = $request ->param();

        $index = isset($param['index']) ? $param['index'] : 0;
        if($index == 0)
        {//今天
            $_time = '%Y-%m-%d %H:%m';
            $data = $this ->getHours();
        }
        if($index == 1){
            //  周
            $_time = '%Y-%m-%d';

            $data = $this ->getDay();
        }

        if($index == 2){
            // 月
            $_time = '%Y-%m-%d';

            $data = $this ->getMonth();
        }

        if($index == 3){

            $_time = '%Y-%m';

            $data = $this ->getYear();
        }
        foreach($data as $key =>&$val){
            $startTime=strtotime($val.':00:00');
            $endTime=strtotime($val.':59:59');
            $where['add_time']=array('between',"$startTime,$endTime");
            $res[] = Db::name('visitors')
                                  ->where($where)
                                  ->count();
        }

        $max = max($res);

        $max = $this ->getMax($max);

        $max = $max == 1 ? 10 : $max;

        ajaReturn($res,0,'success',$max);

    }

    public function getMax($string){

        $string = (string)$string;

        $length = strlen($string);

        $first_wei = (int)$string[0];

        ++$first_wei;

        $length = strlen($first_wei) == 1 ? $length : ++$length;

        $first_wei = (string)$first_wei;

        $first_wei = str_pad($first_wei, $length, 0, STR_PAD_RIGHT);

        return $first_wei;
    }
    public function getHours()
    {
        $beginTime = mktime(0,0,0,date("m"),date("d"),date("y"));
        for($i = 0; $i < 24; $i++){
            $b = $beginTime + ($i * 3600);
            $arr[$i]=date('Y-m-d H',strtotime(date("Y-m-d H:i:m",$b))); 
        }
        return $arr;
    }
    //  获取本周每一天的日期
    public function getDay(){
        $timestr = time();    //当前时间戳
        $now_day = date('w',$timestr);  //当前是周几

        //获取周一
        $monday_str = $timestr - ($now_day-1)*60*60*24;
        $monday = date('Y-m-d', $monday_str);

        //获取周日
        $sunday_str = $timestr + (7-$now_day)*60*60*24;
        $sunday = date('Y-m-d', $sunday_str);

        for($i=0;$i<7;$i++)  
        {  
            $arr[$i]=date('Y-m-d',strtotime($monday.'+'.$i.'day'));  
        }
        return $arr; 
    }
    // 获取本月每一天的日期
    public function getMonth(){

        $days = date("t");
        
        for($i = 0; $i < intval($days); $i++){
            # 获取当月每天
            $day[] = date('Y-m-d', strtotime("+" .$i. " day", strtotime(date("Y-m-01"))));
            # 获取每天开始时间
            // $start = date('Y-m-d H:i:s', strtotime("+" . $i . " day", strtotime(date("Y-m-01 00:00:00"))));
            # 获取每天结束时间
            // $end = date('Y-m-d H:i:s', strtotime("+" . $i . " day", strtotime(date("Y-m-01 23:59:59"))));
        }
        return $day;
    }
    // 获取 本年 每月的月份的开始时间和结束时间
    public function getYear(){

        $year = date('Y');

        $start = 1;

        $end = 12;

        if($year == 2019){

            $start = 7;
        }

        for($i = $start; $i <= $end; $i++){

            $data[] = $year.'-'.str_pad($i, 2, 0, STR_PAD_LEFT);
        }

        return $data;
    }

    /**
     * 获取指定年月日的开始时间戳和结束时间戳(本地时间戳非GMT时间戳)
     * [1] 指定年：获取指定年份第一天第一秒的时间戳和下一年第一天第一秒的时间戳
     * [2] 指定年月：获取指定年月第一天第一秒的时间戳和下一月第一天第一秒时间戳
     * [3] 指定年月日：获取指定年月日第一天第一秒的时间戳
     * @param  integer $year     [年份]
     * @param  integer $month    [月份]
     * @param  integer $day      [日期]
     * @return array('start' => '', 'end' => '')
     */
    function getStartAndEndUnixTimestamp($year = 0, $month = 0, $day = 0)
    {
        if(empty($year))
        {
            $year = date("Y");
        }

        $start_year = $year;
        $start_year_formated = str_pad(intval($start_year), 4, "0", STR_PAD_RIGHT);
        $end_year = $start_year + 1;
        $end_year_formated = str_pad(intval($end_year), 4, "0", STR_PAD_RIGHT);

        if(empty($month))
        {
            //只设置了年份
            $start_month_formated = '01';
            $end_month_formated = '01';
            $start_day_formated = '01';
            $end_day_formated = '01';
        }
        else
        {

            $month > 12 || $month < 1 ? $month = 1 : $month = $month;
            $start_month = $month;
            $start_month_formated = sprintf("%02d", intval($start_month));

            if(empty($day))
            {
                //只设置了年份和月份
                $end_month = $start_month + 1;
                
                if($end_month > 12)
                {
                    $end_month = 1;
                }
                else
                {
                    $end_year_formated = $start_year_formated;
                }
                $end_month_formated = sprintf("%02d", intval($end_month));
                $start_day_formated = '01';
                $end_day_formated = '01';
            }
            else
            {
                //设置了年份月份和日期
                $startTimestamp = strtotime($start_year_formated.'-'.$start_month_formated.'-'.sprintf("%02d", intval($day))." 00:00:00");
                $endTimestamp = $startTimestamp + 24 * 3600 - 1;
                return array('start' => $startTimestamp, 'end' => $endTimestamp);
            }
        }

        $startTimestamp = strtotime($start_year_formated.'-'.$start_month_formated.'-'.$start_day_formated." 00:00:00");            
        $endTimestamp = strtotime($end_year_formated.'-'.$end_month_formated.'-'.$end_day_formated." 00:00:00") - 1;
        return array('start' => date('Y-m-d', $startTimestamp), 'end' => date('Y-m-d', $endTimestamp));
    }
    public function loginout(){
        Cookie::delete('manageinfo');
        Session::delete('manageinfo');
        jsonMsg("退出成功","0");
    }

    public function getnavs(Request $request){//获取左侧导航列表
        if($request->isAjax()){
            $menulist = New Menu();
            $rs = $menulist->getmenu();
            if($rs){jsonMsg("success",0,$rs);}}else{jsonMsg("非法请求",1);
        }
    }

    public function showmenu(Request $request){//显示菜单管理界面
        $data = DB::name('menu')->where(['type'=>1])->order('menuid asc')->select();
        $result = tree($data);
        $this->assign("list",$result);
        return $this->fetch('menu/menulist');
    }

    public function menuadd(Request $request){//输出添加菜单页面
        $param=$request->param();
        $result = Db::name('menu')->where(['type'=>1])->select();
        $this->assign("menulist",$result);
        if(isset($param['id'])){
            $list = Db::name('menu')->where(['id'=>$param['id']])->find();
            $this->assign("list",$list);
        }

        return $this->fetch('menu/menuadd');
    }

    public function menuadd_handle(Request $request){
        if($request->isAjax()){
            $rs= new Menu();
            if($rs->addmenu_menu()){jsonMsg("更新成功",0);}else{jsonMsg("更新失败",1);}
        }else{
            jsonMsg("非法提交",1);
        }
    }

    public function menu_update(Request $request){
        $param=$request->param();
        $result = Db::name('menu')->where(['parentid'=>0,'type'=>1])->select();
        $this->assign("menulist",$result);
        if(isset($param['id'])){
            $list = Db::name('menu')->where(['id'=>$param['id']])->find();
            $this->assign("list",$list);
        }

        return $this->fetch('menu/menu_update');
    }

    public function menuupdate_handle(Request $request){//修改菜单
        if($request->isAjax()){
            $rs= new Menu();
            if($rs->update_menu()){jsonMsg("更新成功",0);}else{jsonMsg("更新失败",1);}
        }else{
            jsonMsg("非法提交",1);
        }
    }

    public function showicon(){//输出添加菜单图标界面
        return view('menu/icon');
    }

    public function menuswitch(Request $request){//菜单状态
        $param = $request->param();
        $data = DB::name('menu')->update(['id'=>$param['id'],'status'=>$param['status']]);
        if($data>0){
            jsonMsg("更新成功",0);
        }else{
            jsonMsg("更新失败",1);
        }
    }

    public function delmenu(Request $request){
        if($request->isAjax()){
            $menu = new Menu();
            if($menu->deletemenu()){
                jsonMsg("删除成功",0);
            }else{
                jsonMsg("删除失败",1);
            }
        }else{
            jsonMsg("非法提交",1);
        }

    }
    public function menu_sort(Request $request){
        if($request->isAjax()){
            $menu = new Menu();
            if($menu->upmenuid()){
                jsonMsg("修改成功",0);
            }else{
                jsonMsg("修改失败",1);
            }
        }else{
            jsonMsg("非法提交",1);
        }
    }
    public function bannerlist(){
        return view("manage/banner/banner");
    }
    public function getbannerdata(){
        $rs = Banner::all();
        return jsonMsg("success",0,$rs);
    }
    public function showbanneradd(){
        return view("manage/banner/addbanner");
    }
    public function upload(){
        $file = request()->file('file');
        // $s3 = new \Aws\S3\S3Client([
        //     'version' => '2006-03-01',
        //     'region'  => 'us-west-2',
        //     'credentials' => [
        //         'key'    => '74e13b42-6c66-43e9-a147-f24225badf90',
        //         'secret' => 'df778d4b-506c-4ea7-bcf5-c8173c44ef59',
        //     ],
        //     'endpoint' => 'http://cosapi-wz.chinacache.com'
        // ]);
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'video');
            // $bucket = 'fckjai-img';
            // $key ='ok_'.time().'.jpg'; 
            // $tr=$s3->putObject([
            //     'Bucket' => $bucket,
            //     'Key'    => $key,
            //     'SourceFile'   =>$info->getpathName()
            // ]);
            // $imageUrl=$info->getpathName();
            // $intr = unlink($imageUrl);
            if($info){
                jsonMsg("成功",0,"/video/".$info->getSaveName());
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }

    public function addbanner(Request $request){
        if($request->isAjax()){
            $param = $request->param();
            if(isset($param['title'])){$where['title']=$param['title'];}
            if(isset($param['url'])){$where['pic']=$param['url'];}
            if(isset($param['link'])){$where['link']=$param['link'];}
            if(isset($param['cate'])){$where['cate']=$param['cate'];}
            if(isset($param['status'])){$where['status']=$param['status'];}
            $where['time']=time();
            $banner = new Banner();
            $rs = $banner->save($where);
            if($rs){jsonMsg("添加成功",0);}else{jsonMsg("添加失败",1);}
        }else{
            jsonMsg("非法提交",1);
        }

    }

    public function delbanner(Request $request){
        if($request->isAjax()){
            $param = $request->param();
            if(isset($param['id'])){
                $banner  = Banner::get($param['id']);
                $rs= $banner->delete();
                // $rs = Db::name("banner")->where(['id'=>$param['id']])->delete();
                //unlink('/public/uploads/PPT/'.$thumb["picName"]);
                if($rs){
                    jsonMsg("删除成功",0);
                }else{
                    jsonMsg("删除失败，请重试",1);
                }
            }else{
                jsonMsg("尚未选择删除的图片",1);
            }
        }else{
            jsonMsg("非法提交",1);
        }
    }

    public function update_handle(Request $request){
        if($request->isAjax()){
            $param = $request->param();
            if(isset($param['id'])){
                $where[$param['field']]=$param['val'];
                $rs = Db::name("banner")->where(['id'=>$param['id']])->update($where);
                if($rs){
                    jsonMsg("修改成功",0);
                }else{
                    jsonMsg("修改失败，请重试",1);
                }
            }else{
                jsonMsg("没有找到条件",1);
            }
        }else{
            jsonMsg("非法提交",1);
        }
    }
    public function update_status(Request $request){
        if($request->isAjax()){
            $param = $request->param();
            if(isset($param['id'])){
                $where["status"]=$param['val'];
                $rs = Db::name("banner")->where(['id'=>$param['id']])->update($where);
                if($rs){
                    jsonMsg("修改成功",0);
                }else{
                    jsonMsg("修改失败，请重试",1);
                }
            }else{
                jsonMsg("没有找到条件",1);
            }
        }else{
            jsonMsg("非法提交",1);
        }
    }

    public function about(Request $request){
        $param = $request->param();
        $where['id']=$param['id'];
        $rs = Db::name("other")->where($where)->find();
        if(!$rs){
            $where['content']="暂无内容";
            $where['title']="请填写内容标题例如：公司简介";
            Db::name("other")->insert($where);
        }
        $this->assign("data",$rs);
        return $this->fetch("manage/other/content");
    }

    public function update_about(Request $request){
        $param = $request->param();
        if(isset($param['id'])){$where['id']=$param['id'];}else{jsonMsg("没有修改目标","1");}
        if(isset($param['content'])){$where['content']=$param['content'];}else{jsonMsg("没有填写内容","1");}
        if(isset($param['title'])){$where['title']=$param['title'];}else{jsonMsg("没有标题","1");}
        $where['time']=time();
        $rs = Db::name("other")->update($where);
        if($rs){jsonMsg("修改成功",0);}else{jsonMsg("修改失败",1);}
    }

    public function shownews(Request $request){
        $param = $request->param();
        if($param){
            return view("news/newsList",['title'=>"文章列表"]);
        }else{
            return view("news/newState",['title'=>"待审核文章"]);
        }

    }

    public function getnewslist(Request $request){
        $param = $request->param();
        if($param['type']=='null' || $param['type']==''){
            $where['newsType']=['>',1];
        }else{
            $where['newsType']=$param['type'];
        }
        $news= Db::table('guard_newslist')
            ->where($where)
            ->page($param['page'],$param['limit'])
            ->column('id,newsName,newsAuthor,isShow,newsType,newsTime');
        $count= Db::table('guard_newslist')
            ->where($where)
            ->count();
        if($news){jsonMsg("success","0",$news,$count);}else{jsonMsg('暂时没有内容',1,$news,$count);}
    }
    public function newStateList(Request $request){
        $param = $request->param();
        $where['newsType']=2;
        $news= Db::table('guard_newslist')
            ->where($where)
            ->page($param['page'],$param['limit'])
            ->column('id,newsName,newsAuthor,isShow,newsType,newsTime');
        $count= Db::table('guard_newslist')
            ->where($where)
            ->count();
        if($news){jsonMsg("success","0",$news,$count);}else{jsonMsg('暂时没有内容',1,$news,$count);}
    }
  
    public function update_news(Request $request){
        $param = $request->param();
        $news = new News();
        $rs =$news->save([$param['field'] =>$param['val']],['id' =>$param['id']]);
        if($rs){jsonMsg("更新成功",0);}else{jsonMsg("更新失败",1);}

    }
    public function update_news_status(Request $request){
        if($request->isAjax()){
            $param = $request->param();
            if(isset($param['id'])){
                $news = new News();
                $rs =$news->save(["isShow"=>$param['val']],['id' =>$param['id']]);
                if($rs){
                    jsonMsg("修改成功",0);
                }else{
                    jsonMsg("修改失败，请重试",1);
                }
            }else{
                jsonMsg("没有找到条件",1);
            }
        }else{
            jsonMsg("非法提交",1);
        }
    }

    public function addnews(){
        return view("manage/news/newsAdd",['title'=>"添加文章"]);
    }

    public function addnews_handle(Request $request){
        $param = $request->param();
        if(isset($param['title'])){$where['newsName']=$param['title'];}
        if(isset($param['author'])){$where['newsAuthor']=$param['author'];}
        if(isset($param['isShow'])){$where['isShow']=$param['isShow'];}
        if(isset($param['editorValue'])){$where['newsContent']=$param['editorValue'];}
        if(!empty($param['type'])){$where['newsType']=$param['type'];}else{jsonMsg("缺少文章类型",1);}
        $where['newsTime']=time();
        $news = new News();
        $rs = $news->save($where);
        if($rs){jsonMsg("添加成功",0);}else{jsonMsg("添加失败",1);}
    }

    public function getnews(Request $request){
        $param = $request->param();
        $rs =News::get(['id'=>$param['id']]);
        $this->assign("news",$rs);
        return $this->fetch("manage/news/shownews");
    }
    public function delnews(Request $request){
        $id = $request->param("id");
        if(!$id){jsonMsg("请选择文章",0);}
        $rs = News::destroy(['id' =>$id]);
        if($rs){jsonMsg("删除成功",0);}else{jsonMsg("删除失败",1);}
    }

    public function news_update(Request $request){
        $id = $request->param("id");
        $rs = News::get(['id'=>$id]);
        $this->assign("newscon",$rs);
        return $this->fetch("manage/news/newsUpdate");
    }
    public function news_update_handle(Request $request){
        $param = $request->param();
        if(isset($param['title'])){$where['newsName']=$param['title'];}
        if(isset($param['author'])){$where['newsAuthor']=$param['author'];}
        if(isset($param['isShow'])){$where['isShow']=$param['isShow'];}
        if(isset($param['editorValue'])){$where['newsContent']=$param['editorValue'];}
        if(isset($param['url'])){$where['images']=$param['url'];}
        $where['newsTime']=time();
        $news = new News();
        $rs = $news->save($where,['id'=>$param['id']]);
        if($rs){jsonMsg("修改成功",0);}else{jsonMsg("修改失败",1);}
    }

    public function partnerlist(Request $request){
        if($request->isAjax()){
            $rs = Partner::all();
            if($rs){jsonMsg("success",0,$rs);}else{jsonMsg('error',1);}
        }else{
            jsonMsg("非法请求",1);
        }
    }

    public function showpartner_page(){
        return view("manage/partner/partner");
    }
    public function showaddpartner(){
        return view("manage/partner/addpartner");
    }
    public function delpartner(Request $request){
        if($request->isAjax()){
            $param = $request->param();
            if(isset($param['id'])){
                $rs = Db::name("banner")->where(['id'=>$param['id']])->delete();
                if($rs){
                    jsonMsg("删除成功",0);
                }else{
                    jsonMsg("删除失败，请重试",1);
                }
            }else{
                jsonMsg("尚未选择内容",1);
            }
        }else{
            jsonMsg("非法提交",1);
        }
    }

    public function addpartner_handle(Request $request){
        $param = $request->param();
        if(isset($param['title'])){$where['conpanyname']=$param['title'];}
        if(isset($param['isShow'])){$where['status']=$param['isShow'];}
        if(isset($param['editorValue'])){$where['content']=$param['editorValue'];}
        if(!empty($param['link'])){$where['link']=$param['link'];}else{jsonMsg("缺少网址",1);}
        $where['time']=time();
        $news = new Partner();
        $rs = $news->save($where);
        if($rs){jsonMsg("添加成功",0);}else{jsonMsg("添加失败",1);}
    }
    public function update_parener_status(Request $request){
        if($request->isAjax()){
            $param = $request->param();
            if(isset($param['id'])){
                $where["status"]=$param['val'];
                $paertner = new Partner();
                $rs = $paertner->where(['id'=>$param['id']])->update($where);
                if($rs){
                    jsonMsg("修改成功",0);
                }else{
                    jsonMsg("修改失败，请重试",1);
                }
            }else{
                jsonMsg("没有找到条件",1);
            }
        }else{
            jsonMsg("非法提交",1);
        }
    }
    public function update_paertner_handle(Request $request){//修改菜单
        if($request->isAjax()){
            $paertner= new Partner();
            $param = $request->param();
            $where[$param['field']]=$param['val'];
            $rs=$paertner->where(['id'=>$param['id']])->update($where);
            if($rs){jsonMsg("更新成功",0);}else{jsonMsg("更新失败",1);}
        }else{
            jsonMsg("非法提交",1);
        }
    }
    public function delpaertner(Request $request){
        if($request->isAjax()){
            $rs = Partner::get($request->param("id"));
            if($rs->delete()){jsonMsg("删除成功",0);}else{jsonMsg("删除失败",1);}
        }else{
            jsonMsg("非法提交",1);
        }
    }

    public function update_partner(Request $request){
        $rs = Partner::get($request->param("id"));
        $this->assign("result",$rs);
        return $this->fetch("manage/partner/partnerUpdate");
    }

    public function paertner_update_handle(Request $request){
        $param = $request->param();
        if(isset($param['title'])){$where['conpanyname']=$param['title'];}
        if(isset($param['link'])){$where['link']=$param['link'];}
        if(isset($param['status'])){$where['status']=$param['status'];}
        if(isset($param['editorValue'])){$where['content']=$param['editorValue'];}
        $where['time']=time();
        $news = new Partner();
        $rs = $news->save($where,['id'=>$param['id']]);
        if($rs){jsonMsg("修改成功",0);}else{jsonMsg("修改失败",1);}
    }

    public function get_alluser(){
        return view('manage/user/alluser',['title'=>"用户管理"]);
    }
    public function add_user(){
        return view('manage/user/add',['title'=>"添加管理人员"]);
    }
    public function get_userlist(Request $request){
        if($request->isAjax()){
            $rs=Db::view('User')
                ->view('User_profile','*','user_profile.uid=User.uid')
                ->where(['role_id'=>1])
                ->select();
            $arr=array();
            foreach($rs as $key=> $v){
                $v['key']=$key+1;
                $v['addtime'] =date("Y-m-d H:i:s",$v['create_date']);
                array_push($arr,$v);
            }
            jsonMsg('success',0,$arr);
        }else{
            jsonMsg('不能接受的请求');
        }
    }
    public function showadduser(){
        return view("manage/user/adduser",['title'=>'添加用户']);
    }

    public function adduser(Request $request){
        if($request->isAjax()){
            $param = $request->param();
            $user = new User();
            $profile = new Profile();
            if(isset($param['username'])){$user->username=$param['username'];}
            if(isset($param['phone'])){$user->phone=$param['phone'];}
            if(isset($param['password'])){$user->password=$param['password'];}
            $user->create_date=time();
            if(isset($param['corporation'])){$profile->corporation=$param['corporation'];}
            if(isset($param['company'])){$profile->company=$param['company'];}
            if(isset($param['licence'])){$profile->licence=$param['licence'];}
            if(isset($param['establishment'])){$profile->establishment=$param['establishment'];}
            if(isset($param['register_capital'])){$profile->register_capital=$param['register_capital'];}
            if(isset($param['contact_name'])){$profile->contact_name=$param['contact_name'];}
            if(isset($param['company_phone'])){$profile->company_phone=$param['company_phone'];}
            if(isset($param['company_address'])){$profile->company_address=$param['company_address'];}
            if(isset($param['licenceimg'])){$profile->licenceimg=$param['licenceimg'];}
            $user->profile=$profile;
            $rs=$user->together('profile')->save();
            if(User::get(['username'=>$param['username']])){jsonMsg('添加失败，因为用户已存在');}
            if(false === $rs){jsonMsg($user->getError());}
            if($rs){jsonMsg("添加成功",0);}else{jsonMsg("添加失败",1);}
        }else{
            jsonMsg("非法提交",1);
        }
    }

    public function getuserinfo(Request $request){
        if($request->isAjax()){

        }else {
            jsonMsg("非法提交", 1);
        }
    }

    public function update_userstatus(Request $request){
        if($request->isAjax()){
            $param = $request->param();
            if(isset($param['id'])){
                $where["status"]=$param['val'];
                $rs = User::where(['uid'=>$param['id']])->update($where);
                if($rs){
                    jsonMsg("修改成功",0);
                }else{
                    jsonMsg("修改失败，请重试",1);
                }
            }else{
                jsonMsg("没有找到条件",1);
            }
        }else{
            jsonMsg("非法提交",1);
        }
    }

    public function show_userinfo(Request $request){
        $param = $request->param();
        $rs=Db::view('User',['uid'=>'id','*'])
            ->view('User_profile','*','user_profile.uid=User.uid')
            ->where('id','eq',$param['id'])
            ->find();
        $rs['addtime']=date("Y-m-d H:i:s",$rs['create_date']);
        $this->assign('info',$rs);
        return $this->fetch('manage/user/showuser');
    }

    public function show_finance_page(Request $request){
        $param = $request->param();
        $where['id']=$param['id'];
        $other = new Other();
        $rs = Other::get($where);
        if(!$rs){
            $where['content']="暂无内容";
            $where['title']="请填写内容标题例如：公司简介";
            $other->save($where);
        }
        $this->assign("data",$rs);
        return $this->fetch("manage/other/finance");
    }

    public function update_finance_page(Request $request){
        $param = $request->param();
        if(isset($param['id'])){$where['id']=$param['id'];}else{jsonMsg("没有修改目标","1");}
        if(isset($param['content'])){$where['content']=$param['content'];}else{jsonMsg("没有填写内容","1");}
        if(isset($param['title'])){$where['title']=$param['title'];}else{jsonMsg("没有标题","1");}
        $where['time']=time();
        $other = new Other();
        $rs = $other->save($where,['id'=>$param['id']]);
        if($rs){jsonMsg("修改成功",0);}else{jsonMsg("修改失败",1);}
    }

    public function update_indexdata(Request $request){
        if($request->isAjax()){
            $data = Indexdata::get(1);
            $key = $request->param('key');
            $num = $request->param('text');
            if(!is_numeric($num)){jsonMsg('所填写必须是数字');}
            $data->$key=$num;
            $rs=$data->save();
            if($rs){
                jsonMsg("success",0);
            }else{
                jsonMsg("修改失败,或者没做任何修改");
            }
        }else{
            jsonMsg("非法操作");
        }
    }

    public function editbanner(Request $request)
    {
        $param=$request->param();
        $where['id']=$param['id'];
        $res=Db::name('banner')->where($where)->find();
        $this->assign('result',$res);
        return $this->fetch("manage/banner/editbanner");
    }
    public function editbanner_handle(Request $request)
    {
        $param=$request->param();
        $where['id']=$param['id'];
        $data['title']=$param['title'];
        $data['pic']=$param['url'];
        $data['link']=$param['link'];
        $data['file']=$param['file'];
        $data['status']=$param['status'];
        $data['cate']=$param['cate'];
        $data['url']=$param['url'];
        $res=Db::name('banner')->where($where)->update($data);
        if($res){jsonMsg("修改成功",0);}else{jsonMsg("修改失败",1);}
    }
}
