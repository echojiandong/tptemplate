<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:63:"D:\tp\ywd100\application/../Template/manage/wechat\navlist.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>文章列表--layui后台管理模板</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" href="/static/manage/layui//css/layui.css" media="all" />
	<link rel="stylesheet" href="//at.alicdn.com/t/font_tnyc012u2rlwstt9.css" media="all" />
</head>
<body class="childrenBody">
	<blockquote class="layui-elem-quote news_search">
		<div class="layui-inline">
			<a class="layui-btn layui-btn-normal newsAdd_btn" onclick="layui.navadd()">添加</a>
		</div>
		<button class="layui-btn layui-btn-danger" lay-submit="" onclick="layui.pushWechat()">推送至公众号</button>
		
	</blockquote>
	<div class="layui-form news_list">
		<table class="layui-table layui-form" lay-skin="line" >
			<colgroup>
				<col width="5%">
				
				<col width="15%">
				<col width="15%">
				<col>
				<col width="10%">
				<col width="20%">
				<col width="20%">
			</colgroup>
			<thead>
			<tr>
				<th><input type="checkbox" name="" lay-skin="primary" lay-filter="allChoose"></th>
				<th>排序</th>
				
				<th>栏目名称</th>
				<th>栏目类型</th>
				<!-- <th>链接</th> -->
				<th>状态</th>
				<th>操作</th>
			</tr>
			</thead>
			<tbody>
			<?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
			<tr>
				<td><input type="checkbox"  name="id" <?php if($vo['is_tab'] == 1): ?> checked <?php endif; ?> lay-skin="primary" value="<?php echo $vo['id']; ?>"></td>
				<td><input style="width: 3em;" type="number" v-id="<?php echo $vo['id']; ?>" name="sort" lay-skin="primary" onchange="layui.numord(this)" value="<?php echo $vo['sort']; ?>"></td>
				
				<td style="text-align: left;padding-left:<?php echo $vo['level']*20; ?>px">
				<?php $__FOR_START_527032309__=0;$__FOR_END_527032309__=$vo['level'];for($i=$__FOR_START_527032309__;$i < $__FOR_END_527032309__;$i+=1){ ?>
					├
				<?php } ?>
				<?php echo $vo['name']; ?>
				</td>
				<td>
					<?php if($vo['type'] == 'view'): ?> 网页
					<?php elseif($vo['type'] == 'click'): ?> 点击
					<?php elseif($vo['type'] == 'miniprogram'): ?> 小程序
					<?php else: endif; ?>
				</td>
				<!-- <td><?php echo $vo['url']; ?></td> -->
				<td>
					<?php echo $vo['status']==1?'
					<span style="color:#090">使用中</span>
					':'
					<span style="color:#FF5722">已停用</span>
					'; ?>

				</td>
				<td>
				<a class="lay-a" href="javascript:void(0)" onclick="layui.edit('<?php echo $vo['id']; ?>')">编辑</a>
				<!-- <span class="text-explode">|</span> -->
				<!-- <a data-field="status" class="lay-a"  data-value="0" href="javascript:void(0)" onclick="layui.switch('<?php echo $vo['id']; ?>','<?php echo $vo['status']; ?>')"><?php echo $vo['status']==1?'禁用':'启用'; ?></a> -->
				<span class="text-explode">|</span>
				<a  data-field="delete" class="lay-a"  data-action="/admin/menu/del.html" href="javascript:void(0)" onclick="layui.delete('<?php echo $vo['id']; ?>')">删除</a>

				</td>
			</tr>
				<?php if(is_array($vo['children']) || $vo['children'] instanceof \think\Collection || $vo['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
			<tr>
				<td><input type="checkbox" name="id" <?php if($vo['is_tab'] == 1): ?> checked <?php endif; ?> lay-skin="primary" value="<?php echo $vo['id']; ?>"></td>
				<td><input style="width: 3em;" type="number" v-id="<?php echo $vo['id']; ?>" name="sort" lay-skin="primary" onchange="layui.numord(this)" value="<?php echo $vo['sort']; ?>"></td>
				
				<td style="text-align: left;padding-left:<?php echo $vo['level']*40; ?>px">
					<?php $__FOR_START_221243810__=0;$__FOR_END_221243810__=$vo['level'];for($i=$__FOR_START_221243810__;$i < $__FOR_END_221243810__;$i+=1){ ?>
					├
					<?php } ?>
					<?php echo $vo['name']; ?>
				</td>
				
				<td>
					<?php if($vo['type'] == 'view'): ?> 网页
					<?php elseif($vo['type'] == 'click'): ?> 点击
					<?php elseif($vo['type'] == 'miniprogram'): ?> 小程序
					<?php else: endif; ?>
					
				</td>
				<!-- <td><?php echo $vo['url']; ?></td> -->
				<td>
					<?php echo $vo['status']==1?'
					<span style="color:#090">使用中</span>
					':'
					<span style="color:#FF5722">已停用</span>
					'; ?>

				</td>
				<td>
					<a class="lay-a" href="javascript:void(0)" onclick="layui.edit('<?php echo $vo['id']; ?>')">编辑</a>
					<!-- <span class="text-explode">|</span> -->
					<!-- <a data-field="status" class="lay-a"  data-value="0" href="javascript:void(0)" onclick="layui.switch('<?php echo $vo['id']; ?>','<?php echo $vo['status']; ?>')"><?php echo $vo['status']==1?'禁用':'启用'; ?></a> -->
					<span class="text-explode">|</span>
					<a  data-field="delete" class="lay-a"  data-action="/admin/menu/del.html" href="javascript:void(0)" onclick="layui.delete('<?php echo $vo['id']; ?>')">删除</a>

				</td>
			</tr>
				<?php endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; ?>

			</tbody>
		</table>
	</div>
	<script type="text/javascript" src="/static/manage/layui//layui.js"></script>
	<script type="text/javascript" src="/static/manage/js/wechatnavlist.js"></script>
</body>
</html>