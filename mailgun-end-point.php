<?php

// change to current directory
chdir(__DIR__);
require 'includes/primary.i.php';

// authenticate headers
if (!mailgunAuthenticateWebhook($_POST)) {
    // log warning
    Log::addWarning('Unable to authenticate Mailgun header.');

    // reject as forbidden
    header('HTTP/1.1 403 Forbidden');
    echo 'Invalid credentials.';
    exit(1);
}

// does not actually matter
$recipient = $_POST['recipient'];

// subject
$subject = $_POST['subject'];

// mime body
if (isset($_POST['body-html'])) {
    $content = $_POST['body-html'];

    // inject data URLs
    if (isset($_POST['content-id-map'])) {
        foreach (json_decode($_POST['content-id-map']) as $cid => $attachment_name) {
            // find attachment?
            if (isset($_FILES[$attachment_name])) {
                // generate DATA URL
                $attachment_content = @file_get_contents($_FILES[$attachment_name]['tmp_name']);
                if (false === $attachment_content) continue;
                $data_url = 'base64,' . base64_encode($attachment_content);

                // has mime type?
                if (isset($_FILES[$attachment_name]['type'])) {
                    $data_url = sprintf('%s;%s', $_FILES[$attachment_name]['type'], $data_url);
                }

                // add to content
                $content = str_replace(['cid:' . $cid, 'cid:' . trim($cid, '<>')], 'data:' . $data_url, $content);
            }
        }
    }
}
elseif (isset($_POST['body-text'])) {
    $content = $_POST['body-text'];

    // escape and add new lines
    $content = nl2br(htmlspecialchars($content, ENT_HTML5 | ENT_COMPAT | ENT_IGNORE, 'UTF-8'));
}
else {
    // log warning
    Log::addWarning(sprintf('Empty message received from %s.', $_POST['from']));

    // reject message
    header('HTTP/1.1 406 Not Acceptable');
    echo 'No body.';
    exit(1);
}

// too large?
if (strlen($content) > EMAIL_MAX_SIZE) {
    // log warning
    Log::addWarning(sprintf('Received too large of a MIME message from %s.', $_POST['from']));

    // reject message
    header('HTTP/1.1 406 Not Acceptable');
    echo 'Mime to large.';
    exit(1);
}

// sender
$from = $_POST['from'];

// message id
$message_id = uniqid('', true);
foreach (json_decode($_POST['message-headers']) as $header) {
    if ('message-id' === strtolower($header[0])) {
        $message_id = trim($header[1], '<>');
    }
}

// rss file
$rss_file = mapFromToRss($from);

try {
    $rss = Cdn::getFile($rss_file);
}
catch (Exception $e) {
    header('HTTP/1.1 503 Service Unavailable');
    echo $e->getMessage();
    exit(1);
}

// seed rss
if (false === $rss) {
    // full path
    $full_path = sprintf('http://%s/%s%s', AWS_BUCKET, AWS_PREFIX, $rss_file);

    // get blank file
    $rss = Rss::getBlankFile($rss_file, "This is an automatically generated RSS file called $rss_file.", $full_path);
}

// get rss file
$r = new Rss($rss);

// add item
$r->addItem($subject, $content, sprintf('http://%s/%s%s', AWS_BUCKET, AWS_PREFIX, $message_id), $message_id, $_POST['timestamp']);

// clean up
$r->cleanOldItems();

// update RSS
$r->updateChannelDates(time());

// put to cdn
try {
    $rss = Cdn::putFile($rss_file, (string)$r);
}
catch (Exception $e) {
    header('HTTP/1.1 503 Service Unavailable');
    echo $e->getMessage();
    exit(1);
}
