(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 * $( document ).ready(function() same as
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	 
	 //document ready
	$(function(){
	
		var prestyle = "";
		//color picker
		var myOptions = {
			// a callback to fire whenever the color changes to a valid color
			change: function(event, ui){
				var color = ui.color.toString();
				var element = event.target;
				var curid = $(element).attr('id');
				$( element ).val(color)
				//manuall change after css. hack since jquery can't access before and after elements    border-top: 30px solid #943939;
				if(curid=='srfw_template_misc_bgcolor1'){
					prestyle = "<style>.srfw_t1_DIV_2::after{ border-top: 30px solid "+color+"; }</style>";
				}
				changepreviewhtml();
			},
			// a callback to fire when the input is emptied or an invalid color
			clear: function() {}
		};
		 
		$('.my-color-field').wpColorPicker(myOptions);
		

		
		//for style preview changes.-------------
		var starhtml = '<span class="srfw_star_imgs"><img src="' + adminjs_script_vars.pluginsUrl + '/public/partials/imgs/stars_5_yellow.png" alt="" >&nbsp;&nbsp;</span>';
		var sampltext = 'This is a sample review. Great Product! My kids loved it.';
		var datehtml = '<span id="wprev_showdate">1/12/2017</span>';
		var imagehref = adminjs_script_vars.pluginsUrl + '/admin/partials/sample_avatar.png';
		var avatarimg = imagehref;
		
		var style1html ='<div class="srfw_t1_outer_div w3_wprs-row-padding">	\
							<div class="srfw_t1_DIV_1 w3_wprs-col">	\
								<div class="srfw_t1_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
									<p class="srfw_t1_P_3 wprev_preview_tcolor1">	\
										'+starhtml+''+sampltext+'		</p>	\
										<div class="miscpicdiv mpdiv_t wprev_preview_tcolor1_T"><div class="mscpic-img"><div class="mscpic-img-body"><img loading="lazy" src="' + adminjs_script_vars.pluginsUrl + '/public/partials/imgs/sampleproduct.jpeg" class="miscpic-listing-image rounded" height="auto" title="Star Wars  Millenium Falcon,  1/72   Model" alt="Star Wars  Millenium Falcon,  1/72   Model Image"></div></div><div class="mscpic-body"><span>Star Wars  Millenium Falcon,  1/72   Model</span></div></div>	\
								</div><span class="srfw_t1_A_8"><img src="'+avatarimg+'" alt="thumb" class="srfw_t1_IMG_4"></span> <span class="srfw_t1_SPAN_5 wprev_preview_tcolor2">Josh W.<br>'+datehtml+' </span>	\
							</div>	\
							</div>';
		
		changepreviewhtml();
		
		//reset colors to default
		$( "#srfw_pre_resetbtn" ).click(function() {
			resetcolors();
		});
		function resetcolors(){
				var templatenum = $( "#srfw_template_style" ).val();
				//reset colors to default
				if(templatenum=='1'){
					
					$( "#srfw_template_misc_bradius" ).val('0');
					$( "#srfw_template_misc_bgcolor1" ).val('#ffffff');
					$( "#srfw_template_misc_bgcolor2" ).val('#ffffff');
					$( "#srfw_template_misc_tcolor1" ).val('#777777');
					$( "#srfw_template_misc_tcolor2" ).val('#555555');
					prestyle="";
					//reset color picker
					$('#srfw_template_misc_bgcolor1').iris('color', '#ffffff');
					$('#srfw_template_misc_bgcolor2').iris('color', '#ffffff');
					$( "#srfw_template_misc_tcolor1" ).iris('color','#777777');
					$( "#srfw_template_misc_tcolor2" ).iris('color','#555555');
				}
		}

		
		//on template num change
		$( "#srfw_template_style" ).change(function() {
				//reset colors if not editing, otherwise leave alone
				if($( "#edittid" ).val()==""){
				resetcolors();
				}
				changepreviewhtml();
		});
		
		$( "#srfw_template_misc_showstars" ).change(function() {
				changepreviewhtml();
		});
		$( "#srfw_template_misc_showdate" ).change(function() {
				changepreviewhtml();
		});
		$( "#srfw_template_misc_showicon" ).change(function() {
				changepreviewhtml();
		});
		$( "#srfw_template_misc_bradius" ).change(function() {
				changepreviewhtml();
		});
		$( "#srfw_template_misc_bgcolor1" ).change(function() {
				changepreviewhtml();
		});
		$( "#srfw_template_misc_tcolor1" ).change(function() {
				changepreviewhtml();
		});
		//custom css change preview
		var lastValue = '';
		$("#srfw_template_css").on('change keyup paste mouseup', function() {
			if ($(this).val() != lastValue) {
				lastValue = $(this).val();
				changepreviewhtml();
			}
		});
		
		function changepreviewhtml(){
			var templatenum = $( "#srfw_template_style" ).val();
			var bradius = $( "#srfw_template_misc_bradius" ).val();
			var bg1 = $( "#srfw_template_misc_bgcolor1" ).val();
			var bg2 = $( "#srfw_template_misc_bgcolor2" ).val();
			var tcolor1 = $( "#srfw_template_misc_tcolor1" ).val();
			var tcolor2 = $( "#srfw_template_misc_tcolor2" ).val();
			var tcolor3 = $( "#srfw_template_misc_tcolor3" ).val();
			
			if($( "#srfw_template_css" ).val()!=""){
				var customcss = '<style>'+$( "#srfw_template_css" ).val()+'</style>';
				prestyle =  prestyle + customcss;
			}
			
				var temphtml;
				if(templatenum=='1'){
					$( "#srfw_template_preview" ).html(prestyle+style1html);
					//hide background 2 select
					$( ".wprevpre_bgcolor2" ).hide();
					$( ".wprevpre_tcolor3" ).hide();
				}
			//now hide and show things based on values in select boxes
			if($( "#srfw_template_misc_showstars" ).val()=="no"){
				$( ".srfw_star_imgs" ).hide();
			} else {
				$( ".srfw_star_imgs" ).show();
			}
			if($( "#srfw_template_misc_showdate" ).val()=="no"){
				$( "#wprev_showdate" ).hide();
			} else {
				$( "#wprev_showdate" ).show();
			}
			if($( "#srfw_template_misc_showicon" ).val()=="no"){
				$( "#wprev_showicon" ).hide();
			} else {
				$( "#wprev_showicon" ).show();
			}
			//set colors and bradius by changing css via jQuery     border-radius: 10px 10px 10px 10px;
			$( '.wprev_preview_bradius' ).css( "border-radius", bradius+'px' );
			$( '.wprev_preview_bg1' ).css( "background", bg1 );
			$( '.wprev_preview_bg2' ).css( "background", bg2 );
			$( '.wprev_preview_tcolor1' ).css( "color", tcolor1 );
			$( '.wprev_preview_tcolor2' ).css( "color", tcolor2 );
		}
		
		
	
	
		
		//help button clicked
		$( "#srfw_helpicon_posts" ).click(function() {
		  openpopup("Tips", '<p>This page will let you create multiple Reviews Templates that you can then add to your Posts or Pages via a shortcode or template function.</p>', "");
		});
		//display shortcode button click srfw_addnewtemplate
		$( ".srfw_displayshortcode" ).click(function() {
			//get id and template type
			var tid = $( this ).parent().attr( "data-templateid" );
			var ttype = $( this ).parent().attr( "data-templatetype" );
			
		  if(ttype=="widget"){
			openpopup("Widget Instructions", '<p>To display this in your Sidebar or other Widget areas, add the WP Reviews widget under Appearance > Widgets, and then select this template in the drop down.</p>', '');
		  } else {
			openpopup("How to Display", '<p>Enter this shortcode on a post or page: </br></br>[srfw_usetemplate tid="'+tid+'"]</p><p>Or you can add the following php code to your template: </br></br><code> do_action( \'wprev_pro_plugin_action\', '+tid+' ); </code></p>', '');
		  }
		  
		});
		
		
		//launch pop-up windows code--------
		function openpopup(title, body, body2){

			//set text
			jQuery( "#popup_titletext").html(title);
			jQuery( "#popup_bobytext1").html(body);
			jQuery( "#popup_bobytext2").html(body2);
			
			var popup = jQuery('#popup_review_list').popup({
				width: 400,
				offsetX: -100,
				offsetY: 0,
			});
			
			popup.open();
			//set height
			var bodyheight = Number(jQuery( ".popup-content").height()) + 10;
			jQuery( "#popup_review_list").height(bodyheight);

		}
		//--------------------------------
		//get the url parameter-----------
		function getParameterByName(name, url) {
			if (!url) {
			  url = window.location.href;
			}
			name = name.replace(/[\[\]]/g, "\\$&");
			var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
				results = regex.exec(url);
			if (!results) return null;
			if (!results[2]) return '';
			return decodeURIComponent(results[2].replace(/\+/g, " "));
		}
		//---------------------------------
		
		//hide or show new template form ----------
		var checkedittemplate = getParameterByName('taction'); // "lorem"
		if(checkedittemplate=="edit"){
			jQuery("#srfw_new_template").show("slow");
			checkwidgetradio();
		} else {
			jQuery("#srfw_new_template").hide();
		}
		
		$( "#srfw_addnewtemplate" ).click(function() {
		  jQuery("#srfw_new_template").show("slow");
		});	
		$( "#srfw_addnewtemplate_cancel" ).click(function() {
		  jQuery("#srfw_new_template").hide("slow");
		  //reload page without taction and tid
		  setTimeout(function(){ 
			window.location.href = "?page=srfw-templates_posts"; 
		  }, 500);
		  
		});	
		
		//-------------------------------
		
		//form validation
		$("#newtemplateform").submit(function(){   
			if(jQuery( "#srfw_template_title").val()==""){
				alert("Please enter a title.");
				$( "#srfw_template_title" ).focus();
				return false;
			} else if(jQuery( "#srfw_t_display_num_total").val()<1){
				alert("Please enter a 1 or greater.");
				$( "#srfw_t_display_num_total" ).focus();
				return false;
			} else {
			return true;
			}

		});
		
		//widget radio clicked
		$('input[type=radio][name=srfw_template_type]').change(function() {
			checkwidgetradio();
		});
		
		//check widget radio----------------------
		function checkwidgetradio() {
			var widgetvalue = $("input[name=srfw_template_type]:checked").val();
			if (widgetvalue == 'widget') {
				//change how many per a row to 1
				$('#srfw_t_display_num').val("1");
				$('#srfw_t_display_num').hide();
				$('#srfw_t_display_num').prev().hide();
				//force hide arrows and do not allow horizontal scroll on slideshow
				//$('input:radio[name=srfw_sliderdirection]').val(['vertical']);
				//$('input[id=srfw_sliderdirection1-radio]').attr("disabled",true);
				$('input:radio[name=srfw_sliderarrows]').val(['no']);
				$('input[id=srfw_sliderarrows1-radio]').attr("disabled",true);
			}
			else if (widgetvalue == 'post') {
				//alert("post type");
				if($('#edittid').val()==""){
				$('#srfw_t_display_num').val("3");
				}
				$('#srfw_t_display_num').show();
				$('#srfw_t_display_num').prev().show();
				$('input[id=srfw_sliderdirection1-radio]').attr("disabled",false);
				$('input[id=srfw_sliderarrows1-radio]').attr("disabled",false);
			}
		}
		
	});

})( jQuery );