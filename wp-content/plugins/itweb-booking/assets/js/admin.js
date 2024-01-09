if (typeof jQuery !== "function") {
    throw new Error('jQuery is required!');
}
var upload_image_button = false;
jQuery(document).ready(function ($) {
    var path = new URL(window.location.href);
    var helperUrl = '/wp-content/plugins/itweb-booking/classes/Helper.php';

    initDatepickers($);
    initTimepickers($);
    initRangeDatepickers($);

    $singleDatepicker = $('.single-datepicker');
    $singleDatepicker.attr('autocomplete', 'off')
    $singleDatepicker.datepicker({
        language: 'de',
		onSelect: function(formattedDate, date, inst) {
			$(inst.el).trigger('change');
		}
    });

    // orders filter
    $('#order-filter').on('click', function () {
        $row = $(this).closest('.order-filters');
        $token = $row.find('.token').val();
        $datefrom = $row.find('.date_from').val();
        $dateto = $row.find('.date_to').val();

        path.searchParams.set('token', $token);
        path.searchParams.set('date_from', $datefrom);
        path.searchParams.set('date_to', $dateto);
        location.href = path.href;
    })

    // form-filter
    $('.form-filter').on('submit', function (e) {
        e.preventDefault();
        $form = $(this);
        $items = [].slice.call($form.find('.form-item'));
        $items.forEach(function (item) {
            $name = $(item).attr('name');
            if ($(item).val().trim() !== '') {
                path.searchParams.set($name, $(item).val());
            } else {
                path.searchParams.delete($name);
            }
        });
        location.href = path.href;
    });

    // pkw filter
    // $('#pkw-filter').on('click', function(){
    //     $row = $(this).closest('.order-filters');
    //     $token = $row.find('.token').val();
    //     $datefrom = $row.find('.date_from').val();
    //     $dateto = $row.find('.date_to').val();
    //     $kennzeichen = $row.find('.kennzeichen').val();
    //     $lot_nr = $row.find('.lot_nr').val();
    //     $product = $row.find('.product').val();
    //
    //     path.searchParams.set('token', $token);
    //     path.searchParams.set('date_from', $datefrom);
    //     path.searchParams.set('date_to', $dateto);
    //     path.searchParams.set('kennzeichen', $kennzeichen);
    //     path.searchParams.set('lot_nr', $lot_nr);
    //     path.searchParams.set('product', $product);
    //     location.href = path.href;
    // });

	$('#dateFrom').on('change', function () {
		var dateString = $('#dateFrom').val();
		dateSplit = dateString.split(".");
		arrDate = dateSplit[2] + "-" + dateSplit[1] + "-" + dateSplit[0];
		$('#dateTo').datepicker({			
			minDate: new Date(arrDate)
		});
		
		$('#dateTo').val(dateString);
	});
	
	$('#arrivaldateFrom').on('change', function () {
		var dateString = $('#arrivaldateFrom').val();
		dateSplit = dateString.split(".");
		arrDate = dateSplit[2] + "-" + dateSplit[1] + "-" + dateSplit[0];
		$('#arrivaldateTo').datepicker({			
			minDate: new Date(arrDate)
		});
		
		$('#arrivaldateTo').val(dateString);
	});
	
	
	$('.anListeDateTo').on('click', function(e){
		
		e.preventDefault();
		date = $(this).data('date');
		var dateString = date;
		
		dateSplit = dateString.split(".");
		//retDate = dateSplit[2] + "-" + dateSplit[1] + "-" + dateSplit[0];
		//$(this).datepicker( "option", "defaultDate", dateString );
	});
	

    $('#anreiseliste-filter').on('click', function () {
        $row = $(this).closest('.order-filters');
        $date = $row.find('.date').val();
        path.searchParams.set('date', $date);
        location.href = path.href;
    });

    // anreiseliste modal
    $('.anreiseliste-modal').on('click', function (e) {
        e.preventDefault();
        $tr = $(this).closest('tr');
        $modal = $($(this).data('target'));
        $modal.find('.modal-nr').val($tr.find('.order-nr').text().trim());
        $modal.find('.modal-pcode').val($tr.find('.order-pcode').text().trim());
        $modal.find('.modal-code').val($tr.find('.order-code').text().trim());
        $modal.find('.modal-token').val($tr.find('.order-token').text().trim());
        //$modal.find('.modal-fname').val($tr.find('.order-kunde .fname').text().trim());
        $modal.find('.modal-lname').val($tr.find('.order-kunde .lname').text().trim());
        $modal.find('.modal-timefrom').val($tr.find('.order-timefrom').text().trim());
        $modal.find('.modal-persons').val($tr.find('.order-persons').text().trim());
        $modal.find('.modal-parkplatz').val($tr.find('.order-parkplatz').text().trim());
        $modal.find('.modal-dateto').val($tr.find('.order-dateto').text().trim());
		$modal.find('.modal-datefrom').val($tr.find('.order-datefrom').text().trim());
        $modal.find('.modal-ruckflug').val($tr.find('.order-ruckflug').text().trim());
        $modal.find('.modal-landung').val($tr.find('.order-landung').text().trim());
        $modal.find('.modal-betrag').val(parseFloat($tr.find('.order-betrag').text().trim()));
        $modal.find('.modal-fahrer').val($tr.find('.order-fahrer').text().trim());
        $modal.find('.modal-sonstiges').val($tr.find('.order-sonstiges').text().trim());
        $modal.find('.modal-status').val('wc-' + $tr.find('.order-status').text().trim());
        $modal.modal('show');
    })

    $('.save-anreiseliste-row').on('click', function(e){
        e.preventDefault();
        var _this = $(this);
        var txt = $(this).text();
        $(this).html('<div class="loader"></div>');
        $tr = $(this).closest('tr');
        var data = {}
        data['task'] = 'update_anreiseliste';
        data['order-nr'] = $tr.find('.order-nr').val().trim();
        data['order-pcode'] = $tr.find('.order-pcode input').val();
        data['order-code'] = $tr.find('.order-code').text().trim();
        data['order-token'] = $tr.find('.order-token').text().trim();
        data['order-lname'] = $tr.find('.order-kunde input').val();
        data['order-timefrom'] = $tr.find('.order-timefrom input').val();
        data['order-persons'] = $tr.find('.order-persons input').val();
        data['order-parkplatz'] = $tr.find('.order-parkplatz input').val();
        data['order-dateto'] = $tr.find('.order-dateto input').val();
        data['order-datefrom'] = $tr.find('.order-datefrom input').val();
        data['order-ruckflug'] = $tr.find('.order-ruckflug input').val();
        data['order-landung'] = $tr.find('.order-landung input').val();
        data['order-betrag'] = $tr.find('.order-betrag input').val();
        
		select_fahrer = $tr.find('.order-fahrer select').val();
		input_fahrer = $tr.find('.order-fahrer input').val();
		
		if(select_fahrer != null)
			data['order-fahrer'] = select_fahrer;
		else
			data['order-fahrer'] = input_fahrer;
        
		data['order-sonstiges'] = $tr.find('.order-sonstiges input').val();
		
		checkbox_spgp = document.getElementById("order-spgp"+data['order-token']);
		if (checkbox_spgp.checked) {
			data['order-spgp'] = "1";
		} else {
			data['order-spgp'] = "0";
		}
        
		data['order-status'] = $tr.find('.order-status select').val();
        data['ajax'] = true;
		
		
        $.ajax({
            method: 'POST',
            url: helperUrl,
            data: data,
            success: function(data){
                _this.html(txt);
                console.log(data);
            }
        });
    })

    // abreiseliste modal
    $('.abreiseliste-modal').on('click', function (e) {
        e.preventDefault();
        $tr = $(this).closest('tr');
        $modal = $($(this).data('target'));
        $modal.find('.modal-nr').val($tr.find('.order-nr').text().trim());
        $modal.find('.modal-pcode').val($tr.find('.order-pcode').text().trim());
        $modal.find('.modal-token').val($tr.find('.order-token').text().trim());
        //$modal.find('.modal-fname').val($tr.find('.order-kunde .fname').text().trim());
        $modal.find('.modal-lname').val($tr.find('.order-kunde .lname').text().trim());
        $modal.find('.modal-timeto').val($tr.find('.order-timeto').text().trim());
        $modal.find('.modal-persons').val($tr.find('.order-persons').text().trim());
        $modal.find('.modal-parkplatz').val($tr.find('.order-parkplatz').text().trim());
        $modal.find('.modal-ruckflug').val($tr.find('.order-ruckflug').text().trim());
        $modal.find('.modal-sonstige1').val($tr.find('.order-sonstige1').text().trim());
        $modal.find('.modal-sonstige2').val($tr.find('.order-sonstige2').text().trim());
        $modal.find('.modal-status').val('wc-' + $tr.find('.order-status').text().trim());
        $modal.find('.modal-fahrer').val($tr.find('.order-fahrer').text().trim());
		$modal.find('.modal-betrag').val(parseFloat($tr.find('.order-betrag').text().trim()));
		$modal.find('.modal-dateto').val($tr.find('.order-dateto').text().trim());
		$modal.modal('show');
    });

    $('.save-abreiseliste-row').on('click', function(e){
        e.preventDefault();
        var _this = $(this);
        var txt = $(this).text();
        $(this).html('<div class="loader"></div>');
        $tr = $(this).closest('tr');
        var data = {}
        data['task'] = 'update_abreiseliste';
        data['order-nr'] = $tr.find('.order-nr').val().trim();
        data['order-pcode'] = $tr.find('.order-pcode input').val();
        data['order-token'] = $tr.find('.order-token').text().trim();
        data['order-lname'] = $tr.find('.order-kunde input').val();
        data['order-timeto'] = $tr.find('.order-timeto input').val();
        data['order-persons'] = $tr.find('.order-persons input').val();
        data['order-parkplatz'] = $tr.find('.order-parkplatz input').val();
        data['order-ruckflug'] = $tr.find('.order-ruckflug input').val();
        data['order-sonstige1'] = $tr.find('.order-sonstige1 input').val();
        data['order-sonstige2'] = $tr.find('.order-sonstige2 input').val();
        data['order-status'] = $tr.find('.order-status select').val();
        select_fahrer = $tr.find('.order-fahrer select').val();
		input_fahrer = $tr.find('.order-fahrer input').val();
		
		if(select_fahrer != null)
			data['order-fahrer'] = select_fahrer;
		else
			data['order-fahrer'] = input_fahrer;
        data['order-betrag'] = $tr.find('.order-betrag input').val();
        data['order-dateto'] = $tr.find('.order-dateto input').val();
        data['ajax'] = true;

        $.ajax({
            method: 'POST',
            url: helperUrl,
            data: data,
            success: function(data){
                _this.html(txt);
                console.log(data);
            }
        });
    });

    // show anreise template
    $('.anreise-btn-template').on('click', function (e) {
        e.preventDefault();
        $('.anreise-template').removeClass('d-none');
        $('.abreise-template').addClass('d-none');
		$('.anreise-valet-template').addClass('d-none');
		$('.abreise-valet-template').addClass('d-none');
    });

    // show abreise template
    $('.abreise-btn-template').on('click', function (e) {
        e.preventDefault();
        $('.abreise-template').removeClass('d-none');
        $('.anreise-template').addClass('d-none');
		$('.anreise-valet-template').addClass('d-none');
		$('.abreise-valet-template').addClass('d-none');
    });
	
	// show anreise valet template
    $('.anreise-valet-btn-template').on('click', function (e) {
        e.preventDefault();
		$('.anreise-valet-template').removeClass('d-none');
		$('.abreise-valet-template').addClass('d-none');
        $('.anreise-template').addClass('d-none');
        $('.abreise-template').addClass('d-none');
    });
	
    // show abreise valet template
    $('.abreise-valet-btn-template').on('click', function (e) {
        e.preventDefault();
		$('.anreise-valet-template').addClass('d-none');
		$('.abreise-valet-template').removeClass('d-none');
        $('.abreise-template').addClass('d-none');
        $('.anreise-template').addClass('d-none');
    });

    // delete price
    $('.del-price').on('click', function (e) {
        e.preventDefault();
        $this = $(this);
        $id = $(this).data('id');
		$name = $(this).data('name');
        if (confirm('Preisschiene bei APS und APG löschen?')) {
            $.ajax({
                url: helperUrl,
                method: 'POST',
                data: {
                    task: 'delete_price',
                    id: $id,
					name: $name
                },
                success: function () {
                    $this.closest('tr').remove();
                }
            });
        }
    });
	
    // add location
    $('.add_new_locationBtn').on('click', function (e) {
        var _this = $(this);
        if($(this).hasClass('disabled')){
            return;
        }
        $(this).addClass('disabled');
        e.preventDefault();
		$location = $('.newLocation').val();
		var locationsTemplate = `<option></option>`;
		if($location != ""){
			$.ajax({
				url: helperUrl,
				method: 'POST',
				data: {
					task: 'add_location',
					newLocation: $location
				},
				success: function (data) {
				    _this.removeClass('disabled');
				    var dataParsed = JSON.parse(data);
				    // populate locations template
                    dataParsed.forEach(function(item){
                        locationsTemplate += `<option value="${item.id}">${item.location}</option>`;
                    });
                    $('select[name="location"]').empty().html(locationsTemplate);
                    $('.newLocation').val('');
				}
			});
		}
    });
	
	// add prices to calendar fast
    $('.addPrices_calendarFast').on('click', function (e) {
        var _this = $(this);
        e.preventDefault();
		$product = $('.product').val();
		$price = $('.price').val();
		$date = $('.date').val();
		if($product != "" && $price != "" && $date != ""){
			$.ajax({
				url: helperUrl,
				method: 'POST',
				data: {
					task: 'addPrice_calendarFast',
					product: $product,
					price: $price,
					date: $date
				},
				success: function () {
					path.searchParams.set('page', 'produkte-bearbeiten');
					path.searchParams.set('edit', $product);
					location.href = path.href;
					$('#tab5').removeClass('d-none');
					$('#tab1').addClass('d-none');
				}
			});
		}
    });
	
	// delete prices to calendar fast
    $('.delPrices_calendarFast').on('click', function (e) {
        var _this = $(this);
        e.preventDefault();
		$product = $('.product').val();
		$date = $('.date').val();
		if($product != "" && $date != ""){
			$.ajax({
				url: helperUrl,
				method: 'POST',
				data: {
					task: 'delPrice_calendarFast',
					product: $product,
					date: $date
				},
				success: function () {
					path.searchParams.set('page', 'produkte-bearbeiten');
					path.searchParams.set('edit', $product);
					location.href = path.href;
					$('#tab5').removeClass('d-none');
					$('#tab1').addClass('d-none');
				}
			});
		}
    });
	
	// Edit Booking Cancel
	$("#editBooking_cancelBtn").click(function(e){
		var _this = $(this);
        e.preventDefault();
		$order_id = $("#order_id").val();
			$.ajax({
				url: helperUrl,
				data: {
					order_id : $order_id,
					task : "editBooking_cancel"
				},
				type: 'POST',
				success: function(data){				
					location.reload();
				}
			});
	});

    // tabs
    $('.tab-open').on('click', function () {
        $tabs = $(this).closest('.tabs');
        $tabs.find('.tab-content').addClass('d-none');
        $tabs.find($(this).data('target')).removeClass('d-none');
        $('.fc-dayGridMonth-button').click();
    });

    // on change show
    $('.on-change-show').on('change', function () {
        $target = $($(this).data('target'));
        if ($(this).val() !== '') {
            $target.removeClass('d-none');
        } else {
            $target.addClass('d-none');
        }
    });
	
    // check row on click
    $('.check-row').on('click', function () {
        $tr = $(this);
        var price = Number($tr.find('td').last().text());
        if ($tr.hasClass('mark_done')) {
            $tr.removeClass('mark_done');
            $tr.find('input[name="add_ser_id[]"]').val('');
            changeOrderPrice($, path, price, 'remove');
        } else {
            $tr.addClass('mark_done')
            $tr.find('input[name="add_ser_id[]"]').val($tr.data('id'));
            changeOrderPrice($, path, price, 'add');
        }
    })

    // add new restriction template
    $(document).on('click', '.add-restriction-template', function () {
        $clone = $(this).closest('.restriction-item').clone();
        $clone.find('.del-table-row').removeAttr('data-id');
        $('.restrictions-wrapper').append(emptyFields($, $clone));
        $lastItem = $('.restrictions-wrapper .restriction-item').last();
        initDatepickers($, $lastItem);
        initTimepickers($, $lastItem);
    })

    // add new cancellation template
    $(document).on('click', '.add-cancellation-template', function () {
        $clone = $(this).closest('.cancellation-item').clone();
        $('.order_cancellations-wrapper').append(emptyFields($, $clone));
        $lastItem = $('.order_cancellations-wrapper .cancellation-item').last();
    });
	
    // add new commission template
    $(document).on('click', '.add-commission-template', function () {
        $clone = $(this).closest('.commission-item').clone();
        $('.commissions-wrapper').append(emptyFields($, $clone));
        $lastItem = $('.commissions-wrapper .commission-item').last();
		initDatepickers($, $lastItem);
    });
	
    // add new additional_services template
    $(document).on('click', '.add-services-template', function () {
        $clone = $(this).closest('.service-item').clone();
        $('.additional_services-wrapper').append(emptyFields($, $clone));
        $lastItem = $('.additional_services-wrapper .service-item').last();
    });
	
	// add new product_groups template
    $(document).on('click', '.add-group-template', function () {
        $clone = $(this).closest('.group-item').clone();
        $('.product_groups-wrapper').append(emptyFields($, $clone));
        $lastItem = $('.product_groups-wrapper .group-item').last();
    });
	// add new child product_groups template
    $(document).on('click', '.add-child-group-template', function () {
        $clone = $(this).closest('.child-group-item').clone();
        $('.product_child_groups-wrapper').append(emptyFields($, $clone));
        $lastItem = $('.product_child_groups-wrapper .child-group-item').last();
    });
	
    // add new discount template
    $(document).on('click', '.add-discount-template', function () {
        $clone = $(this).closest('.discount-item').clone();
        $('.discounts-wrapper').append(emptyFields($, $clone));
        $lastItem = $('.discounts-wrapper .discount-item').last();
        // initDatepickers($, $lastItem);
        // initTimepickers($, $lastItem);
        initRangeDatepickers($, $lastItem);
    });
	
	// add new api_code template
    $(document).on('click', '.add-api_code-template', function () {
        $clone = $(this).closest('.api-item').clone();
        $('.api_code-wrapper').append(emptyFields($, $clone));
        $lastItem = $('.api_code-wrapper .api-item').last();
    });

    // delete table row
    $(document).on('click', '.del-table-row', function (e) {
        e.preventDefault();
        var _this = $(this);
        var wrapperClass = _this.data('table') + '-wrapper';
        var itemsLength = $('.' + wrapperClass).find('.row-item').length;
        if (!confirm('Are you sure, this actions cannot be undone?')) {
            return;
        }
        if (!_this[0].hasAttribute('data-id')) {
            if (itemsLength > 1) {
                _this.closest('.row-item').remove();
            }
            return;
        }
        if (_this.hasClass('disabled')) {
            return;
        }
        _this.addClass('disabled');
        $.ajax({
            type: 'DELETE',
            url: `/wp-json/api/delete-table-row?table=${_this.data('table')}&id=${_this.data('id')}`,
            success: function () {
                if (itemsLength === 1) {
                    $item = _this.closest('.row-item');
                    $item.find('input').val('');
                    _this.removeClass('disabled');
                    _this.removeAttr('data-id');
                } else {
                    _this.closest('.row-item').remove();
                }
            }
        });
    });

    // delete product gallery image
    $(document).on('click', '.del-img', function (e) {
        e.preventDefault();
        var _this = $(this);
        if (confirm('Are you sure, this action cannot be undone?')) {
            _this.hide();
            $.ajax({
                type: 'DELETE',
                url: '/wp-json/api/delete-product-gallery-image?id=' + _this.data('id'),
                success: function () {
                    _this.closest('.gallery-image').remove();
                }
            });
        }
    })
	
	// delete order valet car image
    $(document).on('click', '.del-valet-img', function (e) {
        e.preventDefault();
        var _this = $(this);
        if (confirm('Bild wirklich löschen?')) {
            _this.hide();
            $.ajax({
                type: 'DELETE',
                url: '/wp-json/api/delet-valet-car-image?id=' + _this.data('id') + '&name=' + _this.data('name'),
                success: function () {
                    _this.closest('.valet-car-image').remove();
                }
            });
        }
    })

    // init calendar
    initCalendar($, path);

    // delete product
    $(document).on('click', '.del-product', function (e) {
        e.preventDefault();
        var _this = $(this);
        if (_this.hasClass('disabled')) {
            return;
        }
        _this.addClass('disabled');
        if (confirm('Are you sure, this action cannot be undone?')) {
            $.ajax({
                type: 'DELETE',
                url: '/wp-json/api/delete-product?id=' + _this.data('id'),
                success: function (data) {
                    _this.removeClass('disabled');
                    _this.closest('tr').remove();
                }
            });
            return;
        }
        _this.removeClass('disabled');
    });

    // new order product on change
    $('#newOrderProduct').on('change', function () {
        var _this = $(this);
        var form = _this.closest('form');
        if (_this.val() === '') {
            path.searchParams.delete('pid');
            path.searchParams.delete('from');
            path.searchParams.delete('to');
        } else {
            path.searchParams.set('pid', _this.val());
            path.searchParams.set('from', form.find('.date-from').val());
            path.searchParams.set('to', form.find('.date-to').val());
        }

        window.location.href = path.href;
    });

    // statistics items on change
    $('.statistics-item').on('change', function(){
        $item = $(this).attr('name');
        path.searchParams.delete('start-date');
        path.searchParams.delete('end-date');
        if($(this).val() === ''){
            path.searchParams.delete($item);
        }else{
            path.searchParams.set($item, $(this).val());
        }
        location.href = path.href;
    });

    // date filter submit
    $('#ifSubmit').on('click', function(e){
        e.preventDefault();
        $form = $('.interval-filters');
        $startDate = $form.find('#ifStartDate');
        $endDate = $form.find('#ifEndDate');
        if($startDate.val() === '' || $endDate.val() === ''){
            return window.location.reload();
        }
        path.searchParams.delete('year');
        path.searchParams.delete('month');
        path.searchParams.set('start-date', $startDate.val());
        path.searchParams.set('end-date', $endDate.val());
        location.href = path.href;
    });

    // show date filters
    $('.if-filters-a').on('click', function(e){
        e.preventDefault();
        $('.interval-filters').slideDown();
    });
	
	var tableNoSearch = jQuery('.tableNoSearch').DataTable( {	
		"language": {
			"lengthMenu": "_MENU_ Einträge pro Seite",
			"zeroRecords": "Keine Einträge vorhanden",
			"info": "Seite _PAGE_ von _PAGES_",
			"infoEmpty": "Keine Einträge vorhanden",
			"infoFiltered": "(filtered from _MAX_ total records)",
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
		},
		
		dom: 'Bfrtip',
		stateSave: true,
		searching: false,
		paging: false, info: false,
		"pageLength": 26
	} );
	
		// PDF Export bookingreports arrival Datatable
		const queryString = window.location.search;
		const urlParams = new URLSearchParams(queryString);
		const datefrom = urlParams.get('dateFrom');
		const dateto = urlParams.get('dateTo');
		const date_from = urlParams.get('date_from');
		const date_to = urlParams.get('date_to');
		
		if(urlParams.has('dateFrom'))
			var show_date = datefrom + " - " + dateto;
		else if(urlParams.has('date_from'))
			var show_date = date_from;
		else if(urlParams.has('date_to'))
			var show_date = date_to;
		else if(queryString == '?page=umsatz'){
			var today = new Date();
			var dd = String(today.getDate()).padStart(2, '0');
			var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
			var yyyy = today.getFullYear();

			var show_date = /*dd + '.' + */mm + '.' + yyyy;
		}
		else{
			var today = new Date();
			var dd = String(today.getDate()).padStart(2, '0');
			var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
			var yyyy = today.getFullYear();

			var show_date = dd + '.' + mm + '.' + yyyy;
		}
	
	var arrivalTable = jQuery('#arrivalBooking').DataTable( {	
		info: false,
		"language": {
            "lengthMenu": "_MENU_ Einträge pro Seite",
            "zeroRecords": "Keine Einträge vorhanden",
            "info": "Seite _PAGE_ von _PAGES_",
            "search": "Suchen:",
			"infoEmpty": "Keine Einträge vorhanden",
            "infoFiltered": "(filtered from _MAX_ total records)",
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
        },
        stateSave: true,
		 //"order": [[ 4, "desc" ]],
		
        "pageLength": 28,
    } );
	
	var arrivalTable_valet = jQuery('#arrivalBooking_valet').DataTable( {	
		"language": {
            "lengthMenu": "_MENU_ Einträge pro Seite",
            "zeroRecords": "Keine Einträge vorhanden",
            "info": "Seite _PAGE_ von _PAGES_",
            "infoEmpty": "Keine Einträge vorhanden",
            "infoFiltered": "(filtered from _MAX_ total records)",
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
        },
		
		dom: 'Bfrtip',
        stateSave: true,
		 //"order": [[ 4, "desc" ]],
		
        "pageLength": 31,
        buttons: [
			{
				extend: 'excelHtml5',
				className: 'btn  btn-success',
			},
            {
                extend: 'pdfHtml5',
                text: '<strong>Anreiseliste exportieren</strong>',
                className: 'btn  btn-success',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                pageSize: 'A4',
                
                title: "Anreiseliste Valet | " + show_date,
                message: " ",
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 , 11, 12, 13]
                },
                customize: function(doc) {
                    //console.log(doc);
					for (var r=0;r<doc.content[2].table.body[0].length;r++) {
						doc.content[2].table.body[0][r].fontSize = 10;		
					}
                    for (var r=1;r<doc.content[2].table.body.length;r++) {
                    	doc.content[2].layout = 'Border';
                        var row = doc.content[2].table.body[r];
                        var exportColor = '#fff';
                        for (c=0;c<row.length;c++) {
							row[c].fontSize = 10;
                            exportColor = arrivalTable_valet
                                                .cell( {row: r-1, column: 0} )
                                                .nodes()
                                                .to$()
                                                .attr('export-color');
								
                        }
                        
                        if (exportColor) {
							row[1].fillColor = exportColor;
                        }
                    }
                }
            }           
        ]
    } );
	
	// PDF Export bookingreports return Datatable
	var returnTable = jQuery('#returnBooking').DataTable( {
		info: false,
		"language": {
            "lengthMenu": "_MENU_ Einträge pro Seite",
            "zeroRecords": "Keine Einträge vorhanden",
            "info": "Seite _PAGE_ von _PAGES_",
            "search": "Suchen:",
			"infoEmpty": "Keine Einträge vorhanden",
            "infoFiltered": "(filtered from _MAX_ total records)",
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
        },
        stateSave: true,
        "pageLength": 28,
    } );
	
	var returnTable_valet = jQuery('#returnBooking_valet').DataTable( {
		"language": {
            "lengthMenu": "_MENU_ Einträge pro Seite",
            "zeroRecords": "Keine Einträge vorhanden",
            "info": "Seite _PAGE_ von _PAGES_",
            "infoEmpty": "Keine Einträge vorhanden",
            "infoFiltered": "(filtered from _MAX_ total records)",
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
        },
		
		dom: 'Bfrtip',
        stateSave: true,
		 //"order": [[ 4, "desc" ]],
		
        "pageLength": 26,
        buttons: [
			{
				extend: 'excelHtml5',
				className: 'btn  btn-success',
			},
            {
                extend: 'pdfHtml5',
                text: '<strong>Abreiseliste exportieren</strong>',
                className: 'btn  btn-success',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                pageSize: 'A4',
                
                title: "Abreiseliste Valet | "  + show_date,
                message: " ",
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                },
                customize: function(doc) {
                    //console.log(doc);
					for (var r=0;r<doc.content[2].table.body[0].length;r++) {
						doc.content[2].table.body[0][r].fontSize = 10;		
					}
                    for (var r=1;r<doc.content[2].table.body.length;r++) {
                    	doc.content[2].layout = 'Border';
                        var row = doc.content[2].table.body[r];
                        var exportColor = '#fff';
                        for (c=0;c<row.length;c++) {
							row[c].fontSize = 10;
                            exportColor = returnTable_valet
                                                .cell( {row: r-1, column: 0} )
                                                .nodes()
                                                .to$()
                                                .attr('export-color');
								
                        }
                        
                        if (exportColor) {
							row[1].fillColor = exportColor;
                        }
                    }
                }
            }           
        ]
    } );
	
	// PDF Export day Sales Datatable
	var salesFor = $('.salesFor').val();
	var daySales = jQuery('#daySales').DataTable( {
		"language": {
            "lengthMenu": "_MENU_ Einträge pro Seite",
            "zeroRecords": "Keine Einträge vorhanden",
            "info": "Seite _PAGE_ von _PAGES_",
            "infoEmpty": "Keine Einträge vorhanden",
            "infoFiltered": "(filtered from _MAX_ total records)",
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
        },
		
		dom: 'Bfrtip',
        stateSave: true,
		 //"order": [[ 4, "desc" ]],
		
        "pageLength": 32,
        buttons: [
			{
				extend: 'excelHtml5',
				className: 'btn  btn-success',
			},
            {
                extend: 'pdfHtml5',
                text: '<strong>Umsätze exportieren</strong>',
                className: 'btn  btn-success',
                orientation: 'portrait',
                pageSize: 'LEGAL',
                pageSize: 'A4',
                
                title: salesFor + " | "  + show_date,
                message: " ",
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                },
                customize: function(doc) {
					
					doc.styles.title = {
						fontSize: '14',
						alignment: 'left'
					}  
					
                    //console.log(doc);
					for (var r=0;r<doc.content[2].table.body[0].length;r++) {
						doc.content[2].table.body[0][r].fontSize = 8;		
					}
                    for (var r=1;r<doc.content[2].table.body.length;r++) {
                    	doc.content[2].layout = 'Border';
                        var row = doc.content[2].table.body[r];
                        var exportColor = '#fff';
                        for (c=0;c<row.length;c++) {
							row[c].fontSize = 8;							
                            exportColor = returnTable
                                                .cell( {row: r-1, column: 0} )
                                                .nodes()
                                                .to$()
                                                .attr('export-color');
								if(row[c].text.includes("Summe")){
									for (c=0;c<row.length;c++) {
										row[c].fillColor = '#fbfcde';
										row[c].bold = 1;
										row[c].fontSize = 8;
									}															
								}
								else
									row[c].fillColor = '#fff';
								
                        }
                        
                        if (exportColor) {
							row[1].fillColor = exportColor;
                        }
                    }
                }
            }           
        ]
    } );
	
    // init datatables
    var table = $('.datatable').DataTable({
		"pageLength": 25,
		"order": [[ 0, 'desc' ]],
        "language": {
            "lengthMenu": "_MENU_ Einträge pro Seite",
            "zeroRecords": "Keine Einträge vorhanden",
            "info": "Seite _PAGE_ von _PAGES_",
			"search": "Suchen:",
            "infoEmpty": "Keine Einträge vorhanden",
            "infoFiltered": "(filtered from _MAX_ total records)",			
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
        }
    });
	
    // init datatable PKWs
    var tablePKWs = $('.datatablePKWs').DataTable({
		"pageLength": 25,
		//"order": [[ 3, 'desc' ], [ 4, 'asc' ]],
        "language": {
            "lengthMenu": "_MENU_ Einträge pro Seite",
            "zeroRecords": "Keine Einträge vorhanden",
            "info": "Seite _PAGE_ von _PAGES_",
			"search": "Suchen:",
            "infoEmpty": "Keine Einträge vorhanden",
            "infoFiltered": "(filtered from _MAX_ total records)",			
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
        }
    });
	
    // init datatable bookings
    var tableBooking = $('.bookings_datatable').DataTable({
		"pageLength": 25,
		"info": false,
		//"order": [[ 3, 'desc' ], [ 4, 'asc' ]],
        "language": {
            "lengthMenu": "_MENU_ Einträge pro Seite",
            "zeroRecords": "Keine Einträge vorhanden",
            "info": "Seite _PAGE_ von _PAGES_",
			"search": "Suchen:",
            "infoEmpty": "Keine Einträge vorhanden",
            "infoFiltered": "(filtered from _MAX_ total records)",			
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
        }
    });
	
    var tableBookingBD = $('.bookings_bd_datatable').DataTable({
		"pageLength": 25,
		"info": false,
		"order": [[ 0, 'desc' ]],
        "language": {
            "lengthMenu": "_MENU_ Einträge pro Seite",
            "zeroRecords": "Keine Einträge vorhanden",
            "info": "Seite _PAGE_ von _PAGES_",
			"search": "Suchen:",
            "infoEmpty": "Keine Einträge vorhanden",
            "infoFiltered": "(filtered from _MAX_ total records)",			
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
        }
    });
	
	// init datatable mitarbeiter
    var tableBooking = $('.mitarbeiter_datatable').DataTable({
		"pageLength": 25,
		"paging": false,
		"info": false,
		"searching": true,
		//"order": [[ 3, 'desc' ], [ 4, 'asc' ]],
        "language": {
            "lengthMenu": "_MENU_ Einträge pro Seite",
            "zeroRecords": "Keine Einträge vorhanden",
            "info": "Seite _PAGE_ von _PAGES_",
			"search": "Suchen:",
            "infoEmpty": "Keine Einträge vorhanden",
            "infoFiltered": "(filtered from _MAX_ total records)",			
			"paginate": {
				"first":      "Erste Seite",
				"last":       "Letzte Seite",
				"next":       "Vor",
				"previous":   "Zurück"
			}
        }
    });	

    // bulk delete
    $(document).on('click', '.bulk-delete-btn', function(e){
        e.preventDefault();
		if (confirm('Buchungen unwiderruflich löschen?')) {
			var checkedIds = [];
			$('.bulk-delete-check').each(function(){
				if($(this).is(':checked')){
					checkedIds = [...checkedIds, $(this).data('id')]
				}
			});

			$(this).addClass('disabled').attr('disabled');

			$.ajax({
				type: 'POST',
				url: helperUrl,
				data: {
					checkedIds: checkedIds,
					table: $(this).data('table'),
					attribute: $(this).data('attribute'),
					task: 'bulk_delete'
				},
				success: function(data){
					window.location.reload();
				}
			});
		}
    })
	
	// dalete prices from calendar
    $(document).on('click', '.price-delete-btn', function(e){
        e.preventDefault();
		if (confirm('Alle Einträge löschen?')) {
 
			$.ajax({
				type: 'POST',
				url: helperUrl,
				data: {
					lotid: $(this).data('lotid'),
					task: 'price_delete_from_calendar'
				},
				success: function(data){
					window.location.reload();
				} 
			});
		}
    })
	
    // Edit Booking DateFrom
    $('.editBookingDateFrom').on('change', function(){
		var _this = $(this);
        var form = _this.closest('form');
		var from = form.find('.editBookingDateFrom').val();
		
		path.searchParams.delete('dateTo');
        if(from === ''){
            path.searchParams.delete('dateFrom');
			path.searchParams.delete('dateTo');
        }else{
            path.searchParams.set('dateFrom', from);
        }
        location.href = path.href;
		
    });
	$('.editBookingDateTo').on('change', function(){
		var _this = $(this);
        var form = _this.closest('form');
        var from = form.find('.editBookingDateFrom').val();
		var to = form.find('.editBookingDateTo').val();
		
        if(to === ''){
			path.searchParams.delete('dateTo');
        }else{
			path.searchParams.set('dateFrom', from);
            path.searchParams.set('dateTo', to);
        }
        location.href = path.href;
		
    });
});

