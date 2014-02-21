<?php

$f3=require('lib/fatfree-master/lib/base.php');
$f3->config($f3->get("ROOT").'/config.ini');
$f3->config($f3->get("ROOT").'/secrets.ini');

$f3->set("main_nav", array("Item 1"=>"#", "Item 2"=>"#", "Item 3"=>"#"));
$f3->set("secondary_nav", array("Item 1"=>"#", "Item 2"=>"#", "Item 3"=>"#"));
$f3->set("inpage_nav", array("Item 1"=>"#", "Item 2"=>"#", "Item 3"=>"#"));

#DO NOT MODIFY THE INTERNAL STYLE FOLDER make your own templates
$f3->set("left_column", array($f3->get("STYLE")."/left_column.htm"));
$f3->set("right_column", array($f3->get("STYLE")."/right_column.htm"));

$includes = array
(
        'functions.php',
        'http_routes.php',
        'lib/redbean/rb.php',
        'lib/FloraForm-template/FloraForm.php'
);

foreach ($includes as $file)
{
        require_once($f3->get("ROOT")."/".$file);
}

$f3->run();

