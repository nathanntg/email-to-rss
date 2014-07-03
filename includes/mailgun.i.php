<?php

function mailgunAuthenticateWebhook() {
    // has timestamp variable?
    if (!isset($_POST['timestamp'])) return false;

    // get timestamp in UNIX time
    $timestamp = $_POST['timestamp'];
    if ((time() - 86400) > $timestamp) return false; // make sure timestamp from last 24 hours

    // token: Randomly generated string with length 50
    if (!isset($_POST['token'])) return false;
    $token = $_POST['token'];

    // get signature variable (HMAC)
    if (!isset($_POST['signature'])) return false;

    // concatenate
    $concat = $timestamp . $token;

    // get key
    $key = configurationGet('MAILGUN_API_KEY');

    // HMAC encode
    return (hash_hmac('sha256', $concat, $key) === $_POST['signature']);
}
