<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$mode = WC_Order_Export_Manage::EXPORT_NOW;
$id = 0;
$settings                 = WC_Order_Export_Manage::get( $mode, $id );
$settings                 = apply_filters( 'woe_settings_page_prepare', $settings );
$order_custom_meta_fields = WC_Order_Export_Data_Extractor_UI::get_all_order_custom_meta_fields();
$readonly_php             = WC_Order_Export_Admin::user_can_add_custom_php() ? '' : 'readonly';
$options                  = WC_Order_Export_Main_Settings::get_settings();

function remove_time_from_date( $datetime ) {
	if ( ! $datetime ) {
		return "";
	}
	$timestamp = strtotime( $datetime );
	if ( ! $timestamp ) {
		return "";
	}
	$date = date( 'Y-m-d', $timestamp );
	return $date ? $date : "";
}
?>
<div class="tabs-content">
    <div class="mx-6">
      <form method="post" id="filter_conditions_form">
        <div class="mt-2 w-full">
          <div id="order_date_section" class="relative h-auto rounded-sm border border-gray-500 flex flex-row items-center" title="<?php _e( 'This date range should not be saved in the scheduled task', 'woo-order-export-lite' ) ?>">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>注文日時</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row items-center">
                <input type="datetime-local" class='hasDatepicker w-1/5' name="settings[from_date]" id="from_date" value='<?php echo ! empty($options['show_date_time_picker_for_date_range']) ? $settings['from_date']: remove_time_from_date($settings['from_date']) ?>'>
                <span class="w-12 text-center">~</span>
                <input type="datetime-local" class='hasDatepicker w-1/5' name="settings[to_date]" id="to_date" value='<?php echo ! empty($options['show_date_time_picker_for_date_range']) ? $settings['to_date']: remove_time_from_date($settings['to_date']) ?>'>
                <button class="ml-8 px-4 rounded-full border border-stone-500 bg-gradient-to-b from-stone-100 to-stone-300 shadow-sm clear-date hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100" >クリア</button>
              </div>
              <div class="px-3 w-full h-12 flex justify-center items-center">
                <div class="py-1 w-full bg-stone-200 flex flex-row">
                  <span class="date-today ml-1 py-1 px-2 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-md font-semibold align-middle cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">当日</span>
                  <span class="date-this-month mx-2 py-1 px-2 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-md font-semibold align-middle cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">当月</span>
                  <div class="h-8 w-1px bg-stone-300"></div>
                  <span class="date-yesterday ml-2 py-1 px-2 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-md font-semibold align-middle cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">前日</span>
                  <span class="date-since-yesterday ml-2 py-1 px-2 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-md font-semibold align-middle cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">前日+当日</span>                
                  <span class="date-since-3-days-bf ml-2 py-1 px-2 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-md font-semibold align-middle cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">過去3日</span>                
                  <span class="date-for-a-week ml-2 py-1 px-2 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-md font-semibold align-middle cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">過去1週間</span>                
                  <span class="date-last-month ml-2 py-1 px-2 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-md font-semibold align-middle cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">前月</span>
                  <span class="date-for-a-month ml-2 py-1 px-2 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-md font-semibold align-middle cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">過去1ヶ月</span>
                  <span class="date-3-months mx-2 py-1 px-2 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-md font-semibold align-middle cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">過去3ヶ月</span>
                  <div class="h-8 w-1px bg-stone-300"></div>
                  <span class="date-last-year ml-2 py-1 px-2 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-md font-semibold align-middle cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">前年</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="w-full flex flex-row flex-wrap">
          <div id="order_type_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center">
              <span>注文種類</span>
            </div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 w-300px flex flex-row">
                <select id="statuses" class="select2-i18n" name="settings[statuses][]" multiple="multiple" style="width: 100%; max-width: 25%;">
                  <?php foreach (apply_filters( 'woe_settings_order_statuses', wc_get_order_statuses() ) as $i => $status ) { ?>
                    <option value="<?php echo $i ?>" <?php if ( in_array( $i, $settings['statuses'] ) ) {
                        echo 'selected';
                    } ?>><?php echo $status ?></option>
                    <?php } ?>
                </select>
              </div>
            </div>
          </div>
          <div id="order_recipient_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>受領者名</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="text" name="settings[order_recipient]" id="order_recipient" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>        
          <div id="memo_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>メモ</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="text" name="settings[memo]" id="memo" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="payment_method__topup_status_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>決済方法 ・<br>入金ステータス</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row items-center">
                <label for="settings[payment_methods][]" class="w-28">決済方法</label>
                <select id="payment_methods" class="select2-i18n ml-3 w-300px h-8" name="settings[payment_methods][]" multiple="multiple" >
                  <?php foreach ( WC()->payment_gateways->payment_gateways() as $gateway ) { ?>
                    <option value="<?php echo $gateway->id ?>" <?php if ( in_array( $gateway->id, $settings['payment_methods'] ) ) {
                      echo 'selected';
                    } ?>><?php echo $gateway->get_title() ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="mt-1 ml-3 flex flex-row items-center">
                <label for="settings[paid_status]" class="w-28">入金ステータス</label>
                <select id="settings[paid_status]" class="w-300px h-8" name="settings[paid_status]" >
                  <option value="0">未払い</option>
                  <option value="1">支払い済み</option>
                </select>
              </div>
            </div>
          </div>
          <div id="expect_ship_date_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>出荷予定日</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="date" name="settings[expect_ship_date]" id="expect_ship_date" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="order_number_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>注文番号</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row items-center">
                <input type="text" name="settings[from_order_id]" id="from_order_id" value='' class="px-2 h-8 w-1/8 rounded-sm border border-gray-500">
                <span class="w-12 text-center">~</span>
                <input type="text" name="settings[to_order_id]" id="to_order_id" value='' class="px-2 h-8 w-1/8 rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="product_name_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>商品名</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="text" name="settings[product_name]" id="product_name" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>        
          <div id="process_type_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>処理区分</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="text" name="settings[process_type]" id="process_type" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="expect_delivery_date_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>配送希望日</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="date" name="settings[expect_delivery_date]" id="expect_delivery_date" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="doc_number_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>伝票番号</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="text" name="settings[doc_number]" id="doc_number" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="product_code_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>商品コード</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="text" name="settings[product_code]" id="product_code" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="email_address_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>メールアドレス</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="text" name="settings[email_address]" id="email_address" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="member_id_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>会員ID</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row items-center">
                <input type="text" name="settings[from_member_id]" id="from_member_id" class="px-2 h-8 w-1/8 rounded-sm border border-gray-500">
                <span class="w-12 text-center">~</span>
                <input type="text" name="settings[to_member_id]" id="to_member_id" class="px-2 h-8 w-1/8 rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="use_device_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>利用端末</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="text" name="settings[use_device]" id="use_device" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="orderer_name_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>注文者名</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row items-center">
                <label for="settings[orderer_first_name]" id="orderer_first_name" class="px-2 h-8 w-28 rounded-sm">姓</label>
                <input type="text" name="settings[orderer_first_name]" id="orderer_first_name" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
              <div class="mt-1 ml-3 flex flex-row items-center">
                <label for="settings[orderer_last_name]" id="orderer_last_name" class="px-2 h-8 w-28 rounded-sm">名</label>
                <input type="text" name="settings[orderer_last_name]" id="orderer_last_name" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="order_money_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>注文金額</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="text" name="settings[from_order_money]" id="from_order_money" class="px-2 h-8 w-1/8 rounded-sm border border-gray-500">
                <span class="w-12 text-center">~</span>
                <input type="text" name="settings[to_order_money]" id="to_order_money" class="px-2 h-8 w-1/8 rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
          <div id="indiv_product_code_section" class="hidden relative w-1/2 h-auto rounded-sm border border-t-0 border-gray-500 flex flex-row items-center">
            <div class="absolute h-full w-32 bg-stone-300 font-semibold text-md flex justify-center items-center"><span>独自商品コード</span></div>
            <div class="grow ml-32 py-1 h-full flex flex-col justify-center">
              <div class="mt-1 ml-3 flex flex-row">
                <input type="text" name="settings[indiv_product_code]" id="indiv_product_code" class="px-2 h-8 w-300px rounded-sm border border-gray-500">
              </div>
            </div>
          </div>
        </div>
      </form>
      <div class="mt-2 w-full">
        <button class="toggle-filter-attribs px-2 h-7 rounded-sm border border-stone-300 bg-gradient-to-b from-gray-100 to-gray-200 text-sm font-semibold text-black flex flex-row items-center justify-center hover:bg-gradient-to-b hover:from-white hover:to-amber-100 focus:outline-none focus:ring focus:ring-amber-100">
          <svg
            width="12px"
            height="12px"
            viewBox="0 0 490 490"
          >
            <g>
              <g>
                <path d="M490,474.459H0L245.009,15.541L490,474.459z" />
              </g>
            </g>
          </svg>
          <span class="ml-1">検索条件項目を開じる</span>
        </button>
        <div class="filter-attribs pt-3 pb-4 px-4 w-full rounded-sm border border-stone-200 bg-gray-100 flex flex-row flex-wrap">
            <div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_order_date" id="check_order_date" checked disabled="disabled" class="filter_check" />
              <label for="check_order_date" class="ml-1 -mt-2">注文日時</label>
            </div>
            <div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_order_type" id="check_order_type" class="filter_check" />
              <label for="check_order_type" class="ml-1 -mt-2">注文種類</label>
            </div>
      			<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_order_recipient" id="check_order_recipient" class="filter_check" />
              <label for="check_order_recipient" class="ml-1 -mt-2">受領者名</label>
            </div>
			      <div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_memo" id="check_memo" class="filter_check"  />
              <label for="check_memo" class="ml-1 -mt-2">メモ</label>
            </div>
			      <div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_payment_method__topup_status" id="check_payment_method__topup_status" class="filter_check"  />
              <label for="check_payment_method__topup_status" class="ml-1 -mt-2">決済方法 ・入金ステータス</label>
            </div>
	      		<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_expect_ship_date" id="check_expect_ship_date" class="filter_check"  />
              <label for="check_expect_ship_date" class="ml-1 -mt-2">出荷予定日</label>
            </div>
			      <div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_order_number" id="check_order_number" class="filter_check"  />
              <label for="check_order_number" class="ml-1 -mt-2">注文番号</label>
            </div>
            <div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_product_name" id="check_product_name" class="filter_check" />
              <label for="check_product_name" class="ml-1 -mt-2">商品名</label>
            </div>
		      	<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_process_type" id="check_process_type" class="filter_check" />
              <label for="check_process_type" class="ml-1 -mt-2">処理区分</label>
            </div>
		      	<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_expect_delivery_date" id="check_expect_delivery_date" class="filter_check" />
              <label for="check_expect_delivery_date" class="ml-1 -mt-2">配送希望日</label>
            </div>
		      	<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_doc_number" id="check_doc_number" class="filter_check"  />
              <label for="check_doc_number" class="ml-1 -mt-2">伝票番号</label>
            </div>
	      		<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_product_code" id="check_product_code" class="filter_check"  />
              <label for="check_product_code" class="ml-1 -mt-2">商品コード</label>
            </div>
		      	<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_email_address" id="check_email_address" class="filter_check" />
              <label for="check_email_address" class="ml-1 -mt-2">メールアドレス</label>
            </div>			
            <div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_member_id" id="check_member_id" class="filter_check" />
              <label for="check_member_id" class="ml-1 -mt-2">会員ID</label>
            </div>
		      	<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_use_device" id="check_use_device" class="filter_check" />
              <label for="check_use_device" class="ml-1 -mt-2">利用端末</label>
            </div>
		      	<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_orderer_name" id="check_orderer_name" class="filter_check"  />
              <label for="check_orderer_name" class="ml-1 -mt-2">注文者名</label>
            </div>
		      	<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_order_money" id="check_order_money" class="filter_check" />
              <label for="check_order_money" class="ml-1 -mt-2">注文金額</label>
            </div>
	      		<div class="mt-4 w-1/4 flex flex-row flex-start" >
              <input type="checkbox" name="check_indiv_product_code" id="check_indiv_product_code" class="filter_check"/>
              <label for="check_indiv_product_code" class="ml-1 -mt-2">独自商品コード</label>
            </div>
        </div>
      </div>
      <div class="mt-4 w-full">
        <div class="relative flex flex-row justify-center">
          <button class="search-btn h-10 w-28 rounded-sm border border-stone-300 bg-gradient-to-b from-white to-gray-100 text-lg font-bold hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">
            検索
          </button>
          <div class="ml-6 flex flex-col items-center">
            <button class="csv-export-btn px-4 h-10 rounded-sm border border-stone-300 bg-gradient-to-b from-white to-gray-100 text-lg font-bold hover:bg-gradient-to-b hover:from-white hover:to-red-100 focus:outline-none focus:ring focus:ring-red-100">
              この内容でcsvを作成する
            </button>
            <svg
              width="50"
              height="50"
              viewBox="0 0 500 500"
              class="csv-export my-1 text-red-300"
              fill="currentColor"
              >
              <defs>
                <linearGradient x1="50%" y1="92%" x2="50%" y2="7%" id="a">
                  <stop offset="0%" stop-color="currentColor" />
                  <stop stop-opacity={0.5} offset="100%" stop-color="#fff" />
                </linearGradient>
              </defs>
              <g fill="url(#a)">
                <g>
                  <path
                    d="M450.823,277.205h-70.777V14.921C380.046,6.683,373.362,0,365.125,0H134.792c-8.238,0-14.921,6.677-14.921,14.921v262.284
                    H49.094c-20.594,0-25.49,11.806-10.924,26.371l185.417,185.418c14.565,14.565,38.183,14.565,52.743,0l185.411-185.418
                    C476.313,289.011,471.424,277.205,450.823,277.205z"
                  />
                </g>
              </g>
            </svg>
            <div class="csv-export h-180px" ></div>
            <div class="csv-export absolute top-24 p-3 w-550px rounded-sm border-4 border-stone-500">
              <div class="flex flex-row">
                <div class="flex flex-col">
                  <label for="csv_format_select" class="">
                    CSV形式を選択
                  </label>
                  <select
                    name="csv_format_select"
                    id="csv_format_select"
                    class="mt-2 rounded-sm border border-gray-300"
                  >
                    <option value="ordinary">普通用</option>
                    <option value="ordinary_csv">普通用(CSV形式)</option>
                    <option value="address">住所用</option>
                    <option value="postpay">後払い.com</option>
                    <option value="NP_postpay">NP後払い用</option>
                    <option value="E_hiden">E飛伝2</option>
                    <option value="yamato_B2">ヤマト運輸B2</option>
                    <option value="jp_fly">日通べりカン便</option>
                    <option value="yuu_pack">ゆうパックプリントR</option>
                    <option value="kangaroo_magic2">カンガル-マジクⅡ</option>
                  </select>
                </div>
                <div class="ml-20 w-12 flex flex-col justify-end text-gray-400">
                  <svg width="32px" height="30px" viewBox="0 0 490 490">
                    <g>
                      <g>
                        <path
                          d="M15.541,490V0l458.917,245.009L15.541,490z"
                          fill="currentColor"
                        />
                      </g>
                    </g>
                  </svg>
                </div>
                <div class="flex flex-col">
                  <label for="csv_template_select" class="flex-nowrap">
                    出力するテンプレートを選択
                  </label>
                  <select
                    name="per-row-mode"
                    id="per-row-mode"
                    class="mt-2 w-auto rounded-sm border border-gray-200"
                  >
                    <option value="order">1注文1行表示</option>
                    <option value="product">1商品1行表示</option>
                  </select>
                </div>
              </div>
              <div class="flex flex-row items-center justify-center">
                <button class="export-btn mt-10 py-1 px-8 rounded-md border-2 border-yellow-400 bg-gradient-to-b from-yellow-200 to-amber-500 flex flex-row items-center shadow-sm hover:bg-gradient-to-b hover:from-white hover:to-amber-300 focus:outline-none focus:ring focus:ring-amber-100">
                  <svg width="32px" height="32px" viewBox="0 0 32 32">
                    <defs>
                      <linearGradient
                        id="b"
                        x1="4.494"
                        y1="-2092.086"
                        x2="13.832"
                        y2="-2075.914"
                        gradientTransform="translate(0 2100)"
                        gradientUnits="userSpaceOnUse"
                      >
                        <stop offset="0" stop-color="#18884f" />
                        <stop offset="0.5" stop-color="#117e43" />
                        <stop offset="1" stop-color="#0b6631" />
                      </linearGradient>
                    </defs>
                    <title>file_type_excel</title>
                    <path
                      d="M19.581,15.35,8.512,13.4V27.809A1.192,1.192,0,0,0,9.705,29h19.1A1.192,1.192,0,0,0,30,27.809h0V22.5Z"
                      fill="#185c37"
                    />
                    <path
                      d="M19.581,3H9.705A1.192,1.192,0,0,0,8.512,4.191h0V9.5L19.581,16l5.861,1.95L30,16V9.5Z"
                      fill="#21a366"
                    />
                    <path d="M8.512,9.5H19.581V16H8.512Z" fill="#107c41" />
                    <path
                      d="M16.434,8.2H8.512V24.45h7.922a1.2,1.2,0,0,0,1.194-1.191V9.391A1.2,1.2,0,0,0,16.434,8.2Z"
                      opacity="0.1"
                    />
                    <path
                      d="M15.783,8.85H8.512V25.1h7.271a1.2,1.2,0,0,0,1.194-1.191V10.041A1.2,1.2,0,0,0,15.783,8.85Z"
                      opacity="0.2"
                    />
                    <path
                      d="M15.783,8.85H8.512V23.8h7.271a1.2,1.2,0,0,0,1.194-1.191V10.041A1.2,1.2,0,0,0,15.783,8.85Z"
                      opacity="0.2"
                    />
                    <path
                      d="M15.132,8.85H8.512V23.8h6.62a1.2,1.2,0,0,0,1.194-1.191V10.041A1.2,1.2,0,0,0,15.132,8.85Z"
                      opacity="0.2"
                    />
                    <path
                      d="M3.194,8.85H15.132a1.193,1.193,0,0,1,1.194,1.191V21.959a1.193,1.193,0,0,1-1.194,1.191H3.194A1.192,1.192,0,0,1,2,21.959V10.041A1.192,1.192,0,0,1,3.194,8.85Z"
                      fill="url(#b)"
                    />
                    <path
                      d="M5.7,19.873l2.511-3.884-2.3-3.862H7.758L9.013,14.6c.116.234.2.408.238.524h.017c.082-.188.169-.369.26-.546l1.342-2.447h1.7l-2.359,3.84,2.419,3.905H10.821l-1.45-2.711A2.355,2.355,0,0,1,9.2,16.8H9.176a1.688,1.688,0,0,1-.168.351L7.515,19.873Z"
                      fill="#fff"
                    />
                    <path
                      d="M28.806,3H19.581V9.5H30V4.191A1.192,1.192,0,0,0,28.806,3Z"
                      fill="#33c481"
                    />
                    <path d="M19.581,16H30v6.5H19.581Z" fill="#107c41" />
                  </svg>
                  <span class="ml-2">CSVダウンロード</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-12 w-full">
        <div class="mb-2 w-full flex flex-row justify-between items-center">
          <div class="flex flex-row">
            <div class="">全<span class="order-total-number">0</span>件中<span class="order-from-index">0</span>~<span class="order-to-index">0</span>件を表示</div>
            <div class="ml-4">注文合計金額 <span class="order-total-money">0</span>円</div>
          </div>
          <div id="sortAndLimitSection" class="invisible flex flex-row items-center">
            <div class="flex items-center">
              <label for="display-limit">表示件数:</label>
              <select id="display-limit" name="display-limit" class="ml-2 w-24">
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
              </select>
            </div>
            <div class="ml-4 flex items-center">
              <label for="sort-columns">整列方法:</label>
              <select id="sort-columns" name="sort-columns" class="ml-2 w-300px">
                <option value="order_id_____DESC">注文日付が新しい順</option>
                <option value="order_id_____ASC">注文日付が古い順</option>
              </select>
            </div>
          </div>
        </div>
        <div class="mt-2 px-4 h-20 w-full rounded-sm border border-red-300 bg-amber-100 flex flex-row items-center justify-between">
          <div class="orders-count-container flex flex-row items-center">
            <div class="">現在<span class="orders-count">0</span>件を選択中</div>
            <button class="invisible ml-2 px-4 h-8 rounded-full border-2 border-gray-300 bg-stone-100 font-semibold shadow-sm">
              一括処理を選択
            </button>
            <div class="ml-16">
              <span>処理内容:</span>
              <span class="text-red-600">[選択なし]</span>
            </div>
          </div>
          <button class="invisible px-4 h-6 rounded-full border-2 border-gray-300 bg-gradient-to-b from-amber-200 to-yello-500 text-black font-bold shadow-sm">
            処理を実行
          </button>
        </div>
        <div id="output_preview" style="overflow: auto; width: 100%">
        </div>
        <div id="output_pagination" class="invisible mt-4 w-full flex flex-row justify-center items-center">
          <span class="firstPage w-12 h-8 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-center vertical-center text-center leading-8 cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">
            先頭
          </span>
          <span class="prevPage ml-1 w-12 h-8 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-center vertical-center text-center leading-8 cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">前</span>
          <input class="ml-1 w-12 h-8 rounded-sm border border-stone-400 text-center" data-count="1" type="text" name="offset" id="offsetPage" value="1" />
          <span class="nextPage ml-1 w-12 h-8 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-center vertical-center text-center leading-8 cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">次</span>
          <span class="lastPage ml-1 w-12 h-8 rounded-sm border border-stone-400 bg-gradient-to-b from-stone-100 to-gray-200 text-center vertical-center text-center leading-8 cursor-pointer hover:bg-gradient-to-b hover:from-white hover:to-sky-100 focus:outline-none focus:ring focus:ring-violet-100">最終</span>
        </div>
      </div>
      <iframe id='export_new_window_frame' width=0 height=0 style='display:none'></iframe>
    </div>
</div>