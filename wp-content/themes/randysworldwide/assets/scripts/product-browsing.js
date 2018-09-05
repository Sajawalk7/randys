jQuery(function ($) {
  var URL = browsing.ajax_url;

  // On load we want to see what fields are populated
  // remove disable and add visual que for populated
  function checkProductInputVals() {
    var fields = [
      'cat-make',
      'cat-model',
      'cat-drivetype',
      'cat-category'
    ];

    for (var indx = 0; indx < fields.length; indx++) {
      if( $('#' + fields[indx]).val() ) {
        $('#' + fields[indx]).prop('disabled', false);
        $('#' + fields[indx]).parent('.products-filter__select').removeClass('select--disabled');
      }
    }
  }

  var catYear = 'cat-year=' + urlParamChecker.getUrlParameter('cat-year');
  var catParam = 'cat-category=' + urlParamChecker.getUrlParameter('cat-category');
  var makeParam = 'cat-make=' + urlParamChecker.getUrlParameter('cat-make');
  var modelParam = 'cat-model=' + urlParamChecker.getUrlParameter('cat-model');
  var driveTypeParam = 'cat-drivetype=' + urlParamChecker.getUrlParameter('cat-drivetype');
  var parentID = 'parent-id=' + urlParamChecker.getUrlParameter('parent-id');
  var diffIDParam = urlParamChecker.getUrlParameter('diffid');
  var diffParams;
  if( urlParamChecker.getUrlParameter('parent-id') ) {
    diffParams = '?' + catYear + '&' + makeParam + '&' + modelParam + '&' + driveTypeParam + '&' + catParam + '&' + '&' + parentID;
  } else {
    diffParams = '?' + catYear + '&' + makeParam + '&' + modelParam + '&' + driveTypeParam + '&' + catParam;
  }

  // Success return function for jquery AJAX call
  function handleProductData(rawData) {
    // Parse and save the data
    var data = JSON.parse(rawData);

    // Remove loading state
    loadingState.isLoading(false);

    // Add active state
    $('#cat-' + data.dropdown).addClass('active');

     // Before we do logic, let's check if we are on the results page and have one result
    if(data.diffdata && data.diffdata.length === 1 && urlParamChecker.getUrlParameter('cat-make') && !diffIDParam) {
      // If request comes back with one diff, append hidden field and add the diff id and submit
      $('.products-filter__form').append('<input id="diffid" type="hidden" name="diffid"  value="" />');
      $('#diffid').val(data.diffdata[0].diffid);
      $('.products-filter__form').trigger('submit');
    } else if(data.diffdata && data.diffdata.length === 1) {
      // If we aren't on the diff-wizard results page but have the diff id
      // Setup the hidden input and wait for user to submit
      $('.products-filter__form').append('<input id="diffid" type="hidden" name="diffid"  value="" />');
      $('#diffid').val(data.diffdata[0].diffid);
    }

    // Get the index of the dropdown we are trying to find
    var indx = this.dropdownIndex;
    var fields = this.fields;

    // Make sure the call was successful
    if(data.success !== true) {
      alert("There was an error with your request, please try again.");
      return;
    }

    // If one of the major 5 dropdowns was returned, populate it.
    if (data.dropdown) {
      // Handle if there was only one value for the next field and it was given back
      while ('cat-' + data.dropdown !== fields[indx]) {
        // Empty out the field
        $('#' + fields[indx] + ' option[value!=""]').remove();
        // Set the 'select'
        $('#' + fields[indx]).append("<option value='" + data.input[fields[indx]] + "'>" + data.input[fields[indx]] + "</option>");
        $('#' + fields[indx]).val(data.input[fields[indx]]);
        // Enable this dropdown
        $('#' + fields[indx]).prop('disabled', false);
        $('#' + fields[indx]).parent('.products-filter__select').removeClass('select--disabled');
        // Increment indx to go to the next field
        indx++;
      }

      // Empty the found field
      $('#cat-' + data.dropdown + ' option[value!=""]').remove();

      for (indx = data.dropdowndata.length - 1; indx >= 0; indx--) {
        $('#cat-' + data.dropdown).append("<option value='" + data.dropdowndata[indx] + "'>" + data.dropdowndata[indx] + "</option>");
      }
      // If there was only 1 in this category, set it as selected.
      if (data.dropdowndata.length === 1) {
        $('#cat-' + data.dropdown).val(data.dropdowndata[0]);
      } else if(data.dropdowndata.length === 0) {
        $('.products-filter-dropdown').prop('disabled', false);
        $('.products-filter__select').removeClass('select--disabled');
        $('.products-filter__submit').prop('disabled', false);
      }
      // Enable this dropdown
      $('#cat-' + data.dropdown).prop('disabled', false);
      $('#cat-' + data.dropdown).parent('.products-filter__select').removeClass('select--disabled');
    }

    // Once we have all 5 major inputs enable form submit button
    if (data.diffdata) {
      $('.products-filter-dropdown').removeClass('active');
      $('.products-filter__submit').prop('disabled', false);
    }

    // Clear the extra dropdowns before populating them again
    //$(".diffwizard-results").removeClass('diffwizard-results--multiple-diffs');
    $("#extra-filters").empty();
    $("#differential-list").empty();

     // If we have filters, populate these
    if (data.filters && data.filters.length >= 1) {
      // Setup Classes for UI
      $("#extra-filters").show();
      $('.diffwizard-results').addClass('diffwizard-results--multiple-diffs');
      $("#extra-filters").append('<h3 class="diffwizard-results__title">Filter Your results</h3>');
      // Go through each filter..
      for (var filterIndex = data.filters.length - 1; filterIndex >= 0; filterIndex--) {
        // Set variables just for ease
        var filterData  = data.filters[filterIndex].data;
        var filterLabel = data.filters[filterIndex].label;
        var filterValue = data.filters[filterIndex].value;
        // Create the select box, with the class of diffwizard-results__select and a key
        var dropdown = $("<select class='diffwizard-results__select' data-label='" + filterLabel + "' id='" + filterValue + "'></select>");
        // Append the initial select value
        $(dropdown).append("<option value=''>ALL</option>");
        // Populate the rest of the data
        for (indx = filterData.length - 1; indx >= 0; indx--) {
          $(dropdown).append("<option value='" + filterData[indx] + "'>" + filterData[indx] + "</option>");
        }

        // Append the select box to the list of possible filters
        $("#extra-filters").append("<div class='diffwizard-results__label'>" + filterLabel + "</div>");
        $("#extra-filters").append(dropdown);
      }
      $(".diffwizard-results__select").wrap("<div class='diffwizard-results__select-wrap select'></div>");
      // Apply an event handler on the newly created dropdowns.
      $(".diffwizard-results__select").on('change', handleProductFilterChange);
    } else {
      $('.extra-filters-results').remove();
    }


    // If we have differentials, set the differentials here
    if (data.diffdata) {
      var currentPath = window.location.pathname;
      // Go through each of the differentials
      $("#differential-list").append('<h3 class="diffwizard-results__title">Choose Your Differential</h3>');

      for (indx = data.diffdata.length - 1; indx >= 0; indx--) {
        imageChecker.checkDiffImage("/wp-content/uploads/differential-images/" + data.diffdata[indx].FullImage, 'differential-item__image-' + indx);
        var diffDiv = $("<div class='differential-item m-b-3 row' id='" + data.diffdata[indx].diffid + "'></div>");
        diffDiv.append("<div class='col-sm-6'><img src='' class='differential-item__image differential-item__image-" + indx + " img-fluid m-b-2 mx-auto' /></div>");
        diffDiv.append("<div class='col-sm-6'><p class='differential-item__diffname'>" + data.diffdata[indx].diffname + "</p><p class='diffwizard__diffdescription'>" + data.diffdata[indx].diffdescription + "</p><a href='" + currentPath +"/" + diffParams + "&diffid=" + data.diffdata[indx].diffid + "' class='button button--slim differential-item__diff-select'>Select Differential</a></div>");

        // Finally, append this data to the output div
        $("#differential-list").append(diffDiv);
      }
    }
  }

  // Queries using the filters. Not used by primary dropdowns because the logic is different.
  function queryProductsWithFilters() {

    // reference list for the four main fields
    var fields = [
      'cat-year',
      'cat-make',
      'cat-model',
      'cat-drivetype',
      'cat-category'
    ];

    // Set up an array to populate with query values
    var queryValues = [];

    // Go through each of the fields and put together a query
    for (var indx = 0; indx < fields.length; indx++) {
      queryValues.push(fields[indx] + "=" + encodeURIComponent($('#' + fields[indx]).val()));
    }

    // Query up the extra filters that are currently in place
    var currentFilters = $(".diffwizard--current-filter");
    // Loop through those filters and get thier
    for (var currentFilterIndex = currentFilters.length - 1; currentFilterIndex >= 0; currentFilterIndex--) {
      // Get the filter's name and value
      var filterName = $(currentFilters[currentFilterIndex]).data('filter');
      var filterValue = $(currentFilters[currentFilterIndex]).data('value');
      // Append this filter to the query
      queryValues.push(filterName + "=" + filterValue);
    }

    // Compile together the query values
    var compiledUrl = URL + "?" + (queryValues.join('&'));

    // Include Category Parent to query
    compiledUrl += '&parent-id=' + $('#parent-id').val();

    // Get the info back from the wizard
    $.ajax({
      url: compiledUrl,
      type: 'get',
      data: {
        action: 'product_browsing_ajax_request',
        source: 'product-browsing',
        nonce: browsing.ajax_nonce
      },
      dropdownIndex: 5, // Index of the dropdown we are on
      fields: fields,   // A list of the dropdown fields
      success: handleProductData
    });
  }

  // Handles when a filter is changed
  function handleProductFilterChange() {  // jshint ignore:line
    // If the selected filter was changed to '', do nothing
    if ($(this).val() === '') {
      return;
    }

    $('#extra-filters').hide();

    // Add this filter to the list of filters
    $("#current-filters").show().append("<p class='diffwizard--current-filter current-filter--" + $(this).attr('id') + "' data-filter='" + $(this).attr('id') + "' data-value='" + $(this).val() + "'>" + $(this).data('label') + ": " + $(this).val() + "<br /></p>");

    // Set this new filter so that it will be removed when the 'X' is clicked.
    $(".diffwizard--current-filter--remove").click(function(){
      $("#current-filters").remove();queryProductsWithFilters();
      $("#extra-filters").show();
      $("#current-filters").empty().hide();
    });

    // Now query using the filters we added
    queryProductsWithFilters();
  }

  // Handles when a primary dropdown has changed
  function handleProductDropdownChange() {
    // reference list for the four main fields
    var fields = [
      'cat-year',
      'cat-make',
      'cat-model',
      'cat-drivetype',
      'cat-category'
    ];

    // First, clear out the extra filters and the diff list
    $("#extra-filters").empty();
    $("#differential-list").empty();
    // Also clear out any current filters
    $("#current-filters").empty();

    var indx = '';
    if ($('#cat-year').val() === '') {
      // We shouldn't make a query here, just clear the others
      for (indx = 1; indx < fields.length; indx++) {
        $('#' + fields[indx]).val('');
        $('#' + fields[indx]).prop('disabled', true);
      }
    }

    // Variable to save query values
    var queryValues = [];

    // The next index we want to get, will be figured out in the next for loop
    var nextIndex = 0;
    // Go through each of the fields and put together a query
    for (indx = 0; indx < fields.length; indx++) {
      // Make sure this fields has a value
      if ($('#' + fields[indx]).val()) {
        queryValues.push(fields[indx] + "=" + encodeURIComponent($('#' + fields[indx]).val()));
      } else {
        // Since this index is empty, this is the one we want to get
        nextIndex = indx;
        break;
      }

      // If this is the dropdown that we selected, finish it here
      if ($(this).attr('id') === fields[indx]) {
        // The next dropdown is the one we want to figure out.
        nextIndex = indx + 1;
        if($('.products-filter__pre-populated #' + fields[nextIndex]).val()) {
          queryValues.push(fields[nextIndex] + "=" + encodeURIComponent($('#' + fields[nextIndex]).val()));
          nextIndex++;
        }
        break;
      }
    }

    // Activate Spinner for next dropdown
    loadingState.isLoading(true, $('.products-filter-dropdown'), $('#' + fields[nextIndex]));

    // Compile together the query values
    var compiledUrl = URL + "?" + (queryValues.join('&'));

    // Include Category Parent to query
    compiledUrl += '&parent-id=' + $('#parent-id').val();

    // Disable and set to input name along all following dropdowns
    for (var disableIndex = nextIndex ; disableIndex < fields.length; disableIndex++) {
      $('#' + fields[disableIndex]).prop('disabled', true);
      $('#' + fields[disableIndex]).val('');
      $('.products-filter__submit').prop('disabled', true);
      $('#' + fields[disableIndex]).parent('.products-filter__select').addClass('select--disabled');
    }

    // Get the info back from product browsing
    $.ajax({
      url: compiledUrl,
      type: 'get',
      data: {
        action: 'product_browsing_ajax_request',
        source: 'product-browsing',
        nonce: browsing.ajax_nonce
      },
      dropdownIndex: nextIndex, // Index of the dropdown we are on
      fields: fields,   // A list of the dropdown fields
      success: handleProductData
    });

  }

  // On page load run needed functions
  $(document).ready(function() {

    checkProductInputVals();

    if( urlParamChecker.getUrlParameter('cat-make') && !urlParamChecker.getUrlParameter('diffid') ) {
      queryProductsWithFilters();
    }

    // on dropdwn change we will run our function that handles the ajax request
    $(".products-filter-dropdown").on('change', handleProductDropdownChange);

  });

});
