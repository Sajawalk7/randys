jQuery(function ($) {
  var URL = wpAjax.ajax_url;

  // On load we want to see what fields are populated
  // remove disable and add visual que for populated
  function checkInputVals() {
    var fields = [
      'make',
      'model',
      'drivetype'
    ];

    for (var indx = 0; indx < fields.length; indx++) {
      if( $('#' + fields[indx]).val() ) {
        $('#' + fields[indx]).prop('disabled', false);
        $('#' + fields[indx]).parent('.diffwizard__select').removeClass('select--disabled');
      }
    }
  }

  var yearParam = 'diffyear=' + urlParamChecker.getUrlParameter('diffyear');
  var makeParam = 'make=' + urlParamChecker.getUrlParameter('make');
  var modelParam = 'model=' + urlParamChecker.getUrlParameter('model');
  var driveTypeParam = 'drivetype=' + urlParamChecker.getUrlParameter('drivetype');
  var diffIDParam = urlParamChecker.getUrlParameter('diffid');
  var diffParams = '?' + yearParam + '&' + makeParam + '&' + modelParam + '&' + driveTypeParam;


  // Success return function for jquery AJAX call
  function handleDiffWizardData(rawData) {
    // Parse and save the data
    var data = JSON.parse(rawData);

    // Remove loading state
    loadingState.isLoading(false);

    // Add active state
    $('#' + data.dropdown).addClass('active');

    // Before we do logic, let's check if we are on the results page and have one result
    if(window.location.pathname === '/diff-wizard/' && data.diffdata && data.diffdata.length === 1 && !diffIDParam) {
      // If request comes back with one diff, append hidden field and add the diff id and submit
      $('.diffwizard__form').append('<input id="diffid" type="hidden" name="diffid"  value="" />');
      $('#diffid').val(data.diffdata[0].diffid);
    } else if(data.diffdata && data.diffdata.length === 1) {
      // If we aren't on the diff-wizard results page but have the diff id
      // Setup the hidden input and wait for user to submit
      $('.diffwizard__form').append('<input id="diffid" type="hidden" name="diffid"  value="" />');
      $('#diffid').val(data.diffdata[0].diffid);
    } else {
      $('#diffid').remove();
    }

    // Get the index of the dropdown we are trying to find
    var indx = this.dropdownIndex;
    var fields = this.fields;

    // Make sure the call was successful
    if(data.success !== true) {
      alert("There was an error with your request, please try again.");
      return;
    }

    // If one of the major 4 dropdowns was returned, populate it.
    if (data.dropdown) {
      // Handle if there was only one value for the next field and it was given back
      while (data.dropdown !== fields[indx]) {
        // Empty out the field
        $('#' + fields[indx] + ' option[value!=""]').remove();
        // Set the 'select'
        $('#' + fields[indx]).append("<option value='" + data.input[fields[indx]] + "'>" + data.input[fields[indx]] + "</option>");
        $('#' + fields[indx]).val(data.input[fields[indx]]);
        // Enable this dropdown
        $('#' + fields[indx]).prop('disabled', false);
        $('#' + fields[indx]).parent('.diffwizard__select').removeClass('select--disabled');
        // Increment indx to go to the next field
        indx++;
      }

      // Empty the found field
      $('#' + data.dropdown + ' option[value!=""]').remove();
      // Set the 'select'
      for (indx = data.dropdowndata.length - 1; indx >= 0; indx--) {
        $('#' + data.dropdown).append("<option value='" + data.dropdowndata[indx] + "'>" + data.dropdowndata[indx] + "</option>");
      }
      // If there was only 1 in this category, set it as selected.
      if (data.dropdowndata.length === 1) {
        $('#' + data.dropdown).val(data.dropdowndata[0]);
      }
      // Enable this dropdown
      $('#' + data.dropdown).prop('disabled', false);
      $('#' + data.dropdown).parent('.diffwizard__select').removeClass('select--disabled');
    }

    // Once we have all 4 major inputs enable form submit button
    if (data.diffdata) {
      $('.diffwizard-dropdown').removeClass('active');
      $('.diffwizard__submit').prop('disabled', false);
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
          if( filterLabel === 'Pinion Support') {
            $(dropdown).append("<option value='" + indx + "'>" + filterData[indx] + "</option>");
          } else {
            $(dropdown).append("<option value='" + filterData[indx] + "'>" + filterData[indx] + "</option>");
          }
        }

        // Append the select box to the list of possible filters
        $("#extra-filters").append("<div class='diffwizard-results__label'>" + filterLabel + "</div>");
        $("#extra-filters").append(dropdown);
      }
      $(".diffwizard-results__select").wrap("<div class='diffwizard-results__select-wrap select'></div>");
      // Apply an event handler on the newly created dropdowns.
      $(".diffwizard-results__select").on('change', handleFilterChange);
    }


    // If we have differentials, set the differentials here
    if (data.diffdata) {
      // Go through each of the differentials
      $("#differential-list").append('<h3 class="diffwizard-results__title">Choose Your Differential</h3>');

      for (indx = data.diffdata.length - 1; indx >= 0; indx--) {
        imageChecker.checkDiffImage("/wp-content/uploads/differential-images/" + data.diffdata[indx].FullImage, 'differential-item__image-' + indx);
        var diffDiv = $("<div class='differential-item__differential-data m-b-3 row' id='" + data.diffdata[indx].diffid + "'></div>");
        diffDiv.append("<div class='col-sm-6'><img src='' class='differential-item__image differential-item__image-" + indx + " img-fluid m-b-2 mx-auto' /></div>");
        diffDiv.append("<div class='col-sm-6'><p class='differential-item__diffname'>" + data.diffdata[indx].diffname + "</p><p class='diffwizard__diffdescription'>" + data.diffdata[indx].diffdescription + "</p><a href='/diff-wizard/" + diffParams + "&diffid=" + data.diffdata[indx].diffid + "' class='button button--slim differential-item__diff-select'>Select Differential</a></div>");


        // If we have more than two diffs returned lets update our back button
        if( window.location.pathname === '/diff-wizard/' &&
            urlParamChecker.getUrlParameter('drivetype') &&
            data.diffdata.length > 1 ) {

          $('.back-button').attr('href', '/diff-wizard' + diffParams);

        }

        // Finally, append this data to the output div
        $("#differential-list").append(diffDiv);
      }
    }
  }

  // Queries using the filters. Not used by primary dropdowns because the logic is different.
  function queryWithFilters() {

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
      queryValues.push(fields[indx] + "=" + $('#' + fields[indx]).val());
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

    // Get the info back from the wizard
    $.ajax({
      url: compiledUrl,
      type: 'get',
      data: {
        action: 'diffwizard_ajax_request'
      },
      dropdownIndex: 4, // Index of the dropdown we are on
      fields: fields,   // A list of the dropdown fields
      success: handleDiffWizardData
    });
  }

  // Handles when a filter is changed
  function handleFilterChange() {  // jshint ignore:line
    // If the selected filter was changed to '', do nothing
    if ($(this).val() === '') {
      return;
    }

    $('#extra-filters').hide();

    // Add this filter to the list of filters
    $("#current-filters").show().append("<p class='diffwizard--current-filter current-filter--" + $(this).attr('id') + "' data-filter='" + $(this).attr('id') + "' data-value='" + $(this).val() + "'>" + $(this).data('label') + ": " + $(this).val() + "<br /></p>");

    // Set this new filter so that it will be removed when the 'X' is clicked.
    $(".diffwizard--current-filter--remove").click(function(){
      $("#current-filters").remove();queryWithFilters();
      $("#extra-filters").show();
      $("#current-filters").empty().hide();
    });

    // Now query using the filters we added
    queryWithFilters();
  }

  // Handles when a primary dropdown has changed
  function handleDropdownChange() {

    // reference list for the four main fields
    var fields = [
      'year',
      'make',
      'model',
      'drivetype'
    ];

    // First, clear out the extra filters and the diff list
    $("#extra-filters").empty();
    $("#differential-list").empty();
    // Also clear out any current filters
    $("#current-filters").empty();

    // If we are on mobile, we want to expand the form dropdown
    $('.diffwizard__select--m-collapse').addClass('open');
    $('.diffwizard__m-toggle').addClass('diffwizard__m-toggle--open');

    if ($('#year').val() === '') {
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
        queryValues.push(fields[indx] + "=" + $('#' + fields[indx]).val());
      } else {
        // Since this index is empty, this is the one we want to get
        nextIndex = indx;
        break;
      }

      // If this is the dropdown that we selected, finish it here
      if ($(this).attr('id') === fields[indx]) {
        // The next dropdown is the one we want to figure out.
        nextIndex = indx + 1;
        break;
      }
    }

    // Activate Spinner for next dropdown
    loadingState.isLoading(true, $('.diffwizard-dropdown'), $('#' + fields[nextIndex]));

    // Compile together the query values
    var compiledUrl = URL + "?" + (queryValues.join('&'));

    // Disable and set to ==select== this and all following dropdowns
    for (var disableIndex = nextIndex ; disableIndex < fields.length; disableIndex++) {
      $('#' + fields[disableIndex]).prop('disabled', true);
      $('#' + fields[disableIndex]).val('');
      $('.diffwizard__submit').prop('disabled', true);
      $('#' + fields[disableIndex]).parent('.diffwizard__select').addClass('select--disabled');
    }

    // Get the info back from the wizard
    $.ajax({
      url: compiledUrl,
      type: 'get',
      data: {
        action: 'diffwizard_ajax_request'
      },
      dropdownIndex: nextIndex, // Index of the dropdown we are on
      fields: fields,   // A list of the dropdown fields
      success: handleDiffWizardData
    });

  }

  // Handle for a reset trigger
  function resetInputFields() {
    // Reset values
    $('#year, #make, #model, #drivetype').val('');

    // Disable dropdowns
    $('#make, #model, #drivetype, .diffwizard__submit').prop('disabled', true);
    $('#make, #model, #drivetype').parent('.diffwizard__select').addClass('select--disabled');

    // Enable first dropdown(year)
    $('#year').prop('disabled', false);
    $('#year').parent('.diffwizard__select').removeClass('select--disabled');
  }

  // On page load run needed functions
  $(document).ready(function() {
    // Check what inputs have values
    checkInputVals();

    // on dropdwn change we will run our function that handles the ajax request
    $(".diffwizard-dropdown").on('change', handleDropdownChange);

    // Fire ajax request once we load diff-wizard results page
    var pathname = window.location.pathname;
    if (pathname === '/diff-wizard/' && urlParamChecker.getUrlParameter('make') !== undefined) {
      queryWithFilters();
    }

    // Setup reset to fire on .button--clear click event
    $('.button--clear').on('click', function(e) {
      e.preventDefault();
      resetInputFields();
    });

    // If we don't have a year param, make sure we reset the form
    if( !urlParamChecker.getUrlParameter('diffyear') ) {
      resetInputFields();
    }

    // Results page Functionality
    $('.diffwizard__m-toggle').on('click', function() {
      $(this).toggleClass('diffwizard__m-toggle--open');
      $('.diffwizard__select--m-collapse').toggleClass('open');
    });

  });

  // Get "Another Diff" ajax request
  $('.select-another-diff-dropdown').on('change', function() {
    loadingState.isLoading(true, $('#another-make'), $('#differential'));

    // Get the value of selected make and pass it through the url
    let selectedMake = $(this).val();

    // If change is on the make dropdown lets get the make val
    if( $(this).hasClass("select-another-diff-dropdown--make") ) {
      // First, let's clear out diff dropdown results
      $('#differential')
        .find('option')
        .remove()
        .end()
        .append('<option value="" selected>Differential</option>');

      $('#differential').prop('disabled', true);
      $('#differential').parent('.select-another-diff__select').addClass('select--disabled');

      $('.select-another-diff__submit').prop('disabled', true);
    }

    $.ajax({
      method: 'get',
      url : URL,
      data : {
        action: 'get_another_diff',
        selectedMake: selectedMake,
        nonce: $('#another_diff_nonce').val()
      },
      success: function(response) {
        // Parse and save the data
        var data = JSON.parse(response);

        // Remove loading state
        loadingState.isLoading(false);

        // Enable diff select dropdown
        if( $('#another-make').val() !== null ) {
          $('#differential').prop('disabled', false);
          $('.select-another-diff__select').removeClass('select--disabled');
        }
        // Output diff results
        for (var i = data.length - 1; i >= 0; i--) {
          $('#differential').append("<option value='" + data[i].DiffID + "'>" + data[i].DiffName + "</option>");
        }

        // If we have a diff value enabled submit button
        if( $('#differential').val() !== '' ) {
          $('#diffid').remove();
          $('.select-another-diff__submit').prop('disabled', false);
          $('.select-another-diff').append('<input id="diffid" type="hidden" name="diffid"  value="" />');
          $('#diffid').val($('#differential').val());
        }
      }
    });
  });

});
