<?php
/**
 * Template Name: Archive Events Template
 */
?>

<?php while (have_posts()) : the_post(); ?>
	<?php get_template_part('templates/page', 'header'); ?>
	<?php get_template_part('templates/content', 'events'); ?>
<?php endwhile; ?>
