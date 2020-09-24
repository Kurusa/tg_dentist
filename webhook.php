<?php
error_reporting(E_ALL ^ E_NOTICE);
define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT']);
require_once(SITE_ROOT.'/vendor/autoload.php');
(new \App\WebhookController())->run();
