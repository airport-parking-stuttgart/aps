jQuery(document).ready(function($){
	jQuery(".woocommerce-tabs").before(jQuery(".itweb_content").show());
	var url = jQuery(".itweb_url").val();
	var proid = jQuery(".product_id").val();
	var bookedColor = jQuery(".itweb_type_booked").val();

	// var width_config = jQuery(".width_config").val();
	// var height_config = jQuery(".height_config").val();
	// var current =  jQuery(".itweb_map_bg").width();
	// var ptw = 100 / width_config * current;
	// var newbh = height_config / 100 * ptw;
	// jQuery(".itweb_map_bg").css("height", newbh+"px");
	//
	// jQuery(".itweb_map_slot").each(function(){
	// 	var elthis = jQuery(this);
	// 	var thisstyle = elthis.attr("style");
	// 	var thisstyle = thisstyle.split(";");
	//
	// 	var thisw = elthis.width();
	// 	var neww = thisw / 100 * ptw;
	//
	// 	var thish = elthis.height();
	// 	var newh = thish / 100 * ptw;
	//
	// 	var thisl = thisstyle[4].split(":");
	// 	var thisl = thisl[1].replace("px", "").trim();
	// 	var newl = thisl / 100 * ptw;
	//
	// 	var thist = thisstyle[5].split(":");
	// 	var thist = thist[1].replace("px", "").trim();
	// 	var newt = thist / 100 * ptw;
	//
	// 	elthis.css({
	// 		"width": neww+"px",
	// 		"height": newh+"px",
	// 		"line-height": newh+"px",
	// 		"left": newl+"px",
	// 		"top": newt+"px"
	// 	});
	// });

	jQuery('.itweb_date_from_input').datetimepicker({
		format: 'd-m-Y H:i',
		step: 15,
		onSelectTime:function(ct, $i){
			checkSchedule($i[0].value);
		},
		onSelectDate:function(ct,$i){
			checkSchedule($i[0].value);
			//var datetime = $i[0].value.split(" ");
			//jQuery('.itweb_date_from_input').val(datetime[0]);
		}
	});
	jQuery('.itweb_date_to_input').datetimepicker({
		format: 'd-m-Y H:i',
		step: 15,
		onSelectTime:function(ct, $i){
			//checkSchedule($i[0].value);
		},
		onSelectDate:function(ct,$i){
			//checkSchedule($i[0].value);
		}
	});
	
	////////////
	function checkSchedule(schedule){
		jQuery.ajax({
			type: "POST",
			url: url+"Helper.php",
			data:{
				task: "check_schedule",
				schedule: schedule,
				proid: proid
			},
			beforeSend : function(data){
				jQuery(".itweb_map").css("opacity", "0.5");
			},
			success : function(data){
				jQuery(".itweb_map").css("opacity", "1");
				
				jQuery(".seatbooked").each(function(){
					jQuery(this).removeClass("seatbooked");
					var readcolor = jQuery(this).children(".itweb_map_slot_readcolor").val();
					jQuery(this).css("background", readcolor);
				});
				
				if(data.length > 0){
					jQuery.each(data, function(key, val){
						jQuery(".itweb_map_slot[objectdata='slot"+val+"']").css("background", bookedColor).addClass("seatbooked");
					});
				}
			},
			dataType: 'json'
		});
	}
	
	
	////////
	jQuery(".itweb_map_slot").each(function(){
		var slot = jQuery(this);
		
		slot.click(function(){
			if(!slot.hasClass("seatbooked")){
				if(jQuery(".itweb_date_from_input").val()){
					if(slot.hasClass("active"))
						slot.removeClass("active");
					else
						slot.addClass("active");
					
					var seats = "";
					jQuery(".itweb_map_slot.active").each(function(){
						if(seats)
							seats += "@"+jQuery(this).children(".itweb_map_slot_label").text().trim();
						else
							seats += jQuery(this).children(".itweb_map_slot_label").text().trim();
					});
					var datefrom = jQuery(".itweb_date_from_input").val();
					var dateto = jQuery(".itweb_date_to_input").val();
					
					jQuery.ajax({
						type: "POST",
						url: url+"Helper.php",
						data:{
							task: "check_seats",
							seats: seats,
							datefrom: datefrom,
							dateto: dateto,
							proid: proid
						},
						beforeSend : function(data){
							jQuery(".itweb_map").css("opacity", "0.5");
						},
						success : function(data){
							jQuery(".itweb_map").css("opacity", "1");
							
						}
					});
				}else
					alert("Please choose date first!");
			}
		});
	});


	// custom book function
	$('#bookParkings').on('click', function(){
		var datefrom = $(".itweb_date_from_input").val();
		var dateto = $(".itweb_date_to_input").val();
		if($(".itweb_date_from_input").val()){
			jQuery.ajax({
				type: "POST",
				url: url+"Helper.php",
				data:{
					task: "check_seats",
					// seats: seats,
					datefrom: datefrom,
					dateto: dateto,
					proid: proid
				},
				// beforeSend : function(data){
				// 	$(".itweb_map").css("opacity", "0.5");
				// },
				// success : function(data){
				// 	$(".itweb_map").css("opacity", "1");
				// }
			});
		}else
			alert("Please choose date first!");
	});
});