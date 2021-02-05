<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:82:"E:\phpStudy\WWW\ywd100\application/../Template/manage/admins\employee_account.html";i:1598606565;}*/ ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>员工列表</title>
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
            <label class="layui-form-label">员工账号:</label>
            <div class="layui-input-block">
              <input type="text" name="name" placeholder="员工账号" autocomplete="off" class="layui-input">
            </div>
          </div>

          <div class="layui-inline">
            <label class="layui-form-label">手机号:</label>
            <div class="layui-input-block">
              <input type="text" name="phone" placeholder="员工手机号" autocomplete="off" class="layui-input">
            </div>
          </div>
          <div class="layui-inline">
            
            <label class="layui-form-label">所属职位</label>
            <div class="layui-input-block">
              <select name="org_id" class="newsLook layui-input"  lay-verify="" lay-search>
                <option value="">请选择</option>
                <?php if($orgList): if(is_array($orgList) || $orgList instanceof \think\Collection || $orgList instanceof \think\Paginator): $i = 0; $__LIST__ = $orgList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                <option value="<?php echo $v['id']; ?>"><?php echo str_repeat('—',$v['level']);?><?php echo $v['name']; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; endif; ?>
              </select>
            </div>
          </div>

          <div class="layui-inline">
            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="LAY-user-front-search">
              <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
            </button>
          </div>

        </div>

        
      </div>
      
      <div class="layui-card-body">
        <div style="padding-bottom: 10px;">
          <button class="layui-btn layuiadmin-btn-useradmin" data-type='rowsmanage'>添加员工</button>
        </div>
        
        <table id="LAY-user-manage" id='LAY-user-manage' lay-filter="LAY-user-manage" ></table>
        <script type="text/html" id="table-useradmin-webuser">
          <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="updtest"><i class="layui-icon layui-icon-edit"></i>编辑</a>
          {{# if(d.status == 1){ }}
          <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="batchdel"><i class="layui-icon layui-icon-delete"></i>禁用</a>
          {{# } else { }}
          <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="batchdel"><i class="layui-icon layui-icon-delete"></i>解禁</a>
          {{# }}}
          <!-- {{# if(d.pid == 0){ }}
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
          /*
          *table渲染
           */
          table.render({
            elem: '#LAY-user-manage'
            ,url: '/manage/admins/usertreetable?type=2' //展示接口
            ,where: {}
            ,page: true
            ,cols: [[
              {type: 'checkbox', fixed: 'left'}
              ,{field: 'uid',  title: 'ID', sort: true}
              ,{field: 'username',  title: '员工名称'}
              ,{field: 'group_id', title: '所属权限分组'}
              // ,{field: 'address', title: '地址'}
              ,{field: 'phone', title: '手机号'}
              // ,{field: 'paytype', title: '收款方式'}
              ,{field: 'orgname', title: '职位'}
              // ,{field: 'pid', title: '所属父代理'}
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
                where: field,
                page: {
                  curr: 1 //重新从第 1 页开始
                }
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

          var addedit = function(id){
            var id=id||0;
            var url = '/manage/admins/useradd?id='+id+'&type=2'
              ,title = id == 0?'添加员工':'编辑员工';
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
                      url: '/manage/admins/updusermsg?type=2'
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
          }

          //监听头部导航选中
          $('.layui-btn.layuiadmin-btn-useradmin').on('click', function(){
              var type = $(this).data('type');
              if(type === 'rowsmanage'){
                addedit(0);
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
              //删除
              layer.confirm('<span style="color:red">'+msg+'</span>', function(index){
                    $.ajax({
                      url: '/manage/admins/deluserstree'
                      ,data: {id:id}
                      ,type: 'post'
                      ,dataType: 'json'
                      ,async: false
                      ,success: function(res){
                          if(res.code == 0){
                              layer.msg(res.msg,{icon:2,time:400},function(){
                                  // layer.close(index);
                                  table.reload('LAY-user-manage');
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