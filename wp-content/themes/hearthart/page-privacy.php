<?
	get_header();
?>
		 <!-- C O N T E N T -->
		 <div id="content">
			 <div id="blogwrapper"><div id="blog"><div id="privacy">
				 <div class="alignleft"><img src="<? echo THEME_PATH; ?>images/privacypic.jpg" alt="" /></div>
				 <div id="text">

                   <?
                     while (have_posts()) : the_post();

                        the_content();

                    endwhile;
                   ?>
				 </div>
				 <div class="clear"></div>
				 <div id="termsbottom"></div>
			 </div></div></div>
		 </div>
		 <!-- / C O N T E N T -->
<?
	get_footer();
?>