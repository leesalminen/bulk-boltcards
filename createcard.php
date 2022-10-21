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
$btc_address = "bcq1";

//maybe encoded with base64
$zpub =  "zpub1";
$lndhub_invoice_key = "lndhub";
$telegram_invoice_key = "telegram";

//define card category
$category = "307787943";
$server_url = "naobanco";
/*
          "id": "307790751",          "name": "Free"
          "id": "307790752",          "name": "Premium"
           "id": "307790753",          "name": "Corporate"
          "id": "307790754",          "name": "Student"
          "id": "307790755",          "name": "Personal"
*/
/* to discover the IDs run the query bellow https://app.pipefy.com/graphiql logged into your pipefy account
{
  pipe(id:302766835){
    labels {
      id
      name
    }
  }
}
*/

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
    'query' => 'mutation {   createCard(     input: {pipe_id: ' . PIPEFY_ID . ', title: "Card_UID | BTC_Address", fields_attributes: [{field_id: "card_uid", field_value: "333 card_uid"}, {field_id: "btc_address", field_value: "btc_address"}, {field_id: "zpub", field_value: "zpub"}, {field_id: "lndhub_invoice_key", field_value: "lndhub_invoice_key"}, {field_id: "telegram_invoice_key", field_value: "telegram_invoice_key"}, {field_id: "server_url", field_value: "server_url"}, {field_id: "categoria", field_value: "307790751"}]}   ) {     card {       id, title     }   } }'
  ]
);

$card_id = $response['response']['data']['createCard']['card']['id'];

var_dump($card_id);

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

var_dump($email_address);

//then we need to add 



