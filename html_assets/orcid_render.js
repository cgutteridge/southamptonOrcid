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
	label: "Last Modified", 
	path: ["orcid-history","last-modified-date"],
	type: 'time' 
},
{ 
	label: "Created", 
	path: ["orcid-history","submission-date"],
	type: 'time' 
},
{
	label: "Websites", 
	path: ["orcid-bio","researcher-urls","researcher-url"],
	type: 'links' 
},
{
	label: "Keywords", 
	path: ["orcid-bio","keywords","keyword"],
	type: 'values' 
},
{
	label: "Full Record",
	path: [],
	type: 'debug'
}
];


function updateOrcidRecord( orcid, target )
{	
	$( '#your-orcid-info' ).html( "Looking up your ORCID..." );
	$.ajax( "/orcid/"+orcid+".json" )
		.success( renderOrcid.bind(target) )
		.fail( (function() {
			this.text('Failed to connect to ORCID server.');
		}).bind(target) );
}

function renderOrcid( data )
{
	if( data["error-desc"] )
	{
               	this.text( "Error getting data from ORCID: "+data["error-desc"]["value"] );
		return;
	}

	var profile = data["orcid-profile"];
	this.html('');
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
		if( field.type == 'time' )
		{
			span.text( new Date(value.value).toLocaleString() );
		}
		if( field.type == 'values' )
		{
			var list = [];
			for( j=0;j<value.length;++j )
			{
				list.push( value[j].value );
			}
			span.text( list.join( ", ") );
		}
		if( field.type == 'links' )
		{
			var first = true;
			for( j=0;j<value.length;++j )
			{
				if( !first ) { span.append( $("<span>, </span>") ); }
				span.append( 
					$("<a></a>")
						.attr( 'href', value[j].url.value )
						.text( value[j]["url-name"].value ) );
				first = false;
			}
		}

		if( field.type == 'debug' )
		{
			if( orcidConfig.site_stage != 'dev' )
			{
				// don't show debug info on anything but dev.
				continue;
			}
			span = $("<pre></pre>").text( "This field is only shown on development servers, not preprod or live.\r\n"+JSON.stringify( value, null, 2 ).replace( /\n/g, "\r\n") );
		}

		$( "<div class='field'><strong>"+field.label+":</strong> </div>" )
			.append( $("<span class='value'></span> ").append(span) )
			.appendTo( this );
	}
}

// poly fill for Function.prototype.bind (missing on old IE)

if (!Function.prototype.bind) {
  Function.prototype.bind = function(oThis) {
    if (typeof this !== 'function') {
      // closest thing possible to the ECMAScript 5
      // internal IsCallable function
      throw new TypeError('Function.prototype.bind - what is trying to be bound is not callable');
    }

    var aArgs   = Array.prototype.slice.call(arguments, 1),
        fToBind = this,
        fNOP    = function() {},
        fBound  = function() {
          return fToBind.apply(this instanceof fNOP && oThis
                 ? this
                 : oThis,
                 aArgs.concat(Array.prototype.slice.call(arguments)));
        };

    fNOP.prototype = this.prototype;
    fBound.prototype = new fNOP();

    return fBound;
  };
}
