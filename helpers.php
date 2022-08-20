<?php

require_once 'requests.php';

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