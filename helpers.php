<?php

require_once 'requests.php';

function create_lnaddress($username, $wallet_admin_key) {
	if(LNADDRESS_ADMIN_KEY == "" || LNADDRESS_DOMAIN_ID == "") {
		return false;
	}
	
	$request = request(
		"POST",
		"/lnaddress/api/v1/address/" . LNADDRESS_DOMAIN_ID,
		[],
		['X-Api-Key: ' . LNADDRESS_ADMIN_KEY],
		[
			'username' => $username,
			'duration' => 100000,
			'sats' => 0,
			'wallet_key' => $wallet_admin_key,
			'domain' => LNADDRESS_DOMAIN_ID,
			'wallet_endpoint' => DOMAIN_NAME,
		]
	);

	if($request['status'] != 200) {
		throw new Exception("Error creating LNAddress");
	}

	return true;
}

function create_tipjar($wallet_id, $watchonly_id, $api_key) {
	$request = request(
		"POST",
		"/tipjar/api/v1/tipjars",
		[],
		['X-Api-Key: ' . $api_key],
		[
			'wallet' => $wallet_id,
			'chain' => true,
			'onchain' => $watchonly_id,
			'name' => 'My Tip Jar',
		]
	);

	if($request['status'] != 200) {
		throw new Exception("Error creating tipjar");
	}

	return $request['response']['id'];
}

function create_watchonly($zpub, $api_key) {
	$config_request = request(
		"GET",
		"/watchonly/api/v1/config",
		[],
		['X-Api-Key: ' . $api_key],
	);

	$request = request(
		"POST",
		"/watchonly/api/v1/wallet",
		[],
		['X-Api-Key: ' . $api_key],
		[
			'masterpub' => $zpub,
			'network' => 'Mainnet',
			'title' => 'My On-Chain Wallet',
			'is_unique' => false,
		]
	);

	if($request['status'] != 200) {
		throw new Exception("Error creating watch only wallet");
	}

	return $request['response']['id'];
}

function create_boltcard($card_uid, $wallet_id, $api_key) {

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
		  'card_name' => 'My First Card',
		  'uid' => $card_uid,
		  'k0' => $k0,
		  'k1' => $k1,
		  'k2' => $k2,
		  'counter' => 0,
		  'tx_limit' => "100000",
		  'daily_limit' => "1000000",
		]
	);

	if($request['status'] != 201) {
		throw new Exception("Error creating boltcard. THE card_uid MUST BE GLOBALLY UNIQUE IN LNBITS.");
	}

	return [
		'otp' => $request['response']['otp'],
		'k0' => $k0,
		'k1' => $k1,
		'k2' => $k2,
	];
}

function create_tpos($wallet_id, $api_key, $fiat_currency) {
	$request = request(
		"POST",
		"/tpos/api/v1/tposs",
		[],
		['X-Api-Key: ' . $api_key],
		[
			'wallet' => $wallet_id,
			'name' => 'My Point of Sale',
			'currency' => $fiat_currency,
			'tip_wallet' => $wallet_id,
			'tip_options' => json_encode([
				10,
				15,
				20,
			]),
		]
	);

	if($request['status'] != 201) {
		throw new Exception("Error creating TPoS");
	}

	return $request['response']['id'];
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
	$request = request(
		'GET', 
		'/wallet', 
		[
			'nme' => $username
		], 
		['Accept: text/html']
	);

	if($request['status'] != 307) {
		throw new Exception("Error creating user.");
	}

	$full_url = DOMAIN_NAME . $request['headers']['location'][0];
	$url_components = parse_url($full_url);
	$params = [];

	parse_str($url_components['query'], $params);

	$page_response = request("GET", $request['headers']['location'][0]);

	if($page_response['status'] != 200) {
		throw new Exception("Error loading wallet page to parse keys.");
	}

	preg_match('/<strong>Admin key: <\/strong><em>(.*)<\/em><br \/>/', $page_response['raw_response'], $admin_matches);

	if(!isset($admin_matches[1]) || empty($admin_matches[1])) {
		throw new Exception("Unable to parse Admin API Keys");
	}

	preg_match('/<strong>Invoice\/read key: <\/strong><em>(.*)<\/em>/', $page_response['raw_response'], $invoice_matches);

	if(!isset($invoice_matches[1]) || empty($invoice_matches[1])) {
		throw new Exception("Unable to parse Invoice API Keys");
	}

	return [
		'user_id' => $params['usr'],
		'wallet_id' => $params['wal'],
		'admin_key' => $admin_matches[1],
		'invoice_key' => $invoice_matches[1],
		'username' => $username,
	];
}

function create_qr($string, $size = 110) {
	return QRcode::svg($string, uniqid(), false, QR_ECLEVEL_L, $size);
}