<?php

// this should be the URL to your LNBits installation. Don't add a trailing /
define("DOMAIN_NAME", "https://lnbits.bitcoinjungle.app");

// this will show up in the template for users, can be anything you want
define("ISSUER_NAME", "Praia Bitcoin");

// support tickets
// depends on the Support Tickets extension on LNBits
// pass in the URL to your support ticket screen
define("SUPPORT_URL", "https://lnbits.bitcoinjungle.app/lnticket/C83efGg3P9Xp5fSL3vApmt");
define("SUPPORT_COST_PER_SAT", 10);

// this is used for TPoS, you can set it to whatever shitcoin you'd like
define("FIAT_CURRENCY", "BRL");

// available languages: en, es, pt
define("LANGUAGE", "pt");

// this is shown in the output template
define("SERVER_IP_ADDRESS", "127.0.0.1");

// this is shown in the output template
define("SERVER_LOCATION", "Jericoacoara - CE / Brasil");
define("SERVER_TOR_ADDRESS", "dwbmglz2hwlx3y7udvzb7cbx5kahl33wxotdsd6uk3quq2y2avq66lqd.onion:9735");
define("SERVER_PUBLIC_KEY", "0336350b10294b9d8759944709db961cb9ef8e4c7d3a80d057684ae5b0d841c101");
define("TELEGRAM_BOT_URL", "https://t.me/naobancojeri");

// this is used to generate the QR codes of front page
define("LOCAL_MAP", "https://goo.gl/maps/5QHh2kv1H3f9fHEw9");
define("BOLT_GENERATOR_CODE", "https://github.com/praiabitcoin/bulkcards");
define("IMPLEMENTATION_GUIDE", "https://github.com/praiabitcoin/naobanco");

//Define a SOCKS5 proxy, if you desire it 
//define("SOCKS5", "192.168.0.201:9050");
