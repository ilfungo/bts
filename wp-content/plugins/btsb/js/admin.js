var DEBUG = true;

function log(a){
    if(DEBUG) console.log(a);
}

jQuery(function(){
    jQuery('.startBatch').on('click',function(){
        console.log(jQuery(this).data('input'));
        jQuery.post("http://btsb.bnj.xyz/wp-content/plugins/btsb/bg.php", { arg: jQuery(this).data('input') },
        function(data){ log(data); });
    });
});


