/*
document.querySelector(".wp-bottom-menu-search-form-trigger").addEventListener("click", function() {
    document.querySelector(".wp-bottom-menu-search-form-wrapper").classList.toggle("sf-active");
    document.querySelector(".wp-bottom-menu").classList.toggle("sf-active");
});

*/

/*

const wpbmsft = document.querySelector(".wp-bottom-menu-search-form-trigger");
const wbmsfw = document.querySelector(".wp-bottom-menu-search-form-wrapper");
const wpbmt = document.querySelector(".wp-bottom-menu");

wpbmsft.addEventListener("click", function() {
    wbmsfw.classList.toggle("sf-active");
    wpbmt.classList.toggle("sf-active");
});*/

function wpbmsft(){
	document.querySelector(".wp-bottom-menu-search-form-wrapper").classList.toggle("sf-active");
    document.querySelector(".wp-bottom-menu").classList.toggle("sf-active");
}
function wpopenmainmenu(e){
	/*
    e.preventDefault();
	var $=jQuery;
	if ($("#my-menu").hasClass("mm--open")) {
		mmenu.close();
	} else {
		$("#my-menu")[0].style.display="block";
		mmenu.open();
		$("a.dropdown-toggle").focusin(
				function () {
					$('.dropdown').addClass('open')
				}
		);
		$("#my-menu li:last").focusout(
				function () {
					mmenu.close();
				}
		);
		$("#main-menu-panel").focusin(
				function () {
					mmenu.close();
				}
		);
		$("#main-menu-panel").on('keydown blur', function (e) {
			if (e.shiftKey && e.keyCode === 9) {
				mmenu.close();
			}
		});
	}
    e.stopPropagation();
	*/
	console.log("click main menu");
	e.preventDefault();
	jQuery('#menu-btn').click();
    e.stopPropagation();
	
}