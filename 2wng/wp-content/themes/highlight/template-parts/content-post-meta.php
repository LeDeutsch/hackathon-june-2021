<?php
if (!apply_filters('mesmerize_show_post_meta', true)) {
    return;
}
?>
<div class="row post-meta small">
    <div class="col-md-10 col-xs-9">
        <ul class="is-bar">
            <li><?php esc_html_e('by', 'highlight'); ?> <?php the_author_posts_link(); ?></li>
            <li><?php esc_html_e('in', 'highlight'); ?> <?php the_category(', ');?></li>
            <li><?php esc_html_e('on', 'highlight'); ?> <?php the_time(get_option('date_format')); ?></li>
        </ul>
    </div>
    <div class="col-md-2 col-xs-3 text-right">
        <i class="font-icon-post fa fa-comment-o"></i><span><?php echo get_comments_number(); ?></span>
    </div>
</div>
