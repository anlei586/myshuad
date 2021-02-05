var initdata_obj;
var mission_vue;
var me_vue;
var notice_vue;
var capital_details_vue;
var my_business_partner_vue;
var exchange_coupons_vue;
var draw_money_vue;
var day_mission_reward_vue;
var customer_service_vue;

var team_isdrawmoney_total=0;//团队可提现的总额
var ordermoney_total=0;//总本金
var commissionmoney_total=0;//总佣金
var interestmoney_total=0;//总利息

var my_team_member_total=0;//团队总人数
var my_team_money_total=0;//团队总金额

var interest=0.0000001;//利息分比例
var commission_proportion=0.10;//佣金百分比例

var OrderStatusType = {
	processing:'wc-processing',
	completed:'wc-completed',
	cancelled:"wc-cancelled",
	hold:"wc-on-hold",
};


(function($, window) {  
    //显示加载框  
    $.showLoading = function(message,type) {  
        if ($.os.plus && type !== 'div') {  
            $.plusReady(function() {  
                plus.nativeUI.showWaiting(message);  
            });  
        } else {  
            var html = '';  
            html += '<i class="mui-spinner mui-spinner-white"></i>';  
            html += '<p class="text">' + (message || "loading ...") + '</p>';  

            //遮罩层  
            var mask=document.getElementsByClassName("mui-show-loading-mask");  
            if(mask.length==0){  
                mask = document.createElement('div');  
                mask.classList.add("mui-show-loading-mask");  
                document.body.appendChild(mask);  
                mask.addEventListener("touchmove", function(e){e.stopPropagation();e.preventDefault();});  
            }else{  
                mask[0].classList.remove("mui-show-loading-mask-hidden");  
            }  
            //加载框  
            var toast=document.getElementsByClassName("mui-show-loading");  
            if(toast.length==0){  
                toast = document.createElement('div');  
                toast.classList.add("mui-show-loading");  
                toast.classList.add('loading-visible');  
                document.body.appendChild(toast);  
                toast.innerHTML = html;  
                toast.addEventListener("touchmove", function(e){e.stopPropagation();e.preventDefault();});  
            }else{  
                toast[0].innerHTML = html;  
                toast[0].classList.add("loading-visible");  
            }  
        }     
    };  

    //隐藏加载框  
      $.hideLoading = function(callback) {  
        if ($.os.plus) {  
            $.plusReady(function() {  
                plus.nativeUI.closeWaiting();  
            });  
        }   
        var mask=document.getElementsByClassName("mui-show-loading-mask");  
        var toast=document.getElementsByClassName("mui-show-loading");  
        if(mask.length>0){  
            mask[0].classList.add("mui-show-loading-mask-hidden");  
        }  
        if(toast.length>0){  
            toast[0].classList.remove("loading-visible");  
            callback && callback();  
        }  
      }  
})(mui, window);

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
	//任务分类
	new Vue({
		el: '#sliderSegmentedControl',
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
	/*pullRefresh: {
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
	}*/
});
//复制文字
function copytext(eleid){
	var code_sp = document.getElementById(eleid);
	const range = document.createRange();
	range.selectNode(code_sp);
	const selection = window.getSelection();
	if(selection.rangeCount > 0) selection.removeAllRanges();
	selection.addRange(range);
	document.execCommand('copy');
	mui.toast(lang_var.tab_menu.me.lab.copy_link_tip3);
}
//切换标签时复位滚动条到最顶
function onTabClick(event){
	var scroller = mui("#pullrefresh").scroll();
	scroller.scrollTo(0,0,0);
}

