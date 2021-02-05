<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:59:"D:\tp\ywd100\application/../Template/manage/index\main.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>首页--layui后台管理模板</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" href="/static/manage/layui//css/layui.css" media="all" />
	<!-- <link rel="stylesheet" href="//at.alicdn.com/t/font_tnyc012u2rlwstt9.css" media="all" /> -->
	<link rel="stylesheet" href="/static/manage/css/main.css" media="all" />
</head>
<body class="childrenBody">

<!-- 首页修改列表 -->
  <ul class="main_menu">
    <li class="main_menu_li">
      <div class="menu_li_top clearfix">
        <span class="menu_li_top_left">收入</span>
        <span class="menu_li_top_right">天</span>
      </div>
      <p class="menu_li_price"><?php echo $day_money; ?><span>元/天</span></p>
      <p class="menu_li_num">总收入：<span><?php echo $all_money; ?>元</span></p>
    </li>
    <li class="main_menu_li">
      <div class="menu_li_top clearfix">
        <span class="menu_li_top_left">学员</span>
        <span class="menu_li_top_right menu_li_top_right_two">人</span>
      </div>
      <p class="menu_li_price"><?php echo $buy_person; ?><span>人/已购买</span></p>
      <p class="menu_li_num">总学员：<span><?php echo $person_arr; ?></span></p>
    </li>
    <li class="main_menu_li">
      <div class="menu_li_top clearfix">
        <span class="menu_li_top_left">活跃学员</span>
        <span class="menu_li_top_right menu_li_top_right_three">天</span>
      </div>
      <p class="menu_li_price"><?php echo $no_die_person; ?><span>人/天</span></p>
      <p class="menu_li_num">总学员：<span><?php echo $person_arr; ?></span></p>
    </li>
    <li class="main_menu_li">
      <div class="menu_li_top clearfix">
        <span class="menu_li_top_left">访问量</span>
        <span class="menu_li_top_right menu_li_top_right_four">次</span>
      </div>
      <p class="menu_li_price"><?php echo $today_visitor; ?><span>次/天</span></p>
      <p class="menu_li_num">总计访问量：<span><?php echo $visitors; ?></span></p>
    </li>
  </ul>


  <!-- 收入变化趋势 -->
  <div class="main_content_top clearfix">
    <div class="content_top_left">
      <div class="top_left_one">
        <span class="iconfont_top">
          <svg t="1564736238501" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6522" width="18" height="18"><path d="M387.016962 261.154905l242.128573 0c74.925457 0 132.079154-162.481581 132.079154-162.481581 0-48.660256-4.871961-85.47472-88.069825-88.068801-83.172281-2.619664-103.098099 58.158584-162.871461 58.158584-61.266365 0-91.911316-47.780213-167.301354-58.158584-75.365478-10.426467-88.044242 39.408545-88.044242 88.068801C254.937808 98.674347 312.066947 261.154905 387.016962 261.154905zM642.362558 311.381843l-255.345596 0c-282.762015 0-352.203574 555.509956-352.203574 555.509956 0 72.966848 59.137889 146.252968 132.079154 146.252968l695.569876 0c72.942289 0 132.078131-73.28612 132.078131-146.252968C994.541573 866.892823 925.076478 311.381843 642.362558 311.381843zM671.344636 738.312352c17.036002 0 30.842449 13.829983 30.842449 30.867008 0 17.034978-13.805424 30.864962-30.842449 30.864962l-125.201513 0 0 80.97115c0 17.034978-13.805424 30.867008-30.816866 30.867008-17.036002 0-30.840402-13.83203-30.840402-30.867008L484.485855 800.044322l-126.009925 0c-17.034978 0-30.840402-13.829983-30.840402-30.864962 0-17.037025 13.805424-30.867008 30.840402-30.867008l126.009925 0 0-39.161928-126.009925 0c-17.034978 0-30.840402-13.806447-30.840402-30.842449 0-17.036002 13.805424-30.890544 30.840402-30.890544l85.181031 0-87.212294-154.989957c-8.518001-14.784728-3.451612-33.631982 11.283997-42.148959 14.759145-8.519024 33.607422-3.476171 42.125423 11.258415l105.007589 185.880501 5.310959 0 105.007589-185.880501c8.516977-14.734586 27.365254-19.778462 42.100864-11.258415 14.760169 8.516977 19.827581 27.364231 11.308557 42.148959l-87.212294 154.989957 79.967286 0c17.036002 0 30.842449 13.853519 30.842449 30.890544 0 17.034978-13.805424 30.842449-30.842449 30.842449l-125.201513 0 0 39.161928L671.344636 738.312352z" p-id="6523" fill="#1AA094"></path></svg>

          
        </span>
        <span>收入变化趋势</span></div>
      <div class="top_left_Alltime"></div>
      <div class="top_left_time"><?php echo $_timer; ?>(日)</div>
      <div class="top_left_price" style="font-size: 26px;"><span>当天收入：</span><?php echo $day_money; ?><span> 元</span></div>
      <div class="top_left_Allprice">合计：<span id="_sum"></span> 元</div>
      <!-- <div class="top_left_Allprice">均值<span>300.16</span>万</div> -->
    </div>
    <div class="content_top_right clearfix">
      <div class="top_right_menu clearfix" id="time_tab">
        <button type="button" class="top_right_menu_li menu_li_active">今天</button> 
        <button type="button" class="top_right_menu_li">周</button> 
        <button type="button" class="top_right_menu_li">月</button> 
        <button type="button" class="top_right_menu_li">年</button> 
      </div>
      <div class="mian_top" id="container1"></div>
      <div class="top_right_price">
        <span class="price_line price_line_blue"></span>
        收入金额
      </div>
    </div>
  </div>

  

  <div class="main-card-order clearfix">
    <div class="main-order-card-msg main_order_card_msg">
      <div class="main-order-canvas" id="container" style="height: 100%;background: #fff;">
          <div class="main_order_canvas_text">
            <span class="iconfont_top"><svg t="1564735175233" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1307" width="18" height="18"><path d="M717.664 612.195c-52.734 47.461-121.289 79.102-200.391 79.102s-147.656-31.641-205.664-79.102c-131.836 68.555-221.484 158.203-221.484 321.68l843.75 0c0-163.477-89.648-247.852-216.211-321.68zM512 628.016c131.836 0 237.305-110.742 237.305-242.578s-105.469-242.578-237.305-242.578-237.305 110.742-237.305 242.578c0 137.109 110.742 242.578 237.305 242.578z" p-id="1308" fill="#1AA094"></path></svg></span>
            <span>全部学员情况</span>
          </div>
          <div class="main_order_progress_all">  
            <div class="layui_progress_text clearfix">
              <div class="layui_progress_text_left">
                <span class="progress_text_left_num">1</span>
                总学员
              </div>
              <span class="layui_progress_text_right"><?php echo $person_arr; ?>人</span>
            </div>
            <div class="layui-progress layui-progress-big">
              <div class="layui-progress-bar layui-bg-orange layui_progress_bg" lay-percent="<?php echo !empty($person_arr)?floor($person_arr/$person_arr*100):0; ?>%"></div>
            </div>
          </div>
          <div class="main_order_progress_all">
              <div class="layui_progress_text clearfix">
                <div class="layui_progress_text_left">
                  <span class="progress_text_left_num">2</span>
                  已买学员
                </div>
                <span class="layui_progress_text_right"><?php echo $buy_person; ?>人</span>
              </div>
              <div class="layui-progress layui-progress-big">
                <div class="layui-progress-bar layui-bg-orange layui_progress_bg1" lay-percent="<?php echo !empty($person_arr)?floor($buy_person/$person_arr*100):0; ?>%"></div>
              </div>
          </div>

          <div class="main_order_progress_all">
              <div class="layui_progress_text clearfix">
                <div class="layui_progress_text_left">
                  <span class="progress_text_left_num">3</span>
                  未购买已试听学员
                </div>
                <span class="layui_progress_text_right"><?php echo $no_buy_audition_person; ?>人</span>
              </div>
              <div class="layui-progress layui-progress-big">
                <div class="layui-progress-bar layui-bg-orange layui_progress_bg2" lay-percent="<?php echo !empty($person_arr)?floor($no_buy_audition_person/$person_arr*100):0; ?>%"></div>
              </div>
          </div>

          <div class="main_order_progress_all">
              <div class="layui_progress_text clearfix">
                <div class="layui_progress_text_left">
                  <span class="progress_text_left_num">4</span>
                  未购买未试听已注册学员
                </div>
                <span class="layui_progress_text_right"><?php echo $person_arr-$no_buy_audition_person-$buy_person; ?>人</span>
              </div>
              <div class="layui-progress layui-progress-big">
                <div class="layui-progress-bar layui-bg-orange layui_progress_bg3" lay-percent="<?php echo !empty($person_arr)?floor(($person_arr-$no_buy_audition_person-$buy_person)/$person_arr*100):0; ?>%"></div>
              </div>
          </div>

        </div>
      </div>
    <!-- 订单量情况 -->
    <div class="main-order-card-msg main_order_card_all">
      <!-- <p class="main-order-msg-title"><i class="main-card-order-bg main-order-msg-bg"></i>订单量情况</p> -->
      <div class="main-order-canvas" style="height: 100%;background: #fff;">
          <div class="content_top_right clearfix">
              <div class="content_top_left">
                <div class="top_left_one">
                  <span class="iconfont_top"><svg t="1564736637807" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="7507" width="18" height="18"><path d="M512 205.158566c-222.884276 0-416.184771 124.58446-511.978511 306.84348 95.79374 182.254927 289.095257 306.84041 511.978511 306.84041s416.184771-124.58446 511.978511-306.84041C928.184771 329.743026 734.883253 205.158566 512 205.158566zM512 717.237361c-115.177206 0-208.543663-91.88778-208.543663-205.235314 0-113.350604 93.367481-205.238384 208.543663-205.238384 115.174136 0 208.541617 91.88778 208.541617 205.238384C720.540593 625.348558 627.174136 717.237361 512 717.237361z" p-id="7508" fill="#1AA094"></path></svg></span>
                  <span>访问量记录</span></div>
                <div class="top_left_Alltime">2019-7-22至2019-7-28  过去七天</div>
              </div>
              <div class="top_right_menu clearfix" id="time_tab1">
                <button type="button" class="top_right_menu_li menu_li_active">今天</button> 
                <button type="button" class="top_right_menu_li">周</button> 
                <button type="button" class="top_right_menu_li">月</button> 
                <button type="button" class="top_right_menu_li">年</button> 
              </div>
              <div class="mian_top main_echarts" id="container2"></div>
              <div class="top_right_price">
                <span class="price_line price_line_red"></span>
                访问次数
              </div>
            </div>
      </div>

    </div>
  </div>



	<script type="text/javascript" src="/static/manage/layui//layui.js"></script>
	<script src="/static/manage/layui//layui.all.js"></script>
  <script type="text/javascript" src="/static/manage/js/main.js"></script>
  <script type="text/javascript" src="/static/manage/js/echarts.min.js"></script>
