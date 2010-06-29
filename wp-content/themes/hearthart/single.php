<?
	get_header();
?>


		 <!-- C O N T E N T -->
		 <div id="content">
			 <div id="blogwrapper"><div id="blog"><div id="welcometoblog">
				 <div class="posts">
					 <!-- a r t i c l e -->
                     <? blog_get_post(); ?>
<?
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

	if ( post_password_required() ) { ?>
		<p class="nocomments">This post is password protected. Enter the password to view comments.</p>
	<?php
		return;
	}
?>

<a name="comments"></a>
<!-- You can start editing here. -->
					 <!-- c o m m e n t s-->
					 <div id="comments">
<?

    $post_id = get_the_ID();
  	$str = "select * from `wp_comments` where `comment_post_ID` = ".$post_id." and `comment_approved` = 1";
  	//echo $str;
    $query = mysql_query($str);
    while($row = mysql_fetch_object($query))
    {

                      ?>
                        <!-- COMMENT -->
						 <h1><? echo $row->comment_author; ?></h1>
						 <h2>Posted <? echo $row->comment_date; ?>.</h2>
                         <? echo $row->comment_content; ?>
                        <!-- // -->
<?
	}
?>

						 <div id="postcomment">
							 <div id="headercomment">
								 <h1>POST YOUR COMMENT</h1>
								 <h2>All fields required</h2>
							 </div>
							 <div id="formcomment">
								 <form action="/wp-comments-post.php" method="post" id="commentform">
								 <fieldset>
									 <p>Name</p><input class="textcomment" type="text" name="author" value=""/>
									 <div class="clear"></div>
									 <p>Email</p><input class="textcomment" type="text" name="email" value="" id="comment-form-email"/>
									 <div class="clear"></div>
									 <p>Message</p><textarea id="message" name="comment" rows="" cols=""></textarea>
									 <div class="clear"></div>
									 <input type="hidden" name="url" id="url" value="" size="22" tabindex="3" />
                                     <input type='hidden' name='comment_post_ID' value='<? the_ID(); ?>' id='comment_post_ID' />
									 <input type='hidden' name='comment_parent' id='comment_parent' value='0' />
									 <input id="postcommentbutton" type="image" src="<? echo THEME_PATH; ?>images/postcomment.gif" name="submit" />
								 </fieldset>
								 </form>
							 </div>
						 </div>
					 </div>
					 <!-- / c o m m e n t s-->
				 </div>
				 <!-- c a t e g o r i e s -->
				 <div id="wrcategories"><div id="categories">
					 <h1><span id="categoriespic"></span>CATEGORIES</h1>
						 <? get_block_blogcat();?>
					 <h1><span id="archivespic"></span>ARCHIVES</h1>
						<? get_block_blogarchive();?>
					 <h1><span id="recentspic"></span>RECENT POSTS</h1>
                       <? get_blog_blogrecent();?>
					 <div id="categoriesbottom"></div>
				 </div></div>
				 <!-- / c a t e g o r i e s -->
				 <div class="clear"></div>
				 <div id="blogbottom"></div>
			 </div></div></div>
		 </div>
		 <!-- / C O N T E N T -->

<?
	get_footer();
?>