<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:81:"E:\phpStudy\WWW\ywd100\application/../Template/manage/admins\role_management.html";i:1598606565;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>角色列表</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" href="/static/manage/layui/css/layui.css" media="all" />
	<link rel="stylesheet" href="/static/manage/layui/css/admin.css" media="all" />
</head>
<body>
  <div class="layui-fluid">
    <div class="layui-card">
      <!-- 搜索 -->
      <!-- <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <div class="layui-form-item">
          <div class="layui-inline">
            <label class="layui-form-label">题干关键字:</label>
            <div class="layui-input-block">
              <input type="text" name="stem" placeholder="请输入" autocomplete="off" class="layui-input">
            </div>
          </div>
          <div class="layui-inline">
            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="LAY-user-front-search">
              <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
            </button>
          </div>
        </div>
      </div> -->
      
      <div class="layui-card-body">
        <div style="padding-bottom: 10px;">
          <button class="layui-btn layuiadmin-btn-useradmin" data-type='rowsmanage'>添加角色</button>
        </div>
        
        <table id="LAY-user-manage" id='LAY-user-manage' lay-filter="LAY-user-manage" ></table>
        
        <script type="text/html" id="table-useradmin-webuser">
          <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="updtest"><i class="layui-icon layui-icon-edit"></i>编辑</a>
          {{# if(d.id == 1){ }}
            <a class="layui-btn layui-btn-disabled layui-btn-xs"><i class="layui-icon layui-icon-delete"></i>删除</a>
          {{# } else { }}
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="batchdel"><i class="layui-icon layui-icon-delete"></i>删除</a>
          {{# }}}
        </script>

        <script type="text/html" id="checkboxTpl">
          <!-- 这里的 checked 的状态只是演示 -->
          {{# if(d.id == 1){ }}
            <input type="checkbox" name="lock" value="{{d.status}}" title="禁止更改" lay-filter="lockDemo" disabled="">
          {{# } else { }}
            <input type="checkbox" class="layui-btn layui-btn-danger" name="lock" value="{{d.id}}" title="锁定" lay-filter="lockDemo" {{ d.status == 0 ? 'checked' : '' }}>
          {{# }}}
        </script>
      </div>
    </div>
  </div>

</body>
</html>
<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
<script type="text/javascript">
  layui.use(['element','layer','form','table','tree'],function(){
          var $ = layui.$
              ,layer = layui.layer
              ,form = layui.form
              ,table = layui.table
              ,tree = layui.tree;
          /*
          *table渲染
           */
          table.render({
            elem: '#LAY-user-manage'
            ,url: '/manage/admins/roletable' //展示接口
            ,where: {}
            ,page: true
            ,cols: [[
              {type: 'checkbox', fixed: 'left'}
              ,{field: 'id',  title: 'ID', sort: true}
              ,{field: 'title',  title: '角色名称'}
              ,{field: 'pidName',  title: '所属父级'}
              ,{field: 'time', title: '添加时间'}
              ,{field: 'status',  title: '角色状态', toolbar: '#checkboxTpl'}
              ,{field: 'remarks', title: '角色备注'}
              ,{title: '操作', align: 'center', width: 180, fixed: 'right', toolbar: '#table-useradmin-webuser'}
            ]]
            ,text: {
            none: '数据为空，换一个条件试试吧'
            }
          });

          //监听锁定操作
          form.on('checkbox(lockDemo)', function(obj){
            var status = obj.elem.checked?0:1
                ,id = this.value;
            $.ajax({
              url: '/manage/admins/updrolestatus'
              ,data: {id:id,status:status}
              ,type: 'post'
              ,dataType: 'json'
              ,async: false
              ,success: function(res){
                  if(res.code == 1001){
                      layer.msg(res.msg,{icon:5,time:2000},function(){
                        table.reload('LAY-user-manage');
                      });
                  }
              }
            })
            // layer.tips(this.value + ' ' + this.name + '：'+ obj.elem.checked, obj.othis);
          });
//添加角色
          var addedit = function(id=0,type=0){
            if(type == 1)
            {
              var title = '添加角色';
            }else{
              var title = '编辑角色';
            }
            var url = '/manage/admins/updrolemsg?id='+id+'&type='+type;
            layer.open({
                    type: 2
                    ,title: title
                    ,content: url
                    ,area: ['52%', '82%']
                    ,btn: ['确定', '取消']
                    ,yes: function(index, layero){
                        var iframeWindow = window['layui-layer-iframe'+ index]
                        ,submitID = 'LAY-user-back-submit'
                        ,submit = layero.find('iframe').contents().find('#'+ submitID);
                        //监听提交
                        iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
                          var field = data.field;
                          var trees = iframeWindow.layui.tree.getChecked('demoId');
                          field.ids = gettreeval(trees);
                          delete field.layuiTreeCheck
                          $.ajax({
                            url: '/manage/admins/setrolemenus'
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
                                layer.msg(res.msg,{icon:2,time:2000},function(){
                                  layer.close(index);
                                })
                              }
                                  
                            }
                          });
                        });  
                        submit.trigger('click');
                    }
                  })
          }

          //监听头部导航选中
          $('.layui-btn.layuiadmin-btn-useradmin').on('click', function(){
              //var type = $(this).data('type');
              var id=0,type=1;//添加角色
              addedit(id,type)
          });

          //监听工具条
          table.on('tool(LAY-user-manage)', function(obj){
            var data = obj.data,id = data.id;
            if(obj.event === 'updtest'){
              //编辑
              var type = 2;//编辑权限管理
              addedit(id,type);
            }
            if(obj.event === 'batchdel'){
              //删除
              layer.confirm('<span style="color:red">删除后，该角色对应的子节点、用户、以及用户下面的子节点也会被相应的删除，请谨慎操作？</span>', function(index){
                    $.ajax({
                      url: '/manage/admins/deleterole'
                      ,data: {id:id}
                      ,type: 'post'
                      ,dataType: 'json'
                      ,async: false
                      ,success: function(res){
                          if(res.code == 1001){
                              layer.msg(res.msg,{icon:2,time:1500},function(){
                                  layer.close(index);
                              });
                          }else{
                              layer.close(index);
                              table.reload('LAY-user-manage') //数据刷新
                          }
                      }
                    })
              });

            }
          })
          
          function gettreeval(trees){
            var ids = '';
            $.each(trees,function(i,v){
               ids += v.id + ',';
               if(v.children != undefined){
                   $.each(v.children,function(i1,v1){
                      ids += v1.id + ',';
                      if(v1.children != undefined){
                          $.each(v1.children,function(i2,v2){
                              ids += v2.id + ',';
                          })
                      }
                   })
               }
            })
            return ids;
          }
  })
</script>