<?php
$f3=require('lib/fatfree-master/lib/base.php');
$f3->config($f3->get("ROOT").$f3->get("BASE").'/config.ini');
$f3->config($f3->get("ROOT").$f3->get("BASE").'/local.ini');

$f3->set( "ORCID_OAUTH_REDIRECT_URI", $f3->get( "site_url" )."orcid-return" );
$f3->set( "ORCID_SITE", "http://".$f3->get( "ORCID_DOMAIN" )."/" );
$f3->set( "ORCID_OAUTH_AUTHORIZATION_URL","https://".$f3->get( "ORCID_DOMAIN" )."/oauth/authorize");
$f3->set( "ORCID_OAUTH_TOKEN_URL","https://".$f3->get( "ORCID_API" ).".".$f3->get( "ORCID_DOMAIN" )."/oauth/token");

if( $f3->get( "site_stage" ) != "prod" )
{
	$f3->set('DEBUG',3);
}

# ensure that the authenticaed flag is always set.
if( !$f3->exists( "SESSION.authenticated" ) )
{
	$f3->set( "SESSION.authenticated", false );
}
if( !$f3->exists( "SESSION.messages" ) )
{
	$f3->set( "SESSION.messages", array() );
}

#$f3->set("main_nav", array("Item 1"=>"#", "Item 2"=>"#", "Item 3"=>"#"));
#$f3->set("secondary_nav", array("Item 1"=>"#", "Item 2"=>"#", "Item 3"=>"#"));
#$f3->set("inpage_nav", array("Item 1"=>"#", "Item 2"=>"#", "Item 3"=>"#"));

#DO NOT MODIFY THE INTERNAL STYLE FOLDER make your own templates
#$f3->set("left_column", array($f3->get("STYLE")."/left_column.htm"));
#$f3->set("right_column", array($f3->get("STYLE")."/right_column.htm"));

$f3->set("main_nav", array() );
$f3->set("secondary_nav", array() );
$f3->set("inpage_nav", array() );

#DO NOT MODIFY THE INTERNAL STYLE FOLDER make your own templates
$f3->set("left_column", array() );
$f3->set("right_column", array() );


$includes = array
(
        'functions.php',
        'http_routes.php',
	'lib/ldapauth/lib/authenticate.php'
);


foreach ($includes as $file)
{
        require_once($f3->get("ROOT").$f3->get("BASE")."/".$file);
}

$f3->run();

