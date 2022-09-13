<?php

   $session=trim(isset($_REQUEST['session'])?$_REQUEST['session']:"");
   $output=trim(isset($_REQUEST['output'])?$_REQUEST['output']:"");
   if($output=="")
     $output="html";
   
   //you can send base encoded certificate to pass to gpg 
   $certificate=trim(isset($_REQUEST['certificate'])?$_REQUEST['certificate']:"");

   if($session=="")
     $session=shell_exec("./random_card.sh");

   //header("Content-Type: text/plain");
   //echo $output;
   //exit;

   //setup main command
   $cmdexec="./run.sh $output $session";
   $temp="";
   if ( $certificate!="" ) {
      $dir="/tmp/.gpg-" . getenv('USER');
      $cmd="GNUPGHOME=$dir";
      putenv($cmd);
      
      //setup gpg dummy dir 
      if ( ! file_exists($dir) ) {
          mkdir ( $dir , 0700 );
          $x=`gpg-connect-agent "KILLAGENT" /bye`;
      }
       
      $temp=trim(`mktemp`);

      //puth gpg to output main command; 
      $cmdexec.=" | gpg -e -z 9 --recipient-file $temp -a";
      
      //decode and save certificate 
      //file_put_contents($temp,base64_decode($certificate));      
      file_put_contents($temp,$certificate);
      //set header as plain text
      //header("Content-Type: text/plain"); 
      header("Content-Disposition: attachment; filename=\"card-$session.$output.asc\""); 
   } else if($output != 'html' )
      header("Content-Type: application/$output"); 

   //run main command
   $out=trim(shell_exec($cmdexec));
   
   //delete temp certificate file
   if($temp!="") 
      unlink($temp);

   //send output to user 
   echo "$out";
   
?>