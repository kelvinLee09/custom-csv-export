<?php

$default_export_settings_collection = array (
  'version' => '2.0',
  'mode' => 'now',
  'title' => '',
  'skip_empty_file' => true,
  'log_results' => false,
  'from_status' => 
  array (
  ),
  'to_status' => 
  array (
  ),
  'change_order_status_to' => '',
  'statuses' => 
  array (
    0 => 'wc-pending',
    1 => 'wc-processing',
    2 => 'wc-on-hold',
    3 => 'wc-completed',
  ),
  'from_date' => '',
  'to_date' => '',
  'sub_start_from_date' => '',
  'sub_start_to_date' => '',
  'sub_end_from_date' => '',
  'sub_end_to_date' => '',
  'sub_next_paym_from_date' => '',
  'sub_next_paym_to_date' => '',
  'from_order_id' => '',
  'to_order_id' => '',
  'shipping_locations' => 
  array (
  ),
  'shipping_methods' => 
  array (
  ),
  'item_names' => 
  array (
  ),
  'item_metadata' => 
  array (
  ),
  'user_roles' => 
  array (
  ),
  'user_names' => 
  array (
  ),
  'user_custom_fields' => 
  array (
  ),
  'billing_locations' => 
  array (
  ),
  'payment_methods' => 
  array (
  ),
  'any_coupon_used' => '0',
  'coupons' => 
  array (
  ),
  'order_custom_fields' => 
  array (
  ),
  'product_categories' => 
  array (
  ),
  'product_vendors' => 
  array (
  ),
  'products' => 
  array (
  ),
  'product_sku' => '',
  'exclude_products' => 
  array (
  ),
  'product_taxonomies' => 
  array (
  ),
  'product_custom_fields' => 
  array (
  ),
  'product_attributes' => 
  array (
  ),
  'product_itemmeta' => 
  array (
  ),
  'format' => 'CSV',
  'format_xls_use_xls_format' => '0',
  'format_xls_sheet_name' => 'Orders',
  'format_xls_display_column_names' => '1',
  'format_xls_auto_width' => '1',
  'format_xls_direction_rtl' => '0',
  'format_xls_force_general_format' => '0',
  'format_xls_row_images_width' => '50',
  'format_xls_row_images_height' => '50',
  'format_csv_enclosure' => '"',
  'format_csv_delimiter' => ',',
  'format_csv_linebreak' => '\\r\\n',
  'format_csv_display_column_names' => '1',
  'format_csv_add_utf8_bom' => '0',
  'format_csv_item_rows_start_from_new_line' => '0',
  'format_csv_encoding' => 'UTF-8',
  'format_csv_delete_linebreaks' => '0',
  'format_csv_force_quotes' => '0',
  'all_products_from_order' => '1',
  'skip_refunded_items' => '0',
  'skip_suborders' => '0',
  'export_refunds' => '0',
  'export_matched_items' => '0',
  'date_format' => 'Y-m-d',
  'time_format' => 'H:i',
  'sort_direction' => 'DESC',
  'sort' => 'order_id',
  'format_number_fields' => '0',
  'export_all_comments' => '0',
  'export_refund_notes' => '0',
  'strip_tags_product_fields' => '0',
  'round_item_tax_rate' => '0',
  'cleanup_phone' => '0',
  'convert_serialized_values' => '0',
  'enable_debug' => '0',
  'billing_details_for_shipping' => '0',
  'custom_php' => '0',
  'custom_php_code' => '',
  'mark_exported_orders' => '0',
  'export_unmarked_orders' => '0',
  'summary_report_by_products' => '0',
  'duplicated_fields_settings' => 
  array (
    'products' => 
    array (
      'repeat' => 'rows',
      'populate_other_columns' => '1',
      'max_cols' => '10',
      'group_by' => 'product',
      'line_delimiter' => '\\n',
    ),
    'coupons' => 
    array (
      'repeat' => 'rows',
      'max_cols' => '10',
      'group_by' => 'product',
      'line_delimiter' => '\\n',
    ),
  ),
  'summary_report_by_customers' => '0',
  'order_fields' => array(),
  'order_product_fields' => 
  array (
    0 => 
    array (
      'label' => 'SKU',
      'format' => 'string',
      'colname' => 'SKU',
      'default' => 1,
      'key' => 'sku',
    ),
    1 => 
    array (
      'label' => 'Item #',
      'format' => 'number',
      'colname' => 'Item #',
      'default' => 1,
      'key' => 'line_id',
    ),
    2 => 
    array (
      'label' => 'Item Name',
      'format' => 'string',
      'colname' => 'Item Name',
      'default' => 1,
      'key' => 'name',
    ),
    3 => 
    array (
      'label' => 'Quantity',
      'format' => 'number',
      'colname' => 'Quantity',
      'default' => 1,
      'key' => 'qty',
    ),
    4 => 
    array (
      'label' => 'Item Cost',
      'format' => 'money',
      'colname' => 'Item Cost',
      'default' => 1,
      'key' => 'item_price',
    ),
  ),
  'order_coupon_fields' => 
  array (
    0 => 
    array (
      'label' => 'Coupon Code',
      'format' => 'string',
      'colname' => 'Coupon Code',
      'default' => 1,
      'key' => 'code',
    ),
    1 => 
    array (
      'label' => 'Discount Amount',
      'format' => 'money',
      'colname' => 'Discount Amount',
      'default' => 1,
      'key' => 'discount_amount',
    ),
    2 => 
    array (
      'label' => 'Discount Amount Tax',
      'format' => 'money',
      'colname' => 'Discount Amount Tax',
      'default' => 1,
      'key' => 'discount_amount_tax',
    ),
  ),
  'id' => 0,
  'post_type' => 'shop_order',
  'export_rule_field' => 'date',
  'export_filename' => 'orders-%y-%m-%d-%h-%i-%s.xlsx',
);

$default_main_settings = array (
  'default_tab' => 'export',
  'cron_tasks_active' => true,
  'show_export_status_column' => '1',
  'show_export_actions_in_bulk' => '1',
  'show_export_in_status_change_job' => '0',
  'autocomplete_products_max' => '10',
  'show_all_items_in_filters' => false,
  'apply_filters_to_bulk_actions' => false,
  'ajax_orders_per_step' => '30',
  'limit_button_test' => '1',
  'cron_key' => NULL,
  'ipn_url' => '',
  'notify_failed_jobs' => 0,
  'notify_failed_jobs_email_subject' => '',
  'notify_failed_jobs_email_recipients' => '',
  'zapier_api_key' => '12345678',
  'zapier_file_timeout' => 60,
  'show_date_time_picker_for_date_range' => false,
  'display_profiles_export_date_range' => false,
  'show_destination_in_profile' => false,
  'display_html_report_in_browser' => false,
  'default_date_range_for_export_now' => '',
  'default_html_css' => '',
);
