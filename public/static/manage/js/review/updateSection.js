layui.use(['form', 'layedit', 'laydate','table',"upload",'jquery'], function(){
    var form = layui.form;
        layer = layui.layer;
        layedit = layui.layedit;
        laydate = layui.laydate;
      //  table = layui.table;
        upload = layui.upload;
    	$ = layui.jquery;
    //拖拽上传
    upload.render({
        elem: '#test10'
        ,url: '/manage/review_contorller/upload'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $(".site-demo-upload img").attr("src",res.data);
                    $("#image").attr("value",res.data);
                    $(".site-demo-upload img").show();

                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });

    // 视频
    upload.render({
        elem: '#test11'
        ,accept: 'file'
        ,url: '/manage/review_contorller/uploadvideo_1080'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#videos").attr("src",res.data);
                    $("#video").attr("value",res.data);
                    $("#videos").show();

                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });
    // 视频
    upload.render({
        elem: '#test12'
        ,accept: 'file'
        ,url: '/manage/review_contorller/uploadvideo_720'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#video_720_s").attr("src",res.data);
                    $("#video_720").attr("value",res.data);
                    $("#video_720_s").show();

                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });
    // 视频
    upload.render({
        elem: '#test13'
        ,accept: 'file'
        ,url: '/manage/review_contorller/uploadvideo_480'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#video_480_s").attr("src",res.data);
                    $("#video_480").attr("value",res.data);
                    $("#video_480_s").show();

                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });

	//创建一个编辑器
    laydate.render({
        elem: '#test17'
        ,calendar: true
    });
    //自定义验证规则
    // form.verify({
    //   url:function(value){
    //         if(!value){
    //             return "请填写视频地址";
    //         }
    //     },Type:function(value){
    //         if(!value){
    //             return "请选择分类";
    //         }
    //     }

    // });

    function GetQueryString(name)
    {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return  unescape(r[2]); return null;
    }

 	var editIndex = layedit.build('news_content');
 	var addNewsArray = [],addNews;
 	form.on("submit(addNews)",function(data){
        var index = layer.load(1, {shade: [0.1,'#fff']});
        $.post("/manage/review_contorller/upsection",data.field,function(data){
            layer.close(index);
            if(data.error_code==0){
                layer.msg(data.msg, {icon: 6,time:1500},function(){
                    location.reload();
                });
            }else{
                layer.msg(data.msg, {icon: 5,time:1500});
            }
        })
 		return false;
 	})

})
// layui.use('table', function(){
//   var table = layui.table;
  
//   //第一个实例
//   table.render({
//     elem: '#demo'
//     ,width:440
//     ,height: 315
//     ,url: '/demo/table/user/' //数据接口
//     ,page: true //开启分页
//     ,cols: [[ //表头
//       {field: 'id', title: '动作 ', width:120, fixed: 'center'}
//       ,{field: 'username', title: '个数/时间', width:120}
//       ,{field: 'city', title: '组数', width:120} 
//       ,{field: 'sign', title: '组间休息', width: 80}
//     ]]
//   });
