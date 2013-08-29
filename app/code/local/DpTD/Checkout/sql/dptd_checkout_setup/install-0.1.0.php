<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute('order', 'require_invoice', array(
    'position'          => 1,
    'input'             => 'select',
    'option'            => array('values' => array(1 => 'Yes', 0 => 'No')),
    'type'              => 'varchar',
    'label'             => 'Send Invoice',
    'visible'           => 0,
    'required'          => 0,
    'user_defined'      => 1,
    'global'            => 1,
    'visible_on_front'  => 1,
    'grid'              => true
));
$installer->addAttribute('quote', 'require_invoice', array(
    'position'          => 1,
    'input'             => 'select',
    'option'            => array('values' => array(1 => 'Yes', 0 => 'No')),
    'type'              => 'varchar',
    'label'             => 'Send Invoice',
    'visible'           => 0,
    'required'          => 0,
    'user_defined'      => 1,
    'global'            => 1,
    'visible_on_front'  => 1,
));

$installer->endSetup();