<?
	get_header();
?>


		 <!-- C O N T E N T -->
		 <div id="content">
			 <div id="blogwrapper"><div id="blog"><div id="welcometoblog">
				 <div class="posts">
                   <? blog_get_content(); ?>
				 </div>
				 <div id="wrcategories"><div id="categories">
					 <h1><span id="categoriespic"></span>CATEGORIES</h1>
                      <? get_block_blogcat();?>
					 <h1><span id="archivespic"></span>ARCHIVES</h1>
                     <? get_block_blogarchive();?>
                     <h1><span id="recentspic"></span>RECENT POSTS</h1>
                     <? get_blog_blogrecent();?>
					 <div id="categoriesbottom"></div>
				 </div></div>
				 <div class="clear"></div>
				 <div id="blogbottom"></div>
			 </div></div></div>
		 </div>
		 <!-- / C O N T E N T -->


<?
	get_footer();
?>