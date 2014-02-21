<?php


function front_page($f3)
{
	$f3->set("title", "Hello");
        $f3->set('templates', array("hello.htm"));

        echo Template::instance()->render("internal_style/main.htm");
}
