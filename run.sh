#/bin/sh

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

# inject the json_base64 into the HTML template
html_data=$(sed "s|SCRIPT_WILL_REPLACE_ME|$json_base64|" ./template2.html | base64)

#open up this template HTML as a base64 encoded string
if [[ $1 == "mac" ]]
then
	open -a "Google Chrome.app" "data:text/html;base64,$html_data" --args --incognito
else
	google-chrome "data:text/html;base64,$html_data" --incognito
fi