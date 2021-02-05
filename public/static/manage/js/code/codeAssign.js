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

    table.render({
        elem: '#test'
        ,url:'/manage/code_contorller/getUserList'
        ,height:'full'
        ,cellMinWidth:80
        ,cols: [[
            {type:'checkbox'},
            {field:'uid', width:80, title: 'ID', sort: true}
            ,{field:'username', title: '管理员名称'}
            ,{field:'title', title: '管理员分组 '}
            ,{field:'paytype_name', title: '收款方式'}
            ,{field:'lock',  title: '操作', width:360, toolbar: '#barDemo'}
        ]]
        ,page: true
    });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        if(obj.event === 'userInfo'){
            layer.open({
                type: 2,
                title:"管理员信息预览",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/code_contorller/getUserInfo?id="+data.uid
            });
        }else if(obj.event==='codeAssign'){
            if(data.now_group_id != 9 && data.now_group_id != 3){
                layer.open({
                    type: 2,
                    title:"管理员课程生成权限赠送",
                    skin: 'layui-layer-demo', //样式类名
                    closeBtn: 1, //不显示关闭按钮
                    anim: 2,
                    area: ['100%', '95%'],
                    shadeClose: true, //开启遮罩关闭
                    maxmin: true,
                    content: "/manage/code_contorller/userCodeAssign?id="+data.uid
                });
            }else{
                layer.msg('权限不足！');
            }
        }
    });

    form.on('checkbox(allChoose)', function(data){
        var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
        child.each(function(index, item){
            item.checked = data.elem.checked;
        });
        form.render('checkbox');
    });

    form.on("submit(upCourse)",function(data){
        if(data.field.num != ''){
            $.post("/manage/code_contorller/doCardAssign",data.field,function(data){
                if(data.error_code==0){
                    layer.msg(data.msg, {icon: 6,time:1500},function(){
                        window.parent.location.reload();//刷新父页面
                    });
                }else{
                    layer.msg(data.msg, {icon: 5,time:1500});
                }
            })
            return false;
        }else{
            layer.msg('请输入数量', {icon: 5,time:2000});
        }
    });

    // $('.demoTable .layui-btn').on('click', function(){
    //     var type = $(this).data('type');
    //     active[type] ? active[type].call(this) : '';
    // });
    // active = {
    //     addCode: function(){
    //         layer.open({
    //             type:2,
    //             title:"生成卡号",
    //             skin: 'layui-layer-molv', //加上边框
    //             area: ['100%','100%'], //宽高
    //             content: "/manage/code_contorller/addCode",
    //             end:function(){
    //                 window.location.reload();
    //             }
    //         });
    //     }
    // };
    // active = {
    //     output: function(){
    //         window.location.href="/manage/code_contorller/output";
    //     }
    // }

    form.on("select(selectClass)",function(data) {
        
        var that = $(this);
        var thisParent = that.parent().parent().parent().parent().parent();

        // 学期
        var semesterid = thisParent.find('.semester').val();

        var sid = that.parent().parent().parent().attr('sid'); 
       
         // 年级
         var gradeid = thisParent.find('.grade_id').val(); 
         // 学期
        var semesterid = thisParent.find('.semester').val();

        $.post("/manage/code_contorller/getCourseByClass", {gradeid:gradeid, semesterid:semesterid}, function(data) {
            
            if (data.error_code != 0) {
                thisParent.find('.course').html('');
                thisParent.find('.course').append('暂无课程，请联系管理员!');
                layer.msg(data.msg, {icon: 5,time:1500});
            }
           
            var arr = JSON.parse(data.data);
            var isAdmin = data.count;
            var number = '';
            var disab = '';
            if (!isAdmin) {
                number = '';
            }
            if(arr) {
                var html='';
                for(var i =0; i < arr.length; i++) {
                    if (!isAdmin) {
                        number = '剩余 '+ arr[i].courseNum;
                        disab = 'disabled';
                    }
                    if (arr[i].courseNum > 0) {
                        html+='<label style="width:70px;padding: 9px 3px;">' + arr[i].subject + number +' </label>';
                        html+='<div class="layui-inline" style="width:50px">';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"id"+']" type="hidden" value="'+arr[i].id+'" >';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"num"+']" type="text" class="layui-input newsName" lay-verify="order_num"  value="" >';
                        html+='</div>';
                    } else {
                        number = '剩余 0';
                        html+='<label style="width:70px;padding: 9px 3px;">' + arr[i].subject + number +' </label>';
                        html+='<div class="layui-inline" style="width:50px">';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"id"+']" type="hidden" value="'+arr[i].id+'" >';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"num"+']" '+ disab +' type="text" class="layui-input newsName" lay-verify="order_num"  value="" >';
                        html+='</div>';
                    }

                }
                
                thisParent.find('.course').html('');
                thisParent.find('.course').append(html);

                form.render();
                sid++
            }
        })
    })



    // 筛选-修改前
    // form.on("select(selectGrade)",function(data) {
    //     var that = $(this);
    //     var thisParent = that.parent().parent().parent().parent().parent().parent();
    //     // 年级 
    //     var gradeid = thisParent.find('.grade').val(); 
    //     if (!gradeid) return ;
    //     // 选择年级后学期默认为上学期
    //     thisParent.find('.semester').find('option[value=1]').attr("selected", 'selected');
    //     // 学期
    //     var semesterid = thisParent.find('.semester').val();
    //     var sid = that.parent().parent().parent().attr('sid');
    //     if (sid == 1) {
    //         sid = 0;
    //     }
    //     form.render();   
    //     $.post("/manage/code_contorller/getCourseByClass", {gradeid:gradeid, semesterid:semesterid}, function(data) {
    //         if (data.error_code != 0) {
    //             thisParent.find('#class').find('#course').html('');
    //             thisParent.find('#class').find('#course').append('暂无课程，请联系管理员!');
    //             layer.msg(data.msg, {icon: 5,time:1500});
    //         }          
    //         var arr = JSON.parse(data.data);      
    //         if(arr) {
    //             var html='';
    //             for(var i =0; i < arr.length; i++) {
    //                 if (arr[i].courseNum > 0) {
    //                     html += '<input type="checkbox" name="subject_id'+ sid +'[]" title="'+ arr[i].subject +' 剩余 '+ arr[i].courseNum +' 个"  lay-skin="primary" value="'+ arr[i].id +'"/>';
    //                 } else {
    //                     var num = arr[i].courseNum < 0 ? arr[i].courseNum : 0;
    //                     html += '<input type="checkbox" disabled name="subject_id'+ sid +'[]" title="'+ arr[i].subject +' 剩余 '+ num +' 个"  lay-skin="primary" value="'+ arr[i].id +'"/>';
    //                 }
    //             }               
    //             thisParent.find('#class').find('#course').html('');
    //             thisParent.find('#class').find('#course').append(html);
    //             form.render();
    //         }
    //     })
    // })

})
