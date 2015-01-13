<?php
/**
 * 支付宝通知地址
 *
 * 
 * @copyright  Copyright (c) 2007-2013 ShopNC Inc.
 */
$_GET['act']	= 'payment';
$_GET['op']		= 'notify';
$_GET['payment_code'] = 'alipay';
require_once(dirname(__FILE__).'/../../../index.php');
?>
