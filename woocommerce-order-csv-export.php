<?php

/*

Plugin Name: Woocommerce Order Csv Export
Plugin URI: https://wordpress.org/
Description: Order Search and CSV Export for Woocommerce Japanese. Addon Extension to Advanced Order Export For WooCommerce
Version: 1.0
Author: KelvinLee
License: GPLv2
Text Domain: wc-order-csv-export
 
*/

/**
 * * This plugin is addon to advanced order export.
 * * Csv formats are all for Woocommerce Japanese.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// if ( ! class_exists( 'WC_Order_Export_Admin' ) ) {
//     exit;
// }
add_action( 'plugins_loaded', 'wc_order_csv_export', 1 );

include_once plugin_dir_path( __FILE__ ) . "lib/export-setting.php";

/**
 * Init the class Wc_Order_Csv_Export
 */
function wc_order_csv_export() {
    /**
     * Wc_Order_Csv_Export class
     * 
     * @class Wc_Order_Csv_Export
     */    
    class Wc_Order_Csv_Export {
        protected $tempfile_prefix = 'woocommerce-order-file-';
    	use WC_Order_Export_Ajax_Helpers_reuse;

        /**
         * * construct
         */
        public function __construct() {
            $this->includes();

            add_action( 'admin_menu', array( $this, 'add_submenu' ) );
            // add_filter( 'woe_order_export_admin_tabs', array( $this, 'add_custom_tab' ) );
            add_action( 'wp_ajax_order_search', array( $this, 'order_search' ) );
            add_action( 'wp_ajax_csv_export', array( $this, 'ajax_csv_export' ) );
        }

        /**
         * Include required core files
         * 
         * @access public
         */
        public function includes() {
            add_action( 'admin_enqueue_scripts', array( $this, 'css_enqueue' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'js_enqueue' ) );

            // * merging advanced order export plugin to this one
            include_once plugin_dir_path( __FILE__ ) . "lib/class-wc-order-export-engine.php";
            include_once plugin_dir_path( __FILE__ ) . "lib/class-wc-order-export-labels.php";
            include_once plugin_dir_path( __FILE__ ) . "lib/class-wc-order-export-data-extractor.php";
            include_once plugin_dir_path( __FILE__ ) . "lib/class-wc-order-export-data-extractor-ui.php";
            include_once plugin_dir_path( __FILE__ ) . "class-wc-order-export-engine-customized.php";

        }  

        /**
         * Include css
         */
        public function css_enqueue() {
            wp_enqueue_style( 'woce_css', plugin_dir_url(__FILE__) . 'assets/build/css/global.css' );
        }

        /**
         * Include js
         */
        public function js_enqueue() {
            wp_enqueue_script( 'woce_js', plugin_dir_url(__FILE__) . 'assets/build/js/export-custom.js' );
    		wp_enqueue_script( 'serializejson', plugin_dir_url(__FILE__) . 'assets/jquery.serializejson.js', array( 'jquery' ) );
        }

        /**
         * * add submenu under woocommerce menu
         */
        public function add_submenu() {
            $this->page_id = add_submenu_page(
                'woocommerce',
                __( 'CSVエクスポート', 'wc-order-csv-export' ),
                __( 'CSVエクスポート', 'wc-order-csv-export' ),
                'manage_woocommerce',
                'csv-export',
                array( $this, 'custom_submenu_page_callback' )
            );
        }

        /**
         * * custom submenu page render
         */
        public function custom_submenu_page_callback() {
            require( plugin_dir_path(__FILE__) . 'view/export-custom.php' );
            // require('class-wc-order-export-admin-tab-custom-export.php');
        }

        /**
         * * search orders
         */
        public function order_search() {
            global $wpdb;

            if( !current_user_can('view_woocommerce_reports') ){
                die( __( 'You can not do it', 'woo-order-export-lite' ) );
            }

            $settings = json_decode( stripslashes( $_POST['json'] ), true );
            $per_row_mode = $_POST['per_row_mode'];

            $settings = self::validate_defaults($settings);
            $settings['per_row_mode'] = $per_row_mode;
            // get order ids
            $sql = self::sql_get_order_ids($settings);

            if ($per_row_mode == "product") {
                $order_ids = $wpdb->get_results( $sql ); // or order_id - product_sku array
            } else if ($per_row_mode == "order") {
                $order_ids = $wpdb->get_col( $sql );
            }
            echo json_encode( $order_ids );
            die();
        }

        protected static function validate_defaults( $settings ) {
            if ( empty( $settings['sort'] ) ) {
                $settings['sort'] = 'order_id';
            }
            if ( empty( $settings['sort_direction'] ) ) {
                $settings['sort_direction'] = 'DESC';
            }
            if ( ! isset( $settings['skip_empty_file'] ) ) {
                $settings['skip_empty_file'] = true;
            }
            if( !empty($settings['product_sku']) ) {
                $sku_array = preg_split( "#,|\r?\n#", $settings['product_sku'], null, PREG_SPLIT_NO_EMPTY ) ;
                foreach($sku_array as $sku) {
                    $sku = "_sku = " . $sku;
                    $settings['product_custom_fields'][] = $sku;
                }
            }
            return $settings;
        }

        public static function sql_get_order_ids($settings) {
            global $wpdb;

            $sql_order_filters = self::sql_build_order_filters($settings);
            $sql_order_filters .= " ORDER BY " . $settings['sort'] . " " . $settings['sort_direction'];

            return $sql_order_filters;
        }

        /**
         * * order filter attribs are :
         * *      
         */
        public static function sql_build_order_filters($settings) {
            global $wpdb;

            $post_table = $wpdb->posts;
            $sql = "";
            $sql_query = "WHERE ";
            $filter_settings = $settings[ 'settings' ];

            /**
             * * wp_postmeta table
             * fields list:
             * expect_delivery_date, product_code, expect_ship_date, paid_date, order_money
             */
            $postmeta_table = $wpdb->postmeta;
            $left_join_postmeta = '';
            $query_postmeta = array();
            $field_pos = 1;

            $filters_for_postmeta_from_date = array( 'from_expect_ship_date', 'from_expect_delivery_date' );
            $filters_for_postmeta_to_date = array( 'to_expect_ship_date', 'to_expect_delivery_date' );
            $filters_to_fields_list_for_postmeta = array(
                'from_expect_delivery_date' => 'wc4jp-delivery-date',
                'from_expect_ship_date' => 'wc4jp_tracking_ship_date',
                'to_expect_delivery_date' => 'wc4jp-delivery-date',
                'to_expect_ship_date' => 'wc4jp_tracking_ship_date',
            );

            $join_table_name = "postmeta_cf_" . $field_pos;
            $left_join_postmeta .= " LEFT JOIN $postmeta_table AS $join_table_name ON $join_table_name.post_id = orders.ID AND $join_table_name.meta_key = 'wc4jp_tracking_ship_date' ";
            if ( isset( $filter_settings[ 'from_expect_ship_date' ] ) && $filter_settings[ 'from_expect_ship_date' ] ) {
                $field_value = $filter_settings[ 'from_expect_ship_date' ];
                $field_value = date( 'Y/m/d', strtotime( $field_value ) );
                $query_postmeta[] = " $join_table_name.meta_value >= '$field_value' ";
            }
            if ( isset( $filter_settings[ 'to_expect_ship_date' ] ) && $filter_settings[ 'to_expect_ship_date' ] ) {
                $field_value = $filter_settings[ 'to_expect_ship_date' ];
                $field_value = date( 'Y/m/d', strtotime( $field_value ) );
                $query_postmeta[] = " $join_table_name.meta_value <= '$field_value' ";
            }
            $field_pos += 1;
            $join_table_name = "postmeta_cf_" . $field_pos;
            $left_join_postmeta .= " LEFT JOIN $postmeta_table AS $join_table_name ON $join_table_name.post_id = orders.ID AND $join_table_name.meta_key = 'wc4jp-delivery-date' ";
            if ( isset( $filter_settings[ 'from_expect_delivery_date' ] ) && $filter_settings[ 'from_expect_delivery_date' ] ) {
                $field_value = $filter_settings[ 'from_expect_delivery_date' ];
                $field_value = date( 'Y/m/d', strtotime( $field_value ) );
                $query_postmeta[] = " $join_table_name.meta_value >= '$field_value' ";
            }
            if ( isset( $filter_settings[ 'to_expect_delivery_date' ] ) && $filter_settings[ 'to_expect_delivery_date' ] ) {
                $field_value = $filter_settings[ 'to_expect_delivery_date' ];
                $field_value = date( 'Y/m/d', strtotime( $field_value ) );
                $query_postmeta[] = " $join_table_name.meta_value <= '$field_value' ";
            }
            $field_pos += 1;

            // check paid_status field for paid_status
            if ( isset( $filter_settings[ 'paid_status' ] ) ) {
                $join_table_name = "postmeta_cf_" . $field_pos;
                $field_name = "_paid_date";

                if ( $filter_settings[ 'paid_status' ] == '1' ) {
                    $left_join_postmeta .= " LEFT JOIN $postmeta_table AS $join_table_name ON $join_table_name.post_id = orders.ID "; 
                    $query_postmeta[] = " ( $join_table_name.meta_key = '$field_name' ) ";
                } else {
                    $query_postmeta[] = " ( NOT EXISTS
                        (
                            SELECT null FROM $postmeta_table $join_table_name WHERE $join_table_name.post_id = orders.ID AND $join_table_name.meta_key = '$field_name'
                        )
                    ) ";
                }
                $field_pos += 1;
            }
            // check _order_total field for order_money
            if ( isset( $filter_settings[ 'from_order_money' ] ) &&  $filter_settings[ 'from_order_money' ] ) {
                $join_table_name = "postmeta_cf_" . $field_pos;
                $field_name = "_order_total";
                $filter_value = $filter_settings[ 'from_order_money' ];
                $left_join_postmeta .= " LEFT JOIN $postmeta_table AS $join_table_name ON $join_table_name.post_id = orders.ID AND $join_table_name.meta_key = '$field_name' ";
                $query_postmeta[] = " ( $join_table_name.meta_value >= $filter_value ) ";
                $field_pos += 1;
            }
            if ( isset( $filter_settings[ 'to_order_money' ] ) &&  $filter_settings[ 'to_order_money' ] ) {
                $join_table_name = "postmeta_cf_" . $field_pos;
                $field_name = "_order_total";
                $filter_value = $filter_settings[ 'to_order_money' ];
                $left_join_postmeta .= " LEFT JOIN $postmeta_table AS $join_table_name ON $join_table_name.post_id = orders.ID AND $join_table_name.meta_key = '$field_name' ";
                $query_postmeta[] = " ( $join_table_name.meta_value <= $filter_value ) ";
                $field_pos += 1;
            }
            // check _payment_method field for payment_method
            if ( isset( $filter_settings[ 'payment_methods' ] ) && $filter_settings[ 'payment_methods' ] ) {
                $join_table_name = "postmeta_cf_" . $field_pos;
                $field_name = "_payment_method";
                $field_value_array = array_map( 'single_quote', $filter_settings[ 'payment_methods' ] );
                $left_join_postmeta .= " LEFT JOIN $postmeta_table AS $join_table_name ON $join_table_name.post_id = orders.ID AND $join_table_name.meta_key = '$field_name' ";
                $query_postmeta[] = " ( $join_table_name.meta_value in ( " . join( ',', $field_value_array ) . " ) ) ";
                $field_pos += 1;
            }
            // add to query
            if ( $left_join_postmeta !== '' && count( $query_postmeta ) > 0 ) {
                $sql .= $left_join_postmeta;
                $sql_query .= join( 'AND', $query_postmeta ) . ' AND ';
            }

            /**
             * * wp_posts & wp_wc_order_product_lookup table
             * filter on this case - product_code ::: indiv_product_code excluded
             */
            $wc_orderproduct_table = $wpdb->prefix . "wc_order_product_lookup";
            if ( isset( $filter_settings[ 'product_code' ] ) && $filter_settings[ 'product_code' ] ) {
                $middle_table_name = "middle_orderproduct_" . $field_pos;
                $join_table_name = "postmeta_product_" . $field_pos;
                $field_value = $filter_settings[ 'product_code' ];

                $left_join_postmeta_product = " LEFT JOIN $wc_orderproduct_table AS $middle_table_name
                                                    INNER JOIN $postmeta_table AS $join_table_name
                                                    ON $middle_table_name.product_id = $join_table_name.post_id
                                               ON $middle_table_name.order_id = orders.ID ";
                $sql .= $left_join_postmeta_product;
                $sql_query .= " $join_table_name.meta_value LIKE '%$field_value%' AND ";
                $field_pos += 1;
            }

            /**
             * * wp_woocommerce_order_items table
             * filter on this table only 1 - order_item_name
             */
            $wc_orderitems_table = $wpdb->prefix . "woocommerce_order_items";
            if ( isset( $filter_settings[ 'product_name' ] ) && $filter_settings[ 'product_name' ] ) {
                $join_table_name = "orderitems_cf";
                $field_value = $filter_settings[ 'product_name' ];
                $left_join_orderitems = " LEFT JOIN $wc_orderitems_table AS $join_table_name ON $join_table_name.order_id = orders.ID AND $join_table_name.order_item_type = 'line_item' ";
                $query_orderitems = " ( $join_table_name.order_item_name LIKE '%$field_value%' ) ";

                // add to query
                $sql .= $left_join_orderitems;
                $sql_query .= $query_orderitems . ' AND ';
            }
            /**
             * * post_table table itself
             * filedslist
             */
            $query_orders = " orders.post_type = 'shop_order' ";
            if ( isset( $filter_settings[ 'from_order_id' ] ) && $filter_settings[ 'from_order_id' ] ) {
                $field_value = $filter_settings[ 'from_order_id' ];
                $query_orders .= " AND orders.ID >= $field_value ";
            }
            if ( isset( $filter_settings[ 'to_order_id' ] ) && $filter_settings[ 'to_order_id' ] ) {
                $field_value = $filter_settings[ 'to_order_id' ];
                $query_orders .= " AND orders.ID <= $field_value ";
            }
            if ( isset( $filter_settings[ 'statuses' ] ) && $filter_settings[ 'statuses' ] ) {
                $field_value_array = array_map( 'single_quote', $filter_settings[ 'statuses' ] );
                $query_orders .= " AND orders.post_status in ( " . join( ',', $field_value_array ) . " ) ";
            }
            if ( isset( $filter_settings[ 'from_date' ] ) && $filter_settings[ 'from_date' ] ) {
                $field_value = $filter_settings[ 'from_date' ];
                $query_orders .= " AND orders.post_date >= '$field_value' ";
            }
            if ( isset( $filter_settings[ 'to_date' ] ) && $filter_settings[ 'to_date' ] ) {
                $field_value = $filter_settings[ 'to_date' ];
                $query_orders .= " AND orders.post_date <= '$field_value' ";
            }
            // add to query
            $sql_query .= $query_orders . ' AND ';

            /**
             * * customer_table
             * fields list:
             * first_name, last_name, email_address, member_id (from, to)
             */
            $order_stats_table = $wpdb->prefix . "wc_order_stats";
            $order_stats_table_name = 'order_stats_medium'; // * middle table
            $customer_table = $wpdb->prefix . "wc_customer_lookup";
            $filters_for_customer = array( 'orderer_first_name', 'orderer_last_name', 'email_address' );   // excluded 'member_id'
            $filters_to_fields_list_for_customer = array(
                'orderer_first_name' => 'first_name',
                'orderer_last_name' => 'last_name',
                'email_address' => 'email',
            );
            $middle_table_name = "middle_order_stats" . $field_pos;
            $join_table_name = "customer_cf_" . $field_pos;
            $field_pos += 1;
            $left_join_customer = "LEFT JOIN $order_stats_table AS $middle_table_name
                                        INNER JOIN $customer_table AS $join_table_name 
                                        ON $middle_table_name.customer_id = $join_table_name.customer_id
                                    ON $middle_table_name.order_id = orders.ID";
            $query_customer = array();
            foreach( $filters_for_customer as $filter_item ) {
                if ( isset( $filter_settings[ $filter_item ] ) && $filter_settings[ $filter_item ] ) {
                    $field_name = $filters_to_fields_list_for_customer[ $filter_item ];
                    $filter_value = $filter_settings[ $filter_item ];
                    $query_customer[] = " $join_table_name.$field_name LIKE '%$filter_value%' ";
                }
            }
            // member_id (from, to)
            if ( isset( $filter_settings[ 'from_member_id' ] ) && $filter_settings[ 'from_member_id' ] ) {
                $filter_value = $filter_settings[ 'from_member_id' ];
                $query_customer[] = " $join_table_name.user_id >= $filter_value ";
            }
            if ( isset( $filter_settings[ 'to_member_id' ] ) && $filter_settings[ 'to_member_id' ] ) {
                $filter_value = $filter_settings[ 'to_member_id' ];
                $query_customer[] = " $join_table_name.user_id <= $filter_value ";
            }
            // add to query
            $sql .= $left_join_customer;
            $sql_query .= join( 'AND ', $query_customer );

            /**
             * * comments_table
             * field_name: memo
             */
            $comment_table = $wpdb->comments;
            $query_comments = array();
            if ( isset( $filter_settings[ 'memo' ] ) && $filter_settings[ 'memo' ] ) {
                $query_comments = " ( orders.post_excerpt LIKE '%$filter_value%' ";
                $join_table_name = "comment_cf_" . $field_pos;
                $filter_value = $filter_settings[ 'memo' ];

                $left_join_comment = " LEFT JOIN $comment_table AS $join_table_name ON $join_table_name.comment_post_ID = orders.ID AND $join_table_name.comment_type = 'order_note' ";
                $query_comments .= " OR $join_table_name.comment_content LIKE '%$filter_value%' ) ";
                // add to query
                $sql .= $left_join_comment;
                $sql_query .= $query_comments;
                
                $field_pos += 1;
            }

            // $postmeta_for_product = 
            $product_id_table_name = "product_item_id_lookup_table"; 
            $sql .= " LEFT JOIN $wc_orderproduct_table AS $product_id_table_name ON $product_id_table_name.order_id = orders.ID ";

            $sql_query = trim( $sql_query );
            if ( substr( $sql_query, -3, 3 ) == "AND" ) {
                $sql_query = substr( $sql_query, 0, -4);
            }
            $sql = $sql . ' ' . $sql_query;

            if ($settings['per_row_mode'] == 'order') {
                $sql = "SELECT DISTINCT ID AS order_id" . " FROM {$post_table} AS orders " . $sql;
            } else if ($settings['per_row_mode'] == 'product') {
                $sql = "SELECT ID AS order_id, {$product_id_table_name}.order_item_id AS item_id FROM {$post_table} AS orders " . $sql;
            }

            return $sql;
        }

        /**
         * * csv export function
         */
        public function ajax_csv_export() {
            if( !current_user_can('view_woocommerce_reports') ){
                die( __( 'You can not do it', 'woo-order-export-lite' ) );
            }

            $method = "ajax_" . $_REQUEST['method'];
            if (! method_exists( $this, $method ) ) {
    			die( sprintf( __( 'Unknown tab method %s', 'woo-order-export-lite' ), $method) );
            }
            $this->$method();
        }   

        public function ajax_preview_csv() {
            global $wp_filter, $default_export_settings_collection;

            $current_settings = $default_export_settings_collection;
            $current_csv_format = json_decode( stripslashes( $_POST['json'] ), true );

            $current_settings['orders'] = $current_csv_format;
            $current_settings['order_fields'] = $current_csv_format;
            $current_settings['format'] = 'CSV'; 
            $current_settings['format_csv_add_utf8_bom'] = 1;
            $current_settings['per_row_mode'] = isset( $_POST['per_row_mode'] ) ? $_POST['per_row_mode'] : 'product';

            $order_ids = json_decode( stripslashes( $_POST['order_id_list'] ), true );
            // $order_ids = (gettype($order_ids) == "string" && $order_ids !== "") ? explode(",", $order_ids) : array();  // the fallback value should be all order ids list
      		WC_Order_Export_Engine_Customized::kill_buffers();

            ob_start();

            $current_wp_filter = $wp_filter;
            $limit = 10;
            $offset = 0;

            if (isset($_POST['limit'])) {
                $limit = $_POST['limit'];
            }
            if (isset($_POST['offset'])) {
                $offset = $_POST['offset'];
            }

            WC_Order_Export_Engine_Customized::build_file_customized( $order_ids, $current_settings, 'preview', 'browser', 0, $limit );
            $wp_filter = $current_wp_filter;//revert all hooks/fiilters added by build_file
            
            $html = ob_get_contents();
            ob_end_clean();
            echo json_encode( array( 'total' => count($order_ids), 'html' => $html ) );
            die();
        }


        public function ajax_csv_export_start() {
            global $default_export_settings_collection;

            // get saved settings from export now tab of Woocommerce Advanced Order Export
            $current_settings = $default_export_settings_collection;
            $current_csv_format = json_decode( stripslashes( $_POST['json'] ), true );
            $current_settings['orders'] = $current_csv_format;
            $current_settings['order_fields'] = $current_csv_format;
            $current_settings['format'] = 'CSV'; 
            $current_settings['format_csv_add_utf8_bom'] = 1;
            $current_settings['per_row_mode'] = isset( $_POST['per_row_mode'] ) ? $_POST['per_row_mode'] : 'product';
            $order_ids = json_decode( stripslashes( $_POST['order_id_list'] ), true );

            $filename = WC_Order_Export_Engine_Customized::get_filename( "orders" );
            if ( ! $filename ) {
                die( __( 'Can\'t create temporary file', 'woo-order-export-lite' ) );
            }

            try {
                file_put_contents( $filename, '' );
                $result = WC_Order_Export_Engine_Customized::build_file_customized( $order_ids, $current_settings, 'start_estimate', 'file', 0, 0, $filename );
            } catch ( Exception $e ) {
                die( $e->getMessage() );
            }
            $file_id = current_time( 'timestamp' );
            set_transient( $this->tempfile_prefix . $file_id, $filename, 60 );
            $this->stop_prevent_object_cache();

            echo json_encode( array( 
                'total' => count($order_ids), 
                'file_id' => $file_id,
                'max_line_items' => $result['max_line_items'],
                'max_coupons' => $result['max_coupons'],
            ) );
            exit();
        }

        public function ajax_csv_export_part() {
            global $default_export_settings_collection, $default_main_settings;
            $current_settings = $default_export_settings_collection;
            $current_csv_format = json_decode( stripslashes( $_POST['json'] ), true );
            $current_settings['orders'] = $current_csv_format;
            $current_settings['order_fields'] = $current_csv_format;
            $current_settings['format_csv_add_utf8_bom'] = 1;
            $current_settings['per_row_mode'] = isset( $_POST['per_row_mode'] ) ? $_POST['per_row_mode'] : 'product';
            $order_ids = json_decode( stripslashes( $_POST['order_id_list'] ), true );
            $main_settings = $default_main_settings;

            $current_settings['max_line_items'] = $_POST['max_line_items'];
            $current_settings['max_coupons'] = $_POST['max_coupons'];
            $current_settings['format'] = 'CSV'; 

            WC_Order_Export_Engine_Customized::build_file_customized( $order_ids, $current_settings, 'partial', 'file', intval( $_POST['start'] ),
                $main_settings['ajax_orders_per_step'],
                $this->get_temp_file_name() );
            echo json_encode( array( 'start' => $_POST['start'] + $main_settings['ajax_orders_per_step'] ) );
            exit();
        }

        public function ajax_csv_export_finish() {
            global $default_export_settings_collection;
            $current_settings = $default_export_settings_collection;
            $current_csv_format = json_decode( stripslashes( $_POST['json'] ), true );
            $current_settings['orders'] = $current_csv_format;
            $current_settings['order_fields'] = $current_csv_format;
            $current_settings['format'] = 'CSV'; 
            $current_settings['format_csv_add_utf8_bom'] = 1;
            $current_settings['per_row_mode'] = isset( $_POST['per_row_mode'] ) ? $_POST['per_row_mode'] : 'product';
            $order_ids = json_decode( stripslashes( $_POST['order_id_list'] ), true );

            WC_Order_Export_Engine_Customized::build_file_customized( $order_ids, $current_settings, 'finish', 'file', 0, 0, $this->get_temp_file_name() );

            // !should fix this part
            $filename = WC_Order_Export_Engine_Customized::make_filename( $current_settings['export_filename'] );
            $filename_split = explode('.', $filename);
            array_pop($filename_split);
            array_push($filename_split, 'csv');
            $filename = implode('.', $filename_split);
            $this->start_prevent_object_cache();
            set_transient( $this->tempfile_prefix . 'download_filename', $filename, 60 );
            $this->stop_prevent_object_cache();
            echo json_encode( array( 'done' => true ) );
            exit();
        }

        public function ajax_csv_export_download() {
            $this->start_prevent_object_cache();
            $format   = basename( $_GET['format'] );
            $filename = $this->get_temp_file_name();
            delete_transient( $this->tempfile_prefix . $_GET['file_id'] );

            $download_name = get_transient( $this->tempfile_prefix . 'download_filename' );
            $this->send_headers( $format, $download_name );
            $this->send_contents_delete_file( $filename );
            $this->stop_prevent_object_cache();
            exit();

        }

    }

    new Wc_Order_Csv_Export();
}

