layui.use(['element','layer','form','table','laydate','layedit'],function(){
	var element = layui.element
		,layer = layui.layer
		,$ = layui.$
		,form = layui.form
		,table = layui.table
		,laydate = layui.laydate
		,layedit = layui.layedit
		,gradeId = $('#gradeId').val()
		,q_parent = $('#q_parent').val()
		//查询条件
		,q_status = $('#q_parent').attr('data-status') == 0?2:1;
	/*
	*table渲染
	 */
	table.render({
	  elem: '#LAY-user-manage'
	  ,url: '/manage/quest/questList' //展示接口
	  ,where: {gradeId:gradeId,q_parent:q_parent,q_status:q_status}
	  ,page: true
	  ,cols: [[
	    {type: 'checkbox', fixed: 'left'}
	    ,{field: 'q_id', width: 80, title: 'ID', sort: true}
	    ,{field: 'q_type', width: 120,title: '题目类型'}
	    ,{field: 'q_stem', title: '题干'}
	    ,{field: 'teacher_name', width: 120, title: '教师名称'}
	    ,{field: 'creat_time', title: '录入时间'}
	    ,{field: 'q_subjectid', width: 80, title: '科目'}
	    ,{title: '操作', align: 'center', width: 180, fixed: 'right', toolbar: '#table-useradmin-webuser'}
	  ]]
	  ,text: {
		none: '数据为空，换一个条件试试吧'
		}
	});
	//监听下拉框选中
	form.on('select(filter)',function(data){
		var _type = $(data.elem).attr('data-type');
		switch (_type){
			case '1': 
				if(data.value != ''){
					linkAge(_type,data.value);
				}else{
					$('select[name="videClass"]').html("<option value=''>课程</option>");
					$('select[name="chapter"]').html('<option value="">章节</option>');
				}
			break;
			case '2': 
				if(data.value != ''){
					var subjectId = $('select[name="subjectId"]').val();
					linkAge(_type,subjectId, data.value);
				}else{
					$('select[name="videClass"]').html("<option value=''>课程</option>")
				}
			break;

			case '5': 
				var subjectId = $('select[name="subjectId"]').val();
				var semeId = $('select[name="semeId"]').val();
				if(subjectId != '' && semeId != ''){
					linkAge(2,subjectId, semeId);
				}else if(subjectId != '' && semeId == ''){
					linkAge(1,subjectId);
				}

			break;
			case '4': 
				var subjectId = $('select[name="subjectId"]').val();
				if(subjectId != ''){
					linkAge(_type,subjectId);
				}else{
					$('select[name="videClass"]').html("<option value=''>课程</option>");
					$('select[name="chapter"]').html('<option value="">章节</option>');
				}
			break;
		}
		form.render();
	})

	//联动操作
	function linkAge(_type, subject = 0, chapter = 0){
		var field = {}, semeId = $('select[name="semeId"]').val()
			,gradeId = $('#gradeId').val();
		field.gradeId = gradeId;
		field.subjectId = subject;
		if(chapter != 0){
			field.chapter = chapter;
		}
		if(semeId != ''){
			field.semeId = semeId;
		}
		$.ajax({
			url: '/manage/quest/linkAge'
			,data: field
			,type: 'post'
			,async: false
			,dataType: 'json'
			,success: function(res){
				if(res.code == 0){
					RenderForm(_type,res.data);
				}
			}
		})
	}

	//渲染
	function RenderForm(_type,obj){
		switch(_type){
			case '1':
					$('select[name="videClass"]').html("<option value=''>课程</option>")
					var html = "<option value=''>章节</option>";
					$.each(obj,function(i,v){
						html += "<option value='"+v.id+"'>"+v.testclass+"</option>";
					})
					$('select[name="chapter"]').html(html);
				break;

			case '2':
					var html = "<option value=''>课程</option>";
					$.each(obj,function(i,v){
						html += "<option value='"+v.id+"'>"+v.testclass+"</option>";
					})
					$('select[name="videClass"]').html(html);
				break;

			case '4':
					$('select[name="videClass"]').html("<option value=''>课程</option>")
					var html = "<option value=''>章节</option>";
					$.each(obj,function(i,v){
						html += "<option value='"+v.id+"'>"+v.testclass+"</option>";
					})
					$('select[name="chapter"]').html(html);

				break;
		}
	}

	//监听搜索
	form.on('submit(LAY-user-front-search)', function(data){
			var field = data.field;
			// console.log(field);
			//执行重载
			table.reload('LAY-user-manage', {
			  where: field
			});
	});

	//头部导航选中
	var active = {
		addtest:function(){
			var isadd = $(this).attr('data-isadd')
				,url = '/manage/quest/addquest?isadd='+isadd+'&gradeId='+gradeId
				,title = '添加试题';
			if(isadd == 0){
				var q_id = $(this).attr('data-qId');
				url += '&q_id='+q_id;
				title = '修改试题'
			}
			layer.open({
              type: 2
              ,title: title
              ,content: url
              ,area: ['87%', '97%']
              ,btn: ['确定', '取消']
              ,yes: function(index, layero){

                  var iframeWindow = window['layui-layer-iframe'+ index]
                  ,submitID = 'LAY-user-back-submit'
                  ,submit = layero.find('iframe').contents().find('#'+ submitID);
                  //监听提交
                  iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
                    var field = data.field; //获取提交的字段
                    if(field.pointsId == ''){
                    	layer.msg('请选择知识点',{icon:2,time:2000});
                    	return false;
                    }
                    field.q_parent = q_parent;
                    //题干
	                    //百度编辑器
	                    field.q_stem = iframeWindow.UE.getEditor('editor').getContent();
	                    //layui 编辑器
	                    // field.q_stem = iframeWindow.layui.layedit.getContent(field._index);
                    //备选项
	                    //百度编辑器
	                    field.q_select = iframeWindow.UE.getEditor('editor1').getContent();
	                    //layui 编辑器
	                   	//field.q_select = iframeWindow.layui.layedit.getContent(field._index1);
                    //问题详解
                    	//百度编辑器
	                    field.q_describe = iframeWindow.UE.getEditor('editor2').getContent();
	                    //layui 编辑器
	                    //field.q_describe = iframeWindow.layui.layedit.getContent(field._index2);
	                //填空题 问答题的正确答案
		                //layui 编辑器
	                    // field.q_answers = iframeWindow.layui.layedit.getContent(field._index4);
	                    //百度编辑器
	                    if(field.q_type == 5 || field.q_type == 6){
	                    	field.q_answers = iframeWindow.UE.getEditor('editor3').getContent();
	                    }
                    delete field._index
                    delete field._index1
                    delete field._index2
                    delete field._index4
                    delete field.file;
                    delete field.subjectId
                    delete field.semeterId
                    delete field.chapterId
                    delete field.videoId
                    delete field.knowlengeId
                    //提交 Ajax 成功后，静态更新表格中的数据
                    // return false;
                    $.ajax({
                      url: '/manage/quest/ajaxAddQuest'
                      ,type: 'post'
                      ,dataType: 'json'
                      ,data: field
                      ,async: false
                      ,success: function(res){
                          if(res.code == 0){
                      		layer.msg('请求成功！',{icon:1,time:1000},function(){
                      			table.reload('LAY-user-manage'); //数据刷新
                				layer.close(index); //关闭弹层
                      		});
                          }else{
                          		layer.msg('请求失败',{icon:2,time:2000});
                          }
                      }
                    });
                  });  
                  submit.trigger('click');
              }
            })
		},batchdel:function(){
			var checkStatus = table.checkStatus('LAY-user-manage')
            ,checkData = checkStatus.data; //得到选中的数据
            if(checkData.length === 0){
               return layer.msg('请选择您要删除的数据!',{icon:2,time:2000});
            }
            var array = new Array();
            $.each(checkData,function(i,v){
                array[i] = v.q_id;
            })
            var str = array.join(',');
            layer.confirm('您下定决定要删除嘛？', function(index){
            	PseudoDeletion(str, index);
		     });
		},rowsmanage:function(){
			//题帽题管理
			//maxmin: true   '
			layer.open({
              type: 2
              ,title: '题帽题管理'
              ,content: 'rowsmanage?gradeId='+gradeId
              ,area: ['100%', '100%']
              ,maxmin: true
            })
		},reduction:function(){
			//回收站还原操作
			var checkStatus = table.checkStatus('LAY-user-manage')
            ,checkData = checkStatus.data; //得到选中的数据
            if(checkData.length === 0){
               return layer.msg('请选择您要还原的数据!',{icon:2,time:2000});
            }
            var array = new Array();
            $.each(checkData,function(i,v){
                array[i] = v.q_id;
            })
            var str = array.join(',');
            layer.confirm('您确定要全部还原嘛？', function(index){
            	PseudoDeletion(str, index, 2);
            	
		     });
		},batchdels:function(){
			var checkStatus = table.checkStatus('LAY-user-manage')
            ,checkData = checkStatus.data; //得到选中的数据
            if(checkData.length === 0){
               return layer.msg('请选择您要删除的数据!',{icon:2,time:2000});
            }
            var array = new Array();
            $.each(checkData,function(i,v){
                array[i] = v.q_id;
            })
            var str = array.join(',');
            layer.confirm('删除后不可恢复，您下定决定要删除嘛？', function(index){
            	PseudoDeletion(str, index, 1);
		     });
		}
	}
	//监听头部导航选中
	$('.layui-btn.layuiadmin-btn-useradmin').on('click', function(){
			var type = $(this).data('type');
			active[type] ? active[type].call(this) : '';
	});

	//监听工具条
    table.on('tool(LAY-user-manage)', function(obj){
      var data = obj.data;
      $(this).attr('data-qId',data.q_id);
      if(obj.event === 'addtest'){
          active[obj.event] ? active[obj.event].call(this) : '';
      }
      if(obj.event === 'batchdel'){
      	layer.confirm('确定删除此条数据？', function(index){
	        	// console.log(data.q_id);
	        	PseudoDeletion(data.q_id, index);
	        
	      });
      }
      if(obj.event === 'reduction'){
      		//单条还原
      	layer.confirm('确定还原此条数据？', function(index){
	        	// console.log(data.q_id);
	        	PseudoDeletion(data.q_id, index, 2);
	      });
      		
      }
      if(obj.event === 'batchdels'){

  		layer.confirm('确定删除此条数据？', function(index){
        	// console.log(data.q_id);
        	PseudoDeletion(data.q_id, index, 1);
      	});
      }
    });

	/*
	日期选择
	 */
	lay('.test-item').each(function(){
		laydate.render({
				elem: this
				,trigger: 'click'
			});
		 });
	/*
	 *伪删除
	 */
	function PseudoDeletion(delIds, index, isdel = 0){
		$.ajax({
			url: '/manage/quest/PseudoDeletion'
			,data: {ids:delIds,isdel:isdel}
			,dataType: 'json'
			,type: 'post'
			,async: false
			,success: function(res){
				if(res.code == 0){
					layer.msg(res.data,{icon:1,time:2000},function(){
						table.reload('LAY-user-manage');
	        			layer.close(index);
					});
				}else{
					layer.msg('系统繁忙',{icon:2,time:2000},function(){
						layer.close(index);
					});
				}
			}
		})
	}
});