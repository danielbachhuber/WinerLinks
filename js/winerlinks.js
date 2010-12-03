var winerlinked_paragraphs = [];

/**
 * Identify all of the Winerlinks paragraphs on the page
 */
function winerlinks_allparagraphs() {
    jQuery("p.winerlinks-enabled").each( function( key, value ) {
		if ( value.innerHTML.length > 0 ) {
			winerlinked_paragraphs.push( value );
		}
    });
}

/**
 * Go to a particular highlight on the page
 * Props to the nytimes.com team
 */
function winerlinks_gotohighlight( h, s ) {
	
	if (!h) {
        return;
    }
    var _1b = "<span class='winerlinks-highlight' style='background-color:#FFF8D9;'>";
    var _1c = "</span>";
    for ( var i = 0; i < h.length; i++ ) {
        var _1e = winerlinked_paragraphs[h[i]] || false;
        if (_1e) {
            var _1f = s[h[i].toString()];
            if (_1f == undefined) {
                _1e.innerHTML = _1b + _1e.innerHTML + _1c;
            } else {
                var _20 = winerlinks_getlines( _1e );
                for (var j = 0; j < _1f.length; j++) {
                    var _22 = _20[_1f[j] - 1] || false;
                    if (_22) {
                        _20[_1f[j] - 1] = _1b + _20[_1f[j] - 1] + _1c;
                    }
                }
                _1e.innerHTML = _20.join(". ").replace(/__DOT__/g, ".");
            }
        }
    }
	// @todo Scroll after the highlight. Doesn't work currently
	var graf_location = winerlinked_paragraphs[h[0]] || false;
    if ( graf_location ) {
		graf_location.scrollTo();
    }

}

/**
 * Get the specific lines to highlight
 * Props to the nytimes.com team
 */
function winerlinks_getlines( paragraph ) {
	var _24 = paragraph.innerHTML;
    var _25 = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,Mr,Ms,Mrs,Miss,Msr,Dr,Gov,Pres,Sen,Prof,Gen,Rep,St,Messrs,Col,Sr,Jf,Ph,etc,Sgt,Mgr,oz,cf,viz,sc,ca,Ave,Fr,Rev,No";
    var _26 = "AK,AL,AR,AS,AZ,CA,CO,CT,DC,DE,FL,FM,GA,GU,HI,IA,ID,IL,IN,KS,KY,LA,MA,MD,ME,MH,MI,MN,MO,MP,MS,MT,NC,ND,NE,NH,NJ,NM,NV,NY,OH,OK,OR,PA,PR,PW,RI,SC,SD,TN,TX,UT,VA,VI,VT,WA,WI,WV,WY,AE,AA,AP,NYC,GB,IRL,IE,UK,GB,FR";
    var _27 = "0,1,2,3,4,5,6,7,8,9";
    var _28 = "";
    var _29 = (_25 + "," + _26 + "," + _27 + "," + _28).split(",");
    var len = _29.length;
    for (var i = 0; i < len; i++) {
        _24 = _24.replace(new RegExp((" " + _29[i] + "\\."), "g"), (" " + _29[i] + "__DOT__"));
    }
    return _24.split(". ");
}

/**
 * Read the hash on the location to determine whether we're highlighting anything
 * Props to the nytimes.com team
 */
function winerlinks_readhash() {
	var p,
    a,
    h = [],
    s = {},
    re = /[ph][0-9]+|s[0-9,]+|[0-9]/g,
    lh = location.hash;
    if ( lh ) {
        while ((a = re.exec(lh)) !== null) {
            var f = a[0].substring(0, 1);
            var r = a[0].substring(1);
            if (f != "p") {
				if ( f == "h" ) {
					h.push(parseInt(r));
				} else {
                    a = r.split(",");
                    for (var i = 0; i < a.length; i++) {
                        a[i] = parseInt(a[i]);
                    }
                    s[h[h.length - 1]] = a;
                }
            }
        }
		winerlinks_gotohighlight( h, s );
    }
}

jQuery(document).ready(function(){
	
	winerlinks_allparagraphs();
	winerlinks_readhash();
	
});