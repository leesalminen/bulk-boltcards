<?php

//help with the Pipefy API here
//https://developers.pipefy.com/reference/graphql-endpoint
//https://api-docs.pipefy.com/reference/

//build  query 
//https://developers.pipefy.com/graphql

//test query 
//https://app.pipefy.com/graphiql

require('requests.php');
require('constants.php');

//card data
$card_uid = "1234567890000";
$onchain_address = "bcq1";

//maybe encoded with base64
$zpub =  "zpub1";
$lndhub_invoice_key = "lndhub";
$telegram_invoice_key = "telegram";

function pipefy_create_card($card_uid, $onchain_address, $zpub, $lndhub_invoice_key, $telegram_invoice_key) {
  //create a card on pipefy
  $response = request(
    'POST', 
    'https://api.pipefy.com/graphql', 
    [],
    [
      'Authorization: Bearer ' . PIPEFY_TOKEN,
      'Content-Type: application/json',
      'Accept: application/json',
    ],
    [
      'query' => 'mutation {   createCard(     input: {pipe_id: ' . PIPEFY_ID . ', title: "' . $card_uid . ' | ' . $onchain_address . '", fields_attributes: [{field_id: "card_uid", field_value: "' . $card_uid . '"}, {field_id: "btc_address", field_value: "' . $onchain_address . '"}, {field_id: "zpub", field_value: "' . $zpub . '"}, {field_id: "lndhub_invoice_key", field_value: "' . $lndhub_invoice_key . '"}, {field_id: "telegram_invoice_key", field_value: "' . $telegram_invoice_key . '"}, {field_id: "server_url", field_value: "' . DOMAIN_NAME . '"}, {field_id: "categoria", field_value: "' . PIPEFY_CATEGORY . '"}]}   ) {     card {       id, title     }   } }'
    ]
  );

  $card_id = $response['response']['data']['createCard']['card']['id'];

  return $card_id;
}

function pipefy_get_email($card_id) {
  $response = request(
    'POST', 
    'https://api.pipefy.com/graphql', 
    [],
    [
      'Authorization: Bearer ' . PIPEFY_TOKEN,
      'Content-Type: application/json',
      'Accept: application/json',
    ],
    [
      'query' => 'query MyQuery {   card(id: "' . $card_id . '") {     emailMessagingAddress   } }'
    ]
  );


  $email_address = $response['response']['data']['card']['emailMessagingAddress'];

  return $email_address;
}