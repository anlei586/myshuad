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
'theme_location' => '',//�����ڵ��õ����˵�ʱָ��ע�����ĳһ�������˵��������û��ָ��������ʾ��һ��
'menu' => '',//ʹ�õ����˵������Ƶ��ò˵��������� id, slug, name (��˳��ƥ���) ��
'container' => 'div',//�����������ǩ��
'container_class' => '',//���������class��
'container_id' => '',//���������idֵ
'menu_class' => 'almaira-shop-foot-menu',//ul �ڵ�� class ����ֵ��
'menu_id' => '',//ul �ڵ�� id ����ֵ��
'echo' => true,//ȷ��ֱ����ʾ�����˵����Ƿ��� HTML Ƭ�Σ�����뽫�����Ĵ�����Ϊ��ֵʹ�ã�������Ϊfalse��
'fallback_cb' => 'wp_page_menu',//���õĵ����˵�����������û���ں�̨���õ���ʱ����
'before' => '',//��ʾ�ڵ���a��ǩ֮ǰ
'after' => '',//��ʾ�ڵ���a��ǩ֮��
'link_before' => '<span>',//��ʾ�ڵ���������֮ǰ
'link_after' => '</span>',//��ʾ�ڵ���������֮��
'items_wrap' => '<ul id="%1$s" class="%2$s" style="text-align: center;margin: 0;">%3$s</ul>',//ʹ���ַ����滻�޸�ul��class��
'depth' => 0,//��ʾ�˵������, ����ֵΪ 0 ʱ��ʾ������ȵĲ˵���
'walker' => ''//�Զ���ı������󣬵���һ����������ʾ�����˵���
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