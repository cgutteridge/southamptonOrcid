var orcid_fields = [
{ 
	label: "Given Names", 
	path: ["orcid-bio","personal-details","given-names"],
	type: 'value' 
},
{ 
	label: "Family Name", 
	path: ["orcid-bio","personal-details","family-name"],
	type: 'value' 
},
{ 
	label: "Biography", 
	path: ["orcid-bio","biography"],
	type: 'value' 
},
{ 
	label: "Last Modified Date", 
	path: ["orcid-history","last-modified-date"],
	type: 'timestamp' 
},
{ 
	label: "Submission Date", 
	path: ["orcid-history","submission-date"],
	type: 'timestamp' 
}
];


$(document).ready( function() { 
	
	var orcid='{{@record->orcid()}}';
	
	$( '#your-orcid-info' ).html( "Looking up your ORCID..." );
	$.ajax( "/orcid/"+orcid+".json" )
		.success( renderOrcid )
		.fail( function() {
			$('#your-orcid-info').text('Failed to connect to ORCID server.');
		});
} );

function renderOrcid( data )
{
	if( data["error-desc"] )
	{
               	$('#your-orcid-info').text( "Error getting data from ORCID: "+data["error-desc"]["value"] );
		return;
	}

	var profile = data["orcid-profile"];
	var pre = $("<pre><pre>");
	$('#your-orcid-info').html('');
	for( i=0; i<orcid_fields.length; ++i )
	{
		var field = orcid_fields[i];
		var value = profile;
		var ok = true;
		for( j=0;j<field.path.length;++j )
		{
			if( value[field.path[j]] == null ) { ok = false; break; }
			value = value[field.path[j]];
		}
		if( !ok ) { continue; }
		var span = $("<span></span>");
		if( field.type == 'value' )
		{
			span.text( value.value );
		}
		$( "<div class='field'><strong>"+field.label+":</strong> </div>" )
			.append( $("<span class='value'></span> ").append(span) )
			.appendTo( $('#your-orcid-info') );
	}

	$("<pre></pre>")
		.text( JSON.stringify( data['orcid-profile'], null, '\t' ) )
		.appendTo( $('#your-orcid-info') );
}
