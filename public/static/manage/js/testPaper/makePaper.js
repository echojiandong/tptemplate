layui.use(['form', 'layedit', 'laydate','table',"upload",'jquery'], function(){
    var form = layui.form;
        layer = layui.layer;
        layedit = layui.layedit;
        laydate = layui.laydate;
        table = layui.table; //表格
        //直播时间范围日期时间选择器
          laydate.render({
            elem: '#test6'
            ,type: 'datetime'
            ,range: true
          });
      //  table = layui.table;
        upload = layui.upload;
    	$ = layui.jquery;
    form.on('checkbox(allChoose)', function(data){
        var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
        child.each(function(index, item){
            item.checked = data.elem.checked;
        });
        form.render('checkbox');
    });
    //学员分配
    $('#assign').on('click', function(){
        //获取数据
        var students = $("#students").val();
        var is_all = $("#is_all").val();
        layer.open({
            type: 2,
            title:"分配学员",
            skin: 'layui-layer-demo', //样式类名
            closeBtn: 1, //不显示关闭按钮
            anim: 2,
            area: ['70%', '95%'],
            shadeClose: true, //开启遮罩关闭
            maxmin: true,
            content: "/manage/testpaper_controller/studentAssign?is_all="+is_all+"&students="+students,
        });
    });

    //选题页面
    window.selQuestion = function (id){
        var grade_id = $("select[name='grade_id']").val();
        var subject_id = $("select[name='subject_id']").val();
        var semester = $("select[name='semester']").val();
        if(!grade_id && !subject_id && !semester){
            layer.msg('筛选信息不能为空！', {icon: 5,time:1500});
        }else{
            layer.open({
                type: 2,
                title:"选择题目",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['70%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/testpaper_controller/selQuestion?typeId="+id+"&grade_id="+grade_id+"&subject_id="+subject_id+"&semester="+semester,
            });
        }
    }
    //看题页面
    window.showSelectedQuestion = function(id){
        if(id != 0){
            var selectQuestList = $("#selectQuest_"+id).val();
        }else{
            var selectQuestList = $("#selectQuest").val();
        }
        if(!selectQuestList){
            layer.msg('请先选择题目！', {icon: 5,time:1500});
        }else{
            layer.open({
                type: 2,
                title:"展示题目",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['70%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/testpaper_controller/showSelectedQuestion?typeId="+id+"&selectQuestList="+selectQuestList,
            });
        }
    }
    //选题页面  获取数据 页面渲染
    var grade_id =  $("input[name='grade_id']").val();
    var subject_id =  $("input[name='subject_id']").val();
    var semester =  $("input[name='semester']").val();
    var typeId =  $("input[name='typeId']").val();
    if(typeId != 0){
        var idList =  $(window.parent.document).find("#selectQuest_"+typeId).val();
    }else{
        var idList =  $(window.parent.document).find("#selectQuest").val();
    }
    table.render({
        elem: '#test'
        ,url:"/manage/testpaper_controller/getSelQuestion?typeId="+typeId+"&grade_id="+grade_id+"&subject_id="+subject_id+"&semester="+semester
        ,height:'full'
        ,cellMinWidth:80
        ,cols: [[
             {type: 'checkbox', fixed: 'left',width:70}
            ,{field:'q_id', title: 'ID',width:50}
            ,{field:'questype', title: '题目类型'}
            ,{field:'q_stem', title: '题目题干',width:400}
            ,{field:'level', title: '题目等级'}
        ]]
        ,page: true
        ,even: true
        ,done: function(res, curr, count){
            //数据表格加载完成时调用此函数
            //如果是异步请求数据方式，res即为你接口返回的信息。
            //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度

            //设置全部数据到全局变量
            table_data=res.data;
            //在缓存中找到id ,然后设置data表格中的选中状态
            //循环所有数据，找出对应关系，设置checkbox选中状态
            // idList = idList.split(',');
            // for(var i=0;i< res.data.length;i++){
            //     for (var j = 0; j < idList.length; j++) {
            //         //数据id和要勾选的id相同时checkbox选中
            //         if(res.data[i].q_id == idList[j])
            //         {
            //             //这里才是真正的有效勾选
            //             res.data[i]["LAY_CHECKED"]='true';
            //             //找到对应数据改变勾选样式，呈现出选中效果
            //             var index= res.data[i]['LAY_TABLE_INDEX'];
            //             $('.layui-table-fixed-l tr[data-index=' + index + '] input[type="checkbox"]').prop('checked', true);
            //             $('.layui-table-fixed-l tr[data-index=' + index + '] input[type="checkbox"]').next().addClass('layui-form-checked');
            //         }
            //     }
            // }
            // //设置全选checkbox的选中状态，只有改变LAY_CHECKED的值， table.checkStatus才能抓取到选中的状态
            // var checkStatus = table.checkStatus('my-table');
            // if(checkStatus.isAll){
            //     $(' .layui-table-header th[data-field="0"] input[type="checkbox"]').prop('checked', true);
            //     $('.layui-table-header th[data-field="0"] input[type="checkbox"]').next().addClass('layui-form-checked');
            // }
        }
    });
    //单元 章节 二级联动
    $("#kid").change(function(){
        var video_id = $("select[name='kid']").val();
        $.post("/manage/testpaper_controller/selVideoInfo",{video_id:video_id},function(data){
            if(data.error_code==0){
                var videoList = data.data;
                html = "";
                html+=`
                    <select name="chapter_id" class="newsLook" lay-filter="browseLook" id="chapter_id" lay-verify="Type">
                        <option value="">请选择</option>
                    `;
                for(i in videoList){
                    html+=`<option value="${videoList[i].id}">${videoList[i].testclass}</option>`;
                }
                html+=`</select>`;
                $('#videoList').html(html);
            }
        })
    })
    //搜索
    $('#sousuo').on('click', function(){
        var keyword = $('#demoReload').val();
        var kid = $("select[name='kid']").val();
        var chapter_id = $("select[name='chapter_id']").val();
        var typeId = $("input[name='typeId']").val();
        if(!keyword && !kid && !chapter_id){
            //当用户没有输入内容直接点击查询的时候提示
            layer.msg("查询内容不能为空",{time:1000});
            return false;
        }else{
            //请求接口根据用户搜索查找卡号
            table.render({
                elem: '#test'
                ,url:'/manage/testpaper_controller/getSelQuestion?keyword='+keyword+'&kid='+kid+'&chapter_id='+chapter_id+'&typeId='+typeId
                ,height:'full'
                ,cellMinWidth:80
                ,cols: [[
                     {type: 'checkbox', fixed: 'left'}
                    ,{field:'q_id', title: 'ID',width:50}
                    ,{field:'questype', title: '题目类型'}
                    ,{field:'q_stem', title: '题目题干',width:400}
                    ,{field:'level', title: '题目等级'}
                ]]
                ,page: true
            });
        }  
    });
    //获取选中行
    $('#getSelectquest').on('click',function(){
        var data = layui.table.checkStatus('test').data;
        if(data.length > 0){
            var typeId = data[0]['q_type'] == undefined ? data[0]['qr_type']:data[0]['q_type'];
                $.post("/manage/testpaper_controller/getCheckedQuestion",{checkedId:data},function(res){
                if(res.error_code==0){
                    layer.msg(res.msg, {icon: 6,time:1500},function(){
                        var index = parent.layer.getFrameIndex(window.name);
                        if(typeId != null){
                            parent.$("#selectQuest_"+typeId).val(res.data);
                            parent.$("#select_"+typeId).val(res.count);
                        }else{
                            parent.$("#selectQuest").val(res.data);
                            parent.$("#select").val(res.count);
                        }
                        parent.layer.close(index); 
                    });
                }
                // 
                // $("#sel").attr('value',res.count);
            })
        }else{
             layer.msg("选择不能为空", {icon: 6,time:1500});
        }
    });
    //添加提交
 	form.on("submit(addMakePaper)",function(data){
        $.post("/manage/testpaper_controller/doMakePaper",data.field,function(data){
            if(data.error_code==0){
                layer.msg(data.msg, {icon: 6,time:1500},function(){
                    window.parent.location.reload();//刷新父页面
                });
            }else{
                layer.msg(data.msg, {icon: 5,time:1500});
            }
        })
 		return false;
 	})
    //修改提交
    form.on("submit(doUpdateMakeTestPaper)",function(data){
        $.post("/manage/testpaper_controller/doUpdateMakeTestPaper",data.field,function(data){
            if(data.error_code==0){
                layer.msg(data.msg, {icon: 6,time:1500},function(){
                    window.parent.location.reload();//刷新父页面
                });
            }else{
                layer.msg(data.msg, {icon: 5,time:1500});
            }
        })
        return false;
    })

})