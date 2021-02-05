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
        ,url: '/manage/review_contorller/upload',
        size:'6000'
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

	//创建一个编辑器
    laydate.render({
        elem: '#test17'
        ,calendar: true
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
        $.post("/manage/review_contorller/achapter",data.field,function(data){
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
