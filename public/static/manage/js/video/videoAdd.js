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
        ,url: '/manage/video_contorller/upload'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#imgYes").attr("src",res.data);
                    $("#image").attr("value",res.data);
                    $("#imgYes").show();

                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });
    upload.render({
        elem: '#test11'
        ,url: '/manage/video_contorller/upload'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#imgNo").attr("src",res.data);
                    $("#imageNo").attr("value",res.data);
                    $("#imgNo").show();

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
    form.verify({
        price: function(value){
            var pat=/^[1-9]\d{0,5}(?:|\.\d{0,2})$/;
            if(!pat.test(value)){
                return '价格请输入小于100000并且小数点后两位的非负小数';
            }
        }, rebate: function(value){
            var pattern=/^0\.\d{0,2}$/;
            if(!pattern.test(value)){
                return '折扣请输入小于1且小数点后两位的非负小数';
            }
        },image:function(value){
           /* if(!value){
                return "请选择图片";
            }*/
        },url:function(value){
            if(!value){
                return "请填写视频地址";
            }
        },Type:function(value){
            if(!value){
                return "请选择分类";
            }
        }

    });

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
        $.post("/manage/video_contorller/addVideo",data.field,function(data){
            layer.close(index);
            if(data.error_code==0){
                layer.msg('操作成功', {icon: 6,time:1500},function(){
                    location.reload();
                });
            }else{
                layer.msg('操作失败', {icon: 5,time:1500});
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
