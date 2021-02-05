<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:71:"D:\tp\ywd100\application/../Template/mobile/index\course\go_weixin.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="/static/mobile/js/mui.min.js"></script>
    <link href="/static/mobile/css/mui.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="/static/mobile/css/style.css">
    <title>E点就通名校名师同步课堂</title>
    <style>
    .content_goweixin:link { 
        color: #fff; 
        text-decoration: none; 
        } 
        .content_goweixin:visited { 
        color: #fff; 
        text-decoration: none; 
        } 
        .content_goweixin:hover { 
        color: #fff; 
        text-decoration: none; 
        } 
        @media (min-width:320px) {
            .content_goweixin {
                margin: .2rem .38rem 0;
            }
            .content_title {
                font-size: .2rem;
                line-height: .34rem;
            }
        }
        @media (min-width:768px){
            .content_img img {
                width: 2rem;
                height: 2rem;
            }
            .content_img {
                padding: 13% 0;
            }
            .content_goweixin {
                margin: .7rem .6rem 0;
                font-size: .24rem;
                padding: .1rem 0;
            }
            .content_title {
                line-height: .6rem;
            }
            .content_title_one {
                margin:4% 0;
            }
            .content_img_text {
                font-size: .18rem;
            }
        }
    </style>
</head>
<script type="text/javascript">
    mui.init( { gestureConfig:{
        tap: true, //默认为true
        doubletap: true, //默认为false
        longtap: true, //默认为false
        swipe: true, //默认为true
        drag: true, //默认为true
        hold:true,//默认为false，不监听
        release:false//默认为false，不监听
    }});
    // //保存照片

//给需要长按保存图片的img标签设置 class='saveImg'
var divs = document.getElementsByClassName('saveImg');
    for(var i = 0;i<divs.length;i++){
      divs[i].addEventListener('longtap', function () {
        //开启弹框
          mui('#picture').popover('toggle');
          var imgurl = this.src;
          document.getElementById('saveImg').addEventListener('tap', function () {
            var imgDtask = plus.downloader.createDownload(imgurl,{method:'GET'}, function (d,status) {
                    if(status == 200){
                        plus.gallery.save(d.filename, function () {
                            plus.io.resolveLocalFileSystemURL(d.filename, function (enpty) {
                            // 关闭弹框
                                mui('#picture').popover('toggle');
                                mui.toast('保存成功')
                            });
                        })
                    }else{
                        mui.toast('保存失败')
                    }
              });
            imgDtask.start();
          });
   
      })
    }
</script>
<body>
    <div class="goweixin_content">
        <div class="content_title">为了更好地观看视频</br>请打开微信小程序观看</div>
        <div class="content_title_one">长按保存二维码后点击前往微信</div>
        <div class="content_img">
            <img src="/static/mobile/images/wx_xcx.jpg" alt="" class="saveImg">
            <p class="content_img_text">
                <span>长按保存二维码</span>
                <i class="iconfont icon-changan"></i>
            </p>
        </div>
        <a class="content_goweixin" href="weixin://">前往微信</a>
    </div>



    <div id="picture" class="mui-popover mui-popover-action mui-popover-bottom" style="z-index: 99999999">
        <ul class="mui-table-view">
            <li class="mui-table-view-cell">
                <a href="javascript:;" id="saveImg">保存图片</a>
            </li>
        </ul>
        <ul class="mui-table-view">
            <li class="mui-table-view-cell">
                <a href="#picture"><b>取消</b></a>
            </li>
        </ul>
    </div>
</body>
</html>
<script src="/static/mobile/js/jquery-2.2.3.js"></script>
