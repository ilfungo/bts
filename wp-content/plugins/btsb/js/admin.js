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
        $this=jQuery(this);
        jQuery(this).html('Filtering');
        jQuery.post("http://btsb.bnj.xyz/wp-content/plugins/btsb/bg.php", { arg: $this.data('input') },
        function(data){
            tmr=45;


            to=setInterval(function(){
                strPoints='';
                points = 45 - tmr;

                for(i=1; i<points % 4; i++) strPoints+=".";



                if( points < 10 ) strFilter='Filtering'; else if ( points < 20 ) strFilter='Still filtering'; else if ( points < 30 ) strFilter='Nearly finished'; else strFilter='Praying';
                $this.html(strFilter+strPoints);
                console.log($this.data('output'));
                jQuery.post("http://btsb.bnj.xyz/wp-content/plugins/btsb/checkfile.php", { file: $this.data('output') },
                function(data){
                    console.log(data);
                    tmr--;
                    if(data==1) {clearInterval(to); $this.closest('tr').removeClass('not-done').addClass('done'); $this.parent().html('Yes!'); }
                    else if(tmr<=0) {
                        clearInterval(to);
                        $this.html("Error. Retry.");
                    }
                });
            },1000);
        });
    });


    jQuery('.btsb-plugin').on('click', '.showLog', function(){
        $this=jQuery(this);
        scuola=$this.closest('tr').attr('class').split(' ').pop(); //console.log(scuola);
        log=$this.closest('.table').find('.'+scuola+ ' .log'); //console.log(log);
        if(log.is(":visible")){
            $this.html('Show School Log'); log.fadeToggle();
        } else{
            $this.html('Loading...');
            jQuery.post("http://btsb.bnj.xyz/wp-content/plugins/btsb/log/batch."+jQuery(this).data('input')+".log",
            function(data){ 
                log.html('<pre>'+data.split('\n').reverse().join('\n')+'</pre>'); 
                log.fadeToggle(); $this.html('Hide School Log'); 
            }).fail(function() {
                $this.html('Log non presente.');
  }         );
        }

    });


    jQuery('.btsb-plugin .classe .label').on('click', function (e) {
        jQuery(this).parent().children('.table').fadeToggle();
    });

});


