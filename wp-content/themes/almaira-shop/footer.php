<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @package ThemeHunk
 * @subpackage Almaira Shop
 * @since 1.0.0
 */ 
?>
<footer class="thunk-footer">
	<hr style=" margin-bottom: 12px; ">
	<div class="footer-wrap widget-area">
<?php
$defaults = array(
'theme_location' => '',//用于在调用导航菜单时指定注册过的某一个导航菜单名，如果没有指定，则显示第一个
'menu' => '',//使用导航菜单的名称调用菜单，可以是 id, slug, name (按顺序匹配的) 。
'container' => 'div',//最外层容器标签名
'container_class' => '',//最外层容器class名
'container_id' => '',//最外层容器id值
'menu_class' => 'almaira-shop-foot-menu',//ul 节点的 class 属性值。
'menu_id' => '',//ul 节点的 id 属性值。
'echo' => true,//确定直接显示导航菜单还是返回 HTML 片段，如果想将导航的代码作为赋值使用，可设置为false。
'fallback_cb' => 'wp_page_menu',//备用的导航菜单函数，用于没有在后台设置导航时调用
'before' => '',//显示在导航a标签之前
'after' => '',//显示在导航a标签之后
'link_before' => '<span>',//显示在导航链接名之前
'link_after' => '</span>',//显示在导航链接名之后
'items_wrap' => '<ul id="%1$s" class="%2$s" style="text-align: center;margin: 0;">%3$s</ul>',//使用字符串替换修改ul的class。
'depth' => 0,//显示菜单的深度, 当数值为 0 时显示所有深度的菜单。
'walker' => ''//自定义的遍历对象，调用一个对象定义显示导航菜单。
);
wp_nav_menu( $defaults );
?>
<?php 
$almaira_shop_above_footer_layout = get_theme_mod('almaira_shop_above_footer_layout','ft-abv-none');
$almaira_shop_bottom_footer_widget_layout = get_theme_mod('almaira_shop_bottom_footer_widget_layout','ft-wgt-none');
$almaira_shop_bottom_footer_layout = get_theme_mod('almaira_shop_bottom_footer_layout','ft-btm-one');
if($almaira_shop_above_footer_layout!=='ft-abv-none'):
        almaira_shop_top_footer();
endif;
if($almaira_shop_bottom_footer_widget_layout!=='ft-wgt-none'):
        almaira_shop_widget_footer();
endif;
if($almaira_shop_bottom_footer_layout!=='ft-btm-none'):
        almaira_shop_bottom_footer();
endif;
      ?>
	</div>

</footer>
<?php wp_footer(); ?>
</body>
</html>