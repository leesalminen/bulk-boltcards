#/bin/bash

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

str='s|SCRIPT_WILL_REPLACE_ME|'
str+=$json_base64
str+='|'

cmd="sed '"
cmd+=$str
cmd+="' ./template_ptbr.html | base64"

# inject the json_base64 into the HTML template then base64 encode all the HTML
html_data=$(eval $cmd)

# pass the base64 encoded HTML into the chrome address bar for local rendering.
if [[ $1 == "mac" ]]
then
	open -a "Google Chrome.app" "data:text/html;base64,$html_data" --args --incognito
else
	chromium-browser "data:text/html;base64,$html_data" --incognito
fi