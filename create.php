<?php

// MAKE SURE YOU CHANGE THE VALUES IN constants.php!!
require_once 'constants.php';

// various helper functions to interface with LNBits
require_once 'helpers.php';

// import qr code lib
require_once "./lib/qrcode/qrlib.php"; 

// import bip39 mnemonic generator
require_once "./lib/bip39-mnemonic/Exception/BIP39_Exception.php";
require_once "./lib/bip39-mnemonic/Exception/MnemonicException.php";
require_once "./lib/bip39-mnemonic/Exception/WordListException.php";
require_once "./lib/bip39-mnemonic/WordList.php";
require_once "./lib/bip39-mnemonic/Mnemonic.php";
require_once "./lib/bip39-mnemonic/BIP39.php";
use \FurqanSiddiqui\BIP39\Wordlist;
use \FurqanSiddiqui\BIP39\BIP39;

// the main function
function main($card_uid) {
  // clean up the user input
  $card_uid = strtoupper(trim($card_uid));

  // card UIDs must be even length, or hex2bin() will complain
  if(strlen($card_uid) % 2 !== 0) {
  	throw new Exception("Card UID invalid length"); 
  }

  // convert the hex input
  $card_uid_bin = hex2bin($card_uid);

  // bad conversion, user input sucks
  if(!$card_uid_bin) {
  	throw new Exception("Card UID is not valid");
  }

  // card UIDs are always 7 bytes
  if(strlen($card_uid_bin) != 7) {
  	throw new Exception("Card UID invalid length");
  }

  // set the wordlist language for onchain generation
  switch (LANGUAGE) {
    case 'en':
    $wordlist_language = Wordlist::English();
    break;

    case 'es':
    $wordlist_language = Wordlist::Spanish();
    break;

    case 'pt':
    $wordlist_language = Wordlist::Portuguese();
    break;

    default:
    throw new Exception("Invalid language selection");
    break;
  }

  // generate the on-chain mnemonic phrase
  $mnemonic = (new BIP39(24))
  ->generateSecureEntropy() 
  ->wordlist($wordlist_language)
  ->mnemonic();

  // take that mnemonic phrase and derive a zpub, & bech32 encoded address from path m/0'/0'/0'
  $onchain_info = json_decode(shell_exec(
    './lib/hd-wallet-derive/hd-wallet-derive.php --mnemonic="' . 
    implode(" ", $mnemonic->words) . 
    '" -g --numderive=1 --key-type=z ' .
    ' --cols=all --format=json --includeroot'
  ));
  $onchain_info2 = json_decode(shell_exec(
    './lib/hd-wallet-derive/hd-wallet-derive.php --mnemonic="' .
    implode(" ", $mnemonic->words) .
    '" -g --numderive=1 --key-type=z --path="m/84\'/0\'/x\'"' .
    ' --cols=all --format=json --includeroot'
  ));

  // this is the output of the script.
  // everything here will be passed into the template.html 
  // you can then use these variables in JavaScript to inject into the template.
  $output = [
    // just returning the card UID we output at the beginning
    'card_uid' => $card_uid,

    // used for rendering
    'timestamp' => date('M j, Y'),

    // you can set your own name in constants.php
    'issuer' => ISSUER_NAME,
    'language' => LANGUAGE,
    'server_ip_address' => SERVER_IP_ADDRESS,
    'server_location' => SERVER_LOCATION,
    'server_tor_address' => SERVER_TOR_ADDRESS,
    'server_public_key' => SERVER_PUBLIC_KEY,
    'server_qr_svg' => null,
    
    'telegram_bot_url' => TELEGRAM_BOT_URL,
    'telegram_bot_url_qr_svg' => null,

    'local_map' => LOCAL_MAP,
    'local_map_qr_svg' => null,

    'bolt_generator_code' => BOLT_GENERATOR_CODE,
    'bolt_generator_code_qr_svg' => null,

    'implementation_guide' => IMPLEMENTATION_GUIDE,
    'implementation_guide_qr_svg' => null,

    // you can set this to whatever you want in constants.php
    'support_url' => SUPPORT_URL,
    'support_url_qr_svg' => null,
    'support_cost_per_sat' => SUPPORT_COST_PER_SAT,

    'localization' => null,

    // all the details about the onchain wallet we generated
    'onchain' => [
      'path' => $onchain_info[1]->path,

      'mnemonic' => $mnemonic->words,
      'words' => implode(" ", $mnemonic->words),		  
      'bip39' => $mnemonic->words,
      'bip39_qr_svg' => null,

      'address' => $onchain_info[1]->address,
      'address_qr_svg' => null,
  
      'zpub' => $onchain_info2[1]->xpub,
      'zpub_qr_svg' => null,
    ],

    // lnbits user account details
    'lnbits_user_id' => null,
    'lnbits_wallet_id' => null,
    'lnbits_username' => null,
    'lnbits_invoice_key' => null,
    'lnbits_invoice_key_qr_svg' => null,
    'lnbits_admin_key' => null,
    'lnbits_admin_key_qr_svg' => null,

    // this is the url a user can use to access their account
    'lnbits_access_url' => null,
    'lnbits_access_url_qr_svg' => null,

    // this is a bech32 encoded LNURLp string. Users can use this to receive sats in their wallet.
    'lnbits_lnurlp' => null,
    'lnbits_lnurlp_qr_svg' => null,

    // this is a bech32 encoded LNURLw string. Users can use this to send sats from their wallet.
    'lnbits_lnurlw' => null,
    'lnbits_lnurlw_qr_svg' => null,
    'lnbits_lnurlw_max_uses' => null,

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

    // these are the details needed to connect your LNBits Wallet to BlueWallet on mobile
    'lnbits_lndhub' => [
      'invoice_url' => null,
      'invoice_url_qr_svg' => null,

      'admin_url' => null,
      'admin_url_qr_svg' => null,
    ],

    // this is your Lightning Address, that is just a pretty way of writing a LNURL
    // TODO :: they gotta fix the extension
    'lnbits_lnaddress' => null,
    'lnbits_lnaddress_qr_svg' => null,

    // This is your point of sale for your wallet
    'lnbits_tpos_url' => null,
    'lnbits_tpos_url_qr_svg' => null,

    // This is your tip link for your wallet
    'lnbits_tipjar_url' => null,
    'lnbits_tipjar_url_qr_svg' => null,

    // this just validates that everything worked as intended. true means good. false means no bueno
    'lnurlp_activated' => false,
    'lnurlw_activated' => false,
    'boltcard_activated' => false,
    'lndhub_activated' => false,
    'tpos_activated' => false,
    'watchonly_activated' => false,
    'tipjar_activated' => false,

    // TODO :: implement this extension once PR is approved that fixes things
    'lnaddress_activated' => false,
  ];

  $localization = file_get_contents('./translations/' . LANGUAGE . '.json');
  $output['localization'] = json_decode($localization);

  $output['server_qr_svg'] = create_qr($output['server_public_key'] . '@' . $output['server_ip_address'] . ':9735');

  $output['support_url_qr_svg'] = create_qr($output['support_url']);

  $output['telegram_bot_url_qr_svg'] = create_qr($output['telegram_bot_url']);
    
  $output['local_map_qr_svg'] = create_qr($output['local_map']);
  $output['bolt_generator_code_qr_svg'] = create_qr($output['bolt_generator_code']);
  $output['implementation_guide_qr_svg'] = create_qr($output['implementation_guide']);
  
  
  $output['onchain']['bip39'] = $mnemonic;
  $output['onchain']['bip39_qr_svg'] = create_qr(implode(' ', $output['onchain']['mnemonic']));
  $output['onchain']['zpub_qr_svg'] = create_qr($output['onchain']['zpub']);
  $output['onchain']['address_qr_svg'] = create_qr($output['onchain']['address']);

  $user = create_user($card_uid);
  $output['lnbits_user_id'] = $user['user_id'];
  $output['lnbits_wallet_id'] = $user['wallet_id'];
  $output['lnbits_username'] = $user['username'];
  $output['lnbits_admin_key'] = $user['admin_key'];
  $output['lnbits_admin_key_qr_svg'] = create_qr($output['lnbits_admin_key']);
  $output['lnbits_invoice_key'] = $user['invoice_key'];
  $output['lnbits_invoice_key_qr_svg'] = create_qr($output['lnbits_invoice_key']);
  $output['lnbits_access_url'] = DOMAIN_NAME . '/wallet?usr=' . $user['user_id'] . '&wal=' . $user['wallet_id'];
  $output['lnbits_access_url_qr_svg'] = create_qr($output['lnbits_access_url']); 

  $output['lnurlp_activated'] = enable_extension($user['user_id'], 'lnurlp');
  $output['lnbits_lnurlp'] = create_lnurlp_link($user['admin_key']);
  $output['lnbits_lnurlp_qr_svg'] = create_qr($output['lnbits_lnurlp']); 

  $output['lnurlw_activated'] = enable_extension($user['user_id'], 'withdraw');
  $lnurlw = create_lnurlw_link($user['admin_key']);
  $output['lnbits_lnurlw'] = $lnurlw['lnurl'];
  $output['lnbits_lnurlw_qr_svg'] = create_qr($output['lnbits_lnurlw']); 
  $output['lnbits_lnurlw_max_uses'] = $lnurlw['uses'];

  $output['boltcard_activated'] = enable_extension($user['user_id'], 'boltcards');
  $boltcard = create_boltcard($card_uid, $user['wallet_id'], $user['admin_key']);
  $output['lnbits_boltcard'] = $boltcard;
  $output['lnbits_boltcard']['auth_link'] = DOMAIN_NAME . '/boltcards/api/v1/auth?a=' . $boltcard['otp'];
  $output['lnbits_boltcard']['auth_link_qr_svg'] = create_qr($output['lnbits_boltcard']['auth_link']); 

  $output['lndhub_activated'] = enable_extension($user['user_id'], 'lndhub');
  $output['lnbits_lndhub']['invoice_url'] = "lndhub://invoice:" . $user['invoice_key'] . "@" . DOMAIN_NAME . "/lndhub/ext/";
  $output['lnbits_lndhub']['invoice_url_qr_svg'] = create_qr($output['lnbits_lndhub']['invoice_url']); 
  $output['lnbits_lndhub']['admin_url'] = "lndhub://admin:" . $user['admin_key'] . "@" . DOMAIN_NAME . "/lndhub/ext/";
  $output['lnbits_lndhub']['admin_url_qr_svg'] = create_qr($output['lnbits_lndhub']['admin_url']); 

  $output['tpos_activated'] = enable_extension($user['user_id'], 'tpos');
  $tpos_id = create_tpos($user['wallet_id'], $user['admin_key'], FIAT_CURRENCY);
  $output['lnbits_tpos_url'] = DOMAIN_NAME . '/tpos/' . $tpos_id;
  $output['lnbits_tpos_url_qr_svg'] = create_qr($output['lnbits_tpos_url']);

  $output['watchonly_activated'] = enable_extension($user['user_id'], 'watchonly');
  $watchonly_id = create_watchonly($output['onchain']['zpub'], $user['admin_key']);

  $output['tipjar_activated'] = enable_extension($user['user_id'], 'tipjar');
  $tipjar_id = create_tipjar($user['wallet_id'], $watchonly_id, $user['admin_key']);
  $output['lnbits_tipjar_url'] = DOMAIN_NAME . '/tipjar/' . $tipjar_id;
  $output['lnbits_tipjar_url_qr_svg'] = create_qr($output['lnbits_tipjar_url']);

  // TODO :: this is just a placeholder for now
  $output['lnaddress_activated'] = false;
  $output['lnbits_lnaddress'] = $user['username'] . '@' . str_replace("https://", "", DOMAIN_NAME);
  $output['lnbits_lnaddress_qr_svg'] = create_qr($output['lnbits_lnaddress']);

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
  echo "ERROR: " . $e->getMessage() . "\n";
}