</body>
</html>
<script>



  var dom1 = document.getElementById("container1");
  var dom2 = document.getElementById("container2");
  var myChart1 = echarts.init(dom1);
  var myChart2 = echarts.init(dom2);

  var   base = +new Date()
        ,oneDay = 24 * 3600 * 1000
        ,data = []
        ,data1 = []
        ,_count1 = 0
        ,_count2 = 0;

  window.onload = function(){
      var date = getday();
      var now = new Date(base),
          _year = now.getFullYear();
        //var html = _year+'-'+showWeekFirstDay()+'&nbsp;至&nbsp;'+_year+'-'+showWeekLastDay() + '&nbsp;&nbsp;&nbsp;过去7天';
        var html ='00:00&nbsp;至&nbsp;24:00&nbsp;&nbsp;&nbsp;共计24小时';
        $('.top_left_Alltime').html(html);
      showAjax(0)
      showAjax(0,1);

      myChart1.setOption(setOption(date));
      myChart2.setOption(setOption(date,1));
  }

  // 点击切换
  $('#time_tab button').on('click',function(){
    var index = $(this).index();
    var now = new Date(base),
        _year = now.getFullYear();
    $(this).addClass('menu_li_active').siblings().removeClass('menu_li_active');

    showAjax(index);
    // 计算x轴
    switch (index){
        case 0: //今天
            var html ='00:00&nbsp;至&nbsp;24:00&nbsp;&nbsp;&nbsp;共计24小时';
            var date = getday(); 
        break;
        case 1: //本周
            var html = _year+'-'+showWeekFirstDay()+'&nbsp;至&nbsp;'+_year+'-'+showWeekLastDay() + '&nbsp;&nbsp;&nbsp;共计7天'
            var date = getweek(); 
        break;
        case 2: //本月
            var LastDay = showMonthLastDay().split('-');     //  获取本月最后一天
            var html = _year+'-'+showMonthFirstDay()+'&nbsp;至&nbsp;'+_year+'-'+showMonthLastDay() + '&nbsp;&nbsp;&nbsp;共计'+LastDay[1]+'天'
            var date = getmonth()
        break;
        case 3: 
            //  年 
            var html = now.getFullYear() == 2019 ? '2019-7至2019-12 &nbsp;&nbsp;&nbsp;共计6个月' : now.getFullYear()+'-1至'+now.getFullYear()+'-12 &nbsp;&nbsp;&nbsp;共计12个月';
            var date = getyear();
        break;

    }
    $('.top_left_Alltime').eq(0).html(html);
    myChart1.setOption(setOption(date));
  })

    // 点击切换
  $('#time_tab1 button').on('click',function(){
    var index = $(this).index();
    $(this).addClass('menu_li_active').siblings().removeClass('menu_li_active');
    var now = new Date(base),
        _year = now.getFullYear();
    showAjax(index,1);
    // 计算x轴
    switch (index){
        case 0: //今天
            var html ='00:00&nbsp;至&nbsp;24:00&nbsp;&nbsp;&nbsp;共计24小时';
            var date = getday(); 
        break;
        case 1: 
            var html = _year+'-'+showWeekFirstDay()+'&nbsp;至&nbsp;'+_year+'-'+showWeekLastDay() + '&nbsp;&nbsp;&nbsp;共计7天'
            var date = getweek(); 
        break;
        case 2: 
            var LastDay = showMonthLastDay().split('-');     //  获取本月最后一天
            var html = _year+'-'+showMonthFirstDay()+'&nbsp;至&nbsp;'+_year+'-'+showMonthLastDay() + '&nbsp;&nbsp;&nbsp;共计'+LastDay[1]+'天'
            var date = getmonth()
        break;
        case 3: 
            //  年 
            var html = now.getFullYear() == 2019 ? '2019-7至2019-12 &nbsp;&nbsp;&nbsp;共计6个月' : now.getFullYear()+'-7至'+now.getFullYear()+'-12 &nbsp;&nbsp;&nbsp;共计12个月';
            var date = getyear();
        break;

    }
    $('.top_left_Alltime').eq(1).html(html);
 
    myChart2.setOption(setOption(date,1));
  })


