<?php

require_once( 'database-functions.php' );

function local_authen($f3)
{
	$result = authenticate($f3);
	if( is_array( $result ) )
	{
		$f3->set("SESSION.usertype", $result["usertype"] );
		$f3->set("SESSION.pinumber", $result["staffid"] );
		$f3->set("SESSION.givenname", $result["givenname"] );
		$f3->set("SESSION.familyname", $result["familyname"] );
		$f3->set("SESSION.department", $result["department"] );
		$f3->set("SESSION.departmentcode", $result["departmentcode"] );
		$f3->set("SESSION.email", $result["email"] );
		if( $f3->get( "SERVER.REQUEST_METHOD" )=="POST" &&
		    $f3->get( "REQUEST.pass_through" ) )
		{
			// redirerct to GET version of this page
			header( "Location: ".$f3->get( "REQUEST.pass_through" ) );
			return;
		}
	}
}

function local_authz($f3,$priv )
{
	$allowed = $f3->get( "authz.$priv" );
	if( !is_array( $allowed ) ) { $allowed = array( $allowed ); }
	$username = $f3->get( "SESSION.username" );
	$ok=false;
	foreach( $allowed as $username_with_right )
	{
		if( $username_with_right == $username )
		{
			$ok=true;
			break;
		}
	}
	if( !$ok )
	{
		$f3->error( 403 );
		return false;
	}
	return true;
}

#######################################################
# Pages
#######################################################

function page_error($f3)
{
	$f3->set("title", $f3->get('ERROR.code')." ".$f3->get('ERROR.status' ));
	$f3->set('templates', array("error.htm"));
	if( $f3->get( "ERROR.code" )==500 ) { header( "HTTP/1.1 200 But really 500" ); }
	render_page($f3);
}

function page_frontpage($f3)
{
	$f3->set("title", "Introduction");
	$f3->set('templates', array("frontpage.htm"));
	render_page($f3);
}

function page_profile($f3)
{
	local_authen($f3);
	$f3->set("title", "Your ORCID Profile");
	$f3->set('templates', array("profile.htm"));
	$f3->set('record', UosOrcid::fromPinumber( $f3->get( "SESSION.pinumber" )));
	render_page($f3);
}

function page_clear($f3)
{
	local_authen($f3);

	$f3->set("title", "Forget your ORCID?");
	$f3->set('templates', array("clear.htm"));
	$f3->set('record', UosOrcid::fromPinumber( $f3->get( "SESSION.pinumber" )));
	render_page($f3);
}

function page_log( $f3 )
{
	local_authen($f3);
	local_authz($f3,"log.view");

	$f3->set("title", "Southampton ORCID Log" );
	$f3->set("table.fields", array( 
		array( "event_time", "Time"),
		array( "action", "Action" ),
		array( "pinumber", "UoS ID" ),
		array( "orcid", "ORCID" ) ) );
	$f3->set("table.data", UosOrcid::allLog() );
	$f3->set('templates', array("data-table.htm"));
	render_page($f3);
}

function page_data( $f3 )
{
	local_authen($f3);
	local_authz($f3,"data.view");

	$f3->set("title", "Southampton ORCID Data" );
	$f3->set("table.fields", array( 
		array( "pinumber", "UoS ID" ),
		array( "orcid", "ORCID" ) ,
		array( "modified", "Last Modified") ));
	$f3->set("table.data", UosOrcid::allRecords() );
	$f3->set('templates', array("data-table.htm"));
	render_page($f3);
}

function render_page($f3)
{
	print Template::instance()->render($f3->get("STYLE")."/main.htm");
	
	// clear messages once we've rendered them.
	$f3->set( "SESSION.messages", array() );
}

#######################################################
# AJAX
#######################################################

