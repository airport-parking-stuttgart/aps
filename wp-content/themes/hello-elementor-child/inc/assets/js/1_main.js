jQuery(document).ready(function ($) {
    var helperUrl = "/wp-content/plugins/itweb-booking/classes/Helper.php";
    // On window load show preloader
    $('.preloader-wrapper').fadeOut();

    // add placeholder from label to billing fields
    $('.woocommerce-billing-fields .form-row').each(function(){
        var label = $(this).find('label').text();
        $(this).find('input').attr('placeholder', label);
    });

    //Input datepicker
    $(function () {
        //Init Slick Slider
        $('.customers-slider').slick({
            arrows: true,
            prevArrow: '<span class="arrow-wrap arrow-prev"></span>',
            nextArrow: '<span class="arrow-wrap arrow-next"></span>',
        });

        // Sticky menu on window scroll
        $(window).scroll(function () {
            var header = $('#masthead'),
                scroll = $(window).scrollTop();

            if (scroll >= 1) {
                header.addClass('fixed-header');
            } else {
                header.removeClass('fixed-header');
            }
        });


    });

    /**
     * init tooltips
     */
    $('[data-toggle="tooltip"]').tooltip()

    $('.slider-single').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: false,
        adaptiveHeight: true,
        infinite: false,
        useTransform: true,
        speed: 400,
        cssEase: 'cubic-bezier(0.77, 0, 0.18, 1)',
    });

    $('.slider-nav')
        .on('init', function (event, slick) {
            $('.slider-nav .slick-slide.slick-current').addClass('is-active');
        })
        .slick({
            slidesToShow: 7,
            slidesToScroll: 7,
            dots: false,
            focusOnSelect: false,
            infinite: false,
            responsive: [{
                breakpoint: 1024,
                settings: {
                    slidesToShow: 5,
                    slidesToScroll: 5,
                }
            }, {
                breakpoint: 640,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 4,
                }
            }, {
                breakpoint: 420,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                }
            }]
        });
		
	$('#form-field-home_datefrom').change(function() {
		//$('#form-field-home_dateto').val($('#form-field-home_datefrom').val());
		var d = $('#form-field-home_datefrom').val();
		var dt = d.split(".");
		var dateString = dt[2]+"-"+dt[1]+"-"+dt[0];
		var startDate = new Date(dateString);
		var day = 60 * 60 * 24 * 1000;
		var endDate = new Date(startDate.getTime() + day);			
		$("#form-field-home_dateto").flatpickr({
			minDate: endDate,
			dateFormat: "d.m.Y"
		});
	});
		

    $('.slider-single').on('afterChange', function (event, slick, currentSlide) {
        $('.slider-nav').slick('slickGoTo', currentSlide);
        var currrentNavSlideElem = '.slider-nav .slick-slide[data-slick-index="' + currentSlide + '"]';
        $('.slider-nav .slick-slide.is-active').removeClass('is-active');
        $(currrentNavSlideElem).addClass('is-active');
    });

    $('.slider-nav').on('click', '.slick-slide', function (event) {
        event.preventDefault();
        var goToSingleSlide = $(this).data('slick-index');

        $('.slider-single').slick('slickGoTo', goToSingleSlide);
    });

    $datepickers = $('.single-datepicker');
    $datepickers.attr('autocomplete', 'off').attr('data-language', 'de');
    var dtOptions = {
        format: 'd.m.Y',
        timepicker: false,
        minDate: new Date(),
        onSelect: function (formattedDate, date, inst) {
            if (inst.$el.attr('name') === 'datefrom') {
                $endDate = $('input[name="dateto"]');
                var tmpOptions = {...dtOptions, minDate: date}
                $endDate.datepicker(tmpOptions);
            }
        }
    };
    $datepickers.each(function () {
        if ($(this).hasClass('datetimepicker')) {
            dtOptions['timepicker'] = true;
        }
        $(this).datepicker(dtOptions);
        if ($(this).data('value')) {
            $(this).datepicker().data('datepicker').selectDate(new Date($(this).data('value')));
        }
    });

    $timepickers = $('.timepicker input');
    $timepickers.attr('autocomplete', 'off')
    $timepickers.timepicker({
        minuteStep: 15,
        showInputs: false,
        showSeconds: false,
        defaultTime: false,
        showMeridian: false,
        disableFocus: true
    });

    $timepickers.on('focus', function () {
        $('.bootstrap-timepicker-widget').css({
            'display': 'block',
            'top': (parseFloat($('.bootstrap-timepicker-widget').css('top')) + 10) + 'px'
        });
    });
	


    var div,
        n,
        v = document.getElementsByClassName("youtube-player");
    for (n = 0; n < v.length; n++) {
        div = document.createElement("div");
        div.setAttribute("data-id", v[n].dataset.id);
        div.innerHTML = noThumb(v[n].dataset.id);
        div.onclick = noIframe;
        v[n].appendChild(div);
    }

    function noThumb(id) {
        var thumb = '<img src="https://i.ytimg.com/vi/ID/maxresdefault.jpg">',
            play = '<div class="play"></div>';
        return thumb.replace("ID", id) + play;
    }

    function noIframe() {
        var iframe = document.createElement("iframe");
        var embed =
            "https://www.youtube-nocookie.com/embed/ID?autoplay=1&modestbranding=1&iv_load_policy=3&rel=0&showinfo=0";
        iframe.setAttribute("src", embed.replace("ID", this.dataset.id));
        iframe.setAttribute("frameborder", "0");
        iframe.setAttribute("allowfullscreen", "1");
        iframe.setAttribute("allow", "autoplay; encrypted-media");
        this.parentNode.replaceChild(iframe, this);
    }

    var path = new URL(window.location.href);
    // custom book function
    $('.btn-order-parklot').on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var datefrom = path.searchParams.get('datefrom');
        var dateto = path.searchParams.get('dateto');
        if (_this.hasClass('disabled')) {
            return true;
        }

        _this.addClass('disabled');
        const data = {
            task: "add_to_cart",
            // seats: seats,
            datefrom: datefrom,
            dateto: dateto,
            proid: _this.data('pid'),
            checked_services: $('#chSer').val(),
            shuttle: path.searchParams.get('shuttle'),
            discount: !!_this.data('discount')
        };
        if ($(this).data('type') === 'extern') {
            data.extern = true;
            data.price = _this.closest('.externe-parking').find('.ep-p').val();
            data.name = $(this).data('name');
            data.address = $(this).data('address');
            data.valet = $(this).data('valet');
            data.code = $(this).data('code');

        }
        jQuery.ajax({
            type: "POST",
            url: helperUrl,
            data: data,
            success: function () {
                _this.removeClass('disabled');
                window.location.href = '/checkout';
            }
        });
    });

    //
    $('.filter-lots').on('change', function () {
        $checked = '';
        $(this).closest('form').find('input:checked').each(function () {
            $checked += ($checked == '' ? $(this).val() : ',' + $(this).val());
        });
        path.searchParams.set($(this).attr('name'), $checked);
        window.location.href = path.href;
    });


    // cancel order
    $('.cancel-order').on('click', function (e) {
        e.preventDefault();
        $('#cancelOrderModal').modal();
    });
    $(document).on('submit', '.cancel-order-modal form', function (e) {
        e.preventDefault();
        var form = $(this);
        $token = form.find('#form-field-token');
        $email = form.find('#form-field-email');
        $reason = form.find('#form-field-message');
        $button = form.find('.elementor-field-type-submit button');

        // if ($token.val() === '') {
        //     $token.focus();
        //     return false;
        // }
        $button.attr('disabled', 'disabled');
        $.ajax({
            type: 'GET',
            url: '/wp-json/api/cancel-order?token=' + $token.val()+'&email='+$email.val()+'&reason='+$reason.val(),
            success: function (data) {
                if (data.error) {
                    alert(data.error);
                } else {
                    alert(data.message);
                }
                form.find('#form-field-token, #form-field-message, #form-field-email').val('');
                $button.removeAttr('disabled');
            }
        });
    });

    function send(token, email, reason) {
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/plugins/itweb-parking-booking/helper.php",
            data: {
                task: "sendMail_after_cancel",
                token: token,
                email: email,
                reason: reason
            },
            success: function () {

            }
        });
    }


    // show dropdown menu on hover
    $('#menu-main-menu > li > a').on('mouseenter', function () {
        $('.dropdown-menu').hide();
        $(this).next().show();
    });
    $('*').on('mouseleave', function () {
        if ($(this).closest('#menu-main-menu').length <= 0) {
            $('.dropdown-menu').hide();
        }
    });

    if (path.searchParams.has('cancel-order')) {
        $('#orderToken').val(path.searchParams.get('cancel-order'));
        $('#cancelOrderModal').modal('show');
    }

    $('.google-map-link').find('a').attr('target', '_blank').text('Karte anzeigen');

    // delete hotel transfer ajax
    $('.del-ht').on('click', function (e) {
        e.preventDefault();
        var _this = $(this);
        var table = _this.closest('table');
        if (confirm('Sind Sie sicher, dass Sie diese Bestellung stornieren möchten?')) {
            $.ajax({
                type: 'POST',
                data: {
                    task: 'delete_hotel_transfer',
                    order_id: _this.data('id')
                },
                url: helperUrl,
                success: function () {
                    // _this.closest('tr').remove();
                    // table.dataTable().fnClearTable();
                    window.location.reload();
                }
            });
        }
    });
	
	$(".reg_form_firma").css("display", 'none');
	$(".reg_form_ustid").css("display", 'none');
	$('.reg_form_cb_firma').on('change', function () {

		var cb = $('.reg_form_cb_firma').find('input').attr("id");
		var c = document.getElementById(cb);		
		if (c.checked) {
			$(".reg_form_firma").css("display", 'block');
			$(".reg_form_ustid").css("display", 'block');
		}
		if (!c.checked){
			$(".reg_form_firma").css("display", 'none');
			$(".reg_form_ustid").css("display", 'none');
			$(".reg_form_firma").val("");
			$(".reg_form_ustid").val("");
		}
    });
		

});

