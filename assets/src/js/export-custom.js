import {
  CSV_FORMAT_GENERAL,
  CSV_FORMAT_EHIDEN,
  CSV_FORMAT_DATATABLE,
  CSV_FORMAT_POSTPAY,
  CSV_FORMAT_NITTSU,
  CSV_FORMAT_KANGAROO,
  CSV_FORMAT_YAMATO,
  CSV_FORMAT_ADDRESS,
} from "./csv_formats.js";

/**
 * * constants and global variables
 */

// * csv format list
const csvFormatList = {
  datatable: CSV_FORMAT_DATATABLE,
  ordinary: CSV_FORMAT_GENERAL,
  ordinary_csv: CSV_FORMAT_GENERAL,
  address: CSV_FORMAT_ADDRESS,
  postpay: CSV_FORMAT_POSTPAY,
  NP_postpay: CSV_FORMAT_POSTPAY,
  E_hiden: CSV_FORMAT_EHIDEN,
  yamato_B2: CSV_FORMAT_YAMATO,
  jp_fly: CSV_FORMAT_NITTSU,
  yuu_pack: CSV_FORMAT_EHIDEN,
  kangaroo_magic2: CSV_FORMAT_KANGAROO,
};

/**
 * * order id array
 * We are saving the current available order ids list to global variable
 * Pagination uses this array
 */
let orderIDArray = [];

/**
 * * old pagination input value
 */
let oldValue = 1;

/**
 * * before export, check the flag showing ever searched
 */
let searchedPerRowType = false;
// -- end of constants and global variables section --

