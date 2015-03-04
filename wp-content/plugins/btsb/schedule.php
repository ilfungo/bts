<?php


if(isset($_REQUEST['arg'])){
    return file_put_contents('batch.schedule', $_REQUEST['arg']);
}

?>
