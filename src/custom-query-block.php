<?php 

function wpmovies_is_demo_variation($parsed_block)
{
    return isset($parsed_block['attrs']['namespace'])
        && substr($parsed_block['attrs']['namespace'], 0, 8) === 'wpmovies';
}

function wpmovies_update_demo_query($pre_render, $parsed_block)
{
    if ('core/query' !== $parsed_block['blockName']) {
        return;
    }

    if (wpmovies_is_demo_variation($parsed_block)) {
        add_filter(
            'query_loop_block_query_vars',
            'wpmovies_build_cast_query',
            10,
            1
        );
    }
};

function wpmovies_build_cast_query($query)
{
    global $post;
    $taxonomy_type = null;
    if ($post->post_type === 'movies') {
        $taxonomy_type = 'movies_tax';
    }
    if ($post->post_type === 'actors') {
        $taxonomy_type = 'actors_tax';
    }
    if ($taxonomy_type === null) {
        return $query;
    }
    $wp_term = get_term_by('slug', $post->post_name, $taxonomy_type);
    if (!$wp_term) {
        return $query;
    }
    $cast_query = array('taxonomy' => $taxonomy_type, 'terms' => array($wp_term->term_id), 'include_children' => false);
    $new_query = array_replace($query, array('tax_query' => array($cast_query)));
    return $new_query;
}

add_action('pre_render_block', 'wpmovies_update_demo_query', 10, 2);

function wpmovies_add_query_loop_variations()
{
    wp_enqueue_script(
        'query-loop-variations',
        plugin_dir_url(__FILE__) . '../build/index.js',
        array('wp-blocks')
    );
}
add_action('admin_enqueue_scripts', 'wpmovies_add_query_loop_variations');