function initTimepickers($, el = false) {
    var timepickers;
    if (el) {
        timepickers = el.find('.timepicker');
    } else {
        timepickers = $('.timepicker');
    }
    var options = {
        timeFormat: 'HH:mm'
    };
    if ($(timepickers).isDataOk('maxTime')) {
        options['maxTime'] = $(timepickers).data('maxTime');
    }
    if ($(timepickers).isDataOk('minTime')) {
        options['minTime'] = $(timepickers).data('minTime');
    }
    timepickers.timepicker(options);
}

function newOrderDateFromOnSelect(formattedDate, date) {
    $ = jQuery;
    $productId = $('.buchung-erstellen #newOrderProduct').val();
    $dateTo = $('.buchung-erstellen .date-to');
    $timeFrom = $('.buchung-erstellen .time-from');
    $timeFrom.timepicker('destroy');
    $timeFrom.addClass('timepicker')
    $dateTo.removeAttr('disabled').removeClass('disabled').addClass('air-datepicker');
    var dateEn = formattedDate.split('.').reverse().join('-');
    $dateTo.data('dateMin', dateEn);
    initDatepickers(jQuery, $dateTo.parent(), true);
    $dateTo.val('');

    // get restriction time
    $.ajax({
        type: 'GET',
        url: `/wp-json/api/date-restriction?productId=${$productId}&date=${dateEn}`,
        success: function (data) {
            if (data['restriction']) {
                $timeFrom.attr('data-max-time', data.restriction.time);
            } else {
                $timeFrom.attr('data-max-time', '23:30');
            }
            initTimepickers($, $timeFrom.parent());
        }
    });
}

