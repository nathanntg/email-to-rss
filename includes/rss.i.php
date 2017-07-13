<?php

class Rss
{
    protected $_dom;

    /**
     * @var DOMNode
     */
    protected $_channel;

    public function __construct($rss) {
        $this->_dom = new \DOMDocument('1.0', 'UTF-8');
        $this->_dom->recover = true;

        // use internal errors
        libxml_use_internal_errors(true);
        if (!@$this->_dom->loadXML($rss)) {
            throw new Exception('Unable to parse RSS file: ' . implode(', ',libxml_get_errors()));
        }

        // get all channels
        $channels = $this->_dom->getElementsByTagName('channel');
        if (1 != $channels->length) {
            throw new Exception('Expecting 1 channel in RSS file.');
        }

        // set channel element
        $this->_channel = $channels->item(0);
    }

    public function addItem($title, $description, $link, $guid, $pub_date) {
        $el_item = $this->_dom->createElement('item');

        // create elements
        $el_title = $this->_dom->createElement('title');
        $el_title_text = $this->_dom->createTextNode($title);
        $el_title->appendChild($el_title_text);
        $el_item->appendChild($el_title);

        // description
        $el_description = $this->_dom->createElement('description');
        $el_description_cdata = $this->_dom->createCDATASection($description);
        $el_description->appendChild($el_description_cdata);
        $el_item->appendChild($el_description);

        // link
        $el_link = $this->_dom->createElement('link');
        $el_link_text = $this->_dom->createTextNode($link);
        $el_link->appendChild($el_link_text);
        $el_item->appendChild($el_link);

        // guid
        $el_guid = $this->_dom->createElement('guid');
        $el_guid_text = $this->_dom->createTextNode($guid);
        $el_guid->appendChild($el_guid_text);
        $el_item->appendChild($el_guid);

        // pub date
        $el_pub_date = $this->_dom->createElement('pubDate');
        $el_pub_date_text = $this->_dom->createTextNode(is_numeric($pub_date) ? date('r', $pub_date) : $pub_date);
        $el_pub_date->appendChild($el_pub_date_text);
        $el_item->appendChild($el_pub_date);

        // add to channel
        $this->_channel->appendChild($el_item);
    }

    public function cleanOldItems($max_items=15) {
        // all items
        $items = $this->_dom->getElementsByTagName('item');

        // not too many already?
        if ($items->length <= $max_items) return;

        // mapping of time => id
        $created = [];
        foreach ($items as $index => $item) {
            /** @var \DOMNode $item */
            $tm = 0;
            foreach ($item->childNodes as $node) {
                if ('pubDate' === $node->nodeName) {
                    $tm = strtotime($node->textContent);
                    break;
                }
            }
            $created[$index] = $tm;
        }

        // sort ascending
        asort($created);
        $number_to_remove = $items->length - $max_items;

        // items to remove
        $to_remove_indices = array_slice(array_keys($created), 0, $number_to_remove);
        $to_remove_nodes = array();
        foreach ($to_remove_indices as $index) {
            $to_remove_nodes[] = $items->item($index);
        }
        foreach ($to_remove_nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    public function updateChannelDates($tm) {
        foreach ($this->_channel->childNodes as $node) {
            /** @var \DOMNode $node */
            switch ($node->nodeName) {
                case 'pubDate':
                case 'lastBuildDate':
                    $node->nodeValue = date('r', $tm);
            }
        }
    }

    public function __toString() {
        return $this->_dom->saveXML();
    }

    public static function getBlankFile($title, $description, $link) {
        return <<<EOD
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
 <title>$title</title>
 <description>$description</description>
 <link>$link</link>
 <lastBuildDate></lastBuildDate>
 <pubDate></pubDate>
 <ttl>1800</ttl>
</channel>
</rss>
EOD;
    }
}
