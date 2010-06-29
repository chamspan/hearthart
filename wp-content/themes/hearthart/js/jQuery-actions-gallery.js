			$(document).ready(function(){
				var n ='';
				var total = '14';
				var cu = 0;
				var normal = '';
				var closeup = '';
                var sb = 0;
	    	    $.post("/wp-content/themes/hearthart/getphotoinfo.php", { g: 1 },
				  function(data){
                        total = data;
				  });


				$('#carousel').galleryView({
					filmstrip_size: 9,
					frame_width: 90,
					frame_height: 65,
					background_color: 'transparent',
					slide_method: 'pointer'
				});


                $('#carousel').show();
				var img = $('.galimg:first').attr('alt');
				var pid = $('.galimg:first').attr('id').replace("id", "");
				$('#gallerypic img').attr("src", "/wp-content/gallery/main/" + img);

	    	    $.post("/wp-content/themes/hearthart/getphotoinfo.php", { id: pid },
				  function(data){
                        $('#desc').html(data);
                        data = '';
                        n = $('.galimg:first').attr('class').replace("galimg ", "");
						$("#gallerynav h2").html(n + "/" + total);
                        Cufon.replace('#gallerymain h1', { fontFamily: 'Futura Md BT' });
						Cufon.replace('#gallerymain h2', { fontFamily: 'Futura Md BT' });
						Cufon.replace('#gallerymain h3', { fontFamily: 'Futura Md BT' });
						Cufon.replace('#gallerynav h2', { fontFamily: 'Futura Md BT' });

						  if($('.galimg:first').attr('title') != '')
						  {
						  		$("#return-closeup").show();
						  		closeup = $('.galimg:first').attr('title');
						  		normal = $(this).attr('alt');
						  }


				  });


				$('.galimg').click(function(){						   cu = 0;
						   $("#return-closeup").hide();
						  if($(this).attr('title') != '')
						  {

								closeup = $(this).attr('title');
								normal = $(this).attr('alt');
								sb = 1;

						  }
						  else
						  {
						  	  $("#return-closeup").hide();
						  	  sb = 0;
						  }

						   $('#gallerypic img').hide();
						   $('#desc').html("");
						   $('#gallerypic').append("<div id='loading' style='margin-top: 120px; margin-left: 40px; font-weight: bold;'>Loading...</div>");
						   $('body').css({'cursor':'wait'});
						   $('#return-closeup').attr("src", "/wp-content/themes/hearthart/images/close-up-btn.jpg");

		                   var img = $(this).attr('alt');
		                   var pid = $(this).attr('id').replace("id", "");

						   n = $(this).attr('class').replace("galimg ", "");
						   $('#gallerypic img').attr("src", "/wp-content/gallery/main/" + img);

						   $("#gallerynav h2").html(n + "/" + total);
		                   Cufon.replace('#gallerymain h2', { fontFamily: 'Futura Md BT' });
                           $.post("/wp-content/themes/hearthart/getphotoinfo.php", { id: pid },
							function(data){
				                   $('#gallerypic img').ready(function(){

										$("#loading").remove();
										$('#gallerypic img').show();
										$('body').css({'cursor':'default'});

											  if(sb == 1)
											  {
											  		$("#return-closeup").show();

											  }

								  	})
											 $('#desc').html(data);
											 data = '';
											 Cufon.replace('#gallerymain h1', { fontFamily: 'Futura Md BT' });
											 Cufon.replace('#gallerymain h2', { fontFamily: 'Futura Md BT' });
											 Cufon.replace('#gallerymain h3', { fontFamily: 'Futura Md BT' });
						  })

	  			});

	  			$("#return-closeup").click(function(){
				   $('#gallerypic img').hide();
				   $('#gallerypic').append("<div id='loading' style='margin-top: 120px; margin-left: 40px; font-weight: bold;'>Loading...</div>");                   $('body').css({'cursor':'wait'});

	  				if(cu == 0)
	  				{
	  					 $('#gallerypic img').attr("src", "/wp-content/gallery/close-up/" + closeup);
	  				}
	  				else
	  				{
	  					 $('#gallerypic img').attr("src", "/wp-content/gallery/main/" + normal);
	  				}


                    $('#gallerypic img').ready(function(){
						$("#loading").remove();
						$('#gallerypic img').show();
						 $('body').css({'cursor':'default'});


			  				if(cu == 0)
			  				{

			  					 $('#return-closeup').attr("src", "/wp-content/themes/hearthart/images/return.jpg");
			  					 cu = 1;

			  				}
			  				else
			  				{

			  					 $('#return-closeup').attr("src", "/wp-content/themes/hearthart/images/close-up-btn.jpg");
			  					 cu = 0;
			  				}


                    })	  			})

			});