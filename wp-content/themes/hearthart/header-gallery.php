<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	 <head>
		 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		 <title>
			<?php
			 	if (function_exists('seo_title_tag')) { seo_title_tag();echo '|';bloginfo('name'); } else { bloginfo('name'); wp_title();}
			?>
		 </title>
		 <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
		 <script type="text/javascript" src="http://downloads.mailchimp.com/js/jquery.validate.js"></script>
		 <script type="text/javascript" src="http://downloads.mailchimp.com/js/jquery.form.js"></script>
		 <script type="text/javascript" src="<? echo THEME_PATH; ?>js/subscribe.js"></script>
		 <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
		 <script type="text/javascript" src="<? echo THEME_PATH; ?>js/cufon-yui.js"></script>
		 <script type="text/javascript" src="<? echo THEME_PATH; ?>js/FuturaBT.font.js"></script>
		 <script type="text/javascript" src="<? echo THEME_PATH; ?>js/jquery.timers-1.1.2.js"></script>
		 <script type="text/javascript" src="<? echo THEME_PATH; ?>js/jquery.easing.1.2.js"></script>
		 <script type="text/javascript" src="<? echo THEME_PATH; ?>js/jquery.galleryview-1.1.js"></script>
		 <script type="text/javascript">
			Cufon.replace('div.fmenu li', { fontFamily: 'Futura Md BT' });
			Cufon.replace('#gallerymain h1', { fontFamily: 'Futura Md BT' });
			Cufon.replace('#gallerymain h2', { fontFamily: 'Futura Md BT' });
			Cufon.replace('#gallerymain h3', { fontFamily: 'Futura Md BT' });
			Cufon.replace('#gallerynav h2', { fontFamily: 'Futura Md BT' });
		 </script>
         <script type="text/javascript" src="<? echo THEME_PATH; ?>js/jQuery-actions-gallery.js"></script>
	<style type="text/css" media="screen">
		@import url( <?php bloginfo('stylesheet_url'); ?>);
	</style>
	<?php wp_head(); ?>
	 </head>
	 <body>
		 <div id="body">
		 <!-- H E A D E R -->
			 <div id="header">
				 <ul class="menu">
					 <li><a href="/" id="home"></a></li><!--
					 --><li id="galery">
						 <a href="/gallery" id="gallery"></a>
						 <ul id="submenu"><li><a href="/gallery/materials" id="materials"></a></li></ul>
					 </li><!--
					 --><li><a href="/buy" id="where"></a></li>
				 </ul>
				 <a href="http://<? echo $_SERVER['HTTP_HOST']?>/"><img class="logo" src="<? echo THEME_PATH; ?>images/logo.png" alt="" /></a>
				 <ul class="menu">
					 <li><a href="/profile" id="profile"></a></li><!--
					 --><li><a href="/contact" id="contact"></a></li><!--
					 --><li><a href="/flatter" id="flatter"></a></li>
				 </ul>
			 </div>
		 <!-- / H E A D E R -->