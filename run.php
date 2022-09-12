<?php

   $session=isset($_REQUEST['session'])?$_REQUEST['session']:"";
   $output=isset($_REQUEST['output'])?$_REQUEST['output']:"html";
   
   //you can send base encoded certificate to pass to gpg 
   $certificate=isset($_REQUEST['certificate'])?$_REQUEST['certificate']:null;
  
   //setup main command
   $cmdexec="./run.sh $output $session";
   $temp="";
   if ( isset($certificate) ) {
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
      file_put_contents($temp,base64_decode($certificate));      
      
      //set header as plain text
      //header("Content-Type: text/plain"); 
      header("Content-Disposition: attachment; filename=\"card-$session.asc\""); 
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