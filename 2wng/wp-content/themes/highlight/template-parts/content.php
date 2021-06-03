<div class="<?php mesmerize_print_archive_entry_class();?>" data-masonry-width="<?php mesmerize_print_masonry_col_class(true);?>">
    <div id="post-<?php the_ID();?>">
        <div class="<?php highlight_print_sticky_class(array("post-container"));?>">
            <div class="post-inner">
                <div class="row">
                    <div class="col-md-4 thumb-col">
                        <div class="thumb-container">
                            <?php mesmerize_print_post_thumb();?>
                        </div>
                    </div>
                    <div class="col-md-8 text-left item-content">
                        <div class="list-padding">
                            <h2 class="post-title text-left h3">
                                <a href="<?php the_permalink();?>" rel="bookmark"><?php the_title();?></a>
                            </h2>
                            <?php get_template_part('template-parts/content-post-meta');?>
                            <div class="post-excerpt">
                                <?php the_excerpt();?>
                            </div>
                        </div>
                    </div>
                </div><!-- /row -->
            </div>
        </div>
    </div>
</div>
