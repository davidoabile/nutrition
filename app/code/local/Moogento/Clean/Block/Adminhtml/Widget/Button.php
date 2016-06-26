<?php


class Moogento_Clean_Block_Adminhtml_Widget_Button extends Mage_Adminhtml_Block_Widget_Button
{
    protected function _toHtml()
    {
        if (0 && Mage::getStoreConfig(Moogento_Clean_Helper_Data::XML_PATH_THEME) == 'extended') {
            $html = $this->getBeforeHtml() . '<button '
                . ($this->getId() ? ' id="' . $this->getId() . '"' : '')
                . ($this->getElementName() ? ' name="' . $this->getElementName() . '"' : '')
                . ' title="'
                . Mage::helper('core')->quoteEscape($this->getTitle() ? $this->getTitle() : $this->getLabel())
                . '"'
                . ' type="' . $this->getType() . '"'
                . ' class="scalable ' . $this->_convertClass($this->getClass()) . ($this->getDisabled() ? ' disabled'
                    : '') . '"'
                . ' onclick="' . $this->getOnClick() . '"'
                . ' style="' . $this->getStyle() . '"'
                . ($this->getValue() ? ' value="' . $this->getValue() . '"' : '')
                . ($this->getDisabled() ? ' disabled="disabled"' : '')
                . '>'
                . $this->_getIcon($this->getClass())
                . $this->getLabel() . '</button>' . $this->getAfterHtml();

            return $html;
        } else {
            return parent::_toHtml();
        }
    }

    protected function _convertClass($css)
    {
        $classList = explode(' ', $css);
        $finalList = array('btn', 'btn-mini');
        foreach ($classList as $cls) {
            switch ($cls) {
                case 'delete':
                    $finalList[] = 'btn-danger';
                    break;
                case 'back':
                    break;
                default:
                    $finalList[] = 'btn-warning';
            }
        }

        return implode(' ', $finalList);
    }

    protected function _getIcon($css)
    {
        $classList = explode(' ', $css);
        $icon = false;
        foreach ($classList as $cls) {
            switch ($cls) {
                case 'back':
                    $icon = 'icon-circle-arrow-left';
                    break;
                case 'delete':
                    $icon = 'icon-white icon-remove-sign';
                    break;
                case 'add':
                    $icon = 'icon-white icon-plus-sign';
                    break;
                case 'save':
                    $icon = 'icon-white icon-ok-sign';
                    break;
            }
        }

        if ($icon) {
            return '<i class="' . $icon . '"></i>&nbsp;';
        }

        return '';
    }
} 