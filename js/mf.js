/** Helper Functions **/
var mf_ready = (function(){

    var readyList,DOMContentLoaded,class2type = {};
        class2type["[object Boolean]"] = "boolean";
        class2type["[object Number]"] = "number";
        class2type["[object String]"] = "string";
        class2type["[object Function]"] = "function";
        class2type["[object Array]"] = "array";
        class2type["[object Date]"] = "date";
        class2type["[object RegExp]"] = "regexp";
        class2type["[object Object]"] = "object";

    var ReadyObj = {
        isReady: false,
        readyWait: 1,
        holdReady: function( hold ) {
            if ( hold ) {
                ReadyObj.readyWait++;
            } else {
                ReadyObj.ready( true );
            }
        },
        
        ready: function( wait ) {       
            if ( (wait === true && !--ReadyObj.readyWait) || (wait !== true && !ReadyObj.isReady) ) {
                if ( !document.body ) {
                    return setTimeout( ReadyObj.ready, 1 );
                }

                ReadyObj.isReady = true;
                if ( wait !== true && --ReadyObj.readyWait > 0 ) {
                    return;
                }
                readyList.resolveWith( document, [ ReadyObj ] );
            }
        },
        bindReady: function() {
            if ( readyList ) {
                return;
            }
            readyList = ReadyObj._Deferred();

            if ( document.readyState === "complete" ) {
                return setTimeout( ReadyObj.ready, 1 );
            }

            document.addEventListener( "DOMContentLoaded", DOMContentLoaded, false );
            window.addEventListener( "load", ReadyObj.ready, false );
        },
        _Deferred: function() {
            var callbacks = [], fired, firing, cancelled,
                deferred  = {
                    done: function() {
                        if ( !cancelled ) {
                            var args = arguments,
                                i,
                                length,
                                elem,
                                type,
                                _fired;
                            if ( fired ) {
                                _fired = fired;
                                fired = 0;
                            }
                            for ( i = 0, length = args.length; i < length; i++ ) {
                                elem = args[ i ];
                                type = ReadyObj.type( elem );
                                if ( type === "array" ) {
                                    deferred.done.apply( deferred, elem );
                                } else if ( type === "function" ) {
                                    callbacks.push( elem );
                                }
                            }
                            if ( _fired ) {
                                deferred.resolveWith( _fired[ 0 ], _fired[ 1 ] );
                            }
                        }
                        return this;
                    },

                    resolveWith: function( context, args ) {
                        if ( !cancelled && !fired && !firing ) {
                            args = args || [];
                            firing = 1;
                            try {
                                while( callbacks[ 0 ] ) {
                                    callbacks.shift().apply( context, args );
                                }
                            }
                            finally {
                                fired = [ context, args ];
                                firing = 0;
                            }
                        }
                        return this;
                    },

                    resolve: function() {
                        deferred.resolveWith( this, arguments );
                        return this;
                    },

                    isResolved: function() {
                        return !!( firing || fired );
                    },

                    cancel: function() {
                        cancelled = 1;
                        callbacks = [];
                        return this;
                    }
                };

            return deferred;
        },
        type: function( obj ) {
            return obj == null ?
                String( obj ) :
                class2type[ Object.prototype.toString.call(obj) ] || "object";
        }
    }
       
    DOMContentLoaded = function() {
        document.removeEventListener( "DOMContentLoaded", DOMContentLoaded, false );
        ReadyObj.ready();
    };
 
    function mf_ready( fn ) {
        
        ReadyObj.bindReady();

        var type = ReadyObj.type( fn );
        readyList.done( fn );
    }
    return mf_ready;
})();


/** Main Code **/
mf_ready(function(){
  	var mf_iframe_height;
    var __machform_height 		 = document.getElementById("mf_placeholder").dataset.formheight;
    var __machform_url 			 = document.getElementById("mf_placeholder").dataset.formurl;
    var __machform_title         = document.getElementById("mf_placeholder").dataset.formtitle;
    var mf_iframe_padding_bottom = document.getElementById("mf_placeholder").dataset.paddingbottom;
    

    var ifr = document.createElement('iframe');
	
	ifr.id 		= "mf_iframe";
    ifr.title   = "embedded form";
    
	if(__machform_title !== undefined){
        ifr.title 	= __machform_title;
    }

	ifr.src 	= __machform_url;
	ifr.height  = __machform_height;
	
	ifr.setAttribute("allowTransparency","true");
	ifr.setAttribute("frameborder","0");
    ifr.setAttribute("scrolling","no");
	ifr.setAttribute("style","width:100%;border:none;opacity:1");
    ifr.setAttribute("sandbox","allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation allow-top-navigation-by-user-activation");
	
	ifr.innerHTML = '<a href="'+ __machform_url + '">View Form</a>';
	
	var mf_holder = document.getElementById('mf_placeholder');
	
	mf_holder.parentNode.insertBefore(ifr,mf_holder);
    mf_holder.parentNode.removeChild(mf_holder);

	//receive postMessage to adjust iframe height
	window.addEventListener("message", function receiveMessage(e){
		if(e.data !== undefined){
	        //adjust the height of the iframe     
	        var new_height = 0;

            try{
                new_height = Number( e.data.replace( /.*mf_iframe_height=(\d*(\.\d+)?)(?:&|$)/, '$1' ) );
            }catch{};
            
	        if (!isNaN(new_height) && new_height > 0 && new_height !== mf_iframe_height) {
	          new_height += Number(mf_iframe_padding_bottom); //add padding bottom
	         
	          //height has changed, update the iframe
	          document.getElementById("mf_iframe").height = new_height;

	          //just to make sure the height is being applied 
	          document.getElementById("mf_iframe").setAttribute('style','width:100%;border:none;height:' + new_height + 'px !important');
	        }
      	}
      	

	},false);


	//scroll to the top of iframe upon submissions
	document.getElementById("mf_iframe").addEventListener("load", function(event) {
		if(document.getElementById("mf_iframe").dataset.first_loaded === undefined){
			document.getElementById("mf_iframe").dataset.first_loaded = true;
		}else{
	    	this.scrollIntoView({behavior: "smooth"});
		}
	});

});
