<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Date grid column filter
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Moogento_ShipEasy_Block_Adminhtml_Widget_Grid_Column_Filter_Date
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Date
{

    public function getValue($index=null)
    {
        if ($index) {
            if ($data = $this->getData('value', 'orig_'.$index)) {
                return $data;//date('Y-m-d', strtotime($data));
            }
            return null;
        }
        $value = $this->getData('value');
        if (is_array($value)) {
            $value['datetime'] = true;
        }
        return $value;
    }
    
    /*
     * Convert given date to default (UTC) timezone
     *
     * @param string $date
     * @param string $locale
     * @return Zend_Date
     */
    protected function _convertDate($date, $locale)
    {
        $calendarType = Mage::getStoreConfig('moogento_shipeasy/grid/szy_created_at_type');
        if ($calendarType == Moogento_ShipEasy_Helper_Date::TYPE_PERSIAN) {

            if ($locale == 'en_US') {
                list($month, $day, $year) = explode('/', $date);
                list($year, $month, $day) = Mage::helper('moogento_shipeasy/date')->persianToGregorian($year, $month, $day);
                $date = implode('/', array($month, $day, $year));
            } else {
                list($day, $month, $year) = explode('/', $date);
                list($year, $month, $day) = Mage::helper('moogento_shipeasy/date')->persianToGregorian($year, $month, $day);
                $date = implode('/', array($day, $month, $year));
            }
        } elseif ($calendarType == Moogento_ShipEasy_Helper_Date::TYPE_THAI) {

            if ($locale == 'en_US') {
                list($month, $day, $year) = explode('/', $date);
                list($year, $month, $day) = Mage::helper('moogento_shipeasy/date')->thaiToGregorian($year, $month, $day);
                $date = implode('/', array($month, $day, $year));
            } else {
                list($day, $month, $year) = explode('/', $date);
                list($year, $month, $day) = Mage::helper('moogento_shipeasy/date')->thaiToGregorian($year, $month, $day);
                $date = implode('/', array($day, $month, $year));
            }
        } /*else {
            list($day, $month, $year) = explode('/', $date);
            if ($locale == 'en_US') {
                $date = implode('/', array($month, $day, $year));
            } else {
                $date = implode('/', array($day, $month, $year));
            }
        }*/
        return parent::_convertDate($date, $locale);
    }

    public function setValue($value)
    {
        if (isset($value['locale'])) {
            if (!empty($value['from'])) {
                $value['orig_from'] = $value['from'];
                $value['from'] = $this->_convertDate($value['from'], $value['locale']);
            }
            if (!empty($value['to'])) {
                $value['orig_to'] = $value['to'];
                if ($value['orig_from'] == $value['orig_to']) {
                    $value['to'] = clone $value['from'];
                    $value['to']->addDay(1);
                } else {
                    $value['to'] = $this->_convertDate($value['to'], $value['locale']);
                }
            }
        }
        if (empty($value['from']) && empty($value['to'])) {
            $value = null;
        }
        $this->setData('value', $value);
        return $this;
    }
    
    protected function _renderDefaultHtml()
    {
        $htmlId = $this->_getHtmlId();
        $format = $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        if ($this->getColumn()->getFilterTime()) {
            $format .= ' ' . $this->getLocale()->getTimeStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        }
        $localeCode = $this->getLocale()->getLocaleCode();

        $html = '<div class="range"><div class="range-line date">'
            . '<input type="text" name="'.$this->_getHtmlName().'[from]" id="'.$htmlId.'_from"'
            . ' value="'.$this->getEscapedValue('from').'" class="input-text no-changes"/>'
            . '<img src="' . Mage::getDesign()->getSkinUrl('images/grid-cal.gif') . '" alt="" class="v-middle"'
            . ' id="'.$htmlId.'_from_trig"'
            . ' title="'.$this->escapeHtml(Mage::helper('adminhtml')->__('Date selector')).'"/>'
            . '</div>';
        $html.= '<div class="range-line date">'
            . '<input type="text" name="'.$this->_getHtmlName().'[to]" id="'.$htmlId.'_to"'
            . ' value="'.$this->getEscapedValue('to').'" class="input-text no-changes"/>'
            . '<img src="' . Mage::getDesign()->getSkinUrl('images/grid-cal.gif') . '" alt="" class="v-middle"'
            . ' id="'.$htmlId.'_to_trig"'
            . ' title="'.$this->escapeHtml(Mage::helper('adminhtml')->__('Date selector')).'"/>'
            . '</div></div>';
        $html.= '<input type="hidden" name="'.$this->_getHtmlName().'[locale]"'
            . ' value="'.$this->getLocale()->getLocaleCode().'"/>';
        $calendarType = Mage::getStoreConfig('moogento_shipeasy/grid/szy_created_at_type');
        if ($calendarType == Moogento_ShipEasy_Helper_Date::TYPE_PERSIAN) {
            $html .= '<script type="text/javascript">
            jQuery("#' . $htmlId . '_from").persianDatepicker({
                formatDate: "' . ($localeCode == 'en_US' ? 'MM/DD/YYYY' : 'DD/MM/YYYY') . '",
                persianNumbers: ' . (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_created_at_persian_numbers') ? 'true' : 'false' ). '
            });
            jQuery("#' . $htmlId . '_to").persianDatepicker({
                formatDate: "' . ($localeCode == 'en_US' ? 'MM/DD/YYYY' : 'DD/MM/YYYY') . '",
                persianNumbers: ' . (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_created_at_persian_numbers') ? 'true' : 'false' ). '
            });
            </script>';
        } elseif ($calendarType == Moogento_ShipEasy_Helper_Date::TYPE_THAI) {
            $html .= '<script type="text/javascript">
            jQuery("#' . $htmlId . '_from").datepicker({
                    isBE: true,
                    dateFormat: "' . ($localeCode == 'en_US' ? 'mm/dd/yy' : 'dd/mm/yy') . '",
                    autoConversionField: false
            });
            jQuery("#' . $htmlId . '_to").datepicker({
                    isBE: true,
                    dateFormat: "' . ($localeCode == 'en_US' ? 'mm/dd/yy' : 'dd/mm/yy') . '",
                    autoConversionField: false
            });
            </script>';
        } else {
            $html
                .= '<script type="text/javascript">
            Calendar.setup({
                inputField : "' . $htmlId . '_from",
                ifFormat : "' . $format . '",
                button : "' . $htmlId . '_from_trig",
                showsTime: ' . ($this->getColumn()->getFilterTime() ? 'true' : 'false') . ',
                align : "Bl",
                singleClick : true
            });
            Calendar.setup({
                inputField : "' . $htmlId . '_to",
                ifFormat : "' . $format . '",
                button : "' . $htmlId . '_to_trig",
                showsTime: ' . ($this->getColumn()->getFilterTime() ? 'true' : 'false') . ',
                align : "Bl",
                singleClick : true
            });
        </script>';
        }
        return $html;
    }

    protected function _renderCleanHtml()
    {
        if (!Mage::getStoreConfigFlag(Moogento_Clean_Helper_Data::XML_PATH_GRID_CSS)) {
            return $this->_renderDefaultHtml();
        }

        $htmlId = $this->_getHtmlId() . time();
        $localeCode = $this->getLocale()->getLocaleCode();
        $format = 'dd/MM/yy';

        $html = '<div class="range-filter input-group range-filter-date">'
            . '<input type="text" name="'.$this->_getHtmlName().'[from]" id="'.$htmlId.'_from"'
            . ' data-format="' . $format . '" value="'.$this->getEscapedValue('from').'" class="input-text no-changes"/>'

            . '<span class="input-group-addon">' . $this->__('to') . '</span>'

            . '<input type="text" name="'.$this->_getHtmlName().'[to]" id="'.$htmlId.'_to"'
            . ' data-format="' . $format . '" value="'.$this->getEscapedValue('to').'" class="input-text no-changes"/>'
            . '</div>';
        $html.= '<input type="hidden" name="'.$this->_getHtmlName().'[locale]"'
            . 'value="'.$this->getLocale()->getLocaleCode().'"/>';

        $calendarType = Mage::getStoreConfig('moogento_shipeasy/grid/szy_created_at_type');
        if ($calendarType == Moogento_ShipEasy_Helper_Date::TYPE_PERSIAN) {
            $html .= '<script type="text/javascript">
            jQuery("#' . $htmlId . '_from").persianDatepicker({
                formatDate: "' . ($localeCode == 'en_US' ? 'MM/DD/YYYY' : 'DD/MM/YYYY') . '",
                persianNumbers: ' . (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_created_at_persian_numbers') ? 'true' : 'false' ). '
            });
            jQuery("#' . $htmlId . '_to").persianDatepicker({
                formatDate: "' . ($localeCode == 'en_US' ? 'MM/DD/YYYY' : 'DD/MM/YYYY') . '",
                persianNumbers: ' . (Mage::getStoreConfigFlag('moogento_shipeasy/grid/szy_created_at_persian_numbers') ? 'true' : 'false' ). '
            });
            </script>';
        } elseif ($calendarType == Moogento_ShipEasy_Helper_Date::TYPE_THAI) {
            $html .= '<script type="text/javascript">
            jQuery("#' . $htmlId . '_from").datepicker({
                    isBE: true,
                    dateFormat: "' . ($localeCode == 'en_US' ? 'mm/dd/yy' : 'dd/mm/yy') . '",
                    autoConversionField: false
            });
            jQuery("#' . $htmlId . '_to").datepicker({
                    isBE: true,
                    dateFormat: "' . ($localeCode == 'en_US' ? 'mm/dd/yy' : 'dd/mm/yy') . '",
                    autoConversionField: false
            });
            </script>';
        } else {
            $html.= '<script type="text/javascript">
            jQuery(function() {
                jQuery("#'.$htmlId.'_from").datetimepicker({
                    format: "' . ($localeCode == 'en_US' ? 'MM/dd/yy' : 'dd/MM/yy') . '",
                    pickTime: false
                });
                jQuery("#'.$htmlId.'_to").datetimepicker({
                    format: "' . ($localeCode == 'en_US' ? 'MM/dd/yy' : 'dd/MM/yy') . '",
                    pickTime: false
                });
            });
            </script>';
        }

        return $html;
    }
     
    public function getHtml()
    {
        if (Mage::helper('moogento_core')->isInstalled('Moogento_Clean')) {
            return $this->_renderCleanHtml();
        }
        return $this->_renderDefaultHtml();
    }
    public function getEscapedValue($index=null)
    {
        $value = $this->getValue($index);
        if ($value instanceof Zend_Date) {
            if($index == 'to')
                $value->sub('23:59:59', Zend_Date::TIMES);
            if($index == 'from')
                $value->add('23:59:59', Zend_Date::TIMES);
            return $value->get($this->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
        }

        return $value;
    }

}

