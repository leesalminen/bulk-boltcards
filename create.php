<?php

// MAKE SURE YOU CHANGE THE VALUES IN constants.php!!
require_once 'constants.php';

// various helper functions to interface with LNBits
require_once 'helpers.php';

// import qr code lib
include "./lib/full/qrlib.php"; 

// the main function
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
		'lnbits_access_url_qr_svg' => null,

		// this is a bech32 encoded LNURLp string. Users can use this to receive sats in their wallet.
		'lnbits_lnurlp' => null,
		'lnbits_lnurlp_qr_svg' => null,

		// this is a bech32 encoded LNURLw string. Users can use this to send sats from their wallet.
		'lnbits_lnurlw' => null,
		'lnbits_lnurlw_qr_svg' => null,

		// this is all the details about the boltcard we created in LNBits.
		'lnbits_boltcard' => [
			// you'll turn the auth_link into a qr code that the person configuring the card scans with the android app `bolt-nfc-android-app`
			'auth_link' => null,
			'auth_link_qr_svg' => null,

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
	$output['lnbits_access_url_qr_svg'] = QRcode::svg($output['lnbits_access_url'], uniqid(), false, QR_ECLEVEL_L, 250); 

	$output['lnurlp_activated'] = enable_extension($user['user_id'], 'lnurlp');
	$output['lnbits_lnurlp'] = create_lnurlp_link($user['admin_key']);
	$output['lnbits_lnurlp_qr_svg'] = QRcode::svg($output['lnbits_lnurlp'], uniqid(), false, QR_ECLEVEL_L, 250); 

	$output['lnurlw_activated'] = enable_extension($user['user_id'], 'withdraw');
	$lnurlw = create_lnurlw_link($user['admin_key']);
	$output['lnbits_lnurlw'] = $lnurlw['lnurl'];
	$output['lnbits_lnurlw_qr_svg'] = QRcode::svg($output['lnbits_lnurlw'], uniqid(), false, QR_ECLEVEL_L, 250); 

	$output['boltcard_activated'] = enable_extension($user['user_id'], 'boltcards');
	$boltcard = create_boltcard($card_uid, $user['wallet_id'], $lnurlw['id'], $user['admin_key']);
	$output['lnbits_boltcard'] = $boltcard;
	$output['lnbits_boltcard']['auth_link'] = DOMAIN_NAME . '/boltcards/api/v1/auth?a=' . $boltcard['otp'];
	$output['lnbits_boltcard']['auth_link_qr_svg'] = QRcode::svg($output['lnbits_boltcard']['auth_link'], uniqid(), false, QR_ECLEVEL_L, 1000); 

	return $output;
}


// OK, let's run everything!
try {
	if(empty($argv) || empty($argv[1])) {
		throw new Exception("Must provide a card UID.");
	}

	// Card UID should live in position 1
	$card_uid = main($argv[1]);

	echo json_encode($card_uid);

} catch (Exception $e) {
	echo $e->getMessage() . "\n";
}