//  本月第一天
function showMonthFirstDay()     
{     
    var Nowdate=new Date();     
    var MonthFirstDay=new Date(Nowdate.getYear(),Nowdate.getMonth(),1);     
    M=Number(MonthFirstDay.getMonth())+1     
    return M+"-"+MonthFirstDay.getDate();     
}

//  本月最后一天
function showMonthLastDay()     
{     
    var Nowdate=new Date();     
    var MonthNextFirstDay=new Date(Nowdate.getYear(),Nowdate.getMonth()+1,1);     
    var MonthLastDay=new Date(MonthNextFirstDay-86400000);     
    M=Number(MonthLastDay.getMonth())+1     
    return M+"-"+MonthLastDay.getDate();     
}
//  本周第一天
function showWeekFirstDay()     
{     
    var Nowdate=new Date();     
    var WeekFirstDay=new Date(Nowdate-(Nowdate.getDay()-1)*86400000);     
    M=Number(WeekFirstDay.getMonth())+1     
    return M+"-"+WeekFirstDay.getDate();     
}
// 本周最后一天
function showWeekLastDay()     
{     
    var Nowdate=new Date();     
    var WeekFirstDay=new Date(Nowdate-(Nowdate.getDay()-1)*86400000);     
    var WeekLastDay=new Date((WeekFirstDay/1000+6*86400)*1000);     
    M=Number(WeekLastDay.getMonth())+1     
    return M+"-"+WeekLastDay.getDate();     
}
function getday()
{
  date = ['00:59','01:59','02:59','03:59','04:59','05:59','06:59','07:59','08:59','09:59','10:59','11:59','12:59','13:59','14:59','15:59','16:59','17:59','18:59','19:59','20:59','21:59','22:59','23:59'];
  return date;
}
function getweek(){
    //  周
    var WeekFirstDay = showWeekFirstDay().split('-')
        ,WeekLastDay = showWeekLastDay().split('-')
        ,now = new Date(base)
        ,date = [];
    var _now = +new Date(now.getFullYear(), WeekFirstDay[0], WeekFirstDay[1]);
    var data = [Math.random() * 300];

    _now -= oneDay
    for(var i = 1; i <= 7; i++){

        var now1= new Date(_now += oneDay);
        date.push([now1.getFullYear(), now1.getMonth(), now1.getDate()].join('/'))
    }
    return date;
}
function getmonth(){
    //  月
    var FirstDay = showMonthFirstDay().split('-')     //  获取本月第一天
        ,LastDay = showMonthLastDay().split('-');     //  获取本月最后一天
    var date = [];
    for(var i = FirstDay[1]; i <= LastDay[1]; i++){

      var now = new Date(base + oneDay);
      date.push(now.getFullYear()+'/'+FirstDay[0]+'/'+i);
    }

    return date;
}

