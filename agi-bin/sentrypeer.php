#!/usr/bin/env php
<?php
/* SPDX-License-Identifier: AGPL-3.0  */
/* Copyright (c) 2023 Gavin Henry <ghenry@sentrypeer.org> */
/*
   _____            _              _____
  / ____|          | |            |  __ \
 | (___   ___ _ __ | |_ _ __ _   _| |__) |__  ___ _ __
  \___ \ / _ \ '_ \| __| '__| | | |  ___/ _ \/ _ \ '__|
  ____) |  __/ | | | |_| |  | |_| | |  |  __/  __/ |
 |_____/ \___|_| |_|\__|_|   \__, |_|   \___|\___|_|
                              __/ |
                             |___/
*/

include '/etc/freepbx.conf';
$agidir = FreePBX::Config()->get('ASTAGIDIR');
require_once $agidir . "/phpagi.php";

$agi = new AGI();
$phone_number_to_check = $agi->request['agi_arg_1'];
$agi->verbose("Checking phone number [$phone_number_to_check] with the SentryPeer API");

// Get the Bearer token from the FreePBX config
$sentrypeer = FreePBX::Sentrypeer();

// Act on $res_code being 404 Not Found to move on and allow the call.
// Halt/advise/alert on a 200 OK (or other status) and request a new Bearer token on a 401.
$res_code = checkPhoneNumber($sentrypeer, $agi, $phone_number_to_check);

if ($res_code == 404) {
    $agi->verbose("SentryPeer API call res code is 404. Number not seen before. Allowing the call.");
} elseif ($res_code == 401 || $res_code == 403) {
    $agi->verbose("SentryPeer API call res code is 401 or 403. Getting a new Bearer token.");

    if ($sentrypeer->getAndSaveAccessToken()) {
        $agi->verbose("SentryPeer Bearer token is now set. Trying again.");
        $res_code = checkPhoneNumber($sentrypeer, $agi, $phone_number_to_check);

        if ($res_code == 404) {
            $agi->verbose("SentryPeer has not seen this number before. Allowing the call.");
        } elseif ($res_code == 200) {
            $agi->verbose("SentryPeer has seen this number before. Hanging up the call.");
            $agi->goto_dest('sentrypeer-context', 's', 1);
        } else {
            $agi->verbose("SentryPeer API unknown response code: $res_code. Allowing the call.");
        }
    } else {
        $agi->verbose("SentryPeer Bearer token is still empty. Aborting.");
    }
} elseif ($res_code == 200) {
    $agi->verbose("SentryPeer has seen this number before. Hanging up the call.");
    $agi->goto_dest('sentrypeer-context', 's', 1);
} else {
    $agi->verbose("SentryPeer API unknown response code: $res_code. Allowing the call.");
}

exit(0);

function checkPhoneNumber($sentrypeer, $agi, $phone_number_to_check)
{
    $bearer_token = $sentrypeer->getConfig('sentrypeer-access-token');

    if (empty($bearer_token)) {
        $agi->verbose("SentryPeer Bearer token is empty. Getting a new one.");

        if ($sentrypeer->getAndSaveAccessToken()) {
            $agi->verbose("SentryPeer Bearer token is now set.");
        } else {
            $agi->verbose("SentryPeer Bearer token is still empty. Aborting.");
            exit(0);
        }
    }

    // Set the Bearer token from the FreePBX config again
    $bearer_token = $sentrypeer->getConfig('sentrypeer-access-token');

    $phone_number_api = 'https://sentrypeer.com/api/phone-numbers/';
    $timeout = 2;

    $ch = curl_init();

    $headers = ['Content-Type: application/json;charset=utf-8', 'Accept: application/json;charset=utf-8',
        "Authorization: Bearer $bearer_token"];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERAGENT, 'SentryPeer-FreePBX-Module/1.0');
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

// We need to not query local extensions and other known numbers local to FreePBX. How get?
// Set a debug option to add a ?request_logger param for SentryPeer support team.
    curl_setopt($ch, CURLOPT_URL, $phone_number_api . $phone_number_to_check);

    $res_body = curl_exec($ch);
    if (curl_errno($ch)) {
        $agi->verbose("SentryPeer API call failed: " . curl_error($ch));
        // Let the call through if the API call fails.
        exit(0);
    }
    $res_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $res_code;
}

?>
