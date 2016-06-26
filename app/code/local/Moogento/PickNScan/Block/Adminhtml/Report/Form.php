<?php

class Moogento_PickNScan_Block_Adminhtml_Report_Form extends Mage_Adminhtml_Block_Report_Filter_Form
{
    /**
     * Add fields to base fieldset which are general to sales reports
     *
     * @return Mage_Sales_Block_Adminhtml_Report_Filter_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array('id' => 'filter_form', 'action' => '', 'method' => 'get')
        );
        $htmlIdPrefix = 'picknscan_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Filter')));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('store_ids', 'hidden', array(
            'name'  => 'store_ids'
        ));

        $periodField = $fieldset->addField('period_type', 'select', array(
            'name' => 'period_type',
            'options' => array(
                'today'   => Mage::helper('reports')->__('Today'),
                'yesterday' => Mage::helper('reports')->__('Yesterday'),
                '7days' => Mage::helper('reports')->__('Last 7 days'),
                '30days' => Mage::helper('reports')->__('Last 30 days'),
                'day'   => Mage::helper('reports')->__('Day'),
                'week'   => Mage::helper('reports')->__('Week'),
                'month' => Mage::helper('reports')->__('Month'),
                'year'  => Mage::helper('reports')->__('Year')
            ),
            'label' => Mage::helper('reports')->__('Period'),
            'title' => Mage::helper('reports')->__('Period')
        ));

        $fromField = $fieldset->addField('from', 'date', array(
            'name'      => 'from',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('reports')->__('From'),
            'title'     => Mage::helper('reports')->__('From'),
            'required'  => true
        ));

        $toField = $fieldset->addField('to', 'date', array(
            'name'      => 'to',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('reports')->__('To'),
            'title'     => Mage::helper('reports')->__('To'),
            'required'  => true
        ));

        $users = array();
        foreach (Mage::getModel('admin/user')->getCollection() as $user) {
            $users[] = array(
                'value' => $user->getUserId(),
                'label' => $user->getFirstname() . ' ' . $user->getLastname(),
            );
        }

        $fieldset->addField('user_id', 'multiselect', array(
            'name'      => 'user_id',
            'label'     => Mage::helper('moogento_pickscan')->__('User'),
            'values'   => $users,
        ));

        $fieldset->addField('split_per_user', 'select', array(
            'name'      => 'split_per_user',
            'options'   => array(
                '1' => Mage::helper('moogento_pickscan')->__('Yes'),
                '0' => Mage::helper('moogento_pickscan')->__('No')
            ),
            'label'     => Mage::helper('moogento_pickscan')->__('Split per user'),
            'title'     => Mage::helper('moogento_pickscan')->__('Split per user')
        ));

        /*$fieldset->addField('show_empty_rows', 'select', array(
            'name'      => 'show_empty_rows',
            'options'   => array(
                '1' => Mage::helper('reports')->__('Yes'),
                '0' => Mage::helper('reports')->__('No')
            ),
            'label'     => Mage::helper('reports')->__('Empty Rows'),
            'title'     => Mage::helper('reports')->__('Empty Rows')
        ));*/

        $form->setUseContainer(true);
        $this->setForm($form);

        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                                           ->addFieldMap($periodField->getHtmlId(), $periodField->getName())
                                           ->addFieldMap($fromField->getHtmlId(), $fromField->getName())
                                           ->addFieldMap($toField->getHtmlId(), $toField->getName())
                                           ->addFieldDependence(
                                               $fromField->getName(),
                                               $periodField->getName(),
                                               array('day', 'week', 'month', 'year')
                                           )
                                           ->addFieldDependence(
                                               $toField->getName(),
                                               $periodField->getName(),
                                               array('day', 'week', 'month', 'year')
                                           )
        );

        return $this;
    }
}