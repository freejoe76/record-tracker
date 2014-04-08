<?php
/*
Template Name: Thermometer Page
*/
include(get_template_directory() . '/header.php');
?>
     <div id="content">
	  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	       <div class="post">
		    <div id="post-<?php the_ID(); ?>">
			 <h1><?php the_title(); ?></h1>
	       
			 <div class="pagecontent">
			      <?php the_content(); include_once('template-widget.php'); ?>
			 </div>
		    </div>
	       </div>
     
    	       <?php endwhile; else: ?>
	       <div class="noresults">
		    <h1>Not Found: 404 Error</h1>
		    Oops, it appears that page doesn't exist. Well, we're not all perfect, but we try. Can you try this again or maybe visit our <a 
title="Our Site" href="http://blogs.denverpost.com">Home Page</a> to start fresh. We'll do better next time.
	       </div>
	       <?php endif; ?>
     </div><!-- Closes the content div-->
     

	<?php include(get_template_directory() . '/sidebar2.php'); ?>
         
<?php include(get_template_directory() . '/footer.php'); ?>
