var initdata_obj;
var mission_vue;
var me_vue;
var notice_vue;
var capital_details_vue;



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
	//填充help view
	new Vue({
		el: '#help_view',
		data: {
			tab_menu:lang_var.tab_menu
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

function getItemMinMoney(item){
	var total_sales = parseFloat(item.total_sales);
	var net_total = parseFloat(item.net_total);
	var _end = Math.min(total_sales, net_total);
	return _end;
}

//下拉刷新
function pulldownRefresh() {
	myajax(config_var.host+"initdata.php?ac=1",
	{dataType:'json',success:function(res) {
		mui('#pullrefresh').pullRefresh().endPulldownToRefresh();
		mui('#pullrefresh').pullRefresh().endPullupToRefresh();
		initdata_obj = res;
		
		initdata_obj.uid = localStorage.getItem("uid");
		initdata_obj.email = localStorage.getItem("email");
		initdata_obj.token = localStorage.getItem("token");
		
		//测试数据-我的
		var sv = {
			me:{
				user_email:initdata_obj.email
				,total_commission:(22.86+Math.random()*1).toFixed(2)
				,total_principal:831.56
				,total_interest:2.53
			}
		};
		//填充-任务列表
		if(!mission_vue){
			mission_vue = new Vue({
				el: '#tabbar-with-mission',
				data: {
					tab_menu:lang_var.tab_menu,
					mission_items:res.mission
				}
			});
		}else{
			mission_vue.mission_items=res.mission;
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
		//填充-公告
		if(!notice_vue){
			notice_vue = new Vue({
				el: '#public_notice',
				data: {
					tab_menu:lang_var.tab_menu,
					notice_items:res.notice
				}
			});
		}else{
			notice_vue.notice_items = res.notice;
		}
		//填充-资金明细
		if(!capital_details_vue){
			capital_details_vue = new Vue({
				el: '#capital_details',
				data: {
					tab_menu:lang_var.tab_menu,
					capital_details:res.meorder
				},
				methods:{
					getMinMoney:function(item){//取最小的金额
						var _money = parseFloat(getItemMinMoney(item));
						var money_proportion = _money * parseFloat(getConfig(res.config, "commission_proportion"));
						var _str = "0";
						if(item.status == "wc-completed"){
							_str = _money.toFixed(2)+"(" + money_proportion.toFixed(2) + ")";
						}
						return _str;
					},
					getInterest:function(item){//利息
						var _ovint = 0;
						if(item.status == "wc-completed"){
							//利息百分比
							var _interest = parseFloat(getConfig(res.config, "interest"));
							//服务器当前日期-下单日期=共下单了多少天
							var _day = (new Date(res.date.date)-new Date(item.date_created_gmt)) / 1000 / (60 * 60 * 24);
							_day = parseInt(_day);
							_ovint = _interest * parseFloat(getItemMinMoney(item)) * _day;
							_ovint = parseFloat(_ovint.toFixed(8));
						}
						return _ovint;
					},
					getMeIsDrawMoney:function(item){ //用户提现：0=可以提现，3=提现审核中，(1=通过审核，2=已据绝)
						var _str = "";
						if(item.status == "wc-completed"){
							var _tomoney = parseInt(item.tomoney);
							if(_tomoney == 0){
								_str = lang_var.tab_menu.me.lab.draw_money_ok;
							}else if(_tomoney == 1){
								_str = lang_var.tab_menu.me.lab.draw_money_verify_passport;
							}else if(_tomoney == 2){
								_str = lang_var.tab_menu.me.lab.draw_money_verify_decline;
							}else if(_tomoney == 3){
								_str = lang_var.tab_menu.me.lab.draw_money_verifing;
							}
						}
						return _str;
					},
					getOrderDate:function(item){//取下单日期
						var date_created = item.date_created;
						var date_created_gmt = item.date_created_gmt;
						var oid = item.order_id;
						var _str = '['+oid+'] ' + date_created + ' | ' + date_created_gmt;
						return _str;
					}
				}
			});
		}else{
			capital_details_vue.capital_details = res.meorder;
		}
		//填充免费招募员工 view
		new Vue({
			el: '#share_link_view',
			data: {
				tab_menu:lang_var.tab_menu
			},
			methods:{
				createShareLink:function(){
					var share_make_money = getConfig(res.config, "share_make_money");
					var _url = share_make_money+"reg.html?incode="+initdata_obj.uid;
					return _url;
				},
				onCopyShareLink:function(event){
					var code_sp = document.getElementById("sharelink_txt");
					const range = document.createRange();
					range.selectNode(code_sp);
					const selection = window.getSelection();
					if(selection.rangeCount > 0) selection.removeAllRanges();
					selection.addRange(range);
					document.execCommand('copy');
					mui.toast(lang_var.tab_menu.me.lab.copy_link_tip3);
					
				},
			}
		});
		
		
	
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
	if(event.target && event.target.id == "quit_account_btn"){
		quitAccount();
	}
});

//pulldownRefresh();

function quitAccount(){
	console.log("Quit");
	localStorage.removeItem("token");
	mui.openWindow({
		url: 'login.html',
		id: 'login',
		preload: true,
		show: {
			aniShow: 'pop-in'
		},
		styles: {
			popGesture: 'hide'
		},
		waiting: {
			autoShow: false
		}
	});
}

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
/////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////