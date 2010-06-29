<?
	get_header();
?>
		 <!-- C O N T E N T -->
		 <div id="content">
			 <div id="blogwrapper"><div id="blog"><div id="faq">
				 <div id="wrfaqdiv">
					 <div id="faqdiv">
						 <p>Frequently Asked Questions are those that our visitors and members most frequently ask us. Here you will be able to find quick simple answers to many questions you my have. Feel free to submit any questions or comments concerning our website.</p>
						 <input id="syq" type="image" src="<? echo THEME_PATH; ?>images/syq.png" />
					 </div>
				 </div>
				 <div id="ask">
					 <p>Your question:</p>
					 <div id="wrask">
					    <form action="#" method="post">
					      <fieldset>
							 <textarea name="question" rows="5" cols="50" id="qtext"></textarea>
							 <input id="faqsend" type="image" src="<? echo THEME_PATH; ?>images/submit.png" name="send" value="Submit" />
					      </fieldset>
					   </form>
					 </div>
				     <p id="serveranswer"></p>
                  </div>
                   <?
                     while (have_posts()) : the_post();

                        the_content();

                    endwhile;
                   ?>

				 <div id="materialsbottom"></div>
			 </div></div></div>
		 </div>
		 <!-- / C O N T E N T -->
<?
	get_footer();
?>