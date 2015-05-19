<?php
/*
Template Name: Record Tracker Page
*/
include(get_template_directory() . '/header.php');
?>
     <div class="page" id="content">
	  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	       <div class="post">
		    <div id="post-<?php the_ID(); ?>">
			 <h1><?php the_title(); ?></h1>
	       
			 <div class="pagecontent">
			      <?php the_content(); include_once('template-widget.php'); ?>
			 </div>
		    </div>
	       </div>
     
    	       <?php endwhile; ?>
	       <?php endif; ?>
     </div><!-- Closes the content div-->
     

	<?php include(get_template_directory() . '/sidebar2.php'); ?>
         
<?php include(get_template_directory() . '/footer.php'); ?>
