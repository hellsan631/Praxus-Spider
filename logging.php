<?php

    function addLog($string, $filename){

        $string = $string."\n";

       return file_put_contents ($filename, $string);

    }

?>