<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:58:"D:\tp\ywd100\application/../Template/manage/tag\index.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
  <title></title>
  <link rel="stylesheet" type="text/css" href="/static/manage/layui/css/layui.css">
  <link rel="stylesheet" type="text/css" href="/static/manage/layui/css/admin.css">
</head>
<body>
  <button type="button" class="layui-btn layui-btn-primary layui-btn-sm add-button">添加</button>
  <table class="layui-table layui-form" id="treetables" lay-size="sm"></table>
</body>
</html>
<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
<script type="text/javascript">
  layui.config({
    base: '/static/manage/layui/js/',
  })
  layui.use(['treeTable','layer','code','form'],function(){
    var $ = layui.$
      ,treeTable = layui.treeTable
      ,form = layui.form
      ,o = layui.$;
    var re = treeTable.render({
      elem: '#treetables'
      ,url: '/manage/tag/getTagList'
      ,icon_key: 'title'
      ,is_checkbox: false
      ,end :function(e){
        form.render();
      }
      ,cols: [
        {
          key: 'title',title: '名称',width: '100px',template: function(item){
          if(item.level == 0){
            return '<span style="color:red;">'+item.title+'</span>';
          }else if(item.level == 1){
            return '<span style="color:green;">'+item.title+'</span>';
          }else if(item.level == 2){
            return '<span style="color:#aaa;">'+item.title+'</span>';
          }else if(item.level == 3){
            return '<span style="color:#aaa;">'+item.title+'</span>';
          }
        }},
        {
          key: 'id',title: 'ID', width: '100px',align: 'center',
        },
        {
          key: 'type_name',title: '标签类型', width: '100px',align: 'center',
        },
        {
          title: '是否显示',
          width: '100px',
          align: 'center',
          template: function(item){
            if(item.is_show == 1){
              return '<input type="checkbox" name="close" lay-filter="cbox" lay-skin="switch" lay-text="ON|OFF" checked="">';
            }else if(item.is_show == 2){
              //禁止显示
               return '<input type="checkbox" name="close" lay-filter="cbox" lay-skin="switch" lay-text="ON|OFF">';
            }
          }
        },
        {
          title: '操作',
          width: '100px',
          align: 'center',
          template: function(item){
            return '<a lay-filter="add">添加</a> | <a lay-filter="edit">编辑</a> | <a lay-filter="del">删除</a>';
          }
        }
      ]
    });

    var addedit = function(pid,type,isadd = 0){
      var url = '/manage/tag/tagAdd?pid='+pid+'&isadd='+isadd+'&type='+type
        ,title = isadd == 0?'添加标签':'编辑标签';
      layer.open({
              type: 2
              ,title: title
              ,content: url
              ,area: ['40%', '50%']
              ,btn: ['确定', '取消']
              ,yes: function(index, layero){

                  var iframeWindow = window['layui-layer-iframe'+ index]
                  ,submitID = 'LAY-user-back-submit'
                  ,submit = layero.find('iframe').contents().find('#'+ submitID);
                  //监听提交
                  iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
                    var field = data.field; //获取提交的字段
                    //提交 Ajax 成功后，静态更新表格中的数据
                    // console.log(field);return false;
                    $.ajax({
                      url: '/manage/tag/tagAdd'
                      ,type: 'post'
                      ,dataType: 'json'
                      ,data: field
                      ,async: false
                      ,success: function(res){
                        if(res.error_code == 1){
                          layer.msg(res.msg,{icon:6,time:2000},function(){
                            treeTable.render(re) //数据刷新
                            layer.close(index); //关闭弹层
                          })
                        }else{
                          layer.msg(res.msg,{icon:5,time:2000},function(){
                            treeTable.render(re) //数据刷新
                            layer.close(index); //关闭弹层
                          })
                        }
                      }
                    });
                  });  
                  submit.trigger('click');
              }
            })
    }

    //监听开关
    form.on('switch(cbox)',function(data){
      // console.log(data.elem);
      var id = o(data.elem).parents('tr').attr('data-id')
        ,val = data.elem.checked?1:2;
      $.ajax({
          url: '/manage/tag/updstatus'
          ,data: {id:id,is_show:val}
          ,dataType: 'json'
          ,type: 'post'
          ,async: false
          ,success: function(res){
            if(res.error_code == 1){
              treeTable.render(re) //数据刷新
              layer.msg(res.msg,{icon:6,time:2000},function(){
                treeTable.render(re) //数据刷新
              })
            }
          }
        })
    })

    // 监听添加
    treeTable.on('tree(add)',function(data){
      layer.msg(JSON.stringify(data));
      var pid = data.item.id;
      var type = data.item.type;
      addedit(pid,type);
      
    })

    // 监听编辑
    treeTable.on('tree(edit)',function(data){
      layer.msg(JSON.stringify(data));
      var pid = data.item.id;
      var type = data.item.type;
      addedit(pid,type,1);
    })

    //监听删除
    treeTable.on('tree(del)',function(data){
      layer.msg(JSON.stringify(data));
      layer.confirm('<span style="color:red;">删除后该节点下面所有子节点将会被删除！您确定要删除嘛？</span>', function(index){
              var id = data.item.id;
              $.ajax({
                url: '/manage/tag/deltree'
                ,data: {id:id}
                ,dataType: 'json'
                ,type: 'post'
                ,async: false
                ,success: function(res){
                  if(res.error_code == 1){
                    layer.msg(res.msg,{icon:6,time:2000},function(){
                      treeTable.render(re) //数据刷新
                    })
                  }
                }
              })
         });
    })
    //监听导航栏添加
    $('.add-button').click(function(){
      layer.open({
              type: 2
              ,title: '添加菜单'
              ,content: '/manage/tag/tagsAdd'
              ,area: ['42%', '72%']
              ,btn: ['确定', '取消']
              ,yes: function(index, layero){

                  var iframeWindow = window['layui-layer-iframe'+ index]
                  ,submitID = 'LAY-user-back-submit'
                  ,submit = layero.find('iframe').contents().find('#'+ submitID);
                  //监听提交
                  iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
                    var field = data.field; //获取提交的字段
                    //提交 Ajax 成功后，静态更新表格中的数据
                    // console.log(field); return false;
                    field.pid = field.pid == ''?'0':field.pid;
                    field.status = field.status == 'on'?1:0;
                    $.ajax({
                      url: '/manage/tag/tagsAdd'
                      ,type: 'post'
                      ,dataType: 'json'
                      ,data: field
                      ,async: false
                      ,success: function(res){
                        if(res.error_code == 1){
                          layer.msg(res.msg,{icon:6,time:2000},function(){
                            treeTable.render(re) //数据刷新
                            layer.close(index); //关闭弹层
                          })
                        }else{
                          layer.msg(res.msg,{icon:5,time:2000},function(){
                            treeTable.render(re) //数据刷新
                            layer.close(index); //关闭弹层
                          })
                        }
                      }
                    });
                  });  
                  submit.trigger('click');
              }
            })
    })

    //刷新权限树
    //treeTable.render(re);
  })
</script>