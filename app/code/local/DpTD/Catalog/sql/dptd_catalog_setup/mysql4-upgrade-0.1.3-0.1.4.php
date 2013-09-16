<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$content = <<<HTML
<div class="block block-subscription">
<div class="block-title"><a href="{{store url='prenumerata.html' }}"> <strong><span>Prenumerata 2+3D</span></strong> </a></div>
<div class="block-content">
<p><img src="{{media url="wysiwyg/prenumerata.jpg"}}" alt="" /></p>
<div class="add-to-box">
<div class="price-box">
<p class="regular-price"><span class="label">Cena:</span> <span class="price">50,00&nbsp;zł rocznie</span></p>
</div>
<a class="button btn-more" title="Więcej" href="{{store url='prenumerata.html'}}"><span><span>Więcej &gt;&gt;&gt;</span></span></a></div>
</div>
</div>
HTML;

$model = Mage::getModel('cms/block');
$staticBlock = array(
      'title' => 'Prenumerate 2+3D',
      'identifier' => 'prenumerata',
      'content' => $content,
      'is_active' => 1,                   
      'stores' => array(0)
    );
$model->setData($staticBlock)->save();

$content2 = <<<HTML2
<div class="block block-links">
  <div class="block-title"><strong><span>Kategorie</span></strong></div>
  <div class="block-content">
    <p><a title="Książki" href="{{store url='ksiazki.html'}}">Książki</a>, <a title="Kwartalnik" href="{{store url='kwartalnik.html'}}">Kwartalnik</a>, <a title="Prenumerata" href="{{store url='prenumerata.html'}}">Prenumerata</a></p>
  </div>
</div>  
HTML2;
$model = Mage::getModel('cms/block');
$staticBlock = array(
      'title' => 'Kategorie',
      'identifier' => 'kategorie-sidebar',
      'content' => $content2,
      'is_active' => 1,                   
      'stores' => array(0)
    );
$model->setData($staticBlock)->save();

$installer->endSetup();
    