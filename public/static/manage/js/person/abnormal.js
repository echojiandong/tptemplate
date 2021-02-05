layui.use(['laydate', 'laypage', 'layer', 'table', 'carousel', 'upload', 'element'], function(){
    var $ = layui.$, form = layui.form;
    laytpl = layui.laytpl;
 
    var laydate = layui.laydate //日期
  ,laypage = layui.laypage //分页
  layer = layui.layer //弹层
  ,table = layui.table //表格
  ,carousel = layui.carousel //轮播
  ,upload = layui.upload //上传
  ,element = layui.element; //元素操作
    var id = $('#id').val();
    
    table.render({
        elem: '#test'
        ,url:'/manage/person_controller/userAbnormalList?id='+id
        ,height:'full'
        ,cols: [[
            {field:'id', title: 'ID', width:50}
            ,{field:'ip', title: '登录IP地址', width:180}
            ,{field:'phone', title: '登录手机号码', width:180}
            ,{field:'nickname', title: '用户昵称', width:180}
            ,{field:'num', title: '登录次数', width:120}
            ,{field:'create_time', title: '登录时间'} 
            ,{field:'update_time', title: '退出时间'}
        ]]
    });

    $('#abnormal').on('click', function(){
        layer.confirm('真的解除异常吗？', function(index){
            $.post("/manage/person_controller/removeAbnormal?id="+id, function(data) {
                console.log(data)
                if(data.error_code==0){
                    layer.msg('解除成功',{time:400}, function() {
                        layer.close(layer.index);
                        window.parent.location.reload()
                    });
                }else{
                    layer.msg(data.msg,{time:400});
                }
            })

        });
    });
})
