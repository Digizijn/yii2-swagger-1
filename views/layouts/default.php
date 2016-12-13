<?php use app\assets\AppAsset;

AppAsset::register($this); ?>

<?php $this->beginPage() ?>
<?php $this->beginBody() ?>
default
<?= $content ?>
<?php $this->endBody() ?>
<?php $this->endPage() ?>