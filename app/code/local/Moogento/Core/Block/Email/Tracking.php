<?php

class Moogento_Core_Block_Email_Tracking extends Mage_Core_Block_Abstract
{
    protected function _getLinks()
    {
        $tracks = $this->getOrder()->getTracksCollection();
        $links  = array();
        if (count($tracks)) {
            foreach ($tracks as $track) {
                $links[] = Mage::helper('moogento_core/carriers')->getTrackLinkData($track);
            }
        }

        return $links;
    }

    protected function _toHtml()
    {
        $links = $this->_getLinks();
        $html = '';
        foreach ($links as $linkData) {
            $link = str_replace('#tracking#', $linkData['number'], $linkData['url']);
            $link = str_replace('#zipcode#', $this->getOrder()->getShippingAddress()->getPostcode(), $link);
            $link = str_replace('#postcode#', $this->getOrder()->getShippingAddress()->getPostcode(), $link);
            $html .= '<a target="_blank" href="' . $link . '">' . (isset($linkData['image']) ? $linkData['image'] : $linkData['title']) . '</a><br/>';
        }

        return $html;
    }
} 