<?php

namespace SVIApp;

/**
 * Frontend Pages Handler
 */
class Frontend
{
    public static  $alreadyAdded_before_add_to_cart_button = false ;
    public function __construct()
    {
        $this->pid = false;
        $this->options = $this->parseOptions( get_option( 'woosvi_options', [] ) );
        $this->activated = ( array_key_exists( 'default', $this->options ) ? $this->options['default'] : false );
        if ( !$this->activated ) {
            //Check if SVI is enabled
            return;
        }
        $this->suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
        $this->theme = wp_get_theme();
        
        if ( !is_admin() ) {
            if ( $this->options['loop_showcase'] ) {
                add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'svi_product_tn_images' ], 10 );
            }
            add_action( 'woocommerce_before_single_product', [ $this, 'remove_hooks' ], 20 );
            add_action(
                'wp_enqueue_scripts',
                array( $this, 'load_scripts' ),
                20,
                1
            );
            add_action( 'woocommerce_before_single_product_summary', [ $this, 'render_frontend' ], 20 );
            add_filter(
                'wc_get_template',
                [ $this, 'filter_wc_get_template' ],
                1,
                5
            );
            add_filter(
                'jetpack_lazy_images_blacklisted_classes',
                [ $this, 'exclude_svi_images_class_from_lazy_load' ],
                999,
                1
            );
            //JETPACK FIX LAZYLOAD
            
