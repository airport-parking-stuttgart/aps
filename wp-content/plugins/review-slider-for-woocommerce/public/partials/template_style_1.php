<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Summary_Reviews
 * @subpackage Summary_Reviews/public/partials
 */
 //html code for the template style
$plugin_dir = WP_PLUGIN_DIR;
$imgs_url = esc_url( plugins_url( 'imgs/', __FILE__ ) );

//loop if more than one row
for ($x = 0; $x < count($rowarray); $x++) {
	if(	$currentform[0]->template_type=="widget"){
		?>
		<div class="srfw_t1_outer_div_widget w3_wprs-row-padding-small">
		<?php
		} else {
		?>
		<div class="srfw_t1_outer_div w3_wprs-row-padding">
		<?php
	}
	//loop 
	foreach ( $rowarray[$x] as $review ) 
	{

		$userpic = $review->userpic;
		
		//star number 
		
		$reviewtext = "";
		if($review->review_text !=""){
			$reviewtext = $review->review_text;
			$reviewtext = nl2br($reviewtext);
		}
		//if read more is turned on then divide then add read more span links
		if(!isset($currentform[0]->read_more_text)){
			$currentform[0]->read_more_text ='';
		}
		if($currentform[0]->read_more_text==''){
			$currentform[0]->read_more_text = 'read more';
		}
		if(	$currentform[0]->read_more=="yes"){
			$readmorenum = 20;
			$countwords = str_word_count($reviewtext);
			
			if($countwords>$readmorenum){
				//split in to array
				$pieces = explode(" ", $reviewtext);
				//slice the array in to two
				$part1 = array_slice($pieces, 0, $readmorenum);
				$part2 = array_slice($pieces, $readmorenum);
				$reviewtext = implode(" ",$part1)."<a class='wprs_rd_more'>... ".esc_html($currentform[0]->read_more_text)."</a><span class='wprs_rd_more_text' style='display:none;'> ".implode(" ",$part2)."</span>";
			}
		}

		//per a row
		if($currentform[0]->display_num>0){
			$perrow = 12/$currentform[0]->display_num;
		} else {
			$perrow = 4;
		}
		
		//date
		if($review->created_time_stamp=='' || $review->created_time_stamp<1){
			$temptime = esc_html($review->created_time);
			$review->created_time_stamp = strtotime($temptime );
		} 
		$tempdate = date_i18n( get_option('date_format'), $review->created_time_stamp );
		
		//star alt tag
		$staralt = "".$review->rating." Star Review";
		
		if(!isset($currentform[0]->style)){
			$currentform[0]->style='';
		}
		
		//add product image and title for WooCommerce here, use later for instagram/twitter
		$miscpicimagehtml ="";
		$title = strip_tags($review->pagename);
		if($review->type =="WooCommerce"){
			$miscpicsrc = "";
			
			if($review->miscpic!=''){
				$miscpicsrc ='<img src="'.$review->miscpic.'" class="miscpic-listing-image rounded" height="auto" title="'.$title.'" alt="'.$title.' Image">';
			}
			$miscpicimagehtml = "<div class='miscpicdiv mpdiv_t".$currentform[0]->style." wprev_preview_tcolor1_T".$currentform[0]->style."'><div class='mscpic-img'><div class='mscpic-img-body'>".$miscpicsrc."</div></div><div class='mscpic-body'><span>".$title."</span></div></div>";
		}
		//add product link if set
		$linkstart="";
		$linkend="";
		if($review->from_url !="" && $miscpicimagehtml!=''){
			$linkstart='<a href="'.$review->from_url.'" class="miscpiclink" title="'.$title.'">';
			$linkend="</a>";
		}
		
		$miscpichtml= $linkstart.$miscpicimagehtml.$linkend;
		$starfile = "stars_".$review->rating."_yellow.png";
		
		//star yes or no showstars
		if($template_misc_array['showstars']=="no"){
			$starhtml = '';
		} else {
			$starhtml = '<img src="'.$imgs_url."".$starfile.'" alt="'.$staralt.'" class="srfw_t1_star_img_file" loading="lazy">&nbsp;&nbsp;';

		}
		
	?>
		<div class="srfw_t1_DIV_1<?php if(	$currentform[0]->template_type=="widget"){echo ' marginb10';}?> w3_wprs-col l<?php echo esc_attr($perrow); ?>">
			<div class="srfw_t1_DIV_2 wprev_preview_bg1_T<?php echo esc_attr($currentform[0]->style); ?><?php if($iswidget){echo "_widget";} ?> wprev_preview_bradius_T<?php echo esc_attr($currentform[0]->style); ?><?php if($iswidget){echo "_widget";} ?>">
				<p class="srfw_t1_P_3 wprev_preview_tcolor1_T<?php echo esc_attr($currentform[0]->style); ?><?php if($iswidget){echo "_widget";} ?>">
					<span class="srfw_star_imgs_T<?php echo esc_attr($currentform[0]->style); ?><?php if($iswidget){echo "_widget";} ?>"><?php echo wp_kses_post($starhtml); ?></span><?php echo wp_kses_post($reviewtext); ?>
				</p>
				<?php echo wp_kses_post($miscpichtml); ?>
			</div><span class="srfw_t1_A_8"><img src="<?php echo esc_url($userpic); ?>" alt="thumb" class="srfw_t1_IMG_4" loading="lazy" /></span><span class="srfw_t1_SPAN_5 wprev_preview_tcolor2_T<?php echo esc_attr($currentform[0]->style); ?><?php if($iswidget){echo "_widget";} ?>"><?php echo esc_html($review->reviewer_name); ?><br/><span class="wprev_showdate_T<?php echo esc_attr($currentform[0]->style); ?><?php if($iswidget){echo "_widget";} ?>"><?php echo esc_html($tempdate); ?></span> </span>
		</div>
	<?php
	}
	//end loop
	?>
	</div>
<?php
}
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
