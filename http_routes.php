<?php

require_once( 'database-functions.php' );

function local_authenticate($f3)
{
	$result = authenticate($f3);
	if( is_array( $result ) )
	{
		$f3->set("SESSION.usertype", $result["usertype"] );
		$f3->set("SESSION.staffid", $result["staffid"] );
		$f3->set("SESSION.givenname", $result["givenname"] );
		$f3->set("SESSION.familyname", $result["familyname"] );
		$f3->set("SESSION.department", $result["department"] );
		$f3->set("SESSION.departmentcode", $result["departmentcode"] );
		if( $f3->get( "SERVER.REQUEST_METHOD" )=="POST" &&
		    $f3->get( "REQUEST.pass_through" ) )
		{
			// redirerct to GET version of this page
			header( "Location: ".$f3->get( "REQUEST.pass_through" ) );
			return;
		}

	}
}

function page_error($f3)
{
	$f3->set("title", $f3->get('ERROR.code')." ".$f3->get('ERROR.status' ));
	$f3->set('templates', array("error.htm"));
	if( $f3->get( "ERROR.code" )==500 ) { header( "HTTP/1.1 200 But really 500" ); }
	render_page($f3);
}

function page_logout($f3)
{
	$f3->set("SESSION.authenticated", false);
	$f3->set("SESSION.username", null);

	$f3->push( "SESSION.messages", Template::instance()->render("msg-logout.htm") );

	# return to homepage
	header( "Location: /" );
}

function page_frontpage($f3)
{
	$f3->set("title", "Introduction");
	$f3->set('templates', array("frontpage.htm"));
	render_page($f3);
}
function page_profile($f3)
{
	local_authenticate($f3);
	$f3->set("title", "Your ORCID Profile");
	$f3->set('templates', array("profile.htm"));
	$f3->set('record', UosOrcid::fromPinumber( $f3->get( "SESSION.pinumber" )));
	render_page($f3);
}

function render_page($f3)
{
	print Template::instance()->render($f3->get("STYLE")."/main.htm");
	
	// clear messages once we've rendered them.
	$f3->set( "SESSION.messages", array() );
}

function orcid_json($f3)
{
	# curl -H "Accept: application/orcid+json" 'http://pub.orcid.org/v1.1/0000-0001-7857-2795/orcid-bio' -L -i
	$orcid = $f3->get("PARAMS.orcid" );
	$url = "http://pub.orcid.org/v1.1/$orcid/orcid-bio";

	// we are the parent
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Accept: application/orcid+json" ));
	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch); 

	#print $httpCode;  // not being smart about errors yet

	header( "Content-type: text/json" );
	print $result;
}

#######################################################