            if ( array_key_exists( 'variation_thumbnails', $this->options ) && $this->options['variation_thumbnails'] ) {
                if ( !self::$alreadyAdded_before_add_to_cart_button ) {
                    add_action( 'woocommerce_single_variation', [ $this, 'render_before_add_to_cart_button' ], 5 );
                }
                self::$alreadyAdded_before_add_to_cart_button = true;
            }
        
        }
        
        add_action( 'wp_ajax_loadProduct', array( $this, 'loadProduct' ) );
        add_action( 'wp_ajax_nopriv_loadProduct', array( $this, 'loadProduct' ) );
    }
    
    public function exclude_svi_images_class_from_lazy_load( $classes )
    {
        $classes[] = 'svi-img';
        return $classes;
    }
    
    public function filter_wc_get_template(
        $located,
        $template_name,
        $args,
        $template_path,
        $default_path
    )
    {
        // make filter magic happen here...
        global  $product ;
        if ( !is_object( $product ) || defined( 'DOING_AJAX' ) && !$this->options['quick_view'] ) {
            return $located;
        }
        $run = get_post_meta( $product->get_id(), '_checkbox_svipro_enabled', true );
        $has_images = $this->hasImages( $product->get_id() );
        if ( empty($has_images) ) {
            return $located;
        }
        $theme_file = 'single-product/product-image.php';
        
        if ( $this->theme->template == 'flatsome' && $run != 'yes' ) {
            add_filter(
                'woocommerce_single_product_image_thumbnail_html',
                '__return_empty_string',
                10,
                2
            );
            $theme_file = 'woocommerce/single-product/product-gallery-thumbnails.php';
        }
        
        
        if ( $template_name == $theme_file && $run != 'yes' ) {
            return $this->woo_svi_plugin_path() . '/includes/display.php';
            //$this->render_frontend();
        } else {
            return $located;
        }
    
    }
    
    /**
     * Plugin path
     *
     * @since 1.0.0
     * @return html
     */
    public function woo_svi_plugin_path()
    {
        return untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) );
    }
    
    public function parseOptions( $args )
    {
        $defaults = array(
            'main_imagesize'          => 'shop_single',
            'thumb_imagesize'         => 'shop_thumbnail',
            'sviesrcset'              => false,
            'svititleattr'            => false,
            'loop_showcase'           => false,
            'lens'                    => false,
            'containlenszoom'         => true,
            'lens_zoomtype'           => 'lens',
            'lens_mobiledisabled'     => false,
            'lens_zIndex'             => 999,
            'lightbox'                => false,
            'variation_thumbnails_lb' => false,
            'lightbox_enabled'        => 'main',
            'lightbox_icon'           => false,
            'lightbox_close'          => false,
            'lightbox_fullScreen'     => false,
            'lightbox_zoom'           => false,
            'lightbox_share'          => false,
            'lightbox_counter'        => false,
            'lightbox_controls'       => true,
            'slider'                  => false,
            'columns'                 => 4,
            'hide_thumbs'             => false,
            'variation_thumbnails'    => false,
            'columns_variations'      => '5',
        );
        return wp_parse_args( $args, $defaults );
    }
    
    public function parseGlobalData( $pid = false )
    {
        global  $product ;
        $detect = new Mobile_Detect();
        $options = array(
            'is_mobile'               => $detect->isMobile(),
            'status'                  => ( svi_fs()->can_use_premium_code__premium_only() ? true : false ),
            'version'                 => SVI_VERSION,
            'rtl'                     => is_rtl(),
            'template'                => $this->theme->template,
            'flatsome'                => ( $this->theme->template == 'flatsome' ? 'has-hover' : false ),
            'sviesrcset'              => ( $this->options['sviesrcset'] ? true : false ),
            'svititleattr'            => ( $this->options['svititleattr'] ? true : false ),
            'loop_showcase'           => ( $this->options['loop_showcase'] ? true : false ),
            'lens'                    => ( $this->options['lens'] ? true : false ),
            'lens_containlenszoom'    => true,
            'lens_mobiledisabled'     => $this->options['lens_mobiledisabled'],
            'lightbox'                => ( $this->options['lightbox'] ? true : false ),
            'variation_thumbnails_lb' => ( $this->options['variation_thumbnails_lb'] ? true : false ),
            'lightbox_enabled'        => 'main',
            'lightbox_icon'           => ( $this->options['lightbox_icon'] ? true : false ),
            'lightbox_share'          => ( $this->options['lightbox_share'] ? true : false ),
            'lightbox_close'          => ( $this->options['lightbox_close'] ? true : false ),
            'lightbox_controls'       => ( $this->options['lightbox_controls'] ? true : false ),
            'lightbox_zoom'           => ( $this->options['lightbox_zoom'] ? true : false ),
            'lightbox_counter'        => ( $this->options['lightbox_counter'] ? true : false ),
            'lightbox_fullScreen'     => ( $this->options['lightbox_fullScreen'] ? true : false ),
            'slider'                  => ( $this->options['slider'] ? true : false ),
            'columns'                 => $this->options['columns'],
            'hidden_thumb'            => ( $this->options['hide_thumbs'] ? true : false ),
            'variation_thumbnails'    => ( $this->options['variation_thumbnails'] ? true : false ),
            'columns_variations'      => $this->options['columns_variations'],
        );
        return $options;
    }
    
    public function parseData( $pid = false )
    {
        global  $product ;
        
        if ( $pid ) {
            $product = wc_get_product( $pid );
        } else {
            $pid = 0;
            if ( is_object( $product ) ) {
                $pid = $product->get_id();
            }
        }
        
        if ( !is_object( $product ) ) {
            return array();
        }
        $images = apply_filters( 'svi_gallery_images', $this->getImages( $pid ) );
        return array(
            'type'       => ( $product instanceof WC_Product && !$product->is_type( 'variable' ) ? true : false ),
            'images'     => (object) $images,
            'woosvislug' => ( empty($images) ? '' : $this->prepInformation( $product ) ),
        );
    }
    
    public function load_scripts()
    {
        $wp_scripts = wp_scripts();
        //echo "<pre>".print_r($wp_scripts->registered,true)."</pre>";
        if ( !current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
            
            if ( $this->options['lightbox'] ) {
                $handle = 'photoswipe' . $this->suffix . '.js';
                $list = 'enqueued';
                
                if ( !wp_script_is( $handle, $list ) ) {
                    wp_enqueue_script( 'svi-photoswipe' );
                    wp_enqueue_script( 'svi-photoswipe-ui-default' );
                    wp_enqueue_style( 'svi-photoswipe' );
                    wp_enqueue_style( 'svi-photoswipe-default-skin' );
                }
            
            }
        
        }
        $data = $this->parseGlobalData();
        
        if ( $data['slider'] ) {
            //ENQUEUE STYLES
            wp_enqueue_style( 'svi-swiper' );
            //ENQUEUE SCRIPTS
            wp_enqueue_script( 'svi-swiper' );
        }
        
        //ENQUEUE STYLES
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'svi-frontend' );
        //ENQUEUE SCRIPTS
        if ( $this->options['lens'] ) {
            wp_enqueue_script( 'svi-ez-plus' );
        }
        wp_enqueue_script( 'svi-frontend' );
        wp_localize_script( 'svi-frontend', 'wcsviajax', array(
            'call' => WC()->ajax_url(),
        ) );
        wp_localize_script( 'svi-frontend', 'wcsvi', (object) array(
            'data' => $data,
        ) );
    }
    
    public function render_before_add_to_cart_button()
    {
        global  $product ;
        
        if ( is_object( $product ) ) {
            echo  '<div data-sviproduct_id="' . $product->get_id() . '" class="svi-vue-variationthumbs">' ;
            echo  '<div class="svi-vue-variationthumbs-app"></div>' ;
            echo  '</div>' ;
        }
    
    }
    
    /**
     * Render frontend app
     *
     *
     * @return string
     */
    public function render_frontend( $divi_check = true )
    {
        global  $product ;
        if ( !is_object( $product ) ) {
            return;
        }
        
        if ( $this->theme->template == 'Divi' && !$divi_check ) {
            $page_layout = \et_theme_builder_get_template_layouts();
            if ( !empty($page_layout) ) {
                return;
            }
        }
        
        $has_images = $this->hasImages( $product->get_id() );
        if ( empty($has_images) ) {
            return;
        }
        if ( !$divi_check && strpos( $product->get_description(), 'et_pb_wc_images' ) !== false ) {
            // TEST FOR DIVI SHORTCODE
            return;
        }
        $run = get_post_meta( $product->get_id(), '_checkbox_svipro_enabled', true );
        if ( $run == 'yes' ) {
            return;
        }
        $data = $this->parseGlobalData();
        $columns = $this->options['columns'];
        $attr = array(
            'svi-woocommerce-product-gallery',
            'woocommerce-product-gallery--' . (( has_post_thumbnail() ? 'with-images' : 'without-images' )),
            'woocommerce-product-gallery--columns-' . absint( $columns ),
            'images'
        );
        $wrapper_classes = apply_filters( 'svi_single_product_image_gallery_classes', $attr );
        if ( $this->theme->template == 'flatsome' ) {
            $wrapper_classes[] = 'has-hover';
        }
        ?>

        <div class="whitespacesvi">&nbsp;</div>
        <?php 
        wp_localize_script( 'svi-frontend', 'wcsvi_' . $product->get_id(), json_encode( $this->parseData( $product->get_id() ) ) );
        ?>
        <div data-sviproduct_id="<?php 
        echo  $product->get_id() ;
        ?>" class="<?php 
        echo  esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ) ;
        ?>">

            <?php 
        
        if ( $this->theme->template == 'flatsome' ) {
            ?>
                <?php 
            ( !defined( 'DOING_AJAX' ) ? do_action( 'flatsome_sale_flash' ) : '' );
            ?>

                <div class="image-tools absolute top show-on-hover right z-3">
                    <?php 
            do_action( 'flatsome_product_image_tools_top' );
            ?>
                </div>
            <?php 
        }
        
        ?>

            <div class="svi-vue-frontend-app">
                <div class="sivmainloader">
                    <div class="signal"></div>
                </div>
                <?php 
        ?>

            </div>
        </div>
