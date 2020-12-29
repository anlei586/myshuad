
var mission_vue;
var me_vue;



function fillVue(){
	//填充tab栏
	new Vue({
		el: '.vuefill1',
		data: {
			data:lang_var.tab_menu_lab
		}
	});
	//app name
	new Vue({
		el: '.vuefill2',
		data: {
			data:lang_var.tab_menu_lab
		}
	});
}
fillVue();

mui.init({
	//swipeBack:true ,//启用右滑关闭功能
	pullRefresh: {
		container: '#pullrefresh',
		down: {
			style: 'circle',
			offset: '0px',
			auto: true,
			contentrefresh: "",//正在刷新
			contentdown : "",//下拉可以刷新
			contentover : " ",//释放立即刷新
			callback: pulldownRefresh
		}
	}
});
//切换标签时复位滚动条到最顶
function onTabClick(event){
	var scroller = mui("#pullrefresh").scroll();
	scroller.scrollTo(0,0,0);
}

function pulldownRefresh() {
	mui.ajax("http://127.0.0.1/test.php",
	{success:function(rsp) {
		mui('#pullrefresh').pullRefresh().endPulldownToRefresh();
		mui('#pullrefresh').pullRefresh().endPullupToRefresh();
		//测试数据-我的
		var sv = {
			me:{
				user_email:"13321@13"
				,total_commission:(22.86+Math.random()*1).toFixed(2)
				,total_principal:831.56
			}
		};
		//测试数据-任务
		var mission_items = [
			{
				img:"./images/cbd.jpg",
				title:"这是测试商品1",
				link:"https://m.baidu.com/?1",
			},
			{
				img:"./images/cbd.jpg",
				title:"这是测试商品2",
				link:"https://m.baidu.com/?2",
			},
		];
		//填充-任务列表
		if(!mission_vue){
			mission_vue = new Vue({
				el: '#tabbar-with-mission',
				data: {
					tab_menu:lang_var.tab_menu,
					mission_items:mission_items
				},
				methods:{
					onMissionItemClick:function(event){
						console.log(event);
					}
				}
			});
		}else{
			mission_vue.mission_items=mission_items;
		}
		//填充-我的数据
		if(!me_vue){
			me_vue = new Vue({
				el: '#tabbar-with-me',
				data: {
					tab_menu:lang_var.tab_menu,
					sv:sv
				}
			});
		}else{
			me_vue.sv = sv;
		}
	},
	});
	
};

//任务列表点击
mui("#pullrefresh").on('tap', 'li', function (event) {
	//event.stopPropagation();
	//event.preventDefault();
	var ahref = event.target.getAttribute("data-name");
	var adata = event.target.getAttribute("data-item");
	if(!ahref){
		ahref = event.target.parentElement.getAttribute("data-name");
		adata = event.target.parentElement.getAttribute("data-item");
		if(!ahref){
			ahref = event.target.parentElement.parentElement.getAttribute("data-name");
			adata = event.target.parentElement.parentElement.getAttribute("data-item");
			if(!ahref){
				ahref = event.target.parentElement.parentElement.parentElement.getAttribute("data-name");
				adata = event.target.parentElement.parentElement.parentElement.getAttribute("data-item");
			}
		}
	}
	if(ahref && adata){//跳到商品页
		mui.confirm(adata, lang_var.code_lab.OPEN_LINK, [lang_var.code_lab.NO, lang_var.code_lab.YES], function(e) {
			if (e.index == 1) {
				window.open(adata);
			} else {
				
			}
		});
	}
});

pulldownRefresh();

//
//
//初始化单页view
var viewApi = mui('#app').view({
	defaultPage: '#app_content'
});
mui('.mui-scroll-wrapper').scroll();
var view = viewApi.view;
(function($) {
	//处理view的后退与webview后退
	var oldBack = $.back;
	$.back = function() {
		if (viewApi.canBack()) { //如果view可以后退，则执行view的后退
			viewApi.back();
		} else { //执行webview后退
			oldBack();
		}
	};
	//监听页面切换事件方案1,通过view元素监听所有页面切换事件，目前提供pageBeforeShow|pageShow|pageBeforeBack|pageBack四种事件(before事件为动画开始前触发)
	//第一个参数为事件名称，第二个参数为事件回调，其中e.detail.page为当前页面的html对象
	view.addEventListener('pageBeforeShow', function(e) {
		//				console.log(e.detail.page.id + ' beforeShow');
	});
	view.addEventListener('pageShow', function(e) {
		//				console.log(e.detail.page.id + ' show');
	});
	view.addEventListener('pageBeforeBack', function(e) {
		//				console.log(e.detail.page.id + ' beforeBack');
	});
	view.addEventListener('pageBack', function(e) {
		//				console.log(e.detail.page.id + ' back');
	});
})(mui);
