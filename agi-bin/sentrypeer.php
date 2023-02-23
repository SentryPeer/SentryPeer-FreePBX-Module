#!/usr/bin/env php 
<?php

require_once "phpagi.php";

$agi = new AGI();
$phone_number_to_check = $agi->request['agi_extension'];
$agi->verbose("Checking phone number [$phone_number_to_check] with the SentryPeer API");

// Get our Bearer token from db (RDMS or k/v one. Not sure yet. Need to request it from Auth0 yet).
$bearer_token = 'xxx';

// Move to config?
$phone_number_api = 'https://sentrypeer.com/api/phone-numbers/';
$timeout = 2;

$ch = curl_init();

$headers = ['Content-Type: application/json;charset=utf-8','Accept: application/json;charset=utf-8',
"Authorization: Bearer $bearer_token"];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_USERAGENT, 'SentryPeer-FreePBX-Module/1.0');
curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout );
curl_setopt($ch, CURLOPT_TIMEOUT, $timeout );
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

// We need to not query local extensions and other known numbers local to FreePBX. How get? 
// Set a debug option to add a ?request_logger param for SentryPeer support team.
curl_setopt($ch, CURLOPT_URL, $phone_number_api . $phone_number_to_check);

$res_body = curl_exec($ch);
$res_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$agi->verbose("SentryPeer API call res code: $res_code");
$agi->verbose("SentryPeer API call res body: $res_body");

// Act on $res_code being 404 to move on and allow the call. Halt/advise/alert on a 302 and request a new Bearer token on a 401.


?>
