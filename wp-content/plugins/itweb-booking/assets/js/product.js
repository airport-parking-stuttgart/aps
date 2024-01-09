jQuery(document).ready(function ($) {
    var path = new URL(window.location.href);
    var productId = $(".pro-id").val();
    var parklotId = $(".lot-id").val();
    var helperUrl = '/wp-content/plugins/itweb-booking/classes/Helper.php';
    var cartButton = $('.add_to_cart_button');
    var canCreateOrder = false;

    /**
     *
     * show loader by default
     * as on site load, a request is made to get timefrom restrictions and init timefrom timepicker
     *
     */
    // loader('show');
    /**
     *
     * hide add to cart button by default
     *
     */
    cartButton.hide();

    /**
     *
     * Check if datefrom and dateto query params are set
     * else, set default date to today
     *
     */
    let datefrom = new Date().toISOString().split('T')[0];
    let dateto = new Date().toISOString().split('T')[0];
    if (path.searchParams.has('datefrom') && path.searchParams.has('dateto')) {
        datefrom = path.searchParams.get('datefrom');
        dateto = path.searchParams.get('dateto');
    }

    /**
     *
     * Add 'dateto' and 'datefrom' params to product archive button links
     *
     */
    $('.archive-product-link a.elementor-button-link').each(function () {
        var _this = $(this);
        const href = $(this).attr('href');
        $(this).attr('href', href + '?datefrom=' + datefrom + '&dateto=' + dateto);
    });

    /**
     *
     * init product page timepickers restrictions
     *
     */
    // $.ajax({
    //     type: 'GET',
    //     url: '/wp-json/api/check-availibility?productId=' + productId + '&date=' + datefrom,
    //     success: function (data) {
    //         $('.product-page-timefrom, .product-page-timeto').attr('data-disabled-times', data.used_times);
    //
    //         timepickersRestrictions(productId, datefrom, '.product-page-timefrom');
    //     }
    // });


    function timepickersRestrictions(pId, d, elSelector, datefrom = null, timefrom = null, fn = null) {
        loader('show');
        getDateRescrictions(pId, d, datefrom, timefrom, function (data) {
            if (data['restriction']) {
                $(elSelector).attr('data-max-time', data.restriction.time);
            } else {
                $(elSelector).attr('data-max-time', '23:30');
            }

            if (fn) {
                fn();
            }

            if (data.hasOwnProperty('order_lead_time')){
                if(data['order_lead_time']) {
                    canCreateOrder = true;
                } else {
                    showError('This parklot is not available anymore!')
                }
            }

            $(elSelector).addClass('timepicker');
            initTimepickers($, $(elSelector).parent());
            loader('hide');
        });
    }

    /**
     *
     * add timefrom and timeto to add_to_cart ajax
     *
     */

    cartButton
        .attr('data-datefrom', path.searchParams.get('datefrom'))
        .attr('data-dateto', path.searchParams.get('dateto'));

    $(document).on('click', '.ui-timepicker .ui-menu-item > a', function () {
        $('.product-page-timefrom, .product-page-timeto').trigger('change');
    })

    $(document).on('change', '.product-page-timefrom, .product-page-timeto', function (e) {
        var activeEl = 'data-timeto';
        if ($(this).hasClass('product-page-timefrom')) {
            activeEl = 'data-timefrom';

            /**
             *
             * Timefrom on change, request hours for Timeto
             * and set Timeto min-date as Timefrom value
             *
             */

            var timeFrom = $(this).val();
            timepickersRestrictions(productId, dateto, '.product-page-timeto', datefrom, timeFrom, function () {
                $('.product-page-timeto').attr('data-min-time', timeFrom + ':00');
            });
        } else {
            /**
             *
             * after timeto has nonnull value
             * show add to cart button
             *
             */
            if ($(this).val() !== '' && canCreateOrder) {
                cartButton.show();
            }
        }
        cartButton.attr(activeEl, $(this).val())
    })

    /**
     *
     * change product price on product front page on additional services check
     *
     */
    // var priceEl = $('.front-price');
    var priceEl = $('.product-price');
    var price = parseFloat(priceEl.text());
    var checkedServices = [];
    $('.front-additional-service').on('change', function () {
        var self = $(this);
        var servicePrice = parseFloat(self.data('price'));
        var serviceId = self.data('id');

        if (self.is(':checked')) {
            price += servicePrice
            checkedServices.push(serviceId);
        } else {
            price -= servicePrice
            checkedServices = checkedServices.filter(function (item) {
                return item !== serviceId
            });
        }

        // $('.add_to_cart_button')
        //     .attr('data-checked-services', checkedServices);
        $('#chSer').val(checkedServices);

        priceEl.text(price.toFixed(2));
    })

    function loader(fn) {
        if (fn === 'show') {
            $('.itweb-loader').show();
        } else {
            $('.itweb-loader').hide();
        }
    }

    function showError(error) {
        $('.product-page-errors').text(error).show();
    }
});
