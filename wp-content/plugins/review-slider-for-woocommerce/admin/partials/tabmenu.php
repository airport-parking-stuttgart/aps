<?php
$urltrimmedtab = remove_query_arg( array('page', '_wpnonce', 'taction', 'tid', 'sortby', 'sortdir', 'opt','settings-updated') );

$urlreviewlist = esc_url( add_query_arg( 'page', 'srfw-reviews',$urltrimmedtab ) );
$urltemplateposts = esc_url( add_query_arg( 'page', 'srfw-templates_posts',$urltrimmedtab ) );
$urlgetpro = esc_url( add_query_arg( 'page', 'srfw-get_woo',$urltrimmedtab ) );
$urlforum = esc_url( add_query_arg( 'page', 'srfw-get_pro',$urltrimmedtab ) );
?>	
	<h2 class="nav-tab-wrapper">
	<a href="<?php echo $urlgetpro; ?>" class="nav-tab <?php if($_GET['page']=='srfw-get_woo'){echo 'nav-tab-active';} ?>"><?php _e('Sync Reviews', 'review-slider-for-woocommerce'); ?></a>
	<a href="<?php echo $urlreviewlist; ?>" class="nav-tab <?php if($_GET['page']=='srfw-reviews'){echo 'nav-tab-active';} ?>"><?php _e('Review List', 'review-slider-for-woocommerce'); ?></a>
	<a href="<?php echo $urltemplateposts; ?>" class="nav-tab <?php if($_GET['page']=='srfw-templates_posts'){echo 'nav-tab-active';} ?>"><?php _e('Templates', 'review-slider-for-woocommerce'); ?></a>
	<a href="<?php echo $urlforum; ?>" class="nav-tab <?php if($_GET['page']=='srfw-get_pro'){echo 'nav-tab-active';} ?>"><?php _e('Get Pro Version', 'srfw-get_pro'); ?></a>

	</h2>