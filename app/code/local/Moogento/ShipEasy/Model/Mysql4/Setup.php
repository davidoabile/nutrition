<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Setup.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://moogento.com/License.html
*/ 


class Moogento_ShipEasy_Model_Mysql4_Setup extends Mage_Sales_Model_Mysql4_Setup
{
    const LENGTH_INDEX_NAME     = 64;
    const LENGTH_FOREIGN_NAME   = 64;

    protected static $_translateMap = array(
        'address'       => 'addr',
        'admin'         => 'adm',
        'attribute'     => 'attr',
        'enterprise'    => 'ent',
        'catalog'       => 'cat',
        'category'      => 'ctgr',
        'customer'      => 'cstr',
        'notification'  => 'ntfc',
        'product'       => 'prd',
        'session'       => 'sess',
        'user'          => 'usr',
        'entity'        => 'entt',
        'datetime'      => 'dtime',
        'decimal'       => 'dec',
        'varchar'       => 'vchr',
        'index'         => 'idx',
        'compare'       => 'cmp',
        'bundle'        => 'bndl',
        'option'        => 'opt',
        'gallery'       => 'glr',
        'media'         => 'mda',
        'value'         => 'val',
        'link'          => 'lnk',
        'title'         => 'ttl',
        'super'         => 'spr',
        'label'         => 'lbl',
        'website'       => 'ws',
        'aggregat'      => 'aggr',
        'minimal'       => 'min',
        'inventory'     => 'inv',
        'status'        => 'sts',
        'agreement'     => 'agrt',
        'layout'        => 'lyt',
        'resource'      => 'res',
        'directory'     => 'dir',
        'downloadable'  => 'dl',
        'element'       => 'elm',
        'fieldset'      => 'fset',
        'checkout'      => 'chkt',
        'newsletter'    => 'nlttr',
        'shipping'      => 'shpp',
        'calculation'   => 'calc',
        'search'        => 'srch',
        'query'         => 'qr'
    );

    /**
     * Convert name using dictionary
     *
     * @param string $name
     * @return string
     */
    public static function shortName($name)
    {
        return strtr($name, self::$_translateMap);
    }

    protected function _upgradeResourceDb($oldVersion, $newVersion)
    {
        Mage::log('shipEasy installation start ' . $oldVersion . '-' . $newVersion . ' : '.date('d/m/y H:i.s'), null, 'moogento_shipeasy.log');
        parent::_upgradeResourceDb($oldVersion, $newVersion);
        Mage::log('shipEasy installation finish ' . $oldVersion . '-' . $newVersion . ' : '.date('d/m/y H:i.s'), null, 'moogento_shipeasy.log');
    }

    public function columnExists($table, $column)
    {
        return $this->getConnection()->fetchOne("SHOW COLUMNS FROM `{$this->getTable($table)}` LIKE '$column';");
    }

    public function indexExists($table, $column)
    {
        return $this->getConnection()->fetchOne("SHOW INDEX FROM `{$this->getTable($table)}` WHERE Column_name = '$column';");
    }

    public function getFkName($priTableName, $priColumnName, $refTableName, $refColumnName)
    {
        $resource = Mage::getSingleton('core/resource');
        if (method_exists($resource, 'getFkName')) {
            return Mage::getSingleton('core/resource')
                       ->getFkName($priTableName, $priColumnName, $refTableName, $refColumnName);
        } else {
            $priTableName = $this->getTable($priTableName);
            $refTableName = $this->getTable($refTableName);

            $prefix = 'fk_';
            $hash = sprintf('%s_%s_%s_%s', $priTableName, $priColumnName, $refTableName, $refColumnName);
            if (strlen($prefix.$hash) > self::LENGTH_FOREIGN_NAME) {
                $short = self::shortName($prefix.$hash);
                if (strlen($short) > self::LENGTH_FOREIGN_NAME) {
                    $hash = md5($hash);
                    if (strlen($prefix.$hash) > self::LENGTH_FOREIGN_NAME) {
                        $hash = $this->_minusSuperfluous($hash, $prefix, self::LENGTH_FOREIGN_NAME);
                    } else {
                        $hash = $prefix . $hash;
                    }
                } else {
                    $hash = $short;
                }
            } else {
                $hash = $prefix . $hash;
            }

            return strtoupper($hash);
        }
    }

    protected function _minusSuperfluous($hash, $prefix, $maxCharacters)
    {
        $diff        = strlen($hash) + strlen($prefix) -  $maxCharacters;
        $superfluous = $diff / 2;
        $odd         = $diff % 2;
        $hash        = substr($hash, $superfluous, - ($superfluous + $odd));
        return $hash;
    }
}