function getItemMinMoney(item){
	return item.net_total;
	/*var net_total = parseFloat(item.net_total);
	var total_sales = parseFloat(item.total_sales);
	var _end = Math.min(total_sales, net_total);
	return _end;*/
}
//把团队订单树列展平成列表
function coveMeTeamOrder(list){
	var arr = coveMeTeamOrderLoop(list);
	var isdrawmoney_total = 0;
	for(var i=0;i<arr.length;i++){
		var item = arr[i];
		var _tomoney = parseInt(item[getLevelCommissionDrawmoney(item)]);
		if(item.status == OrderStatusType.completed){
			var _money = parseFloat(getItemMinMoney(item));
			my_team_money_total += _money;
		}
		if(item.status == OrderStatusType.completed && _tomoney == 0){//用户提现：0=可以提现，3=提现审核中，(1=通过审核，2=已据绝)
			_ovint = getLevelCommissionParam(item);
			var _money = parseFloat(getItemMinMoney(item));
			var money_proportion = _money * parseFloat(getConfig(initdata_obj.config, "commission_proportion"));
			_ovint = _ovint * money_proportion;
			isdrawmoney_total += _ovint;
		}
	}
	team_isdrawmoney_total = isdrawmoney_total;
	return arr;
}
//展平列表
function coveMeTeamOrderLoop(list){
	var arr = [];
	var carr = [];
	for(var _item in list){
		my_team_member_total++;//团队总人数
		var _orderList = list[_item].order;
		if(_orderList){
			for(var i=0;i<_orderList.length;i++){
				_orderList[i]['email'] = list[_item].email;
				_orderList[i]['level'] = list[_item].level;
				//_orderList[i]['interest'] = getInterestByLevel(_orderList[i]);
				arr.push(_orderList[i]);
			}
		}
		var _orderChildList = list[_item].childorder;
		if(_orderChildList){
			carr = coveMeTeamOrderLoop(_orderChildList);
		}
	}
	arr = arr.concat(carr);
	return arr;
}
//下级员工利息算法
/*function getInterestByLevel(item){
	var _ovint = 0;
	if(item.status == OrderStatusType.completed){
		//利息百分比
		var _interest = getConfig(initdata_obj.config, "pyramid");
		var _interestArr = _interest.split(",");
		var _interestObj = {
			"B": parseFloat(_interestArr[0]),
			"C": parseFloat(_interestArr[1]),
			"D": parseFloat(_interestArr[2])
		};
		_interest = _interestObj[item["level"]];
		//服务器当前日期-下单日期=共下单了多少天
		var _day = dateSub(res.date.date, item.date_created_gmt) / 1000 / (60 * 60 * 24);
		_day = parseInt(_day);
		_ovint = _interest * parseFloat(getItemMinMoney(item)) * _day;
		_ovint = parseFloat(_ovint.toFixed(8));
	}
	return _ovint;
}*/
//取抽取下级员工佣金
function getLevelCommissionParam(order){
	var _interest = getConfig(initdata_obj.config, "pyramid");
	var _interestArr = _interest.split(",");
	var _interestObj = {
		"B": parseFloat(_interestArr[0]),
		"C": parseFloat(_interestArr[1]),
		"D": parseFloat(_interestArr[2])
	};
	return _interestObj[order["level"]];
}
function getLevelCommissionColor(order){
	if(order["level"]=="B"){
		return '<font color="#0062CC"><b>B</b></font>';
	}
	if(order["level"]=="C"){
		return '<font color="#f4a300"><b>C</b></font>';
	}
	if(order["level"]=="D"){
		return '<font color="#aa00ff"><b>D</b></font>';
	}
}
function getLevelCommissionDrawmoney(order){
	if(order["level"]=="B"){
		return 'tomoney1';
	}
	if(order["level"]=="C"){
		return 'tomoney2';
	}
	if(order["level"]=="D"){
		return 'tomoney3';
	}
}

//展平自己的订单
function coveMeOrder(meorder){
	for(var i=0;i<meorder.length;i++){
		var item = meorder[i];
		var _tomoney = parseInt(item.tomoney);
		if(item.status == OrderStatusType.completed && _tomoney == 0){
			var _commission_scale = item.commission_scale;//再买一次的倍数
			var _money = parseFloat(getItemMinMoney(item));
			//总本金
			ordermoney_total += _money;
			//总佣金
			var money_proportion = _money * commission_proportion * _commission_scale;
			commissionmoney_total += money_proportion;
			//算总利息
			var _ovint = colInteres(item);
			interestmoney_total += _ovint;
			
		}
	}
	return meorder;
}
//算利息
function colInteres(item){
	//服务器当前日期-下单日期=共下单了多少天
	var _day = dateSub(initdata_obj.date.date, item.date_created_gmt) / 1000 / (60 * 60 * 24);
	_day = parseInt(_day);
	var _ovint = interest * parseFloat(getItemMinMoney(item)) * _day;
	return _ovint;
}
//以oid查找订单
function findOrderItemByOid(orderList, oid){
	for(var i=0;i<orderList.length;i++){
		if(orderList[i].order_id==oid)
		{
			return orderList[i];
		}
	}
}

