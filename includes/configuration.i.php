<?php

// configuration fil
define('AWS_BUCKET', 'rss.emailtorss.com');

// nicer mapping
function mapFromToRss($from) {
    $mapping = [
        '@qz.com'           =>  'quartz.rss',
        '@nowiknow.com'     =>  'nowiknow.com',
        '@quora.com'        =>  'quora.rss',
        '@davenetics.com'   =>  'nextdraft.rss',
        '@nextdraft.com'    =>  'nextdraft.rss',
        '@lists.stripe.com' =>  'stripe.rss'
    ];

    foreach ($mapping as $key => $val) {
        if (false !== stripos($from, $key)) {
            return $val;
        }
    }

    return 'other.rss';
}