function single_quote( $value ) {
    return "'$value'";
}


trait WC_Order_Export_Ajax_Helpers_reuse {
	protected $tempfile_prefix = 'woocommerce-order-file-';

	protected $_wp_using_ext_object_cache_previous;

	protected function send_headers( $format, $download_name = '' ) {

		WC_Order_Export_Engine_Customized::kill_buffers();

		switch ( $format ) {
			case 'XLSX':
				if ( empty( $download_name ) ) {
					$download_name = "orders.xlsx";
				}
				header( 'Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
				break;
			case 'XLS':
				if ( empty( $download_name ) ) {
					$download_name = "orders.xls";
				}
				header( 'Content-type: application/vnd.ms-excel; charset=utf-8' );
				break;
			case 'CSV':
				if ( empty( $download_name ) ) {
					$download_name = "orders.csv";
				}
				header( 'Content-type: text/csv' );
				break;
		}
		header( 'Content-Disposition: attachment; filename="' . $download_name . '"' );
	}

	protected function start_prevent_object_cache() {

		global $_wp_using_ext_object_cache;

		$this->_wp_using_ext_object_cache_previous = $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache                = false;
	}

	protected function stop_prevent_object_cache() {

		global $_wp_using_ext_object_cache;

		$_wp_using_ext_object_cache = $this->_wp_using_ext_object_cache_previous;
	}

	protected function send_contents_delete_file( $filename ) {
		if ( ! empty( $filename ) ) {
			if( !$this->function_disabled('readfile') ) {
				readfile( $filename );
			} else {
				// fallback, emulate readfile 
				$file = fopen($filename, 'rb');
				if ( $file !== false ) {
					while ( !feof($file) ) {
						echo fread($file, 4096);
					}
					fclose($file);
				}
			}
			unlink( $filename );
		}
	}
	
	function function_disabled($function) {
		$disabled_functions = explode(',', ini_get('disable_functions'));
		return in_array($function, $disabled_functions);
	}

	protected function get_temp_file_name() {

		$this->start_prevent_object_cache();

		$filename = get_transient( $this->tempfile_prefix . $_REQUEST['file_id'] );
		if ( $filename === false ) {
			echo json_encode( array( 'error' => __( 'Can\'t find exported file', 'woo-order-export-lite' ) ) );
			die();
		}
		set_transient( $this->tempfile_prefix . $_REQUEST['file_id'], $filename, 60 );
		$this->stop_prevent_object_cache();

		return $filename;
	}

	protected function delete_temp_file() {

		$this->start_prevent_object_cache();
		$filename = get_transient( $this->tempfile_prefix . $_REQUEST['file_id'] );
		if ( $filename !== false ) {
			delete_transient( $this->tempfile_prefix . $_REQUEST['file_id'] );
			unlink( $filename );
		}
		$this->stop_prevent_object_cache();
	}

}