//下拉刷新
function pulldownRefresh() {
	team_isdrawmoney_total=0;//团队可提现的总额
	ordermoney_total=0;//总本金
	commissionmoney_total=0;//总佣金
	interestmoney_total=0;//总利息
	my_team_member_total=0;//团队总人数
	my_team_money_total = 0;//团队总金额
	
	mui.showLoading("loading ...","div");
	myajax(config_var.host+"initdata.php?ac=1",
	{dataType:'json',success:function(res) {
		mui.hideLoading(null);
		//mui('#pullrefresh').pullRefresh().endPulldownToRefresh();
		//mui('#pullrefresh').pullRefresh().endPullupToRefresh();
		initdata_obj = res;
		
		
		mui("#nav_tab")[0].style.display="block";
		mui("#app")[0].style.display="block";
		
		initdata_obj.uid = localStorage.getItem("uid");
		initdata_obj.email = localStorage.getItem("email");
		initdata_obj.token = localStorage.getItem("token");
		
		//利息百分比
		interest = parseFloat(getConfig(res.config, "interest"));
		//佣金百分比例
		commission_proportion = parseFloat(getConfig(res.config, "commission_proportion"));
		
		
		//任务商品的价格区间  0-25,26-50,51-9999
		var mission_product_price_gap = getConfig(res.config, "mission_product_price_gap");
		var mission_list = {};//商品分为0,1,2类
		var mission_product_price_arr = [];//把价格区间存入数组
		var pa_arr = mission_product_price_gap.split(",");
		for(var pa=0;pa<pa_arr.length;pa++){
			var ___arr = pa_arr[pa].split("-");
			var ___obj = {
				min:___arr[0],
				max:___arr[1]
			};
			mission_product_price_arr.push(___obj);
			mission_list[pa] = [];
		}
		for(var ms=0;ms<res.mission.length;ms++){
			var ___price = parseFloat(res.mission[ms].price);
			for(pa=0;pa<mission_product_price_arr.length;pa++){
				var ___obj = mission_product_price_arr[pa];
				if(___price > ___obj.min && ___price <= ___obj.max){
					mission_list[pa].push(res.mission[ms]);
				}
			}
		}
		//console.log(mission_list);
		
		//填充-任务列表
		if(!mission_vue){
			mission_vue = new Vue({
				el: '#tabbar-with-mission',
				data: {
					tab_menu:lang_var.tab_menu,
					mission_items0:mission_list[0],
					mission_items1:mission_list[1],
					mission_items2:mission_list[2],
				}
			});
		}else{
			mission_vue.mission_items=res.mission;
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
		var __capital_details = coveMeOrder(res.meorder);
		//填充-订单明细
		if(!capital_details_vue){
			capital_details_vue = new Vue({
				el: '#capital_details',
				data: {
					tab_menu:lang_var.tab_menu,
					capital_details:__capital_details
				},
				methods:{
					getItemStatus:function(item){
						var _sta = item.status;
						if(_sta){
							_sta = _sta.replace("wc-", "");
						}
						return _sta;
					},
					isDisplayEvaluate:function(item){//是否显示评价
						if(item.status == OrderStatusType.completed) return false;
						var _day = dateSub(res.date.date, item.date_created_gmt) / 1000 / (60 * 60 * 24);
						if(item.status == OrderStatusType.processing && _day>=2) return true;
						return false;
					},
					submitComments:function(item){//提交评论
						var comments_txt = document.getElementById("comments_txt_"+item.order_id);
						var submit_comments_btn = document.getElementById("submit_comments_btn_"+item.order_id);
						if(!comments_txt.value){
							mui.toast(lang_var.tab_menu.me.lab.COMMENTS_NOT_NULL);
							return;
						}
						mui(submit_comments_btn).button('loading');
						mui.confirm(lang_var.tab_menu.me.lab.confri_tip+"?", lang_var.code_lab.TIP, [lang_var.code_lab.NO, lang_var.code_lab.YES], function(e) {
							if (e.index == 1) {
								myajax(config_var.host+"change.php?ac=4&oid="+item.order_id+"&comments="+comments_txt.value,
								{dataType:'json',success:function(res) {
									mui(submit_comments_btn).button('reset');
									if(res.ret==0){
										var oitem = findOrderItemByOid(capital_details_vue.capital_details, item.order_id);
										oitem.status = OrderStatusType.completed;
										var _money = parseFloat(getItemMinMoney(item));//此单本金
										var _commission_scale = parseInt(oitem.commission_scale);//再买一次的倍数
										var money_proportion = _money * commission_proportion * _commission_scale;//此单佣金
										var _ovint = colInteres(item);//此单利息
										interestmoney_total += _ovint;//总利息
										me_vue.sv.me.total_interest = interestmoney_total.toFixed(4);//总利息
										me_vue.sv.me.total_commission=(commissionmoney_total + money_proportion).toFixed(4);//总佣金
										me_vue.sv.me.total_principal = (ordermoney_total + _money).toFixed(4);//总本金
										me_vue.sv.me.isdrawmoney_total = (parseFloat(me_vue.sv.me.isdrawmoney_total)+_money+money_proportion+interestmoney_total).toFixed(4);//可提现金额
									}
									mui.toast(res.msg);
								}});
							}else{
								mui(submit_comments_btn).button('reset');
							}
						});
						
					},
					getMinMoney:function(item){//取最小的金额
						var _money = parseFloat(getItemMinMoney(item));
						var _commission_scale = item.commission_scale;//再买一次的倍数
						var money_proportion = _money * commission_proportion * _commission_scale;
						var _str = _money.toFixed(2);
						var _tomoney = parseInt(item.tomoney);
						if(item.status == OrderStatusType.completed && _tomoney == 0){
							_str = _str + "(" + money_proportion.toFixed(2) + ")";
						}else{
							if(item.commission_scale>1 && item.status == OrderStatusType.processing){
								_str = _str + "(" + money_proportion.toFixed(2) + ")";
							}else{
								_str = _str;
							}
						}
						return _str;
					},
					getInterest:function(item){//利息
						var _ovint = 0;
						var _tomoney = parseInt(item.tomoney);
						if(item.status == OrderStatusType.completed && _tomoney == 0){
							//服务器当前日期-下单日期=共下单了多少天
							var _day = dateSub(res.date.date, item.date_created_gmt) / 1000 / (60 * 60 * 24);
							_day = parseInt(_day);
							_ovint = interest * parseFloat(getItemMinMoney(item)) * _day;
							_ovint = parseFloat(_ovint.toFixed(8));
						}
						return _ovint;
					},
					getMeIsDrawMoney:function(item){ //用户提现：0=可以提现，3=提现审核中，(1=通过审核，2=已据绝)
						var _str = "";
						if(item.status == OrderStatusType.completed){
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
						}else if(item.status == OrderStatusType.processing){
							_str = lang_var.tab_menu.me.lab.draw_money_wait_completed;
						}
						return _str;
					},
					getOrderDate:function(item){//取下单日期
						var date_created = item.date_created;
						var date_created_gmt = item.date_created_gmt;
						var oid = item.order_id;
						var _str = '['+oid+'] ' + date_created + ' | ' + date_created_gmt;
						return _str;
					},
					isDisplayAgainBuyBtn:function(item){//是否显示再买一次按钮
						var _onf = item.status == OrderStatusType.completed && (item.tomoney.toString()=='0' || item.tomoney.toString()=='2');
						if(_onf){
							return "block";
						}else{
							return "none";
						}
					},
					againBuyOrder:function(orderItem){//再买一次改为兑换优惠券
						var again_btn = document.getElementById('again_btn_'+orderItem.order_id);
						mui(again_btn).button('loading');
						mui.confirm(lang_var.tab_menu.me.lab.again_onec_buy_help_tip, lang_var.code_lab.TIP, [lang_var.code_lab.NO, lang_var.code_lab.YES], function(e) {
							if (e.index == 1) {
								myajax(config_var.host+"change.php?ac=5&oid="+orderItem.order_id,
								{dataType:'json',success:function(res) {
									mui(again_btn).button('reset');
									if(res.ret==0){
										orderItem.tomoney = 1;
										exchange_coupons_vue.exchange_coupons_data.push(res);
										sv.me.coupons_len = exchange_coupons_vue.exchange_coupons_data.length;
										
										var oitem = findOrderItemByOid(capital_details_vue.capital_details, orderItem.order_id);
										//
										var _money = parseFloat(getItemMinMoney(orderItem));
										var _commission_scale = parseInt(oitem.commission_scale);//再买一次的倍数
										var money_proportion = _money * commission_proportion * _commission_scale;
										
										var __tmp1 = commissionmoney_total - money_proportion;
										if(__tmp1<0) __tmp1 = 0;
										me_vue.sv.me.total_commission = __tmp1.toFixed(4);//总佣金
										//
										var _ovint = colInteres(oitem);//此单利息
										interestmoney_total -= _ovint;//总利息
										me_vue.sv.me.total_interest = interestmoney_total.toFixed(4);//总利息
										
										//
										__tmp1 = ordermoney_total-_money;
										if(__tmp1<0) __tmp1 = 0;
										me_vue.sv.me.total_principal = __tmp1.toFixed(4);//总本金
										
										
										__tmp1 = parseFloat(me_vue.sv.me.isdrawmoney_total)-_money-money_proportion-_ovint;
										if(__tmp1<0) __tmp1 = 0;
										me_vue.sv.me.isdrawmoney_total = __tmp1.toFixed(4);//可提现金额
										
										oitem.commission_scale = parseInt(oitem.commission_scale)+1;
										
										oitem.date_created = res.post_date;
										oitem.date_created_gmt = res.post_date;
										
										
										
										orderItem.status=OrderStatusType.completed;
										mui.toast("OK");
									}else{
										mui.toast(res.msg);
									}
								}});
							}else{
								mui(again_btn).button('reset');
							}
						});
					},
					againBuyOrder1:function(orderItem){//再买一次
						var again_btn = document.getElementById('again_btn_'+orderItem.order_id);
						mui(again_btn).button('loading');
						mui.confirm(lang_var.tab_menu.me.lab.again_onec_buy_help_tip+"?", lang_var.code_lab.TIP, [lang_var.code_lab.NO, lang_var.code_lab.YES], function(e) {
							if (e.index == 1) {
								myajax(config_var.host+"change.php?ac=3&oid="+orderItem.order_id,
								{dataType:'json',success:function(res) {
									mui(again_btn).button('reset');
									if(res.ret==0){
										//orderItem.commission_scale++;
										var oitem = findOrderItemByOid(capital_details_vue.capital_details, orderItem.order_id);
										//
										var _money = parseFloat(getItemMinMoney(orderItem));
										var _commission_scale = parseInt(oitem.commission_scale);//再买一次的倍数
										var money_proportion = _money * commission_proportion * _commission_scale;
										
										var __tmp1 = commissionmoney_total - money_proportion;
										if(__tmp1<0) __tmp1 = 0;
										me_vue.sv.me.total_commission = __tmp1.toFixed(4);//总佣金
										//
										var _ovint = colInteres(oitem);//此单利息
										interestmoney_total -= _ovint;//总利息
										me_vue.sv.me.total_interest = interestmoney_total.toFixed(4);//总利息
										
										//
										__tmp1 = ordermoney_total-_money;
										if(__tmp1<0) __tmp1 = 0;
										me_vue.sv.me.total_principal = __tmp1.toFixed(4);//总本金
										
										
										__tmp1 = parseFloat(me_vue.sv.me.isdrawmoney_total)-_money-money_proportion-_ovint;
										if(__tmp1<0) __tmp1 = 0;
										me_vue.sv.me.isdrawmoney_total = __tmp1.toFixed(4);//可提现金额
										
										oitem.commission_scale = parseInt(oitem.commission_scale)+1;
										
										var ldt = getLocalDateTime();
										oitem.date_created = ldt;
										oitem.date_created_gmt = ldt;
										
										oitem.status=OrderStatusType.processing;
										
										mui.toast(res.msg);
									}else{
										mui.toast(res.msg);
									}
								}});
							}else{
								mui(again_btn).button('reset');
							}
						});
					}
				}
			});
		}else{
			capital_details_vue.capital_details = res.meorder;
		}
		
		//每日任务奖励说明
		var daymission_reward_data=[];
		var daymission_reward_data_arr = getConfig(res.config, "daymissionreward").split(",");
		for(var i=0;i<daymission_reward_data_arr.length;i++){
			var _arr = daymission_reward_data_arr[i].split("=");
			var _obj = {
				level:i,
				ordertotal: parseFloat(_arr[0]),
				reward: parseFloat(_arr[1]),
			}
			daymission_reward_data.push(_obj);
		}
		//把自己的订单分割出来
		//得到某天共有多少单
		var _cdObj = {};
		for(var j1=0;j1<__capital_details.length;j1++){
			var __item = __capital_details[j1];
			var __isComp = __item.status==OrderStatusType.completed;
			var __date = __item['date_created_gmt'].split(" ")[0];
			if(!_cdObj.hasOwnProperty(__date))
			{
				_cdObj[__date] = {
					date:__date,
					complete_count: __isComp ? 1 : 0,
					other_count: __isComp ? 0 : 1,
					//complete_count: parseInt(Math.random()*100),	//测试数据，这行需要注释掉
					//other_count: parseInt(Math.random()*100),		//测试数据，这行需要注释掉
				};
			}else{
				if(__isComp){
					_cdObj[__date].complete_count++;
				}else{
					_cdObj[__date].other_count++;
				}
			}
		}
		//把订单按日期从晚到早排序
		var day_mission_reward_total = 0;//每日任务奖励总共有得了多少钱
		var _cdArr = [];
		for(var __date in _cdObj){
			var __progress = "";
			var __reward = 0;
			for(var j2=0;j2<daymission_reward_data.length;j2++){
				if(_cdObj[__date].complete_count>=daymission_reward_data[j2].ordertotal){
					__progress = __progress + "+" + daymission_reward_data[j2].reward;
					__reward = __reward + daymission_reward_data[j2].reward;
					day_mission_reward_total += daymission_reward_data[j2].reward;
				}
			}
			_cdObj[__date]['progress'] = __progress.substr(1);
			_cdObj[__date]['reward'] = __reward;
			_cdArr.push(_cdObj[__date]);
		}
		_cdArr.sort(function(a,b) { return a['date'] < b['date']; });
		var daymission = _cdArr;
		
		//每日任务奖励
		if(!day_mission_reward_vue){
			day_mission_reward_vue = new Vue({
				el: '#daymission_reward',
				data: {
					tab_menu:lang_var.tab_menu,
					daymission_reward_data:daymission_reward_data,
					daymission:daymission,
				},
			});
		}else{
			day_mission_reward_vue.daymission_reward_data = daymission_reward_data;
			day_mission_reward_vue.daymission = daymission;
		}
		
		var __my_business_partner = coveMeTeamOrder(res.meteamorder);
		//我的团队
		if(!my_business_partner_vue){
			my_business_partner_vue = new Vue({
				el: '#my_business_partner',
				data: {
					tab_menu:lang_var.tab_menu,
					my_business_partner:__my_business_partner,
					my_team_member_total:my_team_member_total,
					my_team_money_total:my_team_money_total.toFixed(2)
				},
				methods:{
					getItemStatus:function(item){
						var _sta = item.status;
						if(_sta){
							_sta = _sta.replace("wc-", "");
						}
						return _sta;
					},
					getMinMoney:function(item){//取最小的金额
						var _money = parseFloat(getItemMinMoney(item));
						var money_proportion = _money * commission_proportion;
						var _str = _money.toFixed(2);
						var _tomoney = parseInt(item.tomoney);
						if(item.status == OrderStatusType.completed && _tomoney == 0){
							_str = _str + "(" + money_proportion.toFixed(2) + ")";
						}else{
							_str = _str;
						}
						return _str;
					},
					getInterest:function(item){//抽成
						var _ovint = "";
						if(item.status == OrderStatusType.completed){
							_ovint = getLevelCommissionParam(item);
							var _money = parseFloat(getItemMinMoney(item));
							var money_proportion = _money * commission_proportion;
							_ovint = _ovint * money_proportion;
							_ovint = _ovint.toFixed(2);
						}
						return _ovint;
					},
					getMeIsDrawMoney:function(item){ //用户提现：0=可以提现，3=提现审核中，(1=通过审核，2=已据绝)
						var _str = "";
						if(item.status == OrderStatusType.completed){
							var _tomoney = parseInt(item[getLevelCommissionDrawmoney(item)]);
							if(_tomoney == 0){
								_str = lang_var.tab_menu.me.lab.draw_money_ok;
							}else if(_tomoney == 1){
								_str = lang_var.tab_menu.me.lab.draw_money_verify_passport;
							}else if(_tomoney == 2){
								_str = lang_var.tab_menu.me.lab.draw_money_verify_decline;
							}else if(_tomoney == 3){
								_str = lang_var.tab_menu.me.lab.draw_money_verifing;
							}
						}else if(item.status == OrderStatusType.processing){
							_str = lang_var.tab_menu.me.lab.draw_money_wait_completed;
						}
						return _str;
					},
					getOrderDate:function(item){//取下单日期
						var levelColor = getLevelCommissionColor(item);
						var buyer_email= item.email;
						var date_created_gmt = item.date_created_gmt;
						var oid = item.order_id;
						var _str = '['+oid+'] ' + levelColor + ' | ' + buyer_email + ' | ' + date_created_gmt;
						return _str;
					}
				}
			});
		}else{
			my_business_partner_vue.my_business_partner = res.meorder;
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
					var _lang = localStorage.getItem("lang");
					var _url = share_make_money+"?lang="+_lang+"&incode="+initdata_obj.uid;
					return _url;
				},
				onCopyShareLink:function(event){
					copytext("sharelink_txt");
				},
			}
		});
		
		//填充客服 view
		new Vue({
			el: '#customer_service_view',
			data: {
				tab_menu:lang_var.tab_menu
			},
			methods:{
				getServiceInfo:function(){
					var customer_service = getConfig(res.config, "customer_service");
					return customer_service;
				},
				onCopyServiceInfo:function(event){
					copytext("serviceInfo_txt");
				},
			}
		});
		
		
		var notice_len = res.notice.length;
		var local_notice_len = localStorage.getItem("noticelen");
		notice_len = notice_len-local_notice_len;
		notice_len = notice_len.toString();
		
		var givemoney = parseFloat(initdata_obj.meuser.givemoney);
		
		var __isdrawmoney_total = commissionmoney_total + ordermoney_total + interestmoney_total + team_isdrawmoney_total + day_mission_reward_total + givemoney;
		//我的数据
		var sv = {
			me:{
				user_email:initdata_obj.email
				,total_commission:commissionmoney_total.toFixed(4)//总佣金
				,total_principal:ordermoney_total.toFixed(4)//总本金
				,total_interest:interestmoney_total.toFixed(4)//总利息
				,team_isdrawmoney_total:team_isdrawmoney_total.toFixed(4)//团队佣金
				,isdrawmoney_total:__isdrawmoney_total.toFixed(4)//可提现金额
				,notice_len:notice_len
				,coupons_len:res.coupons.length
				,day_mission_reward_total:day_mission_reward_total.toFixed(4)
			}
		};
		//填充-我的数据
		if(!me_vue){
			me_vue = new Vue({
				el: '#tabbar-with-me',
				data: {
					tab_menu:lang_var.tab_menu,
					sv:sv
				},
				methods:{
					gotoDrawMoney:function(){
						viewApi.go("#draw_money");
					},
				}
			});
		}else{
			me_vue.sv = sv;
		}
		
		//兑换优惠券
		if(!exchange_coupons_vue){
			exchange_coupons_vue = new Vue({
				el: '#exchange_coupons',
				data: {
					tab_menu:lang_var.tab_menu,
					exchange_coupons_data:res.coupons,
				},
				methods:{
					onCopyCoupons:function(item){
						copytext("coupons_item_"+item.ID);
					},
					onSplitCoupons:function(item){
						var again_btn = document.getElementById('split_coupons_btn_'+item.ID);
						mui(again_btn).button('loading');
						var _desc = lang_var.tab_menu.me.lab.split_coupons_desc;
						_desc = _desc.replace("{money}", parseFloat(item.amount).toFixed(2));
						mui.prompt(_desc,
							lang_var.tab_menu.me.lab.amounts_tip,
							lang_var.tab_menu.me.lab.split_coupons_tip,
							[lang_var.code_lab.NO, lang_var.code_lab.YES],
						function(e) {
							if (e.index == 1 && parseFloat(e.value)>0) {
								myajax(config_var.host+"change.php?ac=6&id="+item.ID+"&title="+item.post_title+"&m1="+e.value,
								{dataType:'json',success:function(res) {
									mui(again_btn).button('reset');
									if(res.ret==0){
										for(var i=0;i<exchange_coupons_vue.exchange_coupons_data.length;i++){
											if(exchange_coupons_vue.exchange_coupons_data[i].ID==item.ID){
												exchange_coupons_vue.exchange_coupons_data[i].amount = res.m2;
												break;
											}
										}
										exchange_coupons_vue.exchange_coupons_data.push(res);
										
										mui.toast(res.msg);
									}else{
										mui.toast(res.msg);
									}
								},error:function(err){
									mui(again_btn).button('reset');
								},
								});
							}else{
								mui(again_btn).button('reset');
							}
						});
					},
				},
			});
		}else{
			exchange_coupons_vue.exchange_coupons_data = res.coupons;
		}
		
		var draw_money_data = {
			me_paypal:localStorage.getItem("paypal"),
			me_isdrawmoney_total:__isdrawmoney_total.toFixed(4),
			display_drawmoney_ui:getConfig(res.config, "display_drawmoney_ui")
		}
		//提现UI
		if(!draw_money_vue){
			draw_money_vue = new Vue({
				el: '#draw_money',
				data: {
					tab_menu:lang_var.tab_menu,
					draw_money_data:draw_money_data
				},
				methods:{
					onHelpClick:function(){
						console.log("open udst");
						//window.open("https://www.youtube.com/watch?v=Si_KLSbNSZ8");
						window.open("drawmoneyhelp.html");
					},
					onSubPaypal:function(){//确定提交paypal号
						var draw_money_paypal_txt = document.getElementById('draw_money_paypal_txt');
						var sub_paypal_btn = document.getElementById('sub_paypal_btn');
						mui(sub_paypal_btn).button('loading');
						mui.confirm(lang_var.tab_menu.me.lab.confri_tip+"?", lang_var.code_lab.TIP, [lang_var.code_lab.NO, lang_var.code_lab.YES], function(e) {
							if (e.index == 1) {
								if(draw_money_paypal_txt.value.length<=0){
									mui.alert(lang_var.tab_menu.me.lab.Please_Input_Paypal_Account);
									mui(sub_paypal_btn).button('reset');
									return;
								}
								myajax(config_var.host+"change.php?ac=1&paypal="+draw_money_paypal_txt.value,
								{dataType:'json',success:function(res) {
									mui(sub_paypal_btn).button('reset');
									console.log(res);
									localStorage.setItem('paypal', res.paypal);
									draw_money_vue.draw_money_data.me_paypal = res.paypal;
								}});
							}else{
								mui(sub_paypal_btn).button('reset');
							}
						});
					},
					onConfriDrawMoney:function(type){//确定提现 ，type=0时为提现，type=1时则为转换优惠券
						if(__isdrawmoney_total<100 && type==0){
							mui.toast(lang_var.tab_menu.me.lab.drawmone_100_tip);
							return;
						}
						var sub_draw_money_btn = document.getElementById('sub_draw_money_btn');
						if(type==1){
							sub_draw_money_btn = document.getElementById('sub_covecoupons_btn');
						}
						mui(sub_draw_money_btn).button('loading');
						mui.confirm(lang_var.tab_menu.me.lab.confri_tip+"?", lang_var.code_lab.TIP, [lang_var.code_lab.NO, lang_var.code_lab.YES], function(e) {
							if (e.index == 1) {
								if(!localStorage.getItem('paypal')){
									mui(sub_draw_money_btn).button('reset');
									mui.alert(lang_var.tab_menu.me.lab.Please_Input_Paypal_Account);
									return;
								}
								if(__isdrawmoney_total<=0){
									mui(sub_draw_money_btn).button('reset');
									mui.alert(lang_var.tab_menu.me.lab.TIP_NOT_MONEY_DRAW);
									return;
								}
								myajax(config_var.host+"change.php?ac=2&type="+type,
								{dataType:'json',success:function(res) {
									mui(sub_draw_money_btn).button('reset');
									console.log(res);
									if(res.ret==0){
										mui.alert(lang_var.tab_menu.me.lab.ok_draw_money_complete,'',function() {
											window.location.reload();
										});
									}else{
										mui.toast(res.msg);
									}
								}});
							}else{
								mui(sub_draw_money_btn).button('reset');
							}
						});
					}
				}
			});
		}else{
			draw_money_vue.draw_money_data = draw_money_data;
		}
	
	},
	});
	
	
};


function clickNoticeBtn(){
	var notice_len = initdata_obj.notice.length;
	localStorage.setItem("noticelen", notice_len);
	me_vue.sv.me.notice_len="0";
}


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
	if(event.target && event.target.id == "forgetPassword_btn"){
		forgetPasswdOpen();
		return;
	}
	if(event.target && event.target.id == "quit_account_btn"){
		quitAccount();
		return;
	}
	if(event.target && event.target.id == "open_notice_btn"){
		clickNoticeBtn();
		return;
	}
	
});

pulldownRefresh();

function forgetPasswdOpen(){
	mui.openWindow({
		url: 'forget_password.html',
		id: 'forgetpassword',
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

mui('.mui-scroll-wrapper').scroll({
	bounce: false,
	indicators: true, //是否显示滚动条
	deceleration:0.0009
});


mui("#nav_tab")[0].style.display="none";
mui("#app")[0].style.display="none";

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
