<?
	get_header("gallery");
?>


		 <!-- C O N T E N T -->
		 <div id="content">
				 <div id="blogwrapper">
				 	<div id="blog">
				 		<div id="gallerymain">

							 <div id="gallerymain2">
								 <div id="gallerypic">
				 		                  <img src="" alt="" />
								 </div>
									 <div id="galleryh">
									     <div id="desc"></div>
									     <!--<div style="height: 36px; overflow: hidden; margin-top:70px;"> -->
										 	<input type="image" src="<? echo THEME_PATH; ?>images/close-up-btn.jpg" id="return-closeup" style="display: none;" />                                   <!-- </div> -->
										 <br />
										 <p>If you have questions regarding customization of an order (size, finish or embellishments), please ask: <a href="mailto:inquiries@hearthartassociates.com">inquiries@hearthartassociates.com</a></p>

									 </div>
                                     <div class="clear"></div>
							</div>
							 <div id="carousel">
								 <div id="gallerynav">
			                               <h1>slideshow</h1>
			                               <h2></h2>
								 </div>
			                <?
			                     gallery_get_pics();
							?>

							 </div>
					 	</div>
				 </div>
			 </div>
		 </div>
		 <!-- / C O N T E N T -->

<?
	get_footer();
?>