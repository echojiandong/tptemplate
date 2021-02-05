var $,tab,skyconsWeather;
layui.config({
	base : "/static/manage/js/"
}).use(['form','element','layer','jquery','bodyTab'],function(){
	var form = layui.form,
		layer = parent.layer === undefined ? layui.layer : parent.layer,
		element = layui.element,
		$ = layui.jquery;
	var tab = layui.bodyTab({
        openTabNum : "50", //最大可打开窗口数量
        url : "json/navs.json" //获取菜单json地址
    });
	$(".panel a").on("click",function(){
		window.parent.addTab($(this));
	})
	$('#addPerson').on('click', function(){
        layer.open({
                type: 2,
                title:"生成用户与订单",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/person_controller/addPerson"
        });
    });
    $('#addOrder').on('click', function(){
	    layer.open({
	        type:2,
	        title:"添加用户订单",
	        skin: 'layui-layer-molv', //加上边框
	        area: ['100%','100%'], //宽高
	        content: "/manage/order_contorller/addOrderPerson",
	        end:function(){
	            window.location.reload();
	        }
	    });
	});
	$('#addUser').on('click', function(){
        layer.open({
            type: 2
            ,title: '添加管理员'
            ,content: '/manage/admins/useradd?id=0'
            ,area: ['62%', '92%']
            ,btn: ['确定', '取消']
            ,yes: function(index, layero){

                var iframeWindow = window['layui-layer-iframe'+ index]
                ,submitID = 'LAY-user-back-submit'
                ,submit = layero.find('iframe').contents().find('#'+ submitID);
                //监听提交
                iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
                  var field = data.field;
                  if(!(/^[A-Za-z]{1}[A-Za-z0-9_-]{5,18}$/.test(field.username))){
                      layer.msg('请输入5-18位的数字或者字母组合！');
                      return false;
                  }
                  // console.log(field);
                  // return false;
                  $.ajax({
                    url: '/manage/admins/updusermsg'
                    ,type: 'post'
                    ,dataType: 'json'
                    ,data: field
                    ,async: false
                    ,success: function(res){
                      if(res.code == 0){
                        layer.msg(res.msg,{icon:1,time:1500},function(){
                          table.reload('LAY-user-manage') //数据刷新
                          layer.close(index); //关闭弹层
                        })
                      }else{
                        layer.msg(res.msg,{icon:2,time:2000})
                      }
                          
                    }
                  });
                });  
                submit.trigger('click');
            }
          })
	});
	$('#userList').on('click', function(){
	    // window.location.href="/manage/admin_contorller/adminList";
	    // var url="/manage/admin_contorller/adminList";
     //    tab.tabAdd($(this),url);
        layer.open({
			type: 2,
			title: '管理员列表',
			skin: 'layui-layer-demo', //样式类名
			anim: 2,
			area: ['100%', '95%'],
			shadeClose: true, //开启遮罩关闭
			maxmin: true
			,content: "/manage/admins/userManagement"
		}); 
	});

	//填充数据方法
 	function fillParameter(data){
 		//判断字段数据是否存在
 		function nullData(data){
 			if(data == '' || data == "undefined"){
 				return "未定义";
 			}else{
 				return data;
 			}
 		}
 		$(".version").text(nullData(data.version));      //当前版本
		$(".author").text(nullData(data.author));        //开发作者
		$(".homePage").text(nullData(data.homePage));    //网站首页
		$(".server").text(nullData(data.server));        //服务器环境
		$(".dataBase").text(nullData(data.dataBase));    //数据库版本
		$(".maxUpload").text(nullData(data.maxUpload));    //最大上传限制
		$(".userRights").text(nullData(data.userRights));//当前用户权限
 	}

}).define(['layer', 'laypage','jquery'], function(exports){
    //do something
	$ = layui.jquery;
    exports('update', function(key,e){
    	$value = $(e).text();
        layer.prompt({title: '请输入数字', value:$value,formType:0}, function(text, index){
				$.post('/manage/index/update_indexdata',{key:key,text:text},function(data){
					if(data.error_code==0){
                        layer.msg('修改完成',{icon:6,time:800},function(){
                            window.parent.location.reload();
						});
					}else{
                        layer.msg(data.msg,{time:800});
					}
				})

        });
    });
});
