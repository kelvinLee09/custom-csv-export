<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Engine_Customized extends WC_Order_Export_Engine {
    public static function build_file_customized(
        $order_ids,
        $settings,
        $make_mode,
        $output_mode,
        $offset = false,
        $limit = false,
        $filename = ''
    ) {
            global $wpdb;

            self::$current_job_build_mode = $make_mode;
            if($make_mode != 'preview' AND $make_mode != 'estimate_preview') { // caller  uses kill_buffers() already
                self::kill_buffers();
            }
            $settings                     = self::validate_defaults( $settings );

            self::$current_job_settings   = $settings;
            self::$date_format            = trim( $settings['date_format'] . ' ' . $settings['time_format'] );
            //debug sql?
            if ( $make_mode == 'preview' AND $settings['enable_debug'] ) {
                WC_Order_Export_Data_Extractor::start_track_queries();
            }

            // might run sql!
            self::$extractor_options = self::_install_options( $settings );

            if ( $output_mode == 'browser' ) {
                $filename = 'php://output';
            } else {
                $filename = self::get_filename($settings['format'], $filename);
            }

            $formater = self::init_formater( $make_mode, $settings, $filename, $labels, $static_vals, $offset );
            $format = strtolower( $settings['format'] );

            if ( $make_mode == 'finish' ) {
                $formater->finish();

                return $filename;
            }

            // prepare for XLS/CSV moved to plain formatter
            $formater->adjust_duplicated_fields_settings( $order_ids, $make_mode, $settings );

            // check it once
            self::_check_products_and_coupons_fields( $settings, $export );

            // make header moved to plain formatter
            if ( $make_mode != 'partial' ) { // Preview or start_estimate
                $formater->start();
                if ( $make_mode == 'start_estimate' ) { //Start return total count
                    $duplicate_settings = $formater->get_duplicate_settings();
                    return array(
                        'total' => count($order_ids),
                        'max_line_items' => isset( $duplicate_settings['products']['max_cols'] ) ? $duplicate_settings['products']['max_cols'] : 0,
                        'max_coupons' => isset( $duplicate_settings['coupons']['max_cols'] ) ? $duplicate_settings['coupons']['max_cols'] : 0,
                    );
                }
            }

            WC_Order_Export_Data_Extractor::prepare_for_export();
            self::$orders_exported = 0;// incorrect value

            foreach ( $order_ids as $order_id_item ) {
                $order_id = $order_id_item;
                if (isset($order_id_item['order_id'])) {
                   $order_id = $order_id_item['order_id'];
                }
                
                if ( ! $order_id ) {
                    continue;
                }
                self::$order_id = $order_id;

                $row            = WC_Order_Export_Data_Extractor::fetch_order_data( $order_id, $labels,
                    $export, $static_vals, self::$extractor_options );

                if ($settings['per_row_mode'] == "order") {
                    $first_product = reset($row['products']);
                    $first_product_key = key($row['products']);
                    $row['products'] = array();
                    $row['products'][ $first_product_key ] = $first_product;
                } else if ($settings['per_row_mode'] == "product") {
                    $item_id = $order_id_item['item_id'];
                    $new_row = array();
                    $new_row[$item_id] = $row['products'][$item_id];
                    $row['products'] = $new_row;
                }
                // $row = apply_filters( "woe_fetch_order_row", $row, $order_id );

                if ( $row ) {
                    $formater->output( $row );
                }

            }

            // for modes
            if ( $make_mode == 'partial' ) {
                $formater->finish_partial();
            } elseif ( $make_mode == 'preview' ) {
                if ( $settings['enable_debug'] AND self::is_plain_format( $settings['format'] ) ) {
                    echo "<b>" . __( 'Main SQL queries are listed below', 'woo-order-export-lite' ) . "</b>";
                    echo '<textarea rows=5 style="width:100%">';
                    $s = array();
                    foreach ( WC_Order_Export_Data_Extractor::get_sql_queries() as $sql ) {
                        $s[] = preg_replace( "#\s+#", " ", $sql );
                    }
                    echo join( "\n\n", $s );
                    echo '</textarea>';
                }

                $formater->finish(); //backtrace
            }

            // no action woe_export_finished here!
            return $filename;
    }
}