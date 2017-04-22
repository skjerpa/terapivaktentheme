function faq_loop () {
$menu =   '<div class="entry-content dishes">';
// check if the repeater field has rows of data
        if( have_rows('faq_repeater') ):
            while ( have_rows('faq_repeater') ) : the_row();
            // Your loop code
            $menu .= '<h2>' . get_sub_field('faq_title') . '</h2>';
        endwhile;
        else :
        // no rows found
        endif;
    $menu .= '</div>';
    // Code
    return $menu;
}
add_shortcode('faq-entries', 'faq-loop');
