#/bin/sh

json_data=$(php -f create.php $2)

if [[ $json_data == ERROR* ]]
then
	echo $json_data
	exit
fi

json_base64=$(echo $json_data | base64)
html_data=$(sed "s|SCRIPT_WILL_REPLACE_ME|$json_base64|" ./template2.html | base64)

if [[ $1 == "mac" ]]
then
	open -a "Google Chrome.app" "data:text/html;base64,$html_data" --args --incognito
else
	google-chrome "data:text/html;base64,$html_data" --incognito
fi