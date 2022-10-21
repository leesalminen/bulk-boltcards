<?php

function request($method, $url, $params = [], $headers = [], $body = []) {
	// placeholder for curl request response headers
	$response_headers = [];

	if(count($params)) {
		$url = $url . '?' . http_build_query($params);
	}

	// get cURL resource
	$ch = curl_init();

	if(strpos($url, 'https://') === false) {
		$url = DOMAIN_NAME . $url;
	}

	// set url
	curl_setopt($ch, CURLOPT_URL, $url);
    
        if( defined('SOCKS5') ) {
	 curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
	 curl_setopt($ch, CURLOPT_PROXY, SOCKS5);	    
	}

	// set method
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

	curl_setopt($ch, CURLOPT_HEADER, 1);

	// this function is called by curl for each header received
	curl_setopt($ch, CURLOPT_HEADERFUNCTION,
	  function($curl, $header) use (&$response_headers)
	  {
	    $len = strlen($header);
	    $header = explode(':', $header, 2);
	    if (count($header) < 2) // ignore invalid headers
	      return $len;

	    $response_headers[strtolower(trim($header[0]))][] = trim($header[1]);
	    
	    return $len;
	  }
	);

	// return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// we should set the body to json for put/post requests
	if(in_array($method, ["PUT", "POST"]) && count($body)) {
		$body = json_encode($body);
		// set body
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

		$headers[] = 'Content-Type: application/json';
	}

	// set request headers
	if(count($headers)) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}

	// send the request and save response to $response
	$response = curl_exec($ch);

	// stop if fails
	if (!$response) {
		throw new Exception('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
	}

	$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$response_body = substr($response, $header_size);

	// close curl resource to free up system resources 
	curl_close($ch);

	return [
		'status' => $status_code,
		'headers' => $response_headers,
		'response' => json_decode($response_body, true),
		'raw_response' => $response,
	];
}