function getyear(){
    //  年 
    var now = new Date(base);
    var date = [];
    var now_year = ['7月','8月','9月','10月','11月','12月']
        ,new_year = ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'];
    date = now.getFullYear() == 2019 ? now_year : new_year;
    return date;
}

//封装折线图
function setOption(date,lineStyle = 0){
   if(lineStyle == 0){
      $('#_sum').html(eval(data.join("+")));
   }
   var _number = 33,len = (""+_count2).length;
   switch (len) {
     case 1:  
     case 2: _number = 26; break;
     case 3: _number = 34; break;
     case 4: 
     case 5:
     case 6: _number = 40;
     break;
     case 7:  _number = 55; break;
     case 8:  _number = 68; break;
     case 9:  _number = 75; break;
     case 10: _number = 80; break;
     case 11: _number = 95; break;
   }
   return lineStyle == 0 ?{
          tooltip: {
              trigger: 'axis',
              position: function (pt) {
                  return [pt[0], '10%'];
              }
          },
          toolbox: {
              feature: {
                  dataZoom: {
                      yAxisIndex: 'none'
                  },
                  restore: {},
                  saveAsImage: {}
              }
          },
          xAxis: {
              type: 'category',
              boundaryGap: true,
              axisLine:{
                showboundaryGap: false, //y轴线消失
              },
              axisTick: {
                show:false //y轴坐标点消失
              },
              data: date
          },
          yAxis: {
              type: 'value',
              boundaryGap: [0, '100%'],
              min: 0,
              max: _count1,
              axisLabel:{
                formatter: '{value}', //x轴单位
                show: true, //这行代码控制着坐标轴x轴的文字是否显示
            },
            splitLine: {
                show: true, // 网格线是否显示
              },
              axisLine:{
                show:false, //y轴线消失
              },
              axisTick: {
                show:false //y轴坐标点消失
              },
          },
          dataZoom: [{
              type: 'inside',
              start: 0,
              end: 10
          }, 
          {
              start: 0,
              end: 10,
              handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
              handleSize: '80%',
              handleStyle: {
                  color: '#fff',
                  shadowBlur: 3,
                  shadowColor: 'rgba(0, 0, 0, 0.6)',
                  shadowOffsetX: 2,
                  shadowOffsetY: 2
              }
          }],
          series: [
              {
                  name:'收入统计',
                  type:'line',
                  smooth:true,
                  // symbol: 'none',
                  symbol: 'circle', //设定为实心点
                  symbolSize: 8, //设定实心点的大小
                  sampling: 'average',
                  itemStyle: {
                      color: '#428AEB'
                  },
                  areaStyle: {
                      color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                          offset: 0,
                          color: '#428AEB'
                      }, {
                          offset: 1,
                          color: '#428AEB'
                      }])
                  },
                  data: data
              }
          ],
          grid: {
            y: 20,
            x2: 10,
            x: 66,
            y2: 40,
            borderWidth:0 //此处去掉那个白色边框
          },
  }: {
          tooltip: {
              trigger: 'axis',
              position: function (pt) {
                  return [pt[0], '10%'];
              }
          },
          toolbox: {
              feature: {
                  dataZoom: {
                      yAxisIndex: 'none'
                  },
                  restore: {},
                  saveAsImage: {}
              }
          },
          xAxis: {
              type: 'category',
              boundaryGap: true,
              axisLine:{
                showboundaryGap: false, //y轴线消失
              },
              axisTick: {
                show:false //y轴坐标点消失
              },
              data: date
          },
          yAxis: {
              type: 'value',
              boundaryGap: [0, '100%'],
              min: 0,
              max: _count2,
              axisLabel:{
                formatter: '{value}', //x轴单位
                show: true, //这行代码控制着坐标轴x轴的文字是否显示
            },
            splitLine: {
                show: true, // 网格线是否显示
              },
              axisLine:{
                show:false, //y轴线消失
              },
              axisTick: {
                show:false //y轴坐标点消失
              },
          },
          dataZoom: [{
              type: 'inside',
              start: 0,
              end: 10
          }, 
          {
              start: 0,
              end: 10,
              handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
              handleSize: '80%',
              handleStyle: {
                  color: '#fff',
                  shadowBlur: 3,
                  shadowColor: 'rgba(0, 0, 0, 0.6)',
                  shadowOffsetX: 2,
                  shadowOffsetY: 2
              }
          }],
          series: [
              {
                  name:'访问量统计',
                  type:'line',
                  smooth:true,
                  // symbol: 'none',
                  symbol: 'circle', //设定为实心点
                  symbolSize: 8, //设定实心点的大小
                  sampling: 'average',
                  itemStyle: {
                      color: '#FB8080'
                  },
                  areaStyle: {
                      color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                          offset: 0,
                          color: '#FB8080'
                      }, {
                          offset: 1,
                          color: '#FB8080'
                      }])
                  },
                  data: data1
              }
          ],grid: {
          y: 20,
          x2:14,
          x: _number,
          y2: 40,
          borderWidth:0 //此处去掉那个白色边框
          }
  }
}



function showAjax(index,lineStyle  = 0){
    var fun_name = lineStyle == 0 ? 'incomefuc' : 'visitorsfuc';
    $.ajax({
        url: '/manage/index/'+fun_name
        ,data: {index:index}
        ,dateType: 'json'
        ,type: 'post'
        ,async: false
        ,success: function(res){
            if(res.code == 0){
                if(lineStyle == 0){
                    data = res.data
                    _count1 = res.count
                    return false;
                }
                data1 = res.data;
                _count2 = res.count;
                console.log(data1);
            }
        }
    })
}



</script>