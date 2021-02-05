<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:81:"E:\phpStudy\WWW\ywd100\application/../Template/manage/admins\user_management.html";i:1598606565;}*/ ?>
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
      <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <div class="layui-form-item">
          <div class="layui-inline">
            <label class="layui-form-label">代理商账号:</label>
            <div class="layui-input-block">
              <input type="text" name="name" placeholder="代理商账号" autocomplete="off" class="layui-input">
            </div>
          </div>

          <div class="layui-inline">
            <label class="layui-form-label">手机号:</label>
            <div class="layui-input-block">
              <input type="text" name="phone" placeholder="代理手机号" autocomplete="off" class="layui-input">
            </div>
          </div>

          <div class="layui-inline">
            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="LAY-user-front-search">
              <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
            </button>
          </div>
        </div>
      </div>
     <input type="hidden" id="isAdmin" value="<?php echo $isAdmin; ?>"/>
      <div class="layui-card-body">
        <div style="padding-bottom: 10px;">
          <button class="layui-btn layuiadmin-btn-useradmin" data-type='rowsmanage'>添加代理商</button>
        </div>
        
        <table id="LAY-user-manage" id='LAY-user-manage' lay-filter="LAY-user-manage" ></table>
        <script type="text/html" id="table-useradmin-webuser">
          <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="updtest"><i class="layui-icon layui-icon-edit"></i>编辑</a>

          {{# if(d.status == 1){ }}
            {{# if(d.parent_id != 0){  }} 
            <a class="layui-btn layui-btn-normal layui-btn-xs"  lay-event="batchdel"><i class="layui-icon layui-icon-delete"></i>禁用</a>
            {{# }}}
          
          {{# } else { }}
          <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="batchdel"><i class="layui-icon layui-icon-delete"></i>解禁</a>
          {{# }}}
          

          <!-- {{# if(d.status == 1){ }}
            <a class="layui-btn layui-btn-disabled layui-btn-xs"><i class="layui-icon layui-icon-delete"></i>删除</a>
          {{# } else { }}
              {{# if(d.is_my == 1){ }}
                  <a class="layui-btn layui-btn-disabled layui-btn-xs"><i class="layui-icon layui-icon-delete"></i>删除</a>
              {{# } else { }}
                  <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="batchdel"><i class="layui-icon layui-icon-delete"></i>删除</a>
              {{# }}}
          {{# }}} -->
        </script>

        <script type="text/html" id="checkboxTpl">
          <!-- 这里的 checked 的状态只是演示 -->
          {{# if(d.pid == 0){ }}
            <input type="checkbox" name="lock" value="{{d.status}}" title="禁止更改" lay-filter="lockDemo" disabled="">
          {{# } else { }}
              {{# if(d.is_my == 1){ }}
                  <input type="checkbox" name="lock" value="{{d.status}}" title="禁止更改" lay-filter="lockDemo" disabled="">
              {{# } else { }}
                  <input type="checkbox" class="layui-btn layui-btn-danger" name="lock" value="{{d.id}}" title="锁定" lay-filter="lockDemo" {{ d.status == 2 ? 'checked' : '' }}>
              {{# }}}
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

          var isAdmin = $("#isAdmin").val();
          var pid = true;
          if (isAdmin) {
            pid = false;
          }
          /*
          *table渲染
           */
          table.render({
            elem: '#LAY-user-manage'
            ,url: '/manage/admins/usertreetable' //展示接口
            ,where: {}
            ,page: true
            ,cols: [[
              {type: 'checkbox', fixed: 'left'}
              ,{field: 'uid',  title: 'ID', sort: true}
              ,{field: 'username',  title: '管理员名称'}
              ,{field: 'group_id', title: '分组名称'}
              ,{field: 'address', title: '地址'}
              ,{field: 'phone', title: '手机号'}
              ,{field: 'paytype', title: '收款方式'}
              ,{field: 'pid', title: '所属父代理', hide:pid}
              //,{field: 'status',  title: '状态', toolbar: '#checkboxTpl'}
              ,{title: '操作', align: 'center', width: 180, fixed: 'right', toolbar: '#table-useradmin-webuser'}
            ]]
            ,text: {
            none: '数据为空，换一个条件试试吧'
            }
          });

          //监听搜索
          form.on('submit(LAY-user-front-search)', function(data){
              var field = data.field;
              // console.log(field);
              //执行重载
              table.reload('LAY-user-manage', {
                where: field
              });
          });

          //监听锁定操作
          form.on('checkbox(lockDemo)', function(obj){
            // layer.tips(this.value + ' ' + this.name + '：'+ obj.elem.checked, obj.othis);
            // return false;
            var status = obj.elem.checked?2:1
                ,id = this.uid;
            $.ajax({
              url: '/manage/admins/upduserstatus'
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
            
          });

          var addedit = function(id = 0){
            var url = '/manage/admins/useradd?id='+id
              ,title = id == 0?'添加管理员':'编辑管理员';
            layer.open({
                    type: 2
                    ,title: title
                    ,content: url
                    ,area: ['62%', '92%']
                    ,btn: ['确定', '取消']
                    ,yes: function(index, layero){

                        var iframeWindow = window['layui-layer-iframe'+ index]
                        ,submitID = 'LAY-user-back-submit'
                        ,submit = layero.find('iframe').contents().find('#'+ submitID);
                        //监听提交
                        iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
                          var field = data.field;
                          // if(!(/^[A-Za-z]{1}[A-Za-z0-9_-]{5,18}$/.test(field.username))){
                          //     layer.msg('请输入3-10位的数字或者字母组合！');
                          //     return false;
                          // }
                          console.log(field);
                          // return false;
                          $.ajax({
                            url: '/manage/admins/updusermsg'
                            ,type: 'post'
                            ,dataType: 'json'
                            ,data: field
                            ,async: false
                            ,success: function(res){
                              if(res.code == 0){
                                layer.msg(res.msg,{icon:1,time:400},function(){
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
          }

          //监听头部导航选中
          $('.layui-btn.layuiadmin-btn-useradmin').on('click', function(){
              var type = $(this).data('type');
              if(type === 'rowsmanage'){
                addedit();
              }
          });

          //监听工具条
          table.on('tool(LAY-user-manage)', function(obj){
            var data = obj.data,id = data.uid;
            if(obj.event === 'updtest'){
              console.log(id)
              //编辑
              addedit(id);
            }
            if(obj.event === 'batchdel'){
              var status = data.status;
              var msg = '真的要禁用吗？';
              if (status == 2) {
                  msg = '真的要解禁吗？';
              }
              //删除 删除后，该用户下所有子用户也会被相应的删除，请谨慎操作！
              layer.confirm('<span style="color:red">'+msg+'</span>', function(index){
                    $.ajax({
                      url: '/manage/admins/deluserstree'
                      ,data: {id:id}
                      ,type: 'post'
                      ,dataType: 'json'
                      ,async: false
                      ,success: function(res){
                          if(res.code == 0){
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
  })
</script>