function getDateRescrictions(productId,formattedDate, datefrom = null, timefrom = null, callbackFn){
    // get restriction time
    var url = `/wp-json/api/date-restriction?productId=${productId}&date=${formattedDate}`;
    if(datefrom && timefrom){
        url += `&datefrom=${datefrom}&timefrom=${timefrom}`;
    }
    jQuery.ajax({
        type: 'GET',
        url: url,
        success: function (data) {
            callbackFn(data);
        }
    });
}

function newOrderDateToOnSelect(formattedDate, date) {
    $productId = $('.buchung-erstellen #newOrderProduct').val();
    $dateFrom = $('.buchung-erstellen .date-from').val();
    $timeTo = $('.buchung-erstellen .time-to');
    $timeTo.timepicker('destroy');
    $timeTo.addClass('timepicker');
    $priceTemplate = $('.total-order-price .current-price');
    $('.buchung-erstellen .add-ser-check .check-row').each(function () {
        $(this).removeClass('mark_done');
        $(this).find('input[type="hidden"]').val('');
    });

    $priceTemplate.text('');
    
    getDateRescrictions($productId, formattedDate, null, null,function (data){
        if (data['restriction']) {
            $timeTo.attr('data-max-time', data.restriction.time);
        } else {
            $timeTo.attr('data-max-time', '23:30');
        }
        initTimepickers($, $timeTo.parent());
    });

    // calculate price
    $.ajax({
        type: 'GET',
        url: `/wp-json/api/calc-order-price?productId=${$productId}&dateFrom=${$dateFrom}&dateTo=${formattedDate}`,
        success: function (data) {
            $priceTemplate.data('price', data.price).text(data.price_float);
        }
    });
}

