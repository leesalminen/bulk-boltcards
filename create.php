<?php

define("DOMAIN_NAME", "https://YOUR_LNBITS_DOMAIN");

function main($card_uid) {

	$card_uid = strtoupper(trim($card_uid));
	$card_uid_bin = hex2bin($card_uid);

	if(!$card_uid_bin) {
		throw new Exception("Card UID is not valid");
	}

	if(strlen($card_uid_bin) != 7) {
		throw new Exception("Card UID invalid length");
	}

	$output = [
		// just returning the card UID we output at the beginning
		'card_uid' => $card_uid,

		// lnbits user account details
		'lnbits_user_id' => null,
		'lnbits_wallet_id' => null,
		'lnbits_username' => null,
		'lnbits_admin_key' => null,

		// this is the url a user can use to access their account
		'lnbits_access_url' => null,

		// this is a bech32 encoded LNURLp string. Users can use this to receive sats in their wallet.
		'lnbits_lnurlp' => null,

		// this is a bech32 encoded LNURLw string. Users can use this to send sats from their wallet.
		'lnbits_lnurlw' => null,

		// this is all the details about the boltcard we created in LNBits.
		'lnbits_boltcard' => [
			// you'll turn the auth_link into a qr code that the person configuring the card scans with the android app `bolt-nfc-android-app`
			'auth_link' => null,

			'otp' => null,
			'k0' => null,
			'k1' => null,
			'k2' => null,
		],

		// this just validates that everything worked as intended. true means good. false means no bueno
		'lnurlp_activated' => false,
		'lnurlw_activated' => false,
		'boltcard_activated' => false,
	];

	$user = create_user(bin2hex(random_bytes(8)));
	$output['lnbits_user_id'] = $user['user_id'];
	$output['lnbits_wallet_id'] = $user['wallet_id'];
	$output['lnbits_username'] = $user['username'];
	$output['lnbits_admin_key'] = $user['admin_key'];
	$output['lnbits_access_url'] = DOMAIN_NAME . '/wallet?usr=' . $user['user_id'] . '&wal=' . $user['wallet_id'];

	$output['lnurlp_activated'] = enable_extension($user['user_id'], 'lnurlp');
	$output['lnbits_lnurlp'] = create_lnurlp_link($user['admin_key']);

	$output['lnurlw_activated'] = enable_extension($user['user_id'], 'withdraw');
	$lnurlw = create_lnurlw_link($user['admin_key']);
	$output['lnbits_lnurlw'] = $lnurlw['lnurl'];

	$output['boltcard_activated'] = enable_extension($user['user_id'], 'boltcards');
	$boltcard = create_boltcard($card_uid, $user['wallet_id'], $lnurlw['id'], $user['admin_key']);
	$output['lnbits_boltcard'] = $boltcard;
	$output['lnbits_boltcard']['auth_link'] = DOMAIN_NAME . '/boltcards/api/v1/auth?a=' . $boltcard['otp'];

	return $output;
}

function create_boltcard($card_uid, $wallet_id, $withdraw_id, $api_key) {

	$k0 = bin2hex(random_bytes(16));
	$k1 = bin2hex(random_bytes(16));
	$k2 = bin2hex(random_bytes(16));

	$request = request(
		"POST",
		"/boltcards/api/v1/cards",
		[],
		['X-Api-Key: ' . $api_key],
		[
		  'wallet' => $wallet_id,
		  'withdraw' => $withdraw_id,
		  'card_name' => 'My First Card',
		  'uid' => $card_uid,
		  'k0' => $k0,
		  'k1' => $k1,
		  'k2' => $k2,
		  'counter' => 0,
		]
	);

	if($request['status'] != 201) {
		throw new Exception("Error creating boltcard");
	}

	return [
		'otp' => $request['response']['otp'],
		'k0' => $k0,
		'k1' => $k1,
		'k2' => $k2,
	];
}

function create_lnurlw_link($api_key) {

	$request = request(
		"POST",
		"/withdraw/api/v1/links",
		[],
		['X-Api-Key: ' . $api_key],
		[
		  'title' => 'My Withdraw Link',
		  'min_withdrawable' => 10,
		  'max_withdrawable' => 100000,
		  'uses' => 250,
		  'wait_time' => 1,
		  'is_unique' => true,
		]
	);

	if($request['status'] != 201) {
		throw new Exception("Error creating lnurlw link");
	}

	return $request['response'];
}

function create_lnurlp_link($api_key) {

	$request = request(
		"POST",
		"/lnurlp/api/v1/links",
		[],
		['X-Api-Key: ' . $api_key],
		[
		  'description' => 'My Pay Link',
		  'min' => 1,
		  'max' => 10000000,
		  'comment_chars' => 255,
		]
	);

	if($request['status'] != 201) {
		throw new Exception("Error creating lnurlp link");
	}

	return $request['response']['lnurl'];
}

function enable_extension($user_id, $extension) {
	$request = request(
		'GET', 
		'/extensions', 
		[
			'usr' => $user_id,
			'enable' => $extension,
		]
	);

	if($request['status'] != 200) {
		throw new Exception("Error enabling extension.");
	}

	return true;
}

function create_user($username) {
	$request = request('GET', '/wallet', ['nme' => $username], ['Accept: text/html']);

	if($request['status'] == 307) {
		$full_url = DOMAIN_NAME . $request['headers']['location'][0];
		$url_components = parse_url($full_url);
		$params = [];

		parse_str($url_components['query'], $params);

		$page_response = request("GET", $request['headers']['location'][0]);

		preg_match('/<strong>Admin key: <\/strong><em>(.*)<\/em><br \/>/', $page_response['raw_response'], $matches);

		if(!isset($matches[1]) || empty($matches[1])) {
			throw new Exception("Unable to parse Admin API Keys");
		}

		return [
			'user_id' => $params['usr'],
			'wallet_id' => $params['wal'],
			'admin_key' => $matches[1],
			'username' => $username,
		];
	}
}


function request($method, $url, $params = [], $headers = [], $body = []) {
	// placeholder for curl request response headers
	$response_headers = [];

	if(count($params)) {
		$url = $url . '?' . http_build_query($params);
	}

	// get cURL resource
	$ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, DOMAIN_NAME . $url);

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

	if($method == "POST" && count($body)) {
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


// OK, let's run everything!
try {
	if(empty($argv) || empty($argv[1])) {
		throw new Exception("Must provide a card UID.");
	}

	$data = main($argv[1]);

	echo json_encode($data);

} catch (Exception $e) {
	echo $e->getMessage() . "\n";
}