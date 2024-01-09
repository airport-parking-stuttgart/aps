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

 	//db function variables
	global $wpdb;
	$table_name = $wpdb->prefix . 'srfw_post_templates';
	
 //use the template id to find template in db, echo error if we can't find it or just don't display anything
 	//Get the form--------------------------
	$tidintval = intval($a['tid']);
	$currentform = $wpdb->get_results("SELECT * FROM $table_name WHERE id = ".$tidintval);
	//template misc stuff
		$template_misc_array = json_decode($currentform[0]->template_misc, true);
	//print_r($currentform);
	
	//check to make sure template found
	if(isset($currentform[0])){
		
		//use values from currentform to get reviews from db
		$table_name = $wpdb->prefix . 'srfw_reviews';
		
		if($currentform[0]->hide_no_text=="yes"){
			$min_words = 2;
			$max_words = 5000;
		} else {
			$min_words = 0;
			$max_words = 5000;
		}
		
		if($currentform[0]->display_order=="random"){
			$sorttable = "RAND() ";
			$sortdir = "";
		} else {
			$sorttable = "created_time_stamp ";
			$sortdir = "DESC";
		}

		$reviewsperpage= $currentform[0]->display_num*$currentform[0]->display_num_rows;
		$tablelimit = $reviewsperpage;
		//change limit for slider
		if($currentform[0]->createslider == "yes"){
			$tablelimit = $tablelimit*$currentform[0]->numslides;
		}
		
		//----------------------
		//pro filter settings 	min_words, max_words, min_rating, rtype, rpage, showreviewsbyid========
		if($currentform[0]->min_words>0){
			$min_words = intval($currentform[0]->min_words);
		}
		if($currentform[0]->max_words>0){
			$max_words = intval($currentform[0]->max_words);
		}
		
		//min_rating filter----
		if($currentform[0]->min_rating>0){
			$min_rating = intval($currentform[0]->min_rating);
		} else {
			$min_rating ="";
		}
		
		//rtype filter-----
		$rtypefilter = "";

		//rpage filter-----
		$rpagefilter = "";
		if($currentform[0]->rpage!=""){
			$rpagearray = json_decode($currentform[0]->rpage);
			if(is_array($rpagearray)){
			$rpagearray = array_filter($rpagearray);
			$rpagearray = array_values($rpagearray);
			if(count($rpagearray)>0){
				for ($x = 0; $x < count($rpagearray); $x++) {
					
					if($x==0){
						$rpagefilter = "AND (pageid = '".$rpagearray[$x]."'";
					} else {
						$rpagefilter = $rpagefilter." OR pageid = '".$rpagearray[$x]."'";
					}
				}
				$rpagefilter = $rpagefilter.")";
			}
			}
		}
		
		//showreviewsbyid filter---------replaces all other filters
		$onlyselected = false;
		if($currentform[0]->showreviewsbyid!=""){
			$showreviewsbyidarray = json_decode($currentform[0]->showreviewsbyid);
			$showreviewsbyidarray = array_filter($showreviewsbyidarray);
			$showreviewsbyidarray = array_values($showreviewsbyidarray);
			if(count($showreviewsbyidarray)>0){
				$onlyselected = true;
			}
		}
				//rpostid filter-----
		$rpostidfilter = "";
		//echo "pfilter:".$template_misc_array['postfilter'];
		//echo "<br>";
		if(!isset($template_misc_array['postfilter'])){
			$template_misc_array['postfilter']='no';
		}
		//print_r($template_misc_array);
		if($template_misc_array['postfilter']=='yes'){
			//first grab current post id from this page, doesn't work on load more, need to pass through ajax.
			//check if passed first

				$rpostidarray[] = get_the_ID();
			
			//echo "post id:".get_the_ID();
			
			if(is_array($rpostidarray)){
				$rpostidarray = array_filter($rpostidarray);
				$rpostidarray = array_values($rpostidarray);
				if(count($rpostidarray)>0){
					for ($x = 0; $x < count($rpostidarray); $x++) {
						if($x==0){
							$rpostidfilter = "AND (posts LIKE '%-".$rpostidarray[$x]."-%'";
						} else {
							$rpostidfilter = $rpostidfilter." OR posts LIKE '%-".$rpostidarray[$x]."-%'";
						}
					}
					$rpostidfilter = $rpostidfilter.")";
				}
			}
		}
		//echo "rpostidfilter:".$rpostidfilter;
		//echo "<br>";
		
		if($onlyselected){
			$query = "SELECT * FROM ".$table_name." WHERE id IN (";
			//loop array and add to query
			$n=1;
			foreach ($showreviewsbyidarray as $value) {
				if($value!=""){
					if(count($showreviewsbyidarray)==$n){
						$query = $query." $value";
					} else {
						$query = $query." $value,";
					}
				}
				$n++;
			}
			$query = $query.")";
			$totalreviews = $wpdb->get_results($query);
		} else {
			$totalreviews = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM ".$table_name."
				WHERE id>%d AND review_length >= %d AND review_length <= %d AND rating >= %d ".$rtypefilter." ".$rpagefilter." ".$rpostidfilter." 
				ORDER BY ".$sorttable." ".$sortdir." 
				LIMIT ".$tablelimit." ", "0","$min_words","$max_words","$min_rating")
			);
		}

		//echo $wpdb->last_query ;
			
			//print_r($totalreviews);
			//echo "<br><br>";
			
	//only continue if some reviews found
	$makingslideshow=false;
	if(count($totalreviews)>0){

		//if creating a slider than we need to split into chunks for each slider
		//if($currentform[0]->createslider == "yes"){
			//print_r(array_chunk($totalreviews, $reviewsperpage));
			$totalreviewschunked = array_chunk($totalreviews, $reviewsperpage);
		//}
		//loop through each chunk
		
		//if making slide show then add it here

		if($currentform[0]->createslider == "yes"){
			//make sure we have enough to create a show here
			if($totalreviews>$reviewsperpage){
				$makingslideshow = true;
				echo '<div class="srfw-slider" id="srfw-slider-'.esc_attr($currentform[0]->id).'"><ul>';
			}
		} else {
			echo '<div class="srfw-no-slider" id="srfw-slider-'.esc_attr($currentform[0]->id).'"><ul>';
		}
		
		foreach ( $totalreviewschunked as $reviewschunked ){
			$totalreviewstemp = $reviewschunked;
			
			//need to break $totalreviewstemp up based on how many rows, create an multi array containing them
			if($currentform[0]->display_num_rows>1 && count($totalreviewstemp)>$currentform[0]->display_num){
				//count of reviews total is greater than display per row then we need to break in to multiple rows
				for ($row = 0; $row < $currentform[0]->display_num_rows; $row++) {
					$n=1;
					foreach ( $totalreviewstemp as $tempreview ){
						if($n>($row*$currentform[0]->display_num) && $n<=(($row+1)*$currentform[0]->display_num)){
							$rowarray[$row][$n]=$tempreview;
						}
						$n++;
					}
				}
			} else {
				//everything on one row so just put in multi array
				$rowarray[0]=$totalreviewstemp;
			}
			

			//add styles from template misc here
			$template_misc_array = json_decode($currentform[0]->template_misc, true);
			if(is_array($template_misc_array)){
				$misc_style ="";
				//hide stars and/or date
				if($template_misc_array['showstars']=="no"){
					$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprevpro_star_imgs_T'.$currentform[0]->style.' {display: none;}';
				}
				if($template_misc_array['showdate']=="no"){
					$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprev_showdate_T'.$currentform[0]->style.' {display: none;}';
				}
				
				
				$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprev_preview_bradius_T'.$currentform[0]->style.' {border-radius: '.$template_misc_array['bradius'].'px;}';
				$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprev_preview_bg1_T'.$currentform[0]->style.' {background:'.$template_misc_array['bgcolor1'].';}';
				$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprev_preview_bg2_T'.$currentform[0]->style.' {background:'.$template_misc_array['bgcolor2'].';}';
				$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprev_preview_tcolor1_T'.$currentform[0]->style.' {color:'.$template_misc_array['tcolor1'].';}';
				$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprev_preview_tcolor2_T'.$currentform[0]->style.' {color:'.$template_misc_array['tcolor2'].';}';
				
				//style specific mods 	div > p
				if($currentform[0]->style=="1"){
					$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprev_preview_bg1_T'.$currentform[0]->style.'::after{ border-top: 30px solid '.$template_misc_array['bgcolor1'].'; }';
				}
				if($currentform[0]->style=="2"){
					$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprev_preview_bg1_T'.$currentform[0]->style.' {border-bottom:3px solid '.$template_misc_array['bgcolor2'].'}';
				}
				if($currentform[0]->style=="3"){
					$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprev_preview_tcolor3_T'.$currentform[0]->style.' {text-shadow:'.$template_misc_array['tcolor3'].' 1px 1px 0px;}';
				}
				if($currentform[0]->style=="4"){
					$misc_style = $misc_style . '#srfw-slider-'.$currentform[0]->id.' .wprev_preview_tcolor3_T'.$currentform[0]->style.' {color:'.$template_misc_array['tcolor3'].';}';
				}
		
				echo "<style>".wp_kses_post($misc_style)."</style>";
			}
			
			//print out user style added
			echo "<style>".wp_kses_post($currentform[0]->template_css)."</style>";
			 
			//if making slide show
			if($makingslideshow){
					echo '<li>';
			}
		 
				//include the correct tid here
				if($currentform[0]->style=="1" || $currentform[0]->style=="2" || $currentform[0]->style=="3" || $currentform[0]->style=="4" || $currentform[0]->style=="5" || $currentform[0]->style=="6" || $currentform[0]->style=="7" || $currentform[0]->style=="8" || $currentform[0]->style=="9" || $currentform[0]->style=="10" ){
				$iswidget=false;
					include(plugin_dir_path( __FILE__ ) . 'template_style_'.$currentform[0]->style.'.php');
				}
			
			//if making slide show then end loop here
			if($makingslideshow){
					echo '</li>';
			}
		
		}	//end loop chunks
		//if making slide show then end it
		if($makingslideshow){
			//grab db values
			if($currentform[0]->sliderautoplay!="" && $currentform[0]->sliderautoplay=='yes'){
				$autoplay = 'true';
			} else {
				$autoplay = 'false';
			}
			if($currentform[0]->sliderdirection=='vertical' || $currentform[0]->sliderdirection=='horizontal' || $currentform[0]->sliderdirection=='fade'){
				$animation = $currentform[0]->sliderdirection;
			} else {
				$animation = 'horizontal';
			}
			if($currentform[0]->sliderarrows!="" && $currentform[0]->sliderarrows=='no'){
				$arrows = 'false';
			} else {
				$arrows = 'true';
			}
			if($currentform[0]->sliderdots!="" && $currentform[0]->sliderdots=='no'){
				$slidedots = '$("#srfw-slider-'.esc_html($currentform[0]->id).'").siblings(".wprs_unslider-nav").hide();';
			} else {
				$slidedots = '$("#srfw-slider-'.esc_html($currentform[0]->id).'").siblings(".wprs_unslider-nav").show();';
			}

			if($currentform[0]->sliderdelay!="" && intval($currentform[0]->sliderdelay)>0){
				$delay = intval($currentform[0]->sliderdelay)*1000;
			} else {
				$delay = "5000";
			}
			
			//if($currentform[0]->sliderheight=="" || $currentform[0]->sliderheight!='yes'){
			//	$animateHeight = 'false';
			//} else {
				$animateHeight = 'true';
			//}
			
				echo '</ul></div>';
				echo "<script type='text/javascript' defer>
						function wprs_defer_trip(method) {
							if (window.jQuery) {
								method();
							} else {
								setTimeout(function() { wprs_defer_trip(method) }, 50);
							}
						}
						wprs_defer_trip(function () {
							jQuery(document).ready(function($) {
								$('#srfw-slider-".esc_html($currentform[0]->id)."').wprs_unslider(
									{
									autoplay:".esc_html($autoplay).",
									delay: '".esc_html($delay)."',
									animation: '".esc_html($animation)."',
									arrows: ".esc_html($arrows).",
									animateHeight: ".esc_html($animateHeight).",
									activeClass: 'wprs_unslider-active',
									}
								);
								".$slidedots."
							});
						});
						</script>";
		} else {
		echo '</div>';
		}
	 
	}
}
?>

