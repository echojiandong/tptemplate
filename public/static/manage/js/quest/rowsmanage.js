layui.use(['element', 'layer', 'table', 'form'],function(){
	var element = layui.element
		,layer = layui.layer
		,table = layui.table
		,form = layui.form
		,$ = layui.$
		,gradeId = $('#gradeId').val();


	/*
	*table渲染
	 */
	table.render({
	  elem: '#LAY-user-manage'
	  ,url: 'questionrows' //展示接口
	  ,where: {gradeId:gradeId}
	  ,page: true
	  ,cols: [[
	    {type: 'checkbox', fixed: 'left'}
	    ,{field: 'qr_id', width: 80, title: 'ID', sort: true}
	    ,{field: 'qr_type', width: 120,title: '题帽类型'}
	    ,{field: 'qr_question', title: '题干'}
	    ,{field: 'qr_username', width: 120, title: '教师名称'}
	    ,{field: 'qr_subject', width: 80, title: '科目'}
	    ,{title: '操作', align: 'center', width: 255, fixed: 'right', toolbar: '#table-useradmin-webuser'}
	  ]]
	  ,text: {
		none: '数据为空，换一个条件试试吧'
		}
	});

	//监听搜索
	form.on('submit(LAY-user-front-search)', function(data){
			var field = data.field;
			console.log(field);
			//执行重载
			table.reload('LAY-user-manage', {
			  where: field
			});
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
		var field = {}, semeId = $('select[name="semeId"]').val();
		field.gradeId = gradeId;
		field.subjectId = subject;
		if(chapter != 0){
			field.chapter = chapter;
		}
		if(semeId != ''){
			field.semeId = semeId;
		}
		$.ajax({
			url: 'linkAge'
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

	//监听头部导航选中
	$('.layui-btn.layuiadmin-btn-useradmin').on('click', function(){
			var type = $(this).data('type');
			active[type] ? active[type].call(this) : '';
	});

	//头部导航选中
	var active = {
		addtest:function(){
			var isadd = $(this).attr('data-isadd')
				,url = 'addquestrows.html?isadd='+isadd+'&gradeId='+gradeId
				,title = '添加题帽题';
			if(isadd == 0){
				var q_id = $(this).attr('data-qId');
				url += '&q_id='+q_id;
				title = '修改题帽题'
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
                    //题干
                    field.q_stem = iframeWindow.UE.getEditor('editor').getContent();
                    //提交 Ajax 成功后，静态更新表格中的数据
                    $.ajax({
                      url: 'questRowsAdd'
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
            layer.confirm('您下定决定要删除嘛？（题下的所有试题都会被删除！）', function(index){
            	PseudoDeletion(str, index);
		     });
		}
	}

	//监听工具条
    table.on('tool(LAY-user-manage)', function(obj){
      var data = obj.data;
      $(this).attr('data-qId',data.qr_id);
      if(obj.event === 'addtest'){
          active[obj.event] ? active[obj.event].call(this) : '';
      }
      if(obj.event === 'sonlist'){
      		layer.open({
              type: 2
              ,title: '子试题列表'
              ,content: 'rowsonlist?qr_id='+data.qr_id+'&gradeId='+gradeId
              ,area: ['100%', '100%']
              ,maxmin: true
            })
      }
      if(obj.event === 'batchdel'){
      	layer.confirm('确定删除此条数据？（该题下的所有试题都会被删除！）', function(index){
	        	// console.log(data.q_id);
	        	PseudoDeletion(data.qr_id, index);
	        
	      });
      }
    });

    /*
	 *伪删除
	 */
	function PseudoDeletion(delIds, index){
		$.ajax({
			url: 'PseudoDeletionRows'
			,data: {ids:delIds}
			,dataType: 'json'
			,type: 'post'
			,async: false
			,success: function(res){
				if(res.code == 0){
					layer.msg('删除成功',{icon:1,time:2000},function(){
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

})