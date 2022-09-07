#! /bin/bash

out=$3

if [ -z "$out" ]
then
  out="/dev/stdout"
fi  

#first run the PHP script with the card UID as the input parameter and capture the response
json_data=$(php -f create.php $2)

# if there was an error in the PHP script generating everything, the respnse will start with ERROR
if [[ $json_data == ERROR* ]]
then
	echo $json_data
	exit
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
cmd+="' ./template_ptbr.html"

# run the sed find/replace + base64 encode
html_data=$(eval $cmd)

chrome_string="data:text/html;base64,$html_data"

# pass the base64 encoded HTML into the chrome address bar for local rendering.
if [[ $1 == "mac" ]]
then
	open -a "Google Chrome.app" "data:text/html;base64,$(echo $html_data | base64)" --args --incognito
elif [[ $1 == "linux" ]]
then
    f=$(mktemp --suffix .html)
    echo "$html_data" > "$f"
    #echo "$json_data" > "$f.json"
    google-chrome "$f" --no-sandbox
    shred -u "$f"
elif [[ $1 == "pdf" ]]
then
    f=$(mktemp --suffix .html)
    echo "$html_data" > "$f"
    google-chrome --headless --disable-gpu --no-margins --print-to-pdf-no-header --run-all-compositor-stages-before-draw --print-to-pdf="$f.pdf" "$f" --no-sandbox
    shred -u "$f" 
    cat  "$f.pdf"
    shred -u  "$f.pdf"
elif [[ $1 == "html" ]]
then
   echo "$html_data" > "$out"
fi
