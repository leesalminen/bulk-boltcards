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

# build out the sed s/find/replace string
str='s|SCRIPT_WILL_REPLACE_ME|'
str+=$json_base64
str+='|'

# build out the sed command
# inject the json_base64 into the HTML template then base64 encode all the HTML
cmd="sed '"
cmd+=$str
cmd+="' ./template_ptbr.html | base64"

# run the sed find/replace + base64 encode
html_data=$(eval $cmd)

chrome_string="data:text/html;base64,$html_data"

# pass the base64 encoded HTML into the chrome address bar for local rendering.
if [[ $1 == "mac" ]]
then
	open -a "Google Chrome.app" $chrome_string --args --incognito
else
	echo $chrome_string > a1.html
	google-chrome a1.html
fi
