<?php
/**
 * BD Wordpress Utils
 *
 * @package     BD_Wordpress_Utils
 * @author      BD Wordpress Utils
 * @copyright   2024 Brianna Deleasa
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: BD Wordpress Utils
 * Plugin URI:  https://example.com/plugin-name
 * Description: A small plugin with some Wordpress helper/utils functions.
 * Version:     2.0.0
 * Author:      Brianna Deleasa
 * Author URI:  https://example.com
 * Text Domain: plugin-slug
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */


if ( ! function_exists( 'write_log' ) ) {
    /**
     * Utility function for logging arbitrary variables to the error log.
     *
     * Set the constant WP_DEBUG to true and the constant WP_DEBUG_LOG to true to log to wp-content/debug.log.
     * You can view the log in realtime in your terminal by executing `tail -f debug.log` and Ctrl+C to stop.
     *
     * @since 1.0.0
     *
     * @param mixed $log Whatever to log.
     * @return void
     */
    function write_log( mixed $log ): void
    {
        if ( true === WP_DEBUG ) {
            if ( is_scalar( $log ) ) {
                error_log( $log );
            } else {
                error_log( print_r( $log, true ) );
            }
        }
    }
}


if ( ! function_exists( 'bd_get_svg_code' ) ) {
    /**
     * Outputs the full svg code for the given SVG image url.
     *
     * @since 1.0.0
     *
     * @param $file string The URL or path to the SVG file.
     * @return bool|string svg <use> HTML or false if file doesn't exist.
     */
    function bd_get_svg_code( string $file ): bool|string
    {
        if ( ! file_exists( $file ) ) {
            return false;
        }

        return file_get_contents( $file );
    }
}

if ( ! function_exists( 'bd_insert_array_value' ) ) {
    /**
     * Helper function that easily inserts an array item into an array
     * in a specific position. For example:
     *
     *      $alphabet = array( 'A' => 1, 'B' => 2, 'C' => 3, 'E' => 5 );
     *
     * And you want EASILY and READIBLY to insert:
     *
     *      $d = array( 'D' => 4 );
     *
     * into that above array, you would call this function as follows:
     *
     *      $new_array = bd_insert_array_value( $alphabet, $d, $position = 4 );
     *
     * And it will return:
     *
     *      array( 'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5 );
     *
     * @see https://stackoverflow.com/a/3353956
     *
     * @param $arr array
     * @param $insertedArray array
     * @param $position int
     * @return array|bool
     */
    function bd_insert_array_value(array $arr, array $insertedArray, int $position): bool|array
    {
        $i = 0;
        $new_array=[];
        $size = count($arr);

        // Make sure we have an integer position and it's not bigger than our array size
        if ( !is_int($position) || $position < 0 || $position > $size) {
            return false;
        }

        foreach ($arr as $key => $value) {
            if ($i == $position) {
                foreach ($insertedArray as $ikey => $ivalue) {
                    $new_array[$ikey] = $ivalue;
                }
            }
            $new_array[$key] = $value;
            $i++;
        }
        return $new_array;
    }
}


if ( ! function_exists( 'remove_url_from_string' ) ) {
    /**
     * Parses the given string and removes any URLs from it.
     *
     * Useful for removing URLs in  teaser text when URLs may be in it and
     * not parsed (like embedded YouTube video URLs).
     *
     * @since 1.0.0
     *
     * @param string $string The string to parse.
     * @return string
     */
    function remove_url_from_string( string $string ): string
    {
        $regex = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@";
        return preg_replace($regex, ' ', $string);
    }
}

if ( ! function_exists( 'get_attachment_id_from_url' ) ) {
    /**
     * Get the attachment ID from the URL.
     *
     * @since 1.0.0
     *
     * @param string $attachment_url The URL of the attachment.
     * @return bool|int
     */
    function get_attachment_id_from_url( string $attachment_url = '' ): bool|int
    {
        global $wpdb;
        $attachment_id = false;

        if ( '' == $attachment_url )
            return false;

        $upload_dir_paths = wp_upload_dir();

        if (str_contains($attachment_url, $upload_dir_paths['baseurl'])) {

            $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

            $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

            $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
                          WHERE wposts.ID = wpostmeta.post_id
                          AND wpostmeta.meta_key = '_wp_attached_file'
                          AND wpostmeta.meta_value = '%s'
                          AND wposts.post_type = 'attachment'", $attachment_url ) );
        }

        return $attachment_id;
    }
}


if ( ! function_exists( 'get_yoastseo_primary_taxonomy_term' ) ) {
    /**
     * Returns the primary term for the chosen taxonomy set by Yoast SEO
     * or the first term selected.
     *
     * @since 1.0.0
     *
     * @link https://www.tannerrecord.com/how-to-get-yoasts-primary-category/
     *
     * @param integer $post The post id.
     * @param string $taxonomy The taxonomy to query. Defaults to category.
     * @return array The term with keys of 'title', 'slug', and 'url'.
     */
    function get_yoastseo_primary_taxonomy_term(int $post = 0, string $taxonomy = 'category' ): array
    {
        if ( ! $post ) {
            $post = get_the_ID();
        }

        $terms        = get_the_terms( $post, $taxonomy );
        $primary_term = array();

        if ( $terms ) {
            $term_display = '';
            $term_slug    = '';
            $term_link    = '';
            if ( class_exists( 'WPSEO_Primary_Term' ) ) {
                $wpseo_primary_term = new WPSEO_Primary_Term( $taxonomy, $post );
                $wpseo_primary_term = $wpseo_primary_term->get_primary_term();
                $term               = get_term( $wpseo_primary_term );
                if ( is_wp_error( $term ) ) {
                    $term_display = $terms[0]->name;
                    $term_slug    = $terms[0]->slug;
                    $term_link    = get_term_link( $terms[0]->term_id );
                } else {
                    $term_display = $term->name;
                    $term_slug    = $term->slug;
                    $term_link    = get_term_link( $term->term_id );
                }
            } else {
                $term_display = $terms[0]->name;
                $term_slug    = $terms[0]->slug;
                $term_link    = get_term_link( $terms[0]->term_id );
            }
            $primary_term['url']   = $term_link;
            $primary_term['slug']  = $term_slug;
            $primary_term['title'] = $term_display;
        }
        return $primary_term;
    }
}


if ( ! function_exists( 'format_phone_for_url' ) ) :
    /**
     * Returns the tel:0000000000 value for a given string which is a phone number.
     *
     * @since 1.0.0
     *
     * @param $string string A string phone number formatted in any way
     * @return string
     */
    function format_phone_for_url( string $string ): string
    {
        $phone_url = preg_replace("/[^0-9]/", "", $string );
        return 'tel:' . $phone_url;
    }
endif;