<?php 
    }
    
    public function loadProduct()
    {
        $this->pid = intval( $_POST['id'] );
        header( "Content-type: application/json" );
        echo  json_encode( $this->parseData( $this->pid ) ) ;
        die;
    }
    
    /**
     * Render frontend sub thumbs
     *
     * @since 1.1.1
     * @return instance object
     */
    public function show_thumb_images()
    {
        $data = $this->parseGlobalData();
    }
    
    /**
     * Runs the fallback
     *
     *
     * @since 1.0.0
     * @return HTML
     */
    public function fallback( $pid )
    {
        $return = '';
        $product_image_gallery = array();
        
        if ( metadata_exists( 'post', $pid, '_product_image_gallery' ) ) {
            $product_image_gallery = explode( ',', get_post_meta( $pid, '_product_image_gallery', true ) );
        } else {
            // Backwards compat
            $attachment_ids = get_posts( 'post_parent=' . $pid . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids&meta_key=_woocommerce_exclude_image&meta_value=0' );
            $attachment_ids = array_diff( $attachment_ids, array( get_post_thumbnail_id() ) );
            if ( $attachment_ids ) {
                $product_image_gallery = explode( ',', $attachment_ids );
            }
        }
        
        if ( !is_array( $product_image_gallery ) || count( $product_image_gallery ) < 1 ) {
            return;
        }
        $product_image_gallery = array_filter( $product_image_gallery );
        $order = array();
        foreach ( $product_image_gallery as $key => $value ) {
            $woosvi_slug = get_post_meta( $value, 'woosvi_slug_' . $pid, true );
            
            if ( is_array( $woosvi_slug ) ) {
                $data = array();
                foreach ( $woosvi_slug as $k => $v ) {
                    
                    if ( count( $v ) > 1 ) {
                        $data[] = implode( '_svipro_', $v );
                    } else {
                        $data[] = $v;
                    }
                
                }
                $woosvi_slug = $data;
            }
            
            if ( !$woosvi_slug ) {
                $woosvi_slug = get_post_meta( $value, 'woosvi_slug', true );
            }
            if ( !$woosvi_slug ) {
                $woosvi_slug = 'nullsvi';
            }
            
            if ( is_array( $woosvi_slug ) ) {
                foreach ( $woosvi_slug as $k => $v ) {
                    
                    if ( is_array( $v ) ) {
                        $order[$v[0]][] = $value;
                    } else {
                        $order[$v][] = $value;
                    }
                
                }
            } else {
                $order[$woosvi_slug][] = $value;
            }
        
        }
        unset( $order['nullsvi'] );
        $ordered = array();
        foreach ( $order as $k => $v ) {
            $arr = array(
                'slugs' => explode( '_svipro_', $k ),
                'imgs'  => $v,
            );
            array_push( $ordered, $arr );
        }
        update_post_meta( $pid, 'woosvi_slug', $ordered );
    }
    
    public function prepInformation(
        $product,
        $idonly = false,
        $check_pid = null,
        $lh = false
    )
    {
        global  $product ;
        $original_slugs = [];
        $wpml_slugs = false;
        $gPost_id = false;
        if ( is_object( $product ) ) {
            $gPost_id = $product->get_id();
        }
        $post_id = ( $this->pid ? $this->pid : $gPost_id );
        if ( $check_pid ) {
            $post_id = $check_pid;
        }
        $pid = $this->wpml_original( $post_id );
        $original_slugs = $this->getOriginalAttributes( $pid );
        if ( class_exists( 'SitePress' ) ) {
            $wpml_slugs = $this->wpml( $post_id, $product, $pid );
        }
        $return = array();
        $woosvi_slug = get_post_meta( $pid, 'woosvi_slug', true );
        
        if ( empty($woosvi_slug) ) {
            $this->fallback( $pid );
            $woosvi_slug = get_post_meta( $pid, 'woosvi_slug', true );
        }
        
        if ( is_array( $woosvi_slug ) ) {
            foreach ( $woosvi_slug as $k => $v ) {
                if ( !is_array( $v['slugs'] ) ) {
                    continue;
                }
                $v['slugs'] = array_map( 'trim', $v['slugs'] );
                
                if ( $wpml_slugs ) {
                    $v['slugs'] = $this->wpml_translate_slug( $wpml_slugs, $v['slugs'] );
                } else {
                    $v['slugs'] = $this->fix_slugs( $original_slugs, $v['slugs'] );
                }
                
                $variation_key = strtolower( implode( '_svipro_', $v['slugs'] ) );
                $return[$variation_key] = array();
                
                if ( $idonly ) {
                    $return[$variation_key] = $v['imgs'];
                } else {
                    foreach ( $v['imgs'] as $id ) {
                        $return[$variation_key]['x' . $id] = array();
                    }
                }
            
            }
        }
        
        if ( array_key_exists( 'svidefault', $return ) ) {
            $default_product = get_post_thumbnail_id( $pid );
            
            if ( $default_product ) {
                $return['svidefault'] = array(
                    'x' . $default_product => array(),
                ) + $return['svidefault'];
            } else {
                $return['svidefault'] = $return['svidefault'];
            }
        
        }
        
        return $return;
    }
    
    public function getImages( $o_pid )
    {
        $pid = $this->wpml_original( $o_pid );
        $default_img = get_post_thumbnail_id( $pid );
        $attachment_ids = array( $default_img );
        // $attachment_ids = array_merge($attachment_ids, $product->get_gallery_image_ids());
        $attachment_ids = array_merge( $attachment_ids, explode( ',', get_post_meta( $pid, '_product_image_gallery', true ) ) );
        $attachment_ids = array_unique( $attachment_ids );
        $attachment_ids = array_values( array_filter( $attachment_ids ) );
        $images = array();
        
        if ( $attachment_ids ) {
            $x = 0;
            foreach ( $attachment_ids as $attachment_id ) {
                $img_arr = array(
                    'id'          => intval( $attachment_id ),
                    'product_img' => ( $default_img == $attachment_id ? true : false ),
                    'single'      => $this->getMainImage( $attachment_id, $this->options['main_imagesize'] ),
                    'thumb'       => $this->getMainImage( $attachment_id, $this->options['thumb_imagesize'] ),
                    'key'         => $x,
                );
                array_push( $images, $img_arr );
                $x++;
            }
        }
        
        return $images;
    }
    
    public function hasImages( $o_pid )
    {
        $pid = $this->wpml_original( $o_pid );
        $default_img = get_post_thumbnail_id( $pid );
        $attachment_ids = array( $default_img );
        $attachment_ids = array_merge( $attachment_ids, explode( ',', get_post_meta( $pid, '_product_image_gallery', true ) ) );
        $attachment_ids = array_unique( $attachment_ids );
        $attachment_ids = array_values( array_filter( $attachment_ids ) );
        return $attachment_ids;
    }
    
    public function getMainImage( $attachment_id = false, $main_image = false, $return_img = false )
    {
        global  $product ;
        if ( !is_object( $product ) ) {
            return;
        }
        if ( !$attachment_id ) {
            $attachment_id = get_post_thumbnail_id( $product->get_id() );
        }
        $gallery_thumbnail = wc_get_image_size( $this->options['thumb_imagesize'] );
        $full_size = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
        $thumbnail_size = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
        $image_size = apply_filters( 'woocommerce_gallery_image_size', ( $this->options['main_imagesize'] ? $this->options['main_imagesize'] : $full_size ) );
        $thumbnail_src = wp_get_attachment_image_src( $attachment_id, $this->options['thumb_imagesize'] );
        $large_image = wp_get_attachment_image_src( $attachment_id, $image_size );
        $image_src = wp_get_attachment_image_src( $attachment_id, ( $main_image ? $main_image : $full_size ) );
        $image_full = wp_get_attachment_image_src( $attachment_id, $full_size );
        
        if ( !$return_img ) {
            $img = $this->imgtagger( wp_get_attachment_image(
                $attachment_id,
                $image_size,
                false,
                array(
                'title'                   => get_the_title( $attachment_id ),
                'data-caption'            => wp_get_attachment_caption( $attachment_id ),
                'data-src'                => $image_src[0],
                'data-large_image'        => $large_image[0],
                'data-large_image_width'  => $large_image[1],
                'data-large_image_height' => $large_image[2],
                'data-thumb_image'        => $thumbnail_src[0],
                'data-thumb_image_width'  => $thumbnail_src[1],
                'data-thumb_image_height' => $thumbnail_src[2],
                'class'                   => ( $main_image ? 'svi-img wp-post-image' : 'svi-img' ),
            )
            ) );
        } else {
            return wp_get_attachment_image(
                $attachment_id,
                $image_size,
                false,
                array(
                'title'                   => get_the_title( $attachment_id ),
                'data-caption'            => wp_get_attachment_caption( $attachment_id ),
                'data-src'                => $image_src[0],
                'data-large_image'        => $large_image[0],
                'data-large_image_width'  => $large_image[1],
                'data-large_image_height' => $large_image[2],
                'data-thumb_image'        => $thumbnail_src[0],
                'data-thumb_image_width'  => $thumbnail_src[1],
                'data-thumb_image_height' => $thumbnail_src[2],
                'class'                   => 'svitn_img attachment-svi-icon size-svi-icon',
            )
            );
        }
        
        $full_img_sizes = array(
            'full_image'        => $image_full[0],
            'full_image_width'  => $image_full[1],
            'full_image_height' => $image_full[2],
        );
        $img = array_merge( $img, $full_img_sizes );
        return $img;
    }
    
    /**
     * Break images tags to array to be used
     *
     * @since 1.0.0
     * @return array
     */
    public function imgtagger( $fullimg_tag )
    {
        preg_match_all( '/(alt|title|src|caption|woosvislug|svizoom-image|srcset|title|sizes|width|height|class|thumb_image|thumb_image_width|thumb_image_height|large_image|large_image_width|large_image_height)=("[^"]*")/i', $fullimg_tag, $fullimg_split );
        foreach ( $fullimg_split[2] as $key => $value ) {
            
            if ( $value == '""' ) {
                $fullimg_split[2][$key] = "";
            } else {
                $fullimg_split[2][$key] = str_replace( '"', "", $value );
            }
        
        }
        return array_combine( $fullimg_split[1], $fullimg_split[2] );
    }
    
    /**
     * Get translated Slugs
     *
     * @since 1.0.0
     * @return array
     */
    public function wpml( $pid, $product, $original )
    {
        global  $sitepress ;
        if ( $product instanceof WC_Product && !$product->is_type( 'variable' ) ) {
            return false;
        }
        $slugs = array();
        $attributes = get_post_meta( $pid, '_product_attributes' );
        if ( !empty($attributes) ) {
            foreach ( $attributes[0] as $att => $attribute ) {
                
                if ( $attribute['is_taxonomy'] && $attribute['is_variation'] ) {
                    $valid_attr = esc_attr( $att );
                    $terms = wp_get_post_terms( $pid, $valid_attr, 'all' );
                    
                    if ( is_wp_error( $terms ) ) {
                        $valid_attr = esc_attr( $attribute['name'] );
                        $terms = wp_get_post_terms( $pid, $valid_attr, 'all' );
                    }
                    
                    foreach ( $terms as $tr => $term ) {
                        remove_filter(
                            'get_term',
                            array( $sitepress, 'get_term_adjust_id' ),
                            1,
                            1
                        );
                        $gtb = get_term( icl_object_id(
                            $term->term_id,
                            $valid_attr,
                            true,
                            $sitepress->get_default_language()
                        ) );
                        $slugs[strtolower( esc_attr( $gtb->slug ) )] = esc_attr( $term->slug );
                        add_filter(
                            'get_term',
                            array( $sitepress, 'get_term_adjust_id' ),
                            1,
                            1
                        );
                    }
                }
            
            }
        }
        $attributes_original = get_post_meta( $original, '_product_attributes' );
        if ( !empty($attributes_original) ) {
            foreach ( $attributes_original[0] as $att => $attribute ) {
                if ( !$attribute['is_taxonomy'] && $attribute['is_variation'] ) {
                    
                    if ( array_key_exists( $att, $attributes[0] ) ) {
                        $values = $attributes[0][$att]['value'];
                        
                        if ( !empty($values) ) {
                            $terms = explode( '|', $values );
                            $terms_original = explode( '|', $attribute['value'] );
                            foreach ( $terms_original as $tr => $term ) {
                                $slugs[sanitize_title( $term )] = esc_attr( strtolower( $terms[$tr] ) );
                            }
                        }
                    
                    }
                
                }
            }
        }
        return $slugs;
    }
    
    public function getOriginalAttributes( $original )
    {
        $slugs = [];
        $attributes_original = get_post_meta( $original, '_product_attributes' );
        if ( !empty($attributes_original) ) {
            foreach ( $attributes_original[0] as $att => $attribute ) {
                
                if ( !$attribute['is_taxonomy'] && $attribute['is_variation'] ) {
                    $terms_original = explode( '|', $attribute['value'] );
                    if ( !empty($terms_original) ) {
                        foreach ( $terms_original as $tr => $term ) {
                            $slugs[sanitize_title( $term )] = $term;
                        }
                    }
                } else {
                    $terms = wp_get_post_terms( $original, urldecode( $att ), 'all' );
                    if ( !empty($terms) && !is_wp_error( $terms ) ) {
                        foreach ( $terms as $tr => $term ) {
                            $slugs[$term->slug] = $term->slug;
                        }
                    }
                }
            
            }
        }
        return $slugs;
    }
    
    public function findSimilar(
        $img,
        $slugs_confirm,
        $data,
        $product,
        $html = false
    )
    {
        $found = false;
        foreach ( $slugs_confirm as $index => $slug ) {
            
            if ( array_key_exists( $slug, $data ) ) {
                foreach ( $data[$slug] as $img_k => $img_data ) {
                    $img_id = filter_var( $img_k, FILTER_SANITIZE_NUMBER_INT );
                    
                    if ( $html ) {
                        $dom = new \DOMDocument();
                        $dom->loadHTML( $img );
                        $img = '<div style="margin-bottom: 5px"><img src="' . (( $img_id ? current( wp_get_attachment_image_src( $img_id, 'thumbnail' ) ) : wc_placeholder_img_src() )) . '" alt="' . esc_attr__( 'Product image', 'woocommerce' ) . '" height="' . esc_attr( $dom->getElementsByTagName( 'img' )->item( 0 )->getAttribute( 'height' ) ) . '" width="' . esc_attr( $dom->getElementsByTagName( 'img' )->item( 0 )->getAttribute( 'width' ) ) . '" style="vertical-align:middle; margin-' . (( is_rtl() ? 'left' : 'right' )) . ': 10px;" /></div>';
                    } else {
                        $image_title = $product->get_title();
                        $img = wp_get_attachment_image(
                            $img_id,
                            apply_filters( 'single_product_small_thumbnail_size', $this->options['thumb_imagesize'] ),
                            0,
                            $attr = array(
                            'title' => $image_title,
                            'alt'   => $image_title,
                        )
                        );
                    }
                    
                    $found = true;
                    break;
                }
                break;
            }
        
        }
        
        if ( !$found ) {
            $bigger = 0;
            foreach ( $slugs_confirm as $index => $slug ) {
                foreach ( $data as $key => $k_data ) {
                    $sim = similar_text( $slug, $key, $perc );
                    
                    if ( $perc > $bigger && $perc > 70 ) {
                        $slugs_name = $slug;
                        $bigger = $perc;
                        foreach ( $k_data as $img_k => $img_data ) {
                            $img_id = filter_var( $img_k, FILTER_SANITIZE_NUMBER_INT );
                            
                            if ( $html ) {
                                $dom = new \DOMDocument();
                                $dom->loadHTML( $img );
                                $img = '<div style="margin-bottom: 5px"><img src="' . (( $img_id ? current( wp_get_attachment_image_src( $img_id, 'thumbnail' ) ) : wc_placeholder_img_src() )) . '" alt="' . esc_attr__( 'Product image', 'woocommerce' ) . '" height="' . esc_attr( $dom->getElementsByTagName( 'img' )->item( 0 )->getAttribute( 'height' ) ) . '" width="' . esc_attr( $dom->getElementsByTagName( 'img' )->item( 0 )->getAttribute( 'width' ) ) . '" style="vertical-align:middle; margin-' . (( is_rtl() ? 'left' : 'right' )) . ': 10px;" /></div>';
                            } else {
                                $image_title = $product->get_title();
                                $img = wp_get_attachment_image(
                                    $img_id,
                                    apply_filters( 'single_product_small_thumbnail_size', $this->options['thumb_imagesize'] ),
                                    0,
                                    $attr = array(
                                    'title' => $image_title,
                                    'alt'   => $image_title,
                                )
                                );
                            }
                            
                            break;
                        }
                    }
                
                }
            }
        }
        
        return $img;
    }
    
    public function wpml_original( $id )
    {
        global  $wpdb ;
        
        if ( class_exists( 'SitePress' ) ) {
            $orig_lang_id = $wpdb->get_var( "SELECT trans2.element_id FROM {$wpdb->prefix}icl_translations AS trans1 INNER JOIN {$wpdb->prefix}icl_translations AS trans2 ON trans2.trid = trans1.trid WHERE trans1.element_id = " . $id . " AND trans2.source_language_code IS NULL" );
            
            if ( is_null( $orig_lang_id ) ) {
                return $id;
            } else {
                return $orig_lang_id;
            }
        
        } else {
            return $id;
        }
    
    }
    
    public function wpml_translate_slug( $wpml_slugs, $slugs )
    {
        $return = array();
        foreach ( $slugs as $slug ) {
            $found = false;
            $slug = strtolower( $slug );
            
            if ( array_key_exists( $slug, $wpml_slugs ) ) {
                $return[] = sanitize_title( $wpml_slugs[$slug] );
                $found = true;
            } else {
                foreach ( $wpml_slugs as $k => $value ) {
                    $old_slug = strtolower( str_replace( " ", "", $value ) );
                    
                    if ( $old_slug == $slug ) {
                        $return[] = $k;
                        $found = true;
                    }
                
                }
            }
            
            if ( !$found ) {
                $return[] = $slug;
            }
        }
        return $return;
    }
    
    public function fix_slugs( $original_slugs, $slugs )
    {
        $return = array();
        foreach ( $slugs as $slug ) {
            $found = false;
            $slug = strtolower( $slug );
            
            if ( array_key_exists( $slug, $original_slugs ) ) {
                $return[] = $slug;
                $found = true;
            } else {
                foreach ( $original_slugs as $k => $value ) {
                    $old_slug = strtolower( str_replace( " ", "", $value ) );
                    
                    if ( $old_slug == $slug ) {
                        $return[] = $k;
                        $found = true;
                    }
                
                }
            }
            
            if ( !$found ) {
                $return[] = $slug;
            }
        }
        return $return;
    }
    
    /**
     * Remove hooks for plugin to work properly
     *
     * @since 1.1.1
     * @return instance object
     */
    public function remove_hooks()
    {
        global  $product ;
        $run = 'yes';
        if ( is_object( $product ) ) {
            $run = get_post_meta( $product->get_id(), '_checkbox_svipro_enabled', true );
        }
        if ( $run == 'yes' ) {
            return;
        }
        //if ($this->runsvi) {
        remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 10 );
        remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
        // Mr. Tailor
        remove_action( 'woocommerce_before_single_product_summary_product_images', 'woocommerce_show_product_images', 20 );
        remove_action( 'woocommerce_product_summary_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
        //WOWMALL
        //remove_action('woocommerce_before_single_product_summary', 'wowmall_woocommerce_show_product_images', 20);
        //wp_deregister_script('wowmall-wc-single-product-gallery');
        //wp_deregister_script('single-product-lightbox');
        //Electro support
        remove_action( 'woocommerce_before_single_product_summary', 'electro_show_product_images', 20 );
        //AURUM support
        remove_action( 'woocommerce_before_single_product_summary', 'aurum_woocommerce_show_product_images', 25 );
        // Remove images from Bazar theme
        
        if ( class_exists( 'YITH_WCMG' ) ) {
            $this->remove_filters_for_anonymous_class(
                'woocommerce_before_single_product_summary',
                'YITH_WCMG_Frontend',
                'show_product_images',
                20
            );
            $this->remove_filters_for_anonymous_class(
                'woocommerce_product_thumbnails',
                'YITH_WCMG_Frontend',
                'show_product_thumbnails',
                20
            );
        }
        
        //}
    }
    
    /*
     *
     * Helper: Allow to remove method for a hook when it's a class method used
     *
     * @param str $hook_name Name of the wordpress hook
     * @param str $class_name Name of the class where the add_action resides
     * @param str $method_name Name of the method to unhook
     * @param str $priority The priority of which the above method has in the add_action
     *
     */
    public function remove_filters_for_anonymous_class(
        $hook_name = '',
        $class_name = '',
        $method_name = '',
        $priority = 0
    )
    {
        global  $option ;
        if ( !isset( $option[$hook_name][$priority] ) || !is_array( $option[$hook_name][$priority] ) ) {
            return false;
        }
        foreach ( (array) $option[$hook_name][$priority] as $unique_id => $filter_array ) {
            if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
                if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
                    unset( $option[$hook_name][$priority][$unique_id] );
                }
            }
        }
        return false;
    }
    
    public function svi_product_tn_images()
    {
        global  $product ;
        if ( !is_object( $product ) ) {
            return;
        }
        $data = $this->prepInformation(
            $product,
            false,
            null,
            true
        );
        if ( svi_fs()->is_free_plan() ) {
            $data = array_slice( $data, 0, 2 );
        }
        
        if ( !empty($data) ) {
            echo  ' <div class="svitn_wrapper">' ;
            foreach ( $data as $variation => $a_id ) {
                $img_id = str_replace( 'x', '', key( $a_id ) );
                echo  $this->getMainImage( $img_id, $this->options['thumb_imagesize'], true ) ;
            }
            echo  ' </div>' ;
        }
    
    }

}