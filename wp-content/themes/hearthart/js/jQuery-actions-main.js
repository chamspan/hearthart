			$(document).ready(function(){

			//where to buy

             var wtbt = 0;
             var s = 0;
             var stext = '';
		    	$("area").click(function(){

		    	    if($("#wrstate").css("height") == '0px')
		    	    {
		    	        $("#wherebottom").show();
		    	    	$("#wrstate").animate({height : "+=116px", top: "-=116px", padding: "+=4px"}, 1000);
		    	    }

		    		var id = $(this).attr("href");

					  	if(wtbt == 0)
					  	{
                            //stext = $("div #states_"+id).text;
							//$("#textstate-0").html($("#state_"+id).html);
                            //alert(stext);
			                $("#textstate-0").animate({height : "+=90px", top: "-=90px"}, 1000);
			                wtbt++;
                        }
                        else
                        {

                                //$("#state").append("<div id='textstate-"+wtbt+"'></div>");
                                //stext = $("#states_"+id).text;
								//$("#textstate-"+wtbt).html($("#state_"+id).html);
								//alert(stext);
                                s = wtbt - 1;
								$("#textstate-"+s).slideUp(1000);
								$(document).find("#textstate-"+wtbt).animate({height : "+=90px", top: "-=90px"}, 1000);
								wtbt++;
                        }

	                return false;
		   		 });

             //FAQ
	   		 $("#faq li a").click(function(){

	   		    if($(this).parent().find("p").css("display") == 'none')
	   		    {
			   		 $("#faq li p").slideUp("normal");
			   		 $(this).parent().find("p").slideDown("normal");

	   		    }
	   		    else
	   		    {
	   		    	$(this).parent().find("p").slideUp("normal");
	   		    }

	   		        return false;
			   		 });


			   $("#syq").click(function(){

                   if($("#ask").css("display") == 'none')
                   {
			   			$("#ask").slideDown("normal");
                   }
                   else
                   {
                   	    $("#ask").slideUp("normal");
                   }
			   	});

			   $("#faqsend").click(function(){

			   		var text = $("#qtext").val();
		    	    $.post("/wp-content/themes/hearthart/sendfaq.php", { text: text },
					  function(data){

                           $("#serveranswer").html(data);
                           $("#qtext").val("");

					  })
                    return false;
			   	});


			   	//Email validator

			   	$("#postcommentbutton").click(function(){
			   		var str = $("#comment-form-email").val();

					    var reg =  /^[-0-9a-z_\.]+@[-0-9a-z\.]+\.[a-z]{2,3}$/i;
					    var result=reg.test(str) ? "true" : "false";
					        if(result == 'false' && str.length > 0)
					        {
					        	alert("Please fill valid email!");
					        	//$("#mess").html("Please fill valid email!");
					        	return false;
					        }
					        if(str == '')
					        {
					        	alert("Please fill email!");
					        	//$("#mess").html("Please fill email!");
					        	return false;
					        }

					    else
					    {

					        //$("#mess").hide();
					        return true;
					    }

			   	})

			});