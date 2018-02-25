<?php
/**
 * Plugin Name: Easy Digital Downloads
 * Plugin URI: https://wordpress.org/plugins/easy-digital-downloads/
 *
 * Compatibility Description: 
 *
 */

namespace wpCloud\StatelessMedia {

    if(!class_exists('wpCloud\StatelessMedia\WPBakeryPageBuilder')) {
        
        class WPBakeryPageBuilder extends ICompatibility {
            protected $id = 'wp-bakery-page-builder';
            protected $title = 'WPBakery Page Builder';
            protected $constant = 'WP_STATELESS_COMPATIBILITY_WPB';
            protected $description = 'Ensures compatibility with WPBakery Page Builder Single Image.';

            public function __construct(){
                parent::__construct();

                // We need to add the filter on construct. Init is too late.
                add_filter('vc_wpb_getimagesize', array($this, 'vc_wpb_getimagesize'), 10, 3);
            }

            public function module_init($sm){
                // 
            }

            /**
             * If image size not exist then upload it to GS.
             * 
             * $args = array(
             *      'thumbnail' => $thumbnail,
             *      'p_img_large' => $p_img_large,
             *   )
             */
            public function vc_wpb_getimagesize($args, $attach_id, $params){
                $gs_host = ud_get_stateless_media()->get_gs_host();
                $meta_data = wp_get_attachment_metadata( $attach_id );
                preg_match("/src=[\"|'](.*?)[\"|']/", $args['thumbnail'], $match);
                
                if(!empty($match[1]) && empty($meta_data['sizes'][$params['thumb_size']])){
                    $dir = wp_upload_dir();
                    $url = $match[1];
                    $path = str_replace($gs_host, '', $url);
                    $path = trim($path, '/');
                    $absolute_path = $dir['basedir'] . '/' . $path;
                    
                    $size= getimagesize( $absolute_path );
                    $filetype = wp_check_filetype($absolute_path);
                    $size_info = array(
                        'file' => wp_basename( $absolute_path ),
                        'mime-type' => $filetype['type'],
                        'width' => $size[0],
                        'height' => $size[1],
                    );
                    $meta_data['sizes'][$params['thumb_size']] = $size_info; 
                    wp_update_attachment_metadata($attach_id, $meta_data );
                }
                return $args;
            }
            
            
        }

    }

}
