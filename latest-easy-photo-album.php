<?php
/*
 * Plugin Name: Easy Photo Album Latest Photos
 * Version: 1.0
 * Author: ebinnion
 * Author URI: http://manofhustle.com
 * Description: This plugin makes it simple to get the latest photos from Easy Photo Album
 * Licence: GPL3
 */

class Latest_Easy_Photo_Album {
    private static $photos;

    function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    function init() {
        self::$photos = get_option( 'latest_epa_photos', array() );

        add_action( 'save_post', array( $this, 'epa_save' ), 1, 2 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ), 11 );
    }

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
                'wrapAround' => EasyPhotoAlbum::get_instance()->wraparound,
                'showimagenumber' => EasyPhotoAlbum::get_instance()->showimagenumber,
                'albumLabel' => EasyPhotoAlbum::get_instance()->imagenumberformat,
                'scaleLightbox' => EasyPhotoAlbum::get_instance()->scalelightbox
            )
        );
    }

    function epa_save( $post_id, $post ) {

        // Do not update if the current user does not have edit_epa_album capability or the edit_others_epa_albums capability.
        if ( 'easy-photo-album' != $post->post_type || ! current_user_can ( 'edit_epa_album', $post_id ) || ($post->post_author != get_current_user_id () && ! current_user_can ( 'edit_others_epa_albums' ))) {
            return $post_id;
        }

        // Get albumdata
        if( class_exists( 'EPA_PostType' ) ) {
            $albumdata = isset ( $_POST[ EPA_PostType::INPUT_NAME ]['albumdata'] ) ? $_POST[ EPA_PostType::INPUT_NAME ]['albumdata'] : '';
        } else {
            $albumdata = isset ( $_POST['EasyPhotoAlbums']['albumdata'] ) ? $_POST['EasyPhotoAlbums']['albumdata'] : '';
        }

        $images = (array) json_decode ( stripslashes ( $albumdata ), false );

        if( ! empty( $images ) ) {
            foreach( $images as $image ) {
                if( ! in_array( $image->id, self::$photos ) ) {
                    self::$photos[] = $image->id;
                }
            }

            update_option( 'latest_epa_photos', self::$photos );
        }
    }

    public static function get_latest_epa_ids( $count = 10 ) {
        $reversed = array_reverse( self::$photos );
        $latest = array();

        for ( $i = 0; $i < $count; $i++ ) {
            if( ! isset( $reversed[ $i ] ) ) {
                break;
            }

            $latest[] = $reversed[ $i ];
        }

        return $latest;
    }
}

new Latest_Easy_Photo_Album();
