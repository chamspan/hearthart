			$(document).ready(function(){				var n ='';
				var total = '';
				var cu = 0;
				var normal = '';
				var closeup = '';

	    	    $.post("/wp-content/themes/hearthart/getphotoinfo.php", { g: 1 },
				  function(data){
                        total = data;
				  });

				$('#carousel').galleryView({
					filmstrip_size: 9,
					frame_width: 90,
					frame_height: 65,
					background_color: 'transparent'
				});
                $('#carousel').show();
				var img = $('.galimg:first').attr('alt');
				var pid = $('.galimg:first').attr('id').replace("id", "");
				$('#gallerypic img').attr("src", "/wp-content/gallery/main/" + img);
	    	    $.post("/wp-content/themes/hearthart/getphotoinfo.php", { id: pid },
				  function(data){
                        $('#galleryh').html(data);
						n = $('.galimg:first').attr('alt').replace(".gif", "");

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


				$('.galimg').click(function(){
                   var img = $(this).attr('alt');
                   var pid = $(this).attr('id').replace("id", "");

				   n = $(this).attr('alt').replace(".gif", "");
				   $("#gallerynav h2").html(n + "/" + total);
                   Cufon.replace('#gallerymain h2', { fontFamily: 'Futura Md BT' });

                   $('#gallerypic img').attr("src", "/wp-content/gallery/main/" + img);

	    	   	 $.post("/wp-content/themes/hearthart/getphotoinfo.php", { id: pid },
				  function(data){
                        $('#galleryh').html(data);
						Cufon.replace('#gallerymain h1', { fontFamily: 'Futura Md BT' });
						Cufon.replace('#gallerymain h2', { fontFamily: 'Futura Md BT' });
						Cufon.replace('#gallerymain h3', { fontFamily: 'Futura Md BT' });
				  });

				  if($(this).attr('title') != '')
				  {				  		$("#return-closeup").show();

						closeup = $(this).attr('title');
						normal = $(this).attr('alt');

				  }
				  else
				  {				  	  $("#return-closeup").hide();
				  }

	  			});

	  			$("#return-closeup").click(function(){
	  				if(cu == 0)
	  				{
	  					 $('#gallerypic img').attr("src", "/wp-content/gallery/close-up/" + closeup);
	  					 $('#return-closeup').attr("src", "/wp-content/themes/hearthart/images/close-up-btn.jpg");
	  					 cu = 1;

	  				}
	  				else
	  				{
	  					 $('#gallerypic img').attr("src", "/wp-content/gallery/main/" + normal);
	  					 $('#return-closeup').attr("src", "/wp-content/themes/hearthart/images/return.jpg");
	  					 cu = 0;
	  				}

	  			})

			});