jQuery(document).ready(function($){
    var mf_iframe_height;
    var mf_iframe_padding_bottom = 0;

    if($("#mf_placeholder").data("formheight") !== undefined){
      __machform_height = $("#mf_placeholder").data("formheight");
    }

    if($("#mf_placeholder").data("formurl") !== undefined){
      __machform_url = $("#mf_placeholder").data("formurl");
    }

    if($("#mf_placeholder").data("formtitle") !== undefined){
      __machform_title = $("#mf_placeholder").data("formtitle");
    }else{
      __machform_title = 'embedded form';
    }
    
    if($("#mf_placeholder").data("paddingbottom") !== undefined){
      var mf_iframe_padding_bottom = $("#mf_placeholder").data("paddingbottom");
    }

    var mf_iframe = $('<iframe id="mf_iframe" height="' + __machform_height + '" allowTransparency="true" frameborder="0" scrolling="no" title="' + __machform_title + '" style="width:100%;border:none" src="'+ __machform_url +'"><a href="'+ __machform_url +'">View Form</a></iframe>');
    $("#mf_placeholder").after(mf_iframe);
    $("#mf_placeholder").remove();

    $.receiveMessage(function(e){      
      if(e.data.indexOf('run_safari_cookie_fix') != -1){
        //do nothing
        return;
      }else{
        //adjust the height of the iframe     
        var new_height = Number( e.data.replace( /.*mf_iframe_height=(\d*(\.\d+)?)(?:&|$)/, '$1' ) );
        if (!isNaN(new_height) && new_height > 0 && new_height !== mf_iframe_height) {
          new_height += mf_iframe_padding_bottom; //add padding bottom
          
          //height has changed, update the iframe
          mf_iframe.height(mf_iframe_height = new_height);

          //just to make sure the height is being applied 
          document.getElementById("mf_iframe").setAttribute('style','width:100%;border:none;height:' + new_height + 'px !important');
        }
      }
      
    });

    //scroll to the top of iframe upon submissions
    $("#mf_iframe").on('load',function(){
        if($(this).data("first_loaded") === undefined){
          $(this).data("first_loaded",true);
        }else{
          parent.scrollTo(0,$('#mf_iframe').offset().top - 100);
        }
    });
});
