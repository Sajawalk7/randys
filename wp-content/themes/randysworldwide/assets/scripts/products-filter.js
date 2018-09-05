jQuery(function ($) {

  var URL = wpAjax.ajax_url;

  /*
  ** Product Filter System
  */
  var diffIDSelected =  urlParamChecker.getUrlParameter('diffid');


  function handleProductFilterChange(response) {

    var data = JSON.parse(response);
    // we have a successful response remove spinner
    $('.products-filter__spinner').hide();
    $('.products-filter__list').show();
    $('.products-filter__list').css('visibility', 'visible');
    $('.product-results').removeClass('product-results--loading');
    $('.total-pages').text(data.totalPages);

    // If we have more than 25 items add pagination button
    if( $('.total-pages').text() !== '1') {
      $('.load-more-products').show();
    } else {
      $('.load-more-products').hide();
    }

    // Set returned product results
    $('.product-results').html(data.productList);
    $('.archive-header__count span').html(data.productCount);

    // Clear state then add to our three main fields
    $('.products-filter__form-item').removeClass('unchanged');
    $('.Parent, .Category, .GearRatio').addClass('unchanged');

    // Add class to changed dropdown
    $('.'  + data.changedDropdown).addClass('unchanged');


    // Check what we are changing then remove flagged selections
    if( data.changedDropdown === 'Parent' ) {
      $('.Category, .GearRatio').removeClass('unchanged');
    } else if ( data.changedDropdown === 'Category' ) {
      $('.GearRatio' ).removeClass('unchanged');
    }

    $(".products-filter__list > *:not('.products-filter__form-item.unchanged')").remove();

    // If we have filters, populate these
    if (data.filters && data.filters.length >= 1) {
      // Setup Classes for UI
      // Go through each filter..
      for (var filterIndex = data.filters.length - 1; filterIndex >= 0; filterIndex--) {
        // Set variables just for ease
        var filterData  = data.filters[filterIndex].data;
        var filterLabel = data.filters[filterIndex].label;
        var filterValue = data.filters[filterIndex].value;

        // Exclude parent from our input considering it's always present in template file
        if( filterValue !== 'Parent' && filterValue !== data.changedDropdown && !$('.' + filterValue).hasClass("unchanged") ) {
          var wrapper = $('<div class="products-filter__form-item ' + filterValue + '"><label class="products-filter__label">' + filterLabel + '</label></div>');
          // Create the select box, with the class of diffwizard-results__select and a key
          var dropdown = $("<select class='products-filter__select dynamic' data-label='" + filterLabel + "' id='" + filterValue + "' name='" + filterValue + "'></select>");

          // Append the initial select value
          $(dropdown).append("<option value=''>ALL</option>");

          // Populate the rest of the data
          for (var indx = filterData.length - 1; indx >= 0; indx--) {
            if( filterData.length === 1) {
              $(dropdown).append("<option value='" + filterData[indx] + "' selected>" + filterData[indx] + "</option>");
            } else {
              $(dropdown).append("<option value='" + filterData[indx] + "'>" + filterData[indx] + "</option>");
            }
          }

          $(wrapper).append(dropdown);
          // Append the select box to the list of possible filters
          $('.products-filter__list').append(wrapper);
        }
      }
      // Look through filter and if it's being updated wrap our select styling selector around it
      $('.products-filter__form-item').each(function() {
        if( !$(this).hasClass('unchanged') ) {
          var filterSelector = $(this).attr('class').split(' ')[1];
          $('.' + filterSelector + " .products-filter__select").wrap('<div class="products-filter__select-wrap select"></div>');
        }
      });
    }
    // Prepear product IDs for ability to sort
    if(data.productIDS) {
      var queryIDS = [];
      for (var idIndx = 0; idIndx < data.productIDS.length; idIndx++) {
        queryIDS.push(data.productIDS[idIndx]);
      }
      $('#current-ids').attr('data-query-ids', queryIDS);
    }


    // Once products have loaded run supporting functions
    productsSlider.productsSlider();
    ellipsis.enableEllipsis();
  }

  function productQueryWithFilters(changedDropdown) {

    // While loading show spinner
    $('.products-filter__spinner').show();
    $('.products-filter__list').hide();
    $('.product-results').addClass('product-results--loading');
    $('.product-results').html('<div class="center-align"><div class="spinner"></div></div>');
    $('.load-more-products').hide();

    // reference list for the four main fields
    var fields = [
      'year',
      'make',
      'model',
      'drivetype'
    ];

    // Set up an array to populate with query values
    var queryValues = [];

    // Go through each of the fields and put together a query
    for (var indx = 0; indx < fields.length; indx++) {
      queryValues.push(fields[indx] + "=" + encodeURIComponent($('#' + fields[indx]).val()));
    }

    // Query up the extra filters that are currently in place
    var currentFilters = $("#current-filters .products-filter");

    // Loop through those filters and get thier
    for (var currentFilterIndex = currentFilters.length - 1; currentFilterIndex >= 0; currentFilterIndex--) {
      // Get the filter's name and value
      var filterName = $(currentFilters[currentFilterIndex]).data('filter');
      var filterValue = $(currentFilters[currentFilterIndex]).data('value');
      // Append this filter to the query
      queryValues.push(filterName + "=" + encodeURIComponent(filterValue));
    }

    // Compile together the query values
    var compiledUrl = URL + "?" + (queryValues.join('&'));

    // Get the info back
    $.ajax({
      url: compiledUrl,
      type: 'get',
      data: {
        action: 'get_product_filter',
        diffIDSelected: diffIDSelected,
        changedDropdown: changedDropdown,
        parentValue: $('#Parent').val(),
        categoryValue: $('#Category').val(),
        uJointTypeValue: $('#UJointType').val(),
        source: 'diff-wizard',
        selectedSort: $('#orderby').val(),
        paged: $('.page-numbers.current').text(),
        nonce: $('#product_filter_nonce').val()
      },
      success: handleProductFilterChange
    });
  }

  // Handles when a filter is changed
  function productHandleFilterChange() {  // jshint ignore:line

    var changedDropdown = $(this).attr('id');
    if ($(this).val() === '') {
      $(".current-filter--" + $(this).attr('id')).remove();
    } else if ($(this).attr('id') === 'Parent' || $(this).attr('id') === 'Category' ||  $(this).attr('id') === 'GearRatio' ) {
      $("#current-filters .products-filter").remove();
      $('#current-filters').append("<p class='products-filter current-filter--" + $(this).attr('id') + "' data-filter='" + $(this).attr('id') + "' data-value='" + $(this).val() + "'></p>");
    } else {
      $(".products-filter .current-filter--" + changedDropdown).remove();
      $("#current-filters").append("<p class='products-filter current-filter--" + $(this).attr('id') + "' data-filter='" + $(this).attr('id') + "' data-value='" + $(this).val() + "'></p>");
    }

    // Now query using the filters we added
    productQueryWithFilters(changedDropdown);
  }

  $(document).ready(function() {
    // on load get the active filters
    if( diffIDSelected ) {
      productQueryWithFilters();
    }

  });

  $(document).on('change', '.products-filter__select', productHandleFilterChange);

  // Clear all fields on click of reset button
  $('.products-filter__reset').on('click', function() {
    $('.products-filter__form-item select').val('');
    $('#current-filters').empty();
    productQueryWithFilters();
  });

});
