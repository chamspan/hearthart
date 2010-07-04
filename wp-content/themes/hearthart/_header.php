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
		 <script type="text/javascript" src="<? echo THEME_PATH; ?>js/slideshow.js"></script>
		 <script type="text/javascript" src="<? echo THEME_PATH; ?>js/jquery.maphilight.js"></script>
		 <script type="text/javascript" src="<? echo THEME_PATH; ?>js/jquery.metadata.js"></script>
		 <script type="text/javascript">
			Cufon.replace('div.fmenu li', { fontFamily: 'Futura Md BT' });
			$(function() {$('#mainwindow').cycle();
			              $.fn.maphilight.defaults = {
							fill: true,
							fillColor: '7d4b0c',
							fillOpacity: 1,
							stroke: false,
							strokeColor: '7d4b0c',
							strokeOpacity: 1,
							strokeWidth: 1,
							fade: true,
							alwaysOn: false,
							neverOn: false,
							groupBy: false
						}

						  $('#imgmap').maphilight();
			});
		 </script>
		 <script type="text/javascript" src="<? echo THEME_PATH; ?>js/jQuery-actions-main.js"></script>
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
				 --><li><a href="/gallery" id="gallery"></a></li><!--
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