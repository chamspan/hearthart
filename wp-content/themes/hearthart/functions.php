<?

//Globas

DEFINE('THEME_PATH','/wp-content/themes/hearthart/');
DEFINE('BLOG_CAT_ID', 3);

//main

function blog_get_content()
{

						while (have_posts()) : the_post();
                        $day = get_the_modified_date('d/F/Y');
                        $dat = explode("/", $day);
                      ?>

					 <div class="headerpost">
						 <div class="date">
							 <div class="day"><? echo $dat[0]; ?></div>
							 <div class="month"><? echo $dat[1]; ?></div>
						 </div>
						 <h1><? echo the_title(); ?></h1>
						 <h2>Posted by <? the_author(); ?>, <? echo $dat[2]; ?>. <? the_category(",");?></h2>
						 <div class="clear"></div>
					 </div>
                      <? the_content(); ?>
					 <div class="footerpost">
						 <div class="footerposts">POSTS: <? comments_number('0','1','%'); ?></div>
						 <a href="<? the_permalink(); ?>">Read entire entry</a>
					 </div>

                       <?
                       endwhile;
}


function blog_get_post()
{

						while (have_posts()) : the_post();
                        $day = get_the_modified_date('d/F/Y');
                        $dat = explode("/", $day);
                      ?>

					 <div class="headerpost">
						 <div class="date">
							 <div class="day"><? echo $dat[0]; ?></div>
							 <div class="month"><? echo $dat[1]; ?></div>
						 </div>
						 <h1><? echo the_title(); ?></h1>
						 <h2>Posted by <? the_author(); ?>, <? echo $dat[2]; ?>. <? the_category(",");?></h2>
						 <div class="clear"></div>
					 </div>
                      <? the_content(); ?>
					 <div id="footerarticle">
						 <div id="footeremail"><a href="#" id="share-link" onclick="iBeginShare.handleLink(event);return false;">Email this Article to a Friend</a></div>
						 <script type="text/javascript">
							var el = document.getElementById('share-link');
							el.params = {title: 'Heart Art', link: '<? echo $_SERVER['HTTP_HOST']; ?>', skin: 'blue'};
							</script>
						 <p>Share &amp; Enjoy this Article</p>
						 <ul>

							 <li><a id="frss" href="<? $show ='rss2_url'; echo get_bloginfo_rss($show); ?>"></a></li>

						</ul>
						<ul class="a2a_dd">
							 <li><a id="ftwitter" href=""></a></li>
							 <li><a id="ffacebook" href=""></a></li>
							 <li><a id="ficon" href=""></a></li>
						</ul>
						 <script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
					 </div>

                       <?
                       endwhile;
}


function get_block_blogcat()
{
?>
                    <ul>
<?
			$args = array(
			    'orderby'            => 'name',
			    'order'              => 'ASC',
			    'show_last_update'   => 1,
			    'style'              => 'list',
			    'show_count'         => 0,
			    'hide_empty'         => 1,
			    'use_desc_for_title' => 1,
			    'child_of'           => BLOG_CAT_ID,
			    'current_category'   => 4,
			    'hierarchical'       => true,
			    'title_li'           => __( '' ),
			    'number'             => NULL,
			    'echo'               => 1,
			    'depth'              => 1 );

           wp_list_categories($args);

?>
					</ul>
<?
}

function get_block_blogarchive()
{
?>


<ul>

<?
					$args = array(
				    'type'            => 'monthly',
				    'format'          => 'html',
				    'before'          => '',
				    'after'           => '',
				    'show_post_count' => true,
				    'echo'            => 1 );

                    wp_get_archives( $args );

?>
</ul>

<?
}

function get_blog_blogrecent()
{

	$show_posts = 6;
	$count = 0;

?>

<ul>
<?
      $recent_posts = wp_get_recent_posts(20);
      foreach($recent_posts as $post)
      {

          if($count < $show_posts)
          {
                echo "<li><a href=\"".get_permalink($post["ID"])."\">".$post["post_title"]."</a></li>";
                $count++;

          }

	  }
?>
</ul>
<?
}

function gallery_get_pics()
{
      $query = mysql_query("select * from wp_ngg_pictures where galleryid = 1 and exclude = 0 order by sortorder ASC");
      if(mysql_num_rows($query) > 0)
      {
          echo "<ul class='filmstrip'>";

          while($row = mysql_fetch_object($query))
          {
	          $n = substr($row->filename, 0, strlen($row->filename) - 4);
	          $nn = $n."_closeup.jpg";
              $fname = $n.".gif";
	          echo "<li><img src='/wp-content/gallery/main/thumbs/thumbs_".$fname."' alt='".$row->filename."' class='galimg ".$row->sortorder."' id='id".$row->pid."' title='";


	          $check = $_SERVER['DOCUMENT_ROOT'].'/wp-content/gallery/close-up/'.$nn;
	          if(file_exists($check))
	          {
	          		echo $nn;
	          }

	          echo "'/></li>";

          }

        echo "</ul>";
      }


}


function get_finishes()
{
	$id = 224;
	$cont = get_page($id);

	echo $cont->post_content;

}

function gallery_img_preload()
{
      $query = mysql_query("select * from wp_ngg_pictures where galleryid = 1 and exclude = 0 order by sortorder ASC");
      if(mysql_num_rows($query) > 0)
      {
          while($row = mysql_fetch_object($query))
          {

	          $n = substr($row->filename, 0, strlen($row->filename) - 4);
	          $nn = $n."_closeup.jpg";

	          echo "<img src='/wp-content/gallery/main/".$row->filename."' />";

	          $check = $_SERVER['DOCUMENT_ROOT'].'/wp-content/gallery/close-up/'.$nn;
	          if(file_exists($check))
	          {
	          		echo "<img src='/wp-content/gallery/close-up/".$nn."' />";
	          }


          }

      }

}

function wheretobuy_states_info()
{
 	$query = mysql_query("select ID, post_content from wp_posts where ID in(96, 55, 78, 43, 59, 138, 134, 114, 100, 45, 84, 90, 74, 66, 92, 80, 102, 132, 142, 88, 72, 86, 76, 49, 64, 47, 146, 136, 57, 104, 110, 60, 98, 68, 41, 106, 140, 112, 62, 94, 70, 53, 144, 108, 39, 130, 82, 51)");
 	$count = 0;
 	while($row = mysql_fetch_object($query))
 	{ 		echo "<div id='textstate-".$count."'>".$row->post_content."</div>";
        $count++;
 	}

}

?>