// maps code
(function (exports) {
    "use strict";

    function initMap() {
        if (document.getElementById('map')) {
            var directionsRenderer = new google.maps.DirectionsRenderer();
            var directionsService = new google.maps.DirectionsService();
            var map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: {
                    lat: 37.77,
                    lng: -122.447
                }
            });
            directionsRenderer.setMap(map);
            calculateAndDisplayRoute(directionsService, directionsRenderer);
        }
        // call func on calc button click
        //calculateAndDisplayRoute(directionsService, directionsRenderer);
    }

    function calculateAndDisplayRoute(
        directionsService,
        directionsRenderer
    ) {
        // var selectedMode = document.getElementById("mode").value;
        var selectedMode = 'DRIVING';
        directionsService.route(
            {
                origin: {
                    lat: 37.77,
                    lng: -122.447
                },
                // Haight.
                destination: {
                    lat: 37.768,
                    lng: -122.511
                },
                // Ocean Beach.
                // Note that Javascript allows us to access the constant
                // using square brackets and a string value as its
                // "property."
                travelMode: google.maps.TravelMode[selectedMode]
            },
            function (response, status) {
                if (status == "OK") {
                    directionsRenderer.setDirections(response);
                } else {
                    window.alert("Directions request failed due to " + status);
                }
            }
        );
    }

    exports.calculateAndDisplayRoute = calculateAndDisplayRoute;
    exports.initMap = initMap;
})((this.window = this.window || {}));

function enDate(date) {
    var d = date.split('.');
    return d[2] + '/' + d[1] + '/' + d[0];
}

window.addEventListener( "pageshow", function ( event ) {
  var historyTraversal = event.persisted || 
						 ( typeof window.performance != "undefined" && 
							  window.performance.navigation.type === 2 );
  if ( historyTraversal ) {
	// Handle page restore.
	if(window.location.pathname == '/results/')
		window.location.reload();
  }
});