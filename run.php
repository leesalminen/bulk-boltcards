<?php


   //echo "111";
   $session=$_REQUEST['session'];
   $output=$_REQUEST['output'];
   if (! isset ( $output ) )
      $output = 'html';

   header("Content-Type: application/$output");

   $out=trim(`./run.sh $output $session`);
   echo "$out";
   
?>