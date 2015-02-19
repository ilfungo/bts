<?php

$arg='';
if(isset($_REQUEST['arg'])) { $arg = $_REQUEST['arg']; } elseif (isset($argv[1])) { $arg = $argv[1]; } else { echo "Stopping"; return 0; }

echo "Starting ".$arg;

return exec('/usr/bin/php /home/federico/public_html/btsb.bnj.xyz/wp-content/plugins/btsb/batch.php '.$arg.'  > /dev/null &');


?>