function initDatepickers($, el = false, dependent = false) {
    // init air-datepicker
    var datepickers;
    if (el) {
        datepickers = [].slice.call(el.find('.air-datepicker'));
    } else {
        datepickers = [].slice.call($('.air-datepicker'));
    }
    var disable = false;
    datepickers.forEach(function (el) {
        var options = {
            language: 'de'
        };
        if ($(el).isDataOk('restrictions')) {
            var eventDates = $(el).data('restrictions').split(',');
            // options['onRenderCell'] = function (date, cellType) {
            //     var now = new Date();
            //     date.setHours(now.getHours());
            //     var currentDate = date.toISOString().split('T')[0];
            //     if (disable && dependent && date.toLocaleDateString() !== new Date($(el).data('dateMin')).toLocaleDateString()) {
            //         return {
            //             disabled: true
            //         }
            //     }
            //     // Add extra element, if `eventDates` contains `currentDate`
            //     if (cellType == 'day') {
            //         var d = false;
            //         eventDates.forEach(function (item) {
            //             if (item.indexOf(currentDate) > -1 && now > new Date(item)) {
            //                 console.log(new Date(item) + ' > ' + now);
            //                 if (date > new Date($(el).data('dateMin'))) {
            //                     disable = true;
            //                 }
            //
            //                 d = true;
            //             }
            //         });
            //         if (d) {
            //             return {
            //                 disabled: true
            //             }
            //         }
            //     }
            // }
        }
        if ($(el).isDataOk('onselect')) {
            var fn = $(el).data('onselect');
            options.onSelect = function (fd, date) {
                window[fn](fd, date);
            };
        }

        if ($(el).isDataOk('dateMin')) {
            options['minDate'] = new Date($(el).data('dateMin'));
            options['maxDate'] = new Date($(el).data('dateMax'));
        }
        if ($(el).data('timepicker') === 'true') {
            options['timepicker'] = true;
        }
        if (el.value.trim() !== '') {
            // options['multipleDates'] = true;
            var dates = el.value.split(',').map(function (dateStr) {
                return new Date(dateStr)
            })
            var datepicker = $(el).datepicker(options).data('datepicker');
            datepicker.selectDate(dates);
        } else {
            $(el).datepicker(options);
        }
    });
}

