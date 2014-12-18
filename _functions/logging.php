<?php

/**
 * @ignore
 */
if (!defined('LOGOS'))
{
    exit;
}

/**
 * @ignore
 */
if (!defined('DB_LINK'))
{
    exit;
}

function log_stat($stat, $old, $new, $transaction = "", $comment = "", $trans){

    $change = $new - $old;
    
    $trans_id = md5($trans);
    
    $query = "INSERT INTO `log_stat` (`stat`, `old`,  `new`,  `change`, `transaction`, `comment`, `trans_id`) VALUES ('$stat', '$old', '$new', '$change', '$transaction', '$comment', '$trans_id')";
    
    if(DB::sql($query)){
        return true;
    }
    
    return false;
    
} 

?>