function orcid_json($f3)
{
	$orcid = $f3->get("PARAMS.orcid" );
	$url = $f3->get("ORCID_API_URL")."/v1.1/$orcid/orcid-bio";

	// we are the parent
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Accept: application/orcid+json" ));
	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch); 

	header( "Content-type: text/json" );
	if( $httpCode != 200 )
	{
		print '{"error-desc":{"value":"Got '.$httpCode.' response from the ORCID Server"}}';
		return;
	}
	print $result;
}

#######################################################
# ORCID Actons
#######################################################

function action_logout($f3)
{
	$f3->set("SESSION.authenticated", false);
	$f3->set("SESSION.username", null);

	$f3->push( "SESSION.messages", Template::instance()->render("msg-logout.htm") );

	# return to homepage
	header( "Location: /" );
}

function action_authorise($f3)
{
	local_authen($f3);

	$state = bin2hex(openssl_random_pseudo_bytes(16));
	setcookie('oauth_state', $state, time() + 3600, null, null, false, true);

	$url = $f3->get( "ORCID_OAUTH_AUTHORIZATION_URL" ) . '?' . http_build_query(array(
		'response_type' => 'code',
		'client_id' => $f3->get( "ORCID_OAUTH_CLIENT_ID" ),
		'redirect_uri' => $f3->get( "ORCID_OAUTH_REDIRECT_URI" ),
		'scope' => '/authenticate',
		'state' => $state,
		'family_names' => $f3->get( "SESSION.familyname" ),
		'given_names' => $f3->get( "SESSION.givenname" ),
		'email' => $f3->get( "SESSION.email" ),
	));

	header('Location: ' . $url);
}

function action_return_from_oauth($f3)
{
	local_authen($f3);

	// detect any error message
	if (@$_GET['error'] )
	{
		# this should not happen unless someone is doing something bad
		# or doesn't have cookies enabled
		$f3->push( "SESSION.messages", Template::instance()->render("msg-oauth-error.htm") );
		header( "Location: /profile" );
		return;
	}
		
	// code is returned, check the state
	if (!@$_GET['state'] || $_GET['state'] !== @$_COOKIE['oauth_state']) 
	{
		# this should not happen unless someone is doing something bad
		# or doesn't have cookies enabled
		$f3->push( "SESSION.messages", Template::instance()->render("msg-bad-state.htm") );
		header( "Location: /profile" );
		return;
	}
		
	// fetch the access token
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $f3->get( "ORCID_OAUTH_TOKEN_URL" ),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => array('Accept: application/json'),
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query(array(
			'code' => $_GET['code'],
			'grant_type' => 'authorization_code',
			'client_id' => $f3->get( "ORCID_OAUTH_CLIENT_ID" ),
			'client_secret' => $f3->get( "ORCID_OAUTH_CLIENT_SECRET" )
		))
	));

	$result = curl_exec($curl);
	//$info = curl_getinfo($curl);
	$response = json_decode($result, true);
	if( !@$response["orcid"] )
	{
		$f3->set( "error_message", "" );
		if( @$response["error-desc"][0]["value"] )
		{
			$f3->set( "error_message", $response["error-desc"][0]["value"] );
		}
		
		$f3->push( "SESSION.messages", Template::instance()->render("msg-failed-sync.htm") );
		header( "Location: /profile" );
		return;
	}

	$record = new UosOrcid( $f3->get( "SESSION.pinumber" ), $response["orcid"] );
	if( !$record->write() )
	{
		$f3->push( "SESSION.messages", Template::instance()->render("msg-db-error.htm") );
		header( "Location: /profile" );
		return;
	}

	$f3->push( "SESSION.messages", Template::instance()->render("msg-updated.htm") );
	header( "Location: /profile" );
}

function action_clear($f3)
{
	local_authen($f3);

	$record = UosOrcid::fromPinumber( $f3->get( "SESSION.pinumber" ));
	if( !$record->remove() )
	{
		$f3->push( "SESSION.messages", Template::instance()->render("msg-db-error.htm") );
		header( "Location: /profile" );
		return;
	}

	$f3->push( "SESSION.messages", Template::instance()->render("msg-cleared.htm") );
	header( "Location: /profile" );
}

