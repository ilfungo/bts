jQuery(function ($) {
	jQuery('#recipients').textboxlist({unique: true, bitsOptions: {editable: {addKeys: [188]}}});
});

jQuery('document').ready(function($){
    if( $('.chosen_select').length > 0 ){
        $('.chosen_select').chosen();
    }
    if($('#woocommerce-customer-orders').length > 0 || $('#wc_crm_customers_form table.orders').length > 0){
        $('body').on( 'click', '.show_order_items', function() {
            $(this).closest('td').find('table').toggle();
            return false;
        });
    }
    
    if($('#wp-emaileditor-wrap').length > 0){
        $('#wc_crm_customers_form').submit(function(){
            if($('#subject').val() == ''){
                if(!confirm('The subject field is empty. Are you sure you want to send?')){
                    return false;
                }
            }
        });
    }
    $('select#woocommerce_crm_filters, select#woocommerce_crm_number_of_orders, select#woocommerce_crm_total_value').css('width', '400px').chosen();
    $('#woocommerce_crm_mailchimp').change(function(){
        $('#woocommerce_crm_mailchimp_api_key, #woocommerce_crm_mailchimp_list').closest('tr').hide();

        if ( $(this).attr('checked') ) {
            $('#woocommerce_crm_mailchimp_api_key, #woocommerce_crm_mailchimp_list').closest('tr').show();
        }
    }).change();

    if($('#customer_data #_billing_country').length > 0){
        $('a.edit_address').click(function () {
            $( this ).hide();
            $( this ).closest( '.order_data_column' ).find( 'div.address' ).hide();
            $( this ).closest( '.order_data_column' ).find( 'div.edit_address' ).show();
            return false;
        });
        $('#customer_data').on('change', '#_billing_country, #_shipping_country', function(){

            var country = $(this).val();
            var state   = $('#_billing_state').val();
            var id      = $(this).attr('id').replace('_countries', '');

            var data =  {
                action   : 'woocommerce_crm_loading_states',
                id       : id,
                security : wc_crm_customer_params.wc_crm_loading_states,
                country  : country,
                state    : state,
            };

            xhr = $.ajax({
              type:   'POST',
              url:    wc_crm_customer_params.ajax_url,
              data:   data,
              beforeSend: function(xhr) {
                    $('#customer_data').block({message: null, overlayCSS: {background: '#fff url(' + wc_crm_customer_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});
                },
                complete: function(xhr) {
                    if($(id == '_billing_country' && 'select#_billing_state').length > 0){
                        $('select#_billing_state').chosen();
                    }                

                    if($(id == '_shipping_country' && 'select#_shipping_state').length > 0){
                        $('select#_shipping_state').chosen(); 
                    }                

                    $('#customer_data').unblock()
                },
                success: function(response) {
                    var j_data = JSON.parse(response);
                    var html = $($.parseHTML($.trim(j_data.state_html)));
                    if(id == '_billing_country'){
                        $('#_billing_state').remove();
                        if($('#_billing_state_chosen').length > 0){
                            $('#_billing_state_chosen').remove();
                        }
                        $('label[for="_billing_state"]').after($(html));
                        $('label[for="_billing_state"]').html(j_data.state_label);
                        $('label[for="_billing_postcode"]').html(j_data.zip_label);
                        $('label[for="_billing_city"]').html(j_data.city_label);
                    }
                    if(id == '_shipping_country'){
                        $('#_shipping_state').remove();
                        if($('#_shipping_state_chosen').length > 0){
                            $('#_shipping_state_chosen').remove();
                        }
                        $('label[for="_shipping_state"]').after($(html));
                        $('label[for="_shipping_state"]').html(j_data.state_label);
                        $('label[for="_shipping_postcode"]').html(j_data.zip_label);
                        $('label[for="_shipping_city"]').html(j_data.city_label);
                    }
                }
            });
        });
    }

    if($('#customer_data #_shipping_country').length > 0){
        $('#customer_data').on('click', '#copy-billing-same-as-shipping', function(){
            var answer = confirm(wc_crm_customer_params.copy_billing);
            if (answer){
                $('#order_data_column_billing div.edit_address input, #order_data_column_billing div.edit_address select').each(function(){
                    var b_id  = $(this).attr('id');
                    if(typeof b_id !== typeof undefined && b_id !== false){
                        var b_val = $(this).val();
                        var id    = b_id.replace('_billing', '');
                        var s_id  = '_shipping'+id;
                        if($('#'+s_id).length > 0){
                            $('#'+s_id).val(b_val);
                        }
                    }                    
                });
                $('#order_data_column_shipping div.edit_address select').trigger( 'change' ).trigger( 'chosen:updated' );
            }
        });
    }


    if($('#related_to').length > 0){
        $('#related_to').change(function(){
            $('.related_by').hide();
            if($(this).val() == 'order') $('#related_by_order').show();
            if($(this).val() == 'product') $('#related_by_product').show();
        });
    }
    if($(".display_time").length > 0){
        var callTimer = new (function() {

        // Stopwatch element on the page
        var $stopwatch;

        // Timer speed in milliseconds
        var incrementTime = 60;

        // Current timer position in milliseconds
        var currentTime = 0;

        // Start the timer
        $(function() {
            $stopwatch = $('.display_time');
            callTimer.Timer = $.timer(updateTimer, incrementTime, false);
        });

        // Output time and increment
        function updateTimer() {
            formatTimeDuration(currentTime);
            var timeString = formatTime(currentTime);
            $stopwatch.html(timeString);
            currentTime += incrementTime;
        }

        // Reset timer
        this.resetStopwatch = function() {
            currentTime = 0;
            var timeString = formatTime(currentTime);
            $stopwatch.html(timeString);
            callTimer.Timer.stop();
            $('#stop_timer, #pause_timer, #reset_timer').hide().removeClass('play');
            $('.completed_call_wrap').hide();
            $('#start_timer').show();
        };

    });
        $('#start_timer').click(function(){
            callTimer.Timer.play();
            setCurrentTime();
            $('#stop_timer, #pause_timer, #reset_timer').show();
            $('#start_timer').hide();
            return false;
        });
        $('#stop_timer').click(function(){
            callTimer.Timer.stop();
            $('.completed_call_wrap').show();
            $('#pause_timer').removeClass('play').hide();
            return false;
        });
        $('#pause_timer').click(function(){
            $(this).toggleClass('play');
            callTimer.Timer.toggle();
            return false;
        });
        $('#reset_timer').click(function(){
            callTimer.resetStopwatch();
            return false;
        });

        $('#related_to').change(function(){
            var related_to = $('#related_to').val();
            $('#view_info').attr('href', '?page=wc-customer-relationship-manager&'+related_to+'_list='+related_to+'&order_id='+$('#order_id').val());
        });



        var prettyDate = wc_crm_params.curent_time;
        $("#call_date").val(prettyDate);
        $( "#call_date" ).datepicker({
            dateFormat: "yy-mm-dd",
            numberOfMonths: 1,
            showButtonPanel: true,
            maxDate: prettyDate,
            changeMonth: true,
            changeYear: true

        });


        $('#new_call').click(function(){
            if( $('#user_phone').val() == '' ){
                $( '.error_message', $('#user_phone').parent() ).text('Please enter user phone!').show();
                return false;
            }else if( !checkPhone($('#user_phone').val()) ){
                $( '.error_message', $('#user_phone').parent() ).text('Please enter valid phone number!').show();
                return false;
            }
            else{
                $( '.error_message', $('#user_phone').parent() ).hide();
            }
        });
        $('#wc_crm_customers_form').submit(function(){
            $('.error.below-h2').hide();
            $('.form-invalid').removeClass('form-invalid');
            var err = '';
            if( $('#subject_of_call').val() == '' ){
                var error_text = $( '.error_message', $('#subject_of_call').parent() ).html();
                err += '<p>'+error_text+'</p>';
                $('#subject_of_call').parents('tr').addClass('form-invalid');
            }
            if( $('#call_date').val() == '' && $('#call_date').is(':visible') ){
                var error_text = $( '.error_message', $('#call_date').parent() ).html();
                err += '<p>'+error_text+'</p>';
                $('#call_date').parents('tr').addClass('form-invalid');
            }
            var order_num = $('#number_order_product').val();
            order_num = order_num.replace('#', '') ;

            if( $('#related_to').val() == 'order' && order_num == '' ){
                var error_text = '<strong>ERROR</strong>: Please enter Order Number.';
                err += '<p>'+error_text+'</p>';
                $('#related_to').parents('tr').addClass('form-invalid');
            }
            if( $('#related_to').val() == 'product' && order_num == '' ){
                var error_text = '<strong>ERROR</strong>: Please enter Product Number.';
                err += '<p>'+error_text+'</p>';
                $('#related_to').parents('tr').addClass('form-invalid');
            }
            order_num = order_num.replace(/[0-9]/g, '') ;
            if( order_num != ''){
                var error_text = '<strong>ERROR</strong>: Please enter valid Number.';
                err += '<p>'+error_text+'</p>';
                $('#related_to').parents('tr').addClass('form-invalid');
            }
            if( $('#call_time_h').is(':visible') ){
                var h = $('#call_time_h').val();
                var m = $('#call_time_m').val();
                var s = $('#call_time_s').val();
                if(h=='' || m == '' || s==''){
                    var error_text = $( '.error_message', $('#call_time_h').parent() ).html();
                    err += '<p>'+error_text+'</p>';
                    $('#call_time_h').parents('tr').addClass('form-invalid');
                }
                else if( h.replace(/[0-9]/g, '')!='' || m.replace(/[0-9]/g, '')!='' || s.replace(/[0-9]/g, '')!='' || h>23 || m>59 || s>59){
                    var error_text = $( '.error_message', $('#call_time_h').parent() ).html();
                    err += '<p>'+error_text+'</p>';
                    $('#call_time_h').parents('tr').addClass('form-invalid');
                }
            }
            if( $('#call_duration_h').is(':visible') ){
                var d_h = $('#call_duration_h').val();
                var d_m = $('#call_duration_m').val();
                var d_s = $('#call_duration_s').val();
                if(d_h=='' || d_m == '' || d_s==''){
                    var error_text = $( '.error_message', $('#call_duration_h').parent() ).html();
                    err += '<p>'+error_text+'</p>';
                    $('#call_duration_h').parents('tr').addClass('form-invalid');
                }
                else if( d_h.replace(/[0-9]/g, '')!='' || d_m.replace(/[0-9]/g, '')!='' || d_s.replace(/[0-9]/g, '')!='' || d_h>23 || d_m>59 || d_s>59 ){
                    var error_text = $( '.error_message', $('#call_duration_h').parent() ).html();
                    err += '<p>'+error_text+'</p>';
                    $('#call_duration_h').parents('tr').addClass('form-invalid');
                }else if( d_h == 0 && d_m == 0 && d_s == 0 ){
                    var error_text = $( '.error_message', $('#call_duration_h').parent() ).html();
                    err += '<p>'+error_text+'</p>';
                    $('#call_duration_h').parents('tr').addClass('form-invalid');
                }
            }

            if(err != ''){
                $('.error.below-h2').html(err).show();
                return false;
            }
        });

        $('.call_details input').change(function(){
            var val = $(this).val();
            currentTime = 0;
            if(callTimer.Timer != undefined ) {
                callTimer.Timer.stop();

            }
            var timeString = formatTime(currentTime);
            $('.display_time').html(timeString);
            $('#stop_timer, #pause_timer, #reset_timer').hide().removeClass('play');
            $('#start_timer').show();
            if(val == 'completed_call'){
                $('.completed_call_wrap').removeClass('disabled').show();
                $('#current_call_wrap').hide();
                $('#call_time_h, #call_time_m, #call_time_s, #call_duration_h, #call_duration_m, #call_duration_s').val('');
            }else{
                $('.completed_call_wrap').addClass('disabled').hide();
                $('#current_call_wrap').show();
            }
        });
        $('#current_call').click();
    }
    if( $('#group_last_order_to').length > 0 ){
        $( "#group_last_order_to, #group_last_order_from" ).datepicker({
            dateFormat: "yy-mm-dd",
            numberOfMonths: 1,
            showButtonPanel: true,
            changeMonth: true,
            changeYear: true

        });
    }
    if( $('#customer_data #excerpt').length > 0 ){
        $('#customer_data #excerpt').closest('p').remove();
    }
    if( $('#wc_crm_edit_customer_form').length > 0 ){
        $('#wc_crm_edit_customer_form').submit(function(){
            var user_id = $('input#customer_user').val();
            var order_id = $('input#order_id').val();
            var action = $('#wc_crm_customer_action').val();
            if(user_id != '' && user_id != undefined){
                if(action == 'wc_crm_customer_action_new_order'){
                    var url = 'post-new.php?post_type=shop_order&user_id='+user_id;
                    window.open(url,'_self');
                    return false;
                }else if(action == 'wc_crm_customer_action_send_email'){
                    var url = 'admin.php?page=wc-customer-relationship-manager&action=email&user_id='+user_id;
                    window.open(url,'_blank');
                    return false;
                }else if(action == 'wc_crm_customer_action_phone_call'){
                    var url = 'admin.php?page=wc-customer-relationship-manager&action=phone_call&user_id='+user_id;
                    window.open(url,'_blank');
                    return false;
                }
            }else if(order_id != '' && order_id != undefined){
                if(action == 'wc_crm_customer_action_new_order'){
                    var url = 'post-new.php?post_type=shop_order&last_order_id='+order_id;
                    window.open(url,'_self');
                    return false;
                }else if(action == 'wc_crm_customer_action_send_email'){
                    var url = 'admin.php?page=wc-customer-relationship-manager&action=email&order_id='+order_id;
                    window.open(url,'_blank');
                    return false;
                }else if(action == 'wc_crm_customer_action_phone_call'){
                    var url = 'admin.php?page=wc-customer-relationship-manager&action=phone_call&order_id='+order_id;
                    window.open(url,'_blank');
                    return false;
                }
            }
        });
    }
    if( $('#date_of_birth').length > 0 ){
        $('#date_of_birth').datepicker({
            dateFormat: "yy-mm-dd",
            numberOfMonths: 1,
            showButtonPanel: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '-100y:c+nn',
            maxDate: '-1d'
        });

        $('.handlediv').click(function(){
            $(this).parent().toggleClass('closed');
        });
   }
   if( $('#f_group_type').length > 0){
        $('#f_group_type').change(function(){
            if( $(this).val() == 'dynamic'){
                $('.dynamic_group_type').show();
            }else{
                $('.dynamic_group_type').hide();
            }
        }).change();
   }
   if( $('#group_last_order').length > 0){
        $('#group_last_order').change(function(){
            if( $(this).val() == 'between'){
                $('.group_last_order_between').show();
            }else{
                $('.group_last_order_between').hide();
            }
        }).change();
   }
   if( $('#woocommerce-customer-notes').length > 0 ){
        // Customer notes
        $('#woocommerce-customer-notes').on( 'click', 'a.add_note_customer', function() {
            if ( ! $('textarea#add_order_note').val() ) return;

            $('#woocommerce-customer-notes').block({ message: null, overlayCSS: { background: '#fff url(' + wc_crm_customer_params.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
            var data = {
                action:         'woocommerce_crm_add_customer_note',
                user_id:        $('#customer_user').val(),
                note:           $('textarea#add_order_note').val()
            };

            $.post( wc_crm_customer_params.ajax_url, data, function(response) {
                $('ul.order_notes').prepend( response );
                $('#woocommerce-customer-notes').unblock();
                $('#add_order_note').val('');
            });

            return false;

        });
        $('#woocommerce-customer-notes').on( 'click', 'a.delete_customer_note', function() {
            var note = $(this).closest('li');
            $(note).block({ message: null, overlayCSS: { background: '#fff url(' + wc_crm_customer_params.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

            var data = {
                action:         'woocommerce_crm_delete_customer_note',
                note_id:        $(note).attr('rel'),
            };

            $.post( wc_crm_customer_params.ajax_url, data, function(response) {
                $(note).remove();
            });

            return false;
        });
    }

    jQuery('.fancybox').fancybox({
        'width'         : '75%',
        'height'        : '75%',
        'autoScale'     : false,
        'transitionIn'  : 'none',
        'transitionOut' : 'none',
        'type'          : 'iframe'
    });

    jQuery(".tips").tipTip({
        'attribute' : 'data-tip',
        'fadeIn' : 50,
        'fadeOut' : 50,
        'delay' : 200
    });


});
// Common functions
function pad(number, length) {
    var str = '' + number;
    while (str.length < length) {str = '0' + str;}
    return str;
}
function formatTime(time) {
    time = time / 10;
    var h   = parseInt(time / 360000),
        min = parseInt(time / 6000) - (h * 60),
        sec = parseInt(time / 100) - (h*60*60+min*60);
        hundredths = pad(time - (sec * 100) - (min * 6000), 2);
    return (h > 0 ? pad(h, 2) : "00") + ":" + ((min > 0 && min < 60) ? pad(min, 2) : "00") + ":" + pad(sec, 2) + ':' + hundredths;
}
function formatTimeDuration(time) {
     time = time / 10;
   var h   = parseInt(time / 360000),
        min = parseInt(time / 6000) - (h * 60),
        sec = parseInt(time / 100) - (h*60*60+min*60);
    document.getElementById("call_duration_h").value = h;
    document.getElementById("call_duration_m").value = min;
    document.getElementById("call_duration_s").value = sec;
}
function setCurrentTime() {
    document.getElementById("call_time_h").value = wc_crm_params.curent_time_h;
    document.getElementById("call_time_m").value = wc_crm_params.curent_time_m;
    document.getElementById("call_time_s").value = wc_crm_params.curent_time_s;
}
function isInt(n) {
   return typeof n === 'number' && n % 1 == 0;
}
function checkPhone(e){
    var number_count = 0;
    for(i=0; i < e.length; i++)
        if((e.charAt(i)>='0') && (e.charAt(i) <=9))
            number_count++;

    if (number_count == 10)
        return true;

    return false;
}

jQuery(document).ready(function($){
    if($('#customer_address_map_canvas').length > 0){
        $('#customer_address_map_canvas').gmap({
            zoom : 14,
            'zoomControl': true,
            'mapTypeControl' : false, 
            'navigationControl' : false,
            'streetViewControl' : false 
        }).bind('init', function() {
                $('#customer_address_map_canvas').gmap('search', { 'address': wc_pos_customer_formatted_billing_address }, function(results, status) {
                    console.log(results);
                        if ( status === 'OK' ) {
                            $('#customer_address_map_canvas').gmap('get', 'map').panTo(results[0].geometry.location); 
                            
                            $('#customer_address_map_canvas').gmap(
                                'addMarker',{'position': results[0].geometry.location, 'bounds': false });
                        }
                });
        });
    }
});