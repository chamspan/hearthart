<?
	get_header("gallery");
?>
		 <!-- C O N T E N T -->
		 <div id="content">
			 <div id="blogwrapper"><div id="blog"><div id="gallerymaterials">
				 <div id="naturalmaterials">
					 <div class="materialspic"><img src="<? echo THEME_PATH; ?>images/materialspic1.jpg" alt /></div>
					 <div class="materialspic"><img src="<? echo THEME_PATH; ?>images/materialspic2.jpg" alt /></div>
					 <div class="materialspic"><img src="<? echo THEME_PATH; ?>images/materialspic3.jpg" alt /></div>
					 <div class="materialspic"><img src="<? echo THEME_PATH; ?>images/materialspic4.jpg" alt /></div>
					 <div class="clear"></div>


                 <?
                     while (have_posts()) : the_post();

                        the_content();

                    endwhile;
                 ?>



				 </div>
				 <!-- f i n i s h e s -->
				 <div id="wrfinishes"><div id="finishes">


                   <? get_finishes();?>


					 <div id="categoriesbottom"></div>
				 </div></div>
				 <!-- / f i n i s h e s -->
				 <div class="clear"></div>
				 <div id="materialsbottom"></div>
			 </div></div></div>
		 </div>
		 <!-- / C O N T E N T -->
<?
	get_footer();
?>