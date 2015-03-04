var DEBUG = true;

function log(a){
    if(DEBUG) console.log(a);
}

jQuery(function(){
    jQuery('.btsb-plugin').on('click','.startBatch',function(){
        //log(jQuery(this).data('input'));
        jQuery.post("http://btsb.bnj.xyz/wp-content/plugins/btsb/schedule.php", { arg: jQuery(this).data('input') },
        function(data){
            jQuery('.table').load(window.location.href + " .table > *", { arg: jQuery(this).data('input') },
                function(data){
                    //log(data);
            });
        });
    });

    jQuery('.btsb-plugin').on('click','.startSingle',function(){
        //log(jQuery(this).data('input'));
        jQuery(this).html('Filtering...');
        jQuery.post("http://btsb.bnj.xyz/wp-content/plugins/btsb/bg.php", { arg: jQuery(this).data('input') },
        function(data){
            
            console.log(data);
            //jQuery('.table').load(window.location.href + " .table > *", { arg: jQuery(this).data('input') },
            //    function(data){
            //        log(data);
            //});
        });
    });


    jQuery('.btsb-plugin').on('click', '.showLog', function(){
        //log(jQuery(this).data('input'));
        jQuery.post("http://btsb.bnj.xyz/wp-content/plugins/btsb/log/batch."+jQuery(this).data('input')+".log",
        function(data){ jQuery('.log').html('<pre>'+data.split('\n').reverse().join('\n')+'</pre>'); });
    });

});


