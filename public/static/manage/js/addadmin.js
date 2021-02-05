layui.use(['form', 'layedit', 'laydate','table',"upload",'jquery'], function(){
    var $ = layui.$, form = layui.form;
    table = layui.table;
    upload = layui.upload;
    laytpl = layui.laytpl;
    $ = layui.jquery;
    //拖拽上传
    upload.render({
        elem: '#test10'
        ,url: '/manage/admin_contorller/uploadImg'
        ,size:'60000'
        ,exts:'jpg|png|jpeg'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    var img = res.data;
                    var img = img.replace("\\","/");
                    $("#testImg").attr("src",img);
                    $("#image").attr("value",img);
                    $("#testImg").show();

                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });
    form.on('submit(submit)', function(data){
        var nickname = data.field.username;
        // if(!(/^[A-Za-z]{1}[A-Za-z0-9_-]{3,10}$/.test(nickname))){
        //     layer.msg('请输入3-10位的数字或者字母组合！');
        //     return false;
        // }
        $.post("/manage/admin_contorller/adminAddt",data.field,function(data){
            if(data.error_code==0){
                layer.msg(data.msg,{icon: 6,time: 1000},function () {
                    window.parent.location.reload();//刷新父页面
                });
            }else{
                layer.msg(data.msg,{icon: 5,time: 1000});
            }
        })
        return  false;
    });

}).define(['jquery', 'layer','form','element'],function(exports){
    $=layui.jquery;
    exports('addbanner', function(){
        layer.open({
            type:2,
            skin: 'layui-layer-molv', //加上边框
            area: ['100%','100%'], //宽高
            content: [link],
            end: function(){
                location.reload();
            }
        });
    })
    exports('edit', function(id){
        layer.open({
            type:2,
            skin: 'layui-layer-molv', //加上边框
            area: ['100%','100%'], //宽高
            content: editlink+"?id="+id,
            end: function(){
                location.reload();
            }
        });
    })

    exports('bigimg', function(e){
        var img =$(e).attr('src');
        console.log(img);
        layer.open({
            type: 1,
            title: false,
            closeBtn: 1,
            area: ['60%','95%'],
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,

            content: '<img src="'+img+'" style="width: 100%; height: auto" />'
        });

    })

});