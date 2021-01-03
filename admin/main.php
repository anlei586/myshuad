<!-- 老哥手下留情，小弟已做好防注入工作。 -->
<!-- 老哥手下留情，小弟已做好防注入工作。 -->
<!-- 老哥手下留情，小弟已做好防注入工作。 -->
<?php

session_start();
if (isset($_GET["action"])){
	$action = $_GET["action"];
	if($action == "logout"){
		unset($_SESSION["admin"]);
		session_destroy();
	}
}
require('./utils.php');

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>后台管理</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="./layui/css/layui.css">
	<style>
		::-webkit-scrollbar { width: 10px; height: 10px; }
		::-webkit-scrollbar-track { background: rgba(241, 241, 242, 1); }
		::-webkit-scrollbar-thumb { background-color: #33333375; }
		::-webkit-scrollbar-thumb:window-inactive { background: #d2d2d2; }
	</style>
</head>
<body>
<div style="text-align: right;margin: 4px;display: block;position: fixed;right: 8px;">
</div>
<div id="TOP_menu_header_div" class="layui-header">
	<a href="javascript:;" onclick="onCloseMenu();"></a>
    <ul class="layui-nav" lay-filter="T_navmenu" style="text-align: right;width: 100%; position: fixed; border-radius: 0px;">
	  <li class="layui-nav-item">
        <a href="javascript:;" data-url="changepasswd">改密</a>
      </li>
      <li class="layui-nav-item">
        <a href="?action=logout">退出</a>
      </li>
    </ul>
</div>
<div id="L_menu" class="layui-side" style="display: block; position: absolute; height: 100%;">
    <!-- 侧边导航:  -->
    <ul class="layui-nav layui-nav-tree layui-nav-side" style="top:60px; border-radius: 0px;" lay-filter="L_navmenu">
      
      <li class="layui-nav-item layui-nav-itemed">
        <a href="javascript:;">管理</a>
        <dl class="layui-nav-child">
          <dd><a href="javascript:;" data-url="setting"><i class="layui-icon">&#xe716;</i> 站点设置</a></dd>
          <dd><a href="javascript:;" data-url="products"><i class="layui-icon">&#xe857;</i> 任务商品</a></dd>
          <dd><a href="javascript:;" data-url="orderlist"><i class="layui-icon">&#xe657;</i> 订单列表</a></dd>
          <dd><a href="javascript:;" data-url="userlist"><i class="layui-icon">&#xe770;</i> 用户列表</a></dd>
          <dd><a href="javascript:;" data-url="noticelist"><i class="layui-icon">&#xe670;</i> 公告列表</a></dd>
        </dl>
      </li>
    </ul>
</div>

<script>
var load_ind = -1;
function onViewIfrLoad(){
	layer.close(load_ind);
}
</script>
<div class="layui-body" id="content_div" style=" top: 60px; margin-top: 0px; -webkit-overflow-scrolling:touch;overflow:hidden;">
    <iframe id="view_ifr" onload="onViewIfrLoad();" frameborder="0" scrolling='yes' src="./frame.php" style="width: 100%;height: 100%; border: 0px; -webkit-overflow-scrolling:touch; overflow:auto;"></iframe>
</div>


<script src="./layui/layui.all.js"></script>
<script>
var view_ifr = document.getElementById('view_ifr');
var ip_ahref = document.getElementById('ip_ahref');
//view_ifr.style.height = (window.innerHeight+30)+"px";

var menu_display_onf = false;
function onCloseMenu(){
	
//	L_menu.className='layui-nav layui-nav-tree layui-nav-side ';
	
	menu_display_onf = !menu_display_onf;
	if(menu_display_onf){
		L_menu.className='layui-nav layui-nav-tree layui-nav-side layui-hide';
		L_menu.style.display='none';
		content_div.style.left = "0px";
	}else{
		L_menu.className='layui-nav layui-nav-tree layui-nav-side ';
		L_menu.style.display='block';
		content_div.style.left = "200px";
	}
	content_div.style['-webkit-overflow-scrolling']='touch';
	
}

function onLoadCol(isloader){
	var _ind = 0;
	if(isloader){
		_ind = layer.load(2);
	}
	

}

//一般直接写在一个js文件中
layui.use(['element','layer', 'form'], function(){
  var layer = layui.layer
  ,form = layui.form;

  onLoadCol();

  var element = layui.element; //导航的hover效果、二级菜单等功能，需要依赖element模块
  
  
//监听 左侧 导航点击
element.on('nav(L_navmenu)', function(elem){
	var _url = elem.attr("data-url");
	if(_url){
		load_ind = layer.load(2);
		view_ifr.src = "./"+_url+".php";
		//onCloseMenu();
	}
});

//监听 顶部 导航点击
element.on('nav(T_navmenu)', function(elem){
	var _ret = elem.attr("data-ret");
	if(_ret==1){
		return;
	}
	if(_ret==2){
		onLoadCol(true);
		return;
	}
	var _url = elem.attr("data-url");
	if(_url){
		load_ind = layer.load(2);
		if(_url == 'changepasswd'){
			view_ifr.src = "./apipasswds.php";
		}else{
			view_ifr.src = "./"+_url+".php";
		}
	}
  });

  //layer.msg('Hello World');
});

// 阻止window滚动
function block_window_scroll(e){
	e.preventDefault();
	e.stopPropagation();
}
// 阻止在header/footer上触发iso网页上的滚动/弹性拖拽
// 如果在获得焦点时通过header/footer触发了滚动/弹性拖拽,
// 会有不想看到的情况

var TOP_menu_header_div = document.getElementById('TOP_menu_header_div');
TOP_menu_header_div.addEventListener('touchmove',block_window_scroll);

</script> 



</body>
</html>