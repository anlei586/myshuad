!function(f,b,e,v,n,t,s)
{
	if(f.fbq)return;
	n=f.fbq=function(){
		n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)
	};
	if(!f._fbq)f._fbq=n;
	n.push=n;
	n.loaded=!0;
	n.version='2.0';
	n.queue=[];
	t=b.createElement(e);
	t.async=!0;
	t.src=v;
	s=b.getElementsByTagName(e)[0];
	s.parentNode.insertBefore(t,s);
}(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

function fbq_initpix(pixid, trac){
	fbq('init', pixid);
	fbq('track', trac);
	console.log(pixid + ':' + trac);
}

function fbq_for_sets(pids, trac){
	if(pids){
		for(var i=0;i<pids.length;i++){
			fbq_initpix(pids[i], trac);
		}
	}
}

/*
fbq_for_sets('535674304033452', 'PageView');

fbq_for_sets('', 'ViewContent');
fbq_for_sets('', 'AddToCart');
fbq_for_sets('', 'InitiateCheckout');
fbq_for_sets('', 'Purchase');
*/