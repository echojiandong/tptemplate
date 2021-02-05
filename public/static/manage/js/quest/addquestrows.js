layui.use(['layer','form','layedit'],function(){
		var layer = layui.layer
			,form = layui.form
			,layedit = layui.layedit
			,$ = layui.$
			,gradeId = $('#gradeId').val()
			,q_id = $('#q_id').val()
			,q_type = $('select[name="q_type"]').val();
		var _index;
		var ue = UE.getEditor('editor',{initialFrameWidth:"90.8%",initialFrameHeight:300,zIndex:1});
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
				url: 'setUpdrowsVal'
				,data: {q_id:q_id}
				,dataType: 'json'
				,type: 'post'
				,async: false
				,success: function(obj){
					//百度编辑器赋值
					ue.ready(function() {
					    ue.setContent(''+obj.data.demo+'');
					});
					if(obj.data.q_type != 1){
						var select = 'dd[lay-value='+obj.data.q_type+']';
						$('select[name="q_type"]').siblings("div.layui-form-select").find('dl').find(select).click();
					}
					form.val('layuiadmin-form-admin',obj.data);
				}
			})
		}

  		_index = layedit.build('demo'); //建立编辑器
  		//将富文本索引传到父页面
  		$('input[name="_index"]').val(_index);

		
})