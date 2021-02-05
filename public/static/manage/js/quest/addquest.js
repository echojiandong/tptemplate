layui.use(['layer','form','layedit',"upload"],function(){
		var layer = layui.layer
			,form = layui.form
			,layedit = layui.layedit
			,upload = layui.upload
			,$ = layui.$
			,gradeId = $('#gradeId').val()
			,q_id = $('#q_id').val()
			,q_type = $('select[name="q_type"]').val();
		var _index,_index1,_index2,_index4;
		var ue = UE.getEditor('editor',{initialFrameWidth:"90.8%",initialFrameHeight:300,zIndex:1});
    	var ue_1 = UE.getEditor('editor1',{initialFrameWidth:"90.8%",initialFrameHeight:300,zIndex:1});
    	var ue_2 = UE.getEditor('editor2',{initialFrameWidth:"90.8%",initialFrameHeight:300,zIndex:1});
		/*
		 *选项切换
		 */
		form.on('select(q_type)', function(data){
			  var _type = data.value;
			  switch(_type){
			  	  case '1': 
			  	  		//单选
			  	  		var html = `<input type="radio" name="q_answer" value="A" title="A" checked="">
          							<input type="radio" name="q_answer" value="B" title="B">
          							<input type="radio" name="q_answer" value="C" title="C">
          							<input type="radio" name="q_answer" value="D" title="D">
          							<input type="radio" name="q_answer" value="E" title="E">
          							<input type="radio" name="q_answer" value="F" title="F">
          							<input type="radio" name="q_answer" value="G" title="G">`;
          				
			  	  break;
			  	  case '2': 
			  	  		//多选
			  	  		var html = `<input type="checkbox" name="q_answer[0]" lay-skin="primary" value="A" title="A">
          							<input type="checkbox" name="q_answer[1]" lay-skin="primary" value="B" title="B">
          							<input type="checkbox" name="q_answer[2]" lay-skin="primary" value="C" title="C">
          							<input type="checkbox" name="q_answer[3]" lay-skin="primary" value="D" title="D">
          							<input type="checkbox" name="q_answer[4]" lay-skin="primary" value="E" title="E">
          							<input type="checkbox" name="q_answer[5]" lay-skin="primary" value="F" title="F">
          							<input type="checkbox" name="q_answer[6]" lay-skin="primary" value="G" title="G">`;
			  	  break;
			  	  case '3': 
			  	  		//判断
			  	  		var html = `<input type="radio" name="q_answer" value="A" title="A" checked="">
          							<input type="radio" name="q_answer" value="B" title="B">`;
			  	  break;
			  	  case '4': 
			  	  		//定值填空
			  	  		var html = `<input type="text" name="q_answer" autocomplete="off" placeholder="请输入答案" class="layui-input">`;

			  	  break;
			  	  case '5': 
			  	  case '6':
			  	  		//填空题  、问答题
			  	  		// var html = `<textarea id="demo4" style="display: none;" name='demo4'></textarea>`;
			  	  		var html = `<script id="editor3" type="text/plain" name="content"></script>`;
			  	  break;
			  	}
				$('#q_answer').html(html);
				if(_type == 5 || _type == 6){
					ue_3 = UE.getEditor('editor3',{initialFrameWidth:"90.8%",initialFrameHeight:300,zIndex:1});
				}
			    if(_type == 5 || _type == 6 || _type == 4){
				  	if(_type == 5 || _type == 6){
				  		if(_type != q_type){
				  			_index4 = layedit.build('demo4');
				  			$('input[name="_index4"]').val(_index4);
				  		}
				  	}
			  		$('#demo1').parent().parent().css('display','none');
			 	}else{
			  		$('#demo1').parent().parent().css('display','block');
			  	}
			  form.render();
		})
		/*
		 *监听下拉框选中
		 */
		form.on('select(linkAge)', function(data){
		  	var field = {}
		  	  ,_type = $(data.elem).attr('data-type');
		  	switch(_type){
		  	case '1': 
		  		var semeterId = $('select[name="semeterId"]').val();
		  		if(data.value != '' && semeterId != ''){
		  			field.gradeId = gradeId;
		  			field.subjectId = data.value;
		  			field.semeterId = semeterId;
		  			linkAgeFive(_type, field);
		  		}else{
		  			$('select[name="chapterId"]').html('<option value="">请选择章节</option>');
		  		}
		  		$('select[name="videoId"]').html('<option value="">请选择课程</option>');
		  		$('select[name="knowlengeId"]').html('<option value="">请选择知识点</option>');
		  	break;
		  	case '2': 
		  		var subjectId = $('select[name="subjectId"]').val();
		  		if(data.value != '' && subjectId != ''){
		  			field.gradeId = gradeId;
		  			field.subjectId = subjectId;
		  			field.semeterId = data.value;
		  			linkAgeFive(_type, field);
		  		}else{
		  			$('select[name="chapterId"]').html('<option value="">请选择章节</option>');
		  		}
		  		$('select[name="videoId"]').html('<option value="">请选择课程</option>');
		  		$('select[name="knowlengeId"]').html('<option value="">请选择知识点</option>');
		  	break;
		  	case '3': 
		  		if(data.value != ''){
		  			field.chapterId = data.value;
		  			linkAgeFive(_type, field);
		  		}else{
		  			$('select[name="videoId"]').html('<option value="">请选择课程</option>');
		  		}
		  		$('select[name="knowlengeId"]').html('<option value="">请选择知识点</option>');
		  	break;
		  	case '4': 
		  		if(data.value != ''){
		  			field.videoId = data.value;
		  			linkAgeFive(_type, field);
		  		}else{
		  			$('select[name="knowlengeId"]').html('<option value="">请选择知识点</option>');
		  		}
		  	break;
		  	case '5': break;
		  }
		  form.render();
		});
		//联动请求
		function linkAgeFive(_type, field){
			$.ajax({
				url: 'addquestLinkage'
				,data: field
				,dataTyp:'json'
				,type: 'post'
				,async: false
				,success: function(res){
					console.log(res);
					if(res.code == 0){
						RenderForm(_type, res.data)
					}
				}
			})
		}
		//页面渲染
		function RenderForm(_type, obj){
			switch(_type){
				case '1': 
				case '2':
					var html = '<option value="">请选择章节</option>';
					$.each(obj,function(i,v){
						html += '<option value="'+v.id+'">'+v.testclass+'</option>';
					})
					$('select[name="chapterId"]').html(html);

				break;

				case '3': 
					var html = '<option value="">请选择课程</option>';
					$.each(obj,function(i,v){
						html += '<option value="'+v.id+'">'+v.outline+'</option>';
					})
					$('select[name="videoId"]').html(html);
				break;
				case '4': 
					var html = '<option value="">请选择知识点</option>';
					$.each(obj,function(i,v){
						html += '<option value="'+v.k_id+'">'+v.k_name+'</option>'
					})
					$('select[name="knowlengeId"]').html(html);
				break;
			}
		}
		window.setKnowsList = function(status){
			if(status == '-'){
				console.log($('#knowleng').text());
				$('#knowleng').text('');
				$('#knowleng').val('')
				console.log($('input[name="pointsId"]').val());
				$('input[name="pointsId"]').val('');
			}
			if(status == '+'){
				if($('select[name="knowlengeId"]').val() == ''){
					layer.msg('请选择知识点', {icon:2,time:2000});
					return false;
				}
				var subjectId = $('select[name="subjectId"]').find('option:selected').text(),
					semeterId = $('select[name="semeterId"]').find('option:selected').text(),
					chapterId = $('select[name="chapterId"]').find('option:selected').text(),
					videoId = $('select[name="videoId"]').find('option:selected').text(),
					knowlengeId = $('select[name="knowlengeId"]').find('option:selected').text();
				var text = subjectId+'->'+semeterId+'->'+chapterId+'->'+videoId+'->'+knowlengeId;
				$('#knowleng').text(text);
				$('#knowleng').val(text);
				$('input[name="pointsId"]').val($('select[name="knowlengeId"]').val());
			}
		}

		/**
		 * 表单初始赋值
		 */
		if(q_id != 0){
			$.ajax({
				url: 'setUpdVal'
				,data: {q_id:q_id}
				,dataType: 'json'
				,type: 'post'
				,async: false
				,success: function(obj){
					if(obj.data.q_type != 1){
						var select = 'dd[lay-value='+obj.data.q_type+']';
						$('select[name="q_type"]').siblings("div.layui-form-select").find('dl').find(select).click();
					}
					form.val('layuiadmin-form-admin',obj.data);
					//百度编辑器赋值
					ue.ready(function() {
					    ue.setContent(''+obj.data.demo+'');
					});
					ue_1.ready(function() {
					    ue_1.setContent(''+obj.data.demo1+'');
					});
					ue_2.ready(function() {
					    ue_2.setContent(''+obj.data.demo2+'');
					});
					// $('#image').val(obj.data.q_stem_img);
					if(obj.data.q_stem_img != ''){
						$('#testImg').attr('src',obj.data.q_stem_img);
						$("#testImg").show();
					}
					if(obj.data.q_type == 5 || obj.data.q_type == 6){
						_index4 = layedit.build('demo4');
			  			$('input[name="_index4"]').val(_index4);
						ue_3.ready(function() {
					    	ue_3.setContent(''+obj.data.demo4+'');
						});
					}else{
						ue_1.ready(function() {
					    	ue_1.setContent(''+obj.data.demo1+'');
						});
					}
				}
			})
		}

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
		//上传图片
		layedit.set({
		  uploadImage: {
		    url: '/manage/quest/upload' //接口url
		    ,type: 'post' //默认post
		  }
		});
  		_index = layedit.build('demo'); //建立编辑器
  		_index1 = layedit.build('demo1');
  		_index2 = layedit.build('demo2');
  		//将富文本索引传到父页面
  		$('input[name="_index"]').val(_index);
  		$('input[name="_index1"]').val(_index1);
  		$('input[name="_index2"]').val(_index2);
		
})