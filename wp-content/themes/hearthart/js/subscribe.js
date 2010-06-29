	var fnames = new Array();
	var ftypes = new Array();
	fnames[0]='EMAIL';
	ftypes[0]='email';
	fnames[1]='FNAME';
	ftypes[1]='text';
	fnames[2]='LNAME';
	ftypes[2]='text';
	fnames[3]='MMERGE3';
	ftypes[3]='phone';
	var err_style = '';
	var mce_jQuery = jQuery.noConflict();

	mce_jQuery(document).ready( function($) {
	  var options = {onkeyup: function(){}, onfocusout:function(){}, onblur:function(){}  };
	  var mce_validator = mce_jQuery("#mc-embedded-subscribe-form").validate(options);
	  options = { url: 'http://hearthart.us1.list-manage.com/subscribe/post-json?u=a89645e98fbdf47e3fc496af9&id=5b2ccaa35a&c=?', type: 'GET', dataType: 'json', contentType: "application/json; charset=utf-8",
	                success: mce_success_cb
	            };
	  mce_jQuery('#mc-embedded-subscribe-form').ajaxForm(options);

	})

	function mce_success_cb(resp){
	    if (resp.result=="success"){
	           alert("Thank you for subscribing to our newsletter. We look forward to keeping you updated.");
	           return false;
	        }
	}