jQuery(document).ready(function () {
  /**
   * * connect filter attribs checkboxes with filter condition sections
   * checked means visible, unchecked means not
   */
  jQuery("input.filter_check").click(function (_) {
    const checkName = jQuery(this).attr("name").slice(6);
    const sectionName = "#" + checkName + "_section";
    jQuery(sectionName).toggle(200, "swing");
  });
  // -- end --

  /**
   * * connect buttons with visibilities of filter condition sections
   */
  jQuery("button.toggle-filter-attribs").click(function (_) {
    jQuery(".filter-attribs").toggle(300, "swing");
  });
  jQuery("button.csv-export-btn").click(function (_) {
    jQuery(".csv-export").toggle(300, "swing");
  });
  // -- end --

  /**
   * * order_date btns function
   */
  const inputFromDate = jQuery("#from_date");
  const inputToDate = jQuery("#to_date");

  jQuery("#order_date_section .clear-date").click(function (_) {
    inputFromDate.val("");
    inputToDate.val("");
    _.preventDefault();
  });

  jQuery("#order_date_section .date-today").click(function (_) {
    const todayStart = new Date();
    todayStart.setHours(0);
    todayStart.setMinutes(0);
    const todayEnd = new Date();
    todayEnd.setHours(23);
    todayEnd.setMinutes(59);

    inputFromDate.val(date2Format(todayStart));
    inputToDate.val(date2Format(todayEnd));
  });

  jQuery("#order_date_section .date-this-month").click(function (_) {
    const date = new Date();

    const y = date.getFullYear();
    const m = date.getMonth();
    const firstDay = new Date(y, m, 1);
    const lastDay = new Date(y, m + 1, 0);
    lastDay.setHours(23);
    lastDay.setMinutes(59);

    inputFromDate.val(date2Format(firstDay));
    inputToDate.val(date2Format(lastDay));
  });

  jQuery("#order_date_section .date-yesterday").click(function (_) {
    const yesterdayStart = new Date();
    const yesterday = yesterdayStart.getDate() - 1;

    const yesterdayEnd = new Date();
    yesterdayStart.setDate(yesterday);
    yesterdayEnd.setDate(yesterday);
    setFromToHHMM(yesterdayStart, yesterdayEnd);

    inputFromDate.val(date2Format(yesterdayStart));
    inputToDate.val(date2Format(yesterdayEnd));
  });

  jQuery("#order_date_section .date-since-yesterday").click(function (_) {
    setPostDateSince(1);
  });

  jQuery("#order_date_section .date-since-3-days-bf").click(function (_) {
    setPostDateSince(3);
  });

  jQuery("#order_date_section .date-for-a-week").click(function (_) {
    setPostDateSince(7);
  });

  jQuery("#order_date_section .date-for-a-month").click(function (_) {
    let firstDay = new Date();
    firstDay.setMonth(firstDay.getMonth() - 1);
    const endDay = new Date();
    setFromToHHMM(firstDay, endDay);

    inputFromDate.val(date2Format(firstDay));
    inputToDate.val(date2Format(endDay));
  });

  jQuery("#order_date_section .date-last-month").click(function (_) {
    let firstDay = new Date(),
      lastDay = new Date();

    lastDay.setDate(0);
    firstDay.setDate(1);
    setFromToHHMM(firstDay, lastDay);

    firstDay.setMonth(firstDay.getMonth() - 1);
    inputFromDate.val(date2Format(firstDay));
    inputToDate.val(date2Format(lastDay));
  });

  jQuery("#order_date_section .date-3-months").click(function (_) {
    let firstDay = new Date();
    firstDay.setMonth(firstDay.getMonth() - 3);
    const lastDay = new Date();
    setFromToHHMM(firstDay, lastDay);
    inputFromDate.val(date2Format(firstDay));
    inputToDate.val(date2Format(lastDay));
  });

  jQuery("#order_date_section .date-last-year").click(function (_) {
    let firstDay = new Date(),
      lastDay = new Date();
    firstDay.setFullYear(firstDay.getFullYear() - 1);
    firstDay.setMonth(0);
    firstDay.setDate(1);
    lastDay.setMonth(0);
    lastDay.setDate(0);
    setFromToHHMM(firstDay, lastDay);
    inputFromDate.val(date2Format(firstDay));
    inputToDate.val(date2Format(lastDay));
  });
  // -- end --

  /**
   * * search function
   */
  jQuery("button.search-btn").click(function (_) {
    searchOrders();
  });

  // when conditions change, should refetch order id list
  jQuery("#sort-columns").on("change", function () {
    jQuery("#offsetPage").val(1);
    searchOrders();
  });

  jQuery("#display-limit").on("change", function () {
    jQuery("#offsetPage").val(1);
    fetchOrdersInfo();
    setOrderRanges();
  });

  jQuery("#offsetPage").keypress(function (e) {
    if (e.keyCode == 13) {
      fetchOrdersInfo();
    }
  });
  // -- end --

  /**
   * * export function
   */
  jQuery("button.export-btn").click(function (_) {
    const perRowMode = jQuery("#per-row-mode").val();

    if (!searchedPerRowType || searchedPerRowType !== perRowMode) {
      searchOrders(false, exportOrders);
    } else {
      exportOrders();
    }
  });

  function exportOrders() {
    const data = new Array();
    const currentCSVFormat = csvFormatList[jQuery("#csv_format_select").val()];
    const perRowMode = jQuery("#per-row-mode").val();

    data.push({
      name: "json",
      value: JSON.stringify(currentCSVFormat),
    });
    data.push({
      name: "per_row_mode",
      value: perRowMode,
    });
    data.push({
      name: "action",
      value: "csv_export",
    });
    data.push({ name: "method", value: "csv_export_start" });
    // ! should change this order list to filtered one
    data.push({
      // can be order_id, product_id object array
      name: "order_id_list",
      value: JSON.stringify(orderIDArray),
    });

    jQuery.ajax({
      type: "post",
      data: data,
      cache: false,
      url: ajaxurl,
      error: function (xhr, status, error) {
        csvShowError(xhr.responseText.replace(/<\/?[^>]+(>|$)/g, ""));
      },
      success: function (response) {
        const result = JSON.parse(response);
        if (!result || typeof result["total"] == "undefined") {
          csvShowError(response);
          return;
        }
        window.count = parseInt(result["total"]);
        window.file_id = result["file_id"];
        window.max_line_items = parseInt(result["max_line_items"]);
        window.max_coupons = parseInt(result["max_coupons"]);

        if (window.count > 0) {
          exportAll(0, 0, "csv_export_part");
        } else {
          alert("no_results");
        }
      },
    });
  }

  function exportAll(start, percent, method) {
    if (window.cancelling) {
      return;
    }

    // woe_export_progress(parseInt(percent, 10), jQuery("#progressBar"));
    const data = new Array();
    const currentCSVFormat = csvFormatList[jQuery("#csv_format_select").val()];
    const perRowMode = jQuery("#per-row-mode").val();
    // const currentOrderIDArray = filterOrderArray(orderIDArray);

    if (percent < 100) {
      data.push({
        name: "json",
        value: JSON.stringify(currentCSVFormat),
      });
      data.push({
        name: "action",
        value: "csv_export",
      });
      data.push({
        name: "per_row_mode",
        value: perRowMode,
      });
      data.push({ name: "method", value: method });
      data.push({ name: "start", value: start });
      data.push({ name: "file_id", value: window.file_id });
      // ! should change this orderIDArray to filtered one
      data.push({
        name: "order_id_list",
        value: JSON.stringify(orderIDArray),
      });

      if (method == "csv_export_part") {
        data.push({ name: "max_line_items", value: window.max_line_items });
        data.push({ name: "max_coupons", value: window.max_coupons });
      }

      jQuery.ajax({
        type: "post",
        data: data,
        cache: false,
        url: ajaxurl,
        error: function (xhr, status, error) {
          csvShowError(xhr.responseText);
          // woe_export_progress(100, jQuery("#progressBar"));
        },
        success: function (response) {
          const result = JSON.parse(response);

          if (!result) {
            csvShowError(result);
          } else if (typeof result.error !== "undefined") {
            csvShowError(result.error);
          } else {
            exportAll(
              result.start,
              (result.start / window.count) * 100,
              method
            );
          }
        },
      });
    } else {
      data.push({ name: "action", value: "csv_export" });
      data.push({ name: "method", value: "csv_export_finish" });
      data.push({ name: "file_id", value: window.file_id });
      data.push({
        name: "order_id_list",
        value: JSON.stringify(orderIDArray),
      });
      data.push({
        name: "json",
        value: JSON.stringify(currentCSVFormat),
      });

      jQuery.ajax({
        type: "post",
        data: data,
        cache: false,
        url: ajaxurl,
        error: function (xhr, status, error) {
          console.log(xhr.status);
        },
        success: function (response) {
          var download_format = "CSV";

          jQuery("#export_new_window_frame").attr(
            "src",
            ajaxurl +
              (ajaxurl.indexOf("?") === -1 ? "?" : "&") +
              "action=csv_export&method=csv_export_download&format=" +
              download_format +
              "&file_id=" +
              window.file_id
          );
        },
      });
    }
  }

  /**
   * * pagination section
   */
  jQuery("#offsetPage").on("input", function (e) {
    const censoredValue = parseInt(e.target.value);
    const totalOrderCount = parseInt(jQuery("span.order-total-number").text());
    const pageLimit = jQuery("#display-limit").val();
    const pageCount = parseInt((totalOrderCount - 1) / pageLimit) + 1;

    if (isNaN(censoredValue) || censoredValue > pageCount) {
      jQuery(this).val(oldValue);
    }
    {
      jQuery(this).val(censoredValue);
      oldValue = censoredValue;
    }
  });

  jQuery("#output_pagination .firstPage").click(function (_) {
    let currentPage = parseInt(jQuery("#offsetPage").val());
    const totalOrderCount = parseInt(jQuery("span.order-total-number").text());

    if (currentPage === 1 || totalOrderCount === 0) {
    } else {
      currentPage = 1;
      jQuery("#offsetPage").val(currentPage);
      fetchOrdersInfo();
      setOrderRanges();
    }
  });

  jQuery("#output_pagination .prevPage").click(function (_) {
    let currentPage = parseInt(jQuery("#offsetPage").val());
    const totalOrderCount = parseInt(jQuery("span.order-total-number").text());

    if (currentPage === 1 || totalOrderCount === 0) {
    } else {
      currentPage -= 1;
      jQuery("#offsetPage").val(currentPage);
      fetchOrdersInfo();
      setOrderRanges();
    }
  });

  jQuery("#output_pagination .nextPage").click(function (_) {
    let currentPage = parseInt(jQuery("#offsetPage").val());
    const totalOrderCount = parseInt(jQuery("span.order-total-number").text());
    const pageLimit = jQuery("#display-limit").val();
    const pageCount = parseInt((totalOrderCount - 1) / pageLimit) + 1;

    if (pageCount === 1 || currentPage === pageCount) {
    } else {
      currentPage += 1;
      jQuery("#offsetPage").val(currentPage);
      fetchOrdersInfo();
      setOrderRanges();
    }
  });

  jQuery("#output_pagination .lastPage").click(function (_) {
    let currentPage = parseInt(jQuery("#offsetPage").val());
    const totalOrderCount = parseInt(jQuery("span.order-total-number").text());
    const pageLimit = jQuery("#display-limit").val();
    const pageCount = parseInt((totalOrderCount - 1) / pageLimit) + 1;

    if (currentPage === pageCount || totalOrderCount === 0) {
    } else {
      currentPage = pageCount;
      jQuery("#offsetPage").val(currentPage);
      fetchOrdersInfo();
      setOrderRanges();
    }
  });

  // -- end --

  /**
   * * * utility functions section * *
   */

  // functions for order_date btns functions
  function setPostDateSince(day) {
    let today = new Date(),
      theDay = new Date();
    theDay.setDate(today.getDate() - day);

    setFromToHHMM(theDay, today);

    inputFromDate.val(date2Format(theDay));
    inputToDate.val(date2Format(today));
  }

  function setFromToHHMM(startDay, endDay) {
    startDay.setHours(0);
    startDay.setMinutes(0);
    endDay.setHours(23);
    endDay.setMinutes(59);
  }

  // * search function
  // this function is responsible for fetching order id list of current conditions ( without pagination info )
  function searchOrders(isDrawTable = true, callback = () => {}) {
    const inputSettings = jQuery("#filter_conditions_form input")
      .filter(":visible")
      .filter("input[type!=button]")
      .filter("input[type!=submit]");
    const selectSettings = jQuery("#filter_conditions_form select").filter(
      ":visible"
    );
    const formSettings = jQuery.merge(inputSettings, selectSettings);
    const settings = formSettings.serializeJSON();
    const [sort, sortDirection] = jQuery("#sort-columns").val().split("_____");
    settings["sort"] = sort;
    settings["sort_direction"] = sortDirection;
    const perRowMode = jQuery("#per-row-mode").val();
    searchedPerRowType = perRowMode;

    var jsonData = JSON.stringify(settings);
    var data =
      "json=" +
      jsonData +
      "&action=order_search&method=search&per_row_mode=" +
      perRowMode;

    jQuery
      .post(
        ajaxurl,
        data,
        function (response) {
          if (response.length > 0) {
            if (perRowMode == "order") {
              orderIDArray = response;
            } else if (perRowMode == "product") {
              orderIDArray = response.map((_) => ({
                order_id: _.order_id,
                item_id: _.item_id,
              }));
            }

            if (isDrawTable) {
              // set initial pagination infos
              jQuery("#offsetPage").val(1);
              const orderCount = orderIDArray.length;
              jQuery("span.order-total-number").text(orderCount);
              jQuery("#display-limit").val(10);
              setOrderRanges();
              // --
              fetchOrdersInfo();
            } else {
              callback();
            }
          } else {
            orderIDArray = [];
            jQuery("span.order-total-number").text(0);
            jQuery("#display-limit").val(10);
            setOrderRanges();
          }
        },
        "json"
      )
      .fail(function (xhr, _textStatus, errorThrown) {
        console.log("error occured while fetching order id list");
      });
  }

  // * fetch order infos for datatable using order id list
  function fetchOrdersInfo() {
    const currentCSVFormat = csvFormatList.datatable;
    const perRowMode = jQuery("#per-row-mode").val();
    const data = new Array();
    const currentOrderIDArray = filterOrderArray(orderIDArray);
    if (currentOrderIDArray.length === 0) {
      return;
    }

    data.push({
      name: "json",
      value: JSON.stringify(currentCSVFormat),
    });
    data.push({
      name: "per_row_mode",
      value: perRowMode,
    });
    data.push({
      name: "action",
      value: "csv_export",
    });
    data.push({
      name: "method",
      value: "preview_csv",
    });
    data.push({
      // can be order_id, product_id object array
      name: "order_id_list",
      value: JSON.stringify(currentOrderIDArray),
    });

    jQuery.ajax({
      type: "post",
      data: data,
      cache: false,
      url: ajaxurl,
      error: function (xhr, status, error) {
        drawTable(xhr.responseText);
      },
      success: function (response) {
        drawTable(response);
      },
    });

    // * draw orders datatable
    const drawTable = (response) => {
      var html;
      const result = JSON.parse(response);
      if (!result.html) {
        html = response;
        jQuery("#sortAndLimitSection").addClass("invisible");
        jQuery("#output_pagination").addClass("invisible");
      } else {
        html = result.html;
        jQuery("#sortAndLimitSection").removeClass("invisible");
        jQuery("#output_pagination").removeClass("invisible");
      }
      var id = "output_preview";
      jQuery("#" + id).html(html);
      jQuery("#" + id).show();
      window.scrollTo({
        top: document.body.scrollHeight,
        left: 0,
        behavior: "smooth",
      });
    };
  }

  // * filter order array using pagination
  function filterOrderArray(orderIdsList) {
    if (orderIdsList.length === 0) {
      return [];
    }

    const pageLimit = parseInt(jQuery("#display-limit").val());
    const currentPage = parseInt(jQuery("#offsetPage").val());
    const offset = pageLimit * (currentPage - 1);
    const subOrderIds = orderIdsList.filter(
      (_, index) => index >= offset && index < offset + pageLimit
    );

    return subOrderIds;
  }

  // * set orders range for datatable
  function setOrderRanges() {
    const orderCount = parseInt(jQuery("span.order-total-number").text());
    if (orderCount === 0) {
      jQuery("span.order-from-index").text(0);
      jQuery("span.order-to-index").text(0);
      return;
    }
    const pageLimit = parseInt(jQuery("#display-limit").val());
    const currentPage = parseInt(jQuery("#offsetPage").val());
    const currentPageStart = pageLimit * (currentPage - 1) + 1;
    let currentPageEnd = pageLimit * currentPage;
    currentPageEnd = orderCount < currentPageEnd ? orderCount : currentPageEnd;
    jQuery("span.order-from-index").text(currentPageStart);
    jQuery("span.order-to-index").text(currentPageEnd);
  }
});

/**
 * utility functions (without jQuery)
 */
function date2Format(date) {
  let m = "" + (date.getMonth() + 1);
  let d = "" + date.getDate();
  let y = date.getFullYear();
  let hh = date.getHours();
  let mm = date.getMinutes();

  if (m.length < 2) m = "0" + m;
  if (d.length < 2) d = "0" + d;
  if (hh < 10) {
    hh = "0" + hh;
  }
  if (mm < 10) {
    mm = "0" + mm;
  }

  return [y, m, d].join("-") + "T" + hh + ":" + mm;
}

function csvShowError(text) {
  if (!text) {
    text =
      "Please, open section 'Misc Settings' and \n mark checkbox 'Enable debug output' \n to see exact error message";
  }
  alert(text);
}
