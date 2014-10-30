<?php


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
	}
}

function page_logout($f3)
{
	$f3->set("SESSION.authenticated", false);
	$f3->set("SESSION.username", null);
	
	$f3->set("title", "Logout");
	$f3->set('templates', array("logout.htm"));

	echo Template::instance()->render($f3->get("STYLE")."/main.htm");
}

function page_frontpage($f3)
{
	$f3->set("title", "Introduction");
	$f3->set('templates', array("frontpage.htm"));

	echo Template::instance()->render($f3->get("STYLE")."/main.htm");
}
function page_profile($f3)
{
	local_authenticate($f3);
	print "Your profile: ";
	print $f3->get( "SESSION.givenname" );
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

