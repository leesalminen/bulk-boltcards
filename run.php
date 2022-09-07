<?php


   //echo "111";
   $session=$_REQUEST['session'];
   $out=trim(`./run.sh html $session`);
   echo "$out";
   
?>