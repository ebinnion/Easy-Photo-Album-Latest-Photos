<?php
/*
 * Plugin Name: Easy Photo Album Latest Photos
 * Version: 1.0
 * Author: ebinnion
 * Author URI: http://manofhustle.com
 * Description: This plugin makes it simple to get the latest photos from Easy Photo Album
 * Licence: GPL3
 * Text Domain: easy-photo-album-latest
 */

class Latest_Easy_Photo_Album {

    /**
     * Memeber data for ensuring singleton pattern
     */
    private static $instance = null;

    /**
     * Used to story array of unique Easy Photo Album photo IDs
     */
    private static $photos;

    /**
     * Run with every instantiation of this class
     */
    function __construct() {

        // Enforces a single instance of this class.
        if ( isset( self::$instance ) ) {
            wp_die( esc_html__( 'The Latest_Easy_Photo_Album class has already been loaded.', 'easy-photo-album-latest' ) );
        }

        // Update static variable for enforcing singleton pattern
        self:$instance = $this;

        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Initializes array of photo IDs and adds actions
     */
    function init() {
        self::$photos = get_option( 'latest_epa_photos', array() );

        add_action( 'save_post', array( $this, 'epa_save' ), 1, 2 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ), 11 );
    }

    /**
     * Enqueues Lightbox2 JavaScript and CSS as well as localizing the JavaScript
     */
    function enqueue_frontend() {
        wp_enqueue_style (
            'lightbox2-css',
            plugins_url( 'css/lightbox2.min.css', __FILE__ )
        );

        wp_enqueue_script(
            'lightbox2-js',
            plugins_url( 'js/lightbox2.min.js', __FILE__ ),
            array( 'jquery' )
        );

        wp_localize_script (
            'lightbox2-js',
            'lightboxSettings',
            array (
                'wrapAround'      => EasyPhotoAlbum::get_instance()->wraparound,
                'showimagenumber' => EasyPhotoAlbum::get_instance()->showimagenumber,
                'albumLabel'      => EasyPhotoAlbum::get_instance()->imagenumberformat,
                'scaleLightbox'   => EasyPhotoAlbum::get_instance()->scalelightbox
            )
        );
    }

    /**
     * Run when saving posts. Used to gather images that are added to Easy Photo Albums.
     * Will only add image to photo array iff it is not already in the array.
     * Saves photos array to `latest_epa_photos`
     */
    function epa_save( $post_id, $post ) {

        // Do not update if the current user does not have edit_epa_album capability or the edit_others_epa_albums capability.
        if ( 'easy-photo-album' != $post->post_type || ! current_user_can ( 'edit_epa_album', $post_id )
                || ( $post->post_author != get_current_user_id () && ! current_user_can ( 'edit_others_epa_albums' ) ) ) {
            return $post_id;
        }

        // If we have access to Easy Photo Album's classes, let's use their member data.
        if ( class_exists( 'EPA_PostType' ) ) {
            $albumdata = isset( $_POST[ EPA_PostType::INPUT_NAME ]['albumdata'] ) ? $_POST[ EPA_PostType::INPUT_NAME ]['albumdata'] : '';
        } else {
            $albumdata = isset( $_POST['EasyPhotoAlbums']['albumdata'] ) ? $_POST['EasyPhotoAlbums']['albumdata'] : '';
        }

        $images = json_decode( stripslashes( $albumdata ), false );

        if ( ! empty( $images ) ) {
            foreach ( $images as $image ) {

                // Only add image to array if the image ID is not already in array
                if ( ! in_array( $image->id, self::$photos ) ) {
                    self::$photos[] = $image->id;
                }
            }

            update_option( 'latest_epa_photos', self::$photos );
        }
    }

    /**
     * Will return an array of media IDs, ordered from most to least recent,
     * of images that have been added to Easy Photo Albums.
     * @param int number of Easy Photos to return
     * @return array containing latest Easy Photo Albums photo IDs from most recent to least recent
     */
    public static function get_latest_epa_ids( $count = 10 ) {

        // Since arrays are ordered, reverse the array so that we can get a
        // most to least recent ordering
        $reversed = array_reverse( self::$photos );
        $latest = array();

        for ( $i = 0; $i < $count; $i++ ) {

            // Ensure that we do not go past end of array
            if ( ! isset( $reversed[ $i ] ) ) {
                break;
            }

            $latest[] = $reversed[ $i ];
        }

        return $latest;
    }

    /**
     * Will output the latest photos from Easy Photo Album
     * @param  array $params {
	 * 	string $container The tag to wrap the latest Easy Photo Album photos with
	 * 	array $container_attrs An array of key value pairs where the key is the attribute and the value is the value
	 * 	string $image_before Any HTML to be included before the anchor and image
     *     string $image_after Any HTML to be included after the anchor and image
     *     array $image_attrs An array of key value pairs where the key is the attribute and the value is the value
	 * 	int $count The number of photos to output
	 * }
     */
    public static function output_latest_epa_photos( $args = array() ) {
        $defaults = array(
                'container'       => 'div',
                'container_attrs' => array( 'class' => 'latest-photos' ),
                'image_attrs'     => array( 'class' => 'alignleft' ),
                'image_before'    => '',
                'image_after'     => '',
                'count'           => 10
        );

        $args = wp_parse_args( $args, $defaults  );

        $container_attrs = '';
        foreach ( $args['container_attrs'] as $key => $attr ) {
            $container_attrs .= "$key='$attr' ";
        }

        $photos = self::get_latest_epa_ids( $args['count'] );

        if( ! empty( $photos ) ) {
            echo "<{$args['container']} $container_attrs>";

            foreach( $photos as $photo ) {
                echo $args['image_before'];
                    $full = wp_get_attachment_image_src( $photo, 'full' );
                    echo $args['image_before'];
                    echo "<a href='{$full[0]}' data-lightbox='image-$photo'>";
                        echo wp_get_attachment_image( $photo, 'thumbnail', false, $args['image_attrs'] );
                    echo "</a>";
                    echo $args['image_after'];
                echo $args['image_after'];
            }

            echo "</{$args['container']}>";
        }
    }
}

new Latest_Easy_Photo_Album();
