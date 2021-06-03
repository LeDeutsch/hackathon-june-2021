<div id="post-<?php the_ID(); ?>"<?php post_class(); ?>>
    <div class="post-content-single">
        <h2><?php mesmerize_single_item_title(); ?></h2>
        <?php get_template_part('template-parts/content-post-meta'); ?>
        <div class="post-content-inner">
            <?php
            if (has_post_thumbnail()) {
                the_post_thumbnail('post-thumbnail', array("class" => "space-bottom-small space-bottom-xs"));
            }
            the_content();
            wp_link_pages(array(
                'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__('Pages:', 'highlight') . '</span>',
                'after'       => '</div>',
                'link_before' => '<span>',
                'link_after'  => '</span>',
                'pagelink'    => '<span class="screen-reader-text">' . esc_html__('Page', 'highlight') . ' </span>%',
                'separator'   => '<span class="screen-reader-text">, </span>',
            ));
            ?>
        </div>
        <?php echo get_the_tag_list('<p class="tags-list-child">Tags: ', ', ', '</p>'); ?>
    </div>
    <?php
    the_post_navigation(array(
        'next_text' => '<span class="meta-nav" aria-hidden="true">' . esc_html__('Next:', 'highlight') . '</span> ' .
                       '<span class="screen-reader-text">' . esc_html__('Next post:', 'highlight') . '</span> ' .
                       '<span class="post-title">%title</span>',
        'prev_text' =>
                       '<span class="meta-nav" aria-hidden="true">' . esc_html__('Previous:', 'highlight') . '</span> ' .
                       '<span class="screen-reader-text">' . esc_html__('Previous post:', 'highlight') . '</span> ' .
                       '<span class="post-title">%title</span>',
    ));
    ?>
    <?php
    if (comments_open() || get_comments_number()):
        comments_template();
    endif;
    ?>
</div>
