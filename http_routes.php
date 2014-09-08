<?php


function front_page($f3)
{
	$f3->set("title", "Hello");
	$f3->set('templates', array("hello.htm"));

	echo Template::instance()->render($f3->get("STYLE")."/main.htm");
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
