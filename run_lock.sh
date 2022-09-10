#! /bin/bash

if [ -f "./constants.sh" ]
then
  . ./constants.sh
fi

CMD=$1

if [ -z $CMD ]
then
   CMD=$DEFAULTCMD
   if [ -z $CMD ]
   then
      CMD=html
   fi  
fi   

if [ -z "$TEMPLATE" ]
then
   TEMPLATE="./template.html"
fi

if [ ! -f "$TEMPLATE" ]
then
   echo "ERROR: template $TEMPLATE does not exists"
   exit 1
fi   

out=$3

if [ -z "$out" ]
then
  out="/dev/stdout"
fi  

id=$2

if [ -z "$id" ]
then
   id=$(dd if=/dev/random bs=1 count=7 status=none|xxd -ps)
fi

#first run the PHP script with the card UID as the input parameter and capture the response
json_data=$( php -f create.php $id)

#if there was a timeout waiting for lock, bailout 
if [ -z "$json_data" ]
then
   echo "ERROR: Timeout while waiting for lock"
   exit 1
fi 

# if there was an error in the PHP script generating everything, the respnse will start with ERROR
if [[ $json_data == ERROR* ]]
then
	echo $json_data
	exit 1
fi

# convert the json output from PHP to base64
json_base64=$(printf "%s" "$json_data" | base64)

# build out the sed s/find/replace string
str='s|SCRIPT_WILL_REPLACE_ME|'
str+=$json_base64
str+='|'

# build out the sed command
# inject the json_base64 into the HTML template then base64 encode all the HTML
cmd="sed '"
cmd+=$str
cmd+="' $TEMPLATE"

# run the sed find/replace + base64 encode
html_data=$(eval $cmd)

chrome_string="data:text/html;base64,$html_data"

# pass the base64 encoded HTML into the chrome address bar for local rendering.
if [[ $CMD == "mac" ]]
then
	open -a "Google Chrome.app" "data:text/html;base64,$(echo $html_data | base64)" --args --incognito
elif [[ $CMD == "linux" ]]
then
    f=$(mktemp --suffix .html)
    echo "$html_data" > "$f"
    #echo "$json_data" > "$f.json"
    chromium "$f" --no-sandbox
    shred -u "$f"
elif [[ $CMD == "pdf" ]]
then
    f=$(mktemp --suffix .html)
    echo "$html_data" > "$f"
    chromium --headless --disable-gpu --no-margins --print-to-pdf-no-header --run-all-compositor-stages-before-draw --print-to-pdf="$f.pdf" "$f" --no-sandbox
    shred -u "$f" 
    cat  "$f.pdf" > "$out"    
    shred -u  "$f.pdf"
elif [[ $CMD == "html" ]]
then
   echo "$html_data" > "$out"
else
   echo "ERROR: Command is not valid."
   exit 1
fi
exit 0