jQuery.fn.isDataOk = function (data) {
    return jQuery(this).data(data) !== undefined && jQuery(this).data(data) !== '';
}

function initRangeDatepickers($, el = false) {
    var rangeDatepickers;
    if (el) {
        rangeDatepickers = el.find('.datepicker-range')
    } else {
        rangeDatepickers = $('.datepicker-range');
    }
    rangeDatepickers.each(function () {
        $(this).datepicker(
            {
                language: 'de',
                range: true
            }
        );
        if ($(this).data('from') !== undefined && $(this).data('to') !== undefined) {
            var datepicker = $(this).datepicker().data('datepicker');
            datepicker.selectDate(new Date($(this).data('from')));
            datepicker.selectDate(new Date($(this).data('to')));
        }
    });
}

function emptyFields($, el) {
    $(el).find('input').val('');
    return el;
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function changeOrderPrice($, path, price, type) {
    if (path.searchParams.get('page') !== 'buchung-erstellen') {
        return 0;
    }
    var priceEl = $('.current-price');
    var p = Number(priceEl.text());
    if (type === 'add') {
        priceEl.text(p + price);
    } else {
        priceEl.text(p - price);
    }
}

function generate_token(length) {
    //edit the token allowed characters
    var a = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890".split("");
    var b = [];
    for (var i = 0; i < length; i++) {
        var j = (Math.random() * (a.length - 1)).toFixed(0);
        b[i] = a[j];
    }
    return b.join("");
}

function initCalendar($, path) {
    if (typeof FullCalendar === "object") {
        /* initialize the calendar
           -----------------------------------------------------------------*/

        $('#c_Cat').on('change', function () {
            if ($(this).val() === '') {
                path.searchParams.delete('cat_id');
            } else {
                path.searchParams.set('cat_id', $(this).val());
            }
            window.location.href = path.href;
        });

        var calendarEl = document.getElementById('calendar');
        var eventsAPI = '/wp-json/api/events';
        if (path.searchParams.has('edit')) {
            eventsAPI += '?productId=' + path.searchParams.get('edit');
        }

        /* initialize the external events */
        var Calendar = FullCalendar.Calendar;
        var Draggable = FullCalendarInteraction.Draggable;

        var containerEl = document.getElementById('external-events-list');
        if (containerEl) {
            new Draggable(containerEl, {
                itemSelector: '.fc-event',
                eventData: function (eventEl) {
                    return {
                        title: eventEl.innerText.trim()
                    }
                }
            });
        }

        if (calendarEl) {
            calendar = new Calendar(calendarEl, {
                plugins: ['interaction', 'dayGrid', 'timeGrid', 'list'],
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                events: eventsAPI,
                eventClick: function (arg) {
                    // var confirmed = confirm("Are you sure you want to delete event?");
                    // if (confirmed) {
                    //
                    // }
                    $.ajax({
                        url: '/wp-json/api/delete-event?id=' + arg.event.id,
                        type: 'DELETE',
                        success: function (data) {
                            calendar.getEventById(arg.event.id).remove();
                        }
                    });
                },
                editable: true,
                droppable: true, // this allows things to be dropped onto the calendar
                drop: function (arg) {
                    var data = {
                        datefrom: arg.dateStr,
                        dateto: arg.dateStr,
                        price_id: arg.draggedEl.dataset['id'],
						name: arg.draggedEl.innerText
                    };
                    if (path.searchParams.has('edit') && path.searchParams.get('page') === 'produkte-bearbeiten') {
                        data['product_id'] = path.searchParams.get('edit');
                    }
                    $.ajax({
                        url: '/wp-json/api/save-event',
                        method: 'POST',
                        data: data,
                        success: function (data) {
                            calendar.refetchEvents();
                            setTimeout(function () {
                                $('.fc-resizer').each(function () {
                                    $(this).closest('a').remove();
                                });
                            }, 1000);
                        }
                    });
                    // is the "remove after drop" checkbox checked?
                    if (document.getElementById('drop-remove') && document.getElementById('drop-remove').checked) {
                        // if so, remove the element from the "Draggable Events" list
                        arg.draggedEl.parentNode.removeChild(arg.draggedEl);
                    }
                },
                eventDrop: function (arg) {
                    var dateTo = arg.event.end ? arg.event.end : arg.event.start;
                    if (arg.event.start < new Date()) {
                        arg.event.setStart(arg.oldEvent.start);
                        arg.event.setEnd(arg.oldEvent.end);
                    }
                    $.ajax({
                        url: '/wp-json/api/update-event',
                        method: 'POST',
                        data: {
                            id: arg.event.id,
                            datefrom: parseDate(arg.event.start),
                            dateto: parseDate(dateTo)
                        },
                        success: function () {
                            calendar.refetchEvents();
                        }
                    });
                },
                // eventReceive: function(arg){
                // arg.event.setEnd(new Date());
                // $('#endDateModal').show();
                // console.log(arg);
                // }
            });
            calendar.render();
        }

        /* END - FullCalendar */
    }
}

function parseDate(date) {
    return `${date.getFullYear()}.${date.getMonth() + 1}.${date.getDate()}`;
}

// Reload Page on browser back click to clear cach
window.addEventListener( "pageshow", function ( event ) {
  var historyTraversal = event.persisted || 
                         ( typeof window.performance != "undefined" && 
                              window.performance.navigation.type === 2 );
  if ( historyTraversal ) {
    // Handle page restore.
    window.location.reload();
  }
});

jQuery(document).ready(function ($) {
    // Füge IDs zu den Untermenüs hinzu
    $('#toplevel_page_personalplanung ul').find('li').each(function (index) {
        $(this).attr('id', 'personalplanung-' + (index + 1));
    });
	$('#toplevel_page_berichte ul').find('li').each(function (index) {
        $(this).attr('id', 'berichte-' + (index + 1));
    });
});