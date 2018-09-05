/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can
 * always reference jQuery with $, even when in .noConflict() mode.
 * ======================================================================== */

(function($) {

  // Checks if returned Diff URL is available
  var imageChecker = {
    checkDiffImage: function(url, selector) {
      $.ajax({
        method: 'get',
        url : wpAjax.ajax_url,
        data : {
          action: 'check_diff_image',
          diffURL: url,
          selector: selector,
          nonce: wpAjax.ajax_nonce
        },
        success: function(response) {
          var data = JSON.parse(response);
          $('.' + data.diffSelector).attr('src', data.diffURL);
        }
      });
    }
  };
  window.imageChecker = imageChecker;

  // get url params
  var urlParamChecker = {
    getUrlParameter: function(sParam) {
      var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
      for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
          return sParameterName[1] === undefined ? true : sParameterName[1];
        }
      }
    }
  };
  window.urlParamChecker = urlParamChecker;

  // Product Browsing + Diff Wizard loading state
  var loadingState = {
    isLoading: function(loading, currentInput, nextInput) {
      if( loading === true ) {
        currentInput.removeClass('active');
        nextInput.siblings('.spinner').css('display', 'block');
      } else {
        $('.spinner--input').css('display', 'none');
      }
    }
  };
  window.loadingState = loadingState;

  var productsSlider = {
    productsSlider: function() {
      $('.products-slider').not('.slick-initialized').slick({
        prevArrow: '<div class="left-arrow slick-prev slick-prev--bg-transparent"><i class="fa fa-angle-left" aria-hidden="true"></i></div>',
        nextArrow: '<div class="right-arrow slick-next slick-next--bg-transparent"><i class="fa fa-angle-right" aria-hidden="true"></i></div>',
        slidesToShow: 3,
        responsive: [
        {
          breakpoint: 968,
          settings: {
            slidesToShow: 2
          }
        },
        {
          breakpoint: 543,
          settings: {
            slidesToShow: 1,
            centerMode: true,
            centerPadding: '25px',
          }
        }
      ]
      });
    }
  };

  window.productsSlider = productsSlider;

  // init pagebuilder sliders
  function sliderInit() {
    $('.pagebuilder-slider').slick({
      prevArrow: '<div class="left-arrow slick-prev"><i class="fa fa-angle-left" aria-hidden="true"></i></div>',
      nextArrow: '<div class="right-arrow slick-next"><i class="fa fa-angle-right" aria-hidden="true"></i></div>',
      appendArrows: '.slider__overlap',
      dots: true
    });

    $('.timeline-slider').slick({
      prevArrow: '<div class="left-arrow slick-prev"><i class="fa fa-angle-left" aria-hidden="true"></i></div>',
      nextArrow: '<div class="right-arrow slick-next"><i class="fa fa-angle-right" aria-hidden="true"></i></div>',
      appendArrows: '.timeline__overlap',
      slidesToShow: 4,
      responsive: [
        {
          breakpoint: 968,
          settings: {
            slidesToShow: 3
          }
        },
        {
          breakpoint: 543,
          settings: {
            slidesToShow: 1,
            centerMode: true,
            centerPadding: '25px',
          }
        }
      ]
    });
  }

  // Calls in Youtube API
  // Setups a click event to auto play video in bootstrap modal
  function youtubeModal() {

    // Youtube Videos for Tile Module (/lib/functions/tile.php)
    let tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    let firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    let player;
    function onPlayerReady(event) {
      event.target.playVideo();
    }

    // Once video is over destroy and hide modal
    function onPlayerStateChange(event) {
      if(event.data === YT.PlayerState.ENDED) {
        player.destroy();
        $('#video-modal').modal('hide');
      }
    }

    // On click get Youtube ID and setup player
    $('.video-modal-trigger').on('click', function() {

      const youtubeVideoId = $(this).data('youtube-id');
      player = new YT.Player('player', {
        width : '1067',
        height : '600',
        videoId : youtubeVideoId,
        playerVars: { 'autoplay': 1, 'vq': 'hd720', 'rel': 0 },
        events : {
          'onReady' : onPlayerReady,
          'onStateChange' : onPlayerStateChange
        }
      });

    });

    // When the modal is closed (hidden)
    // Remove video
    $('#video-modal').on('hidden.bs.modal', function() {
      player.destroy();
    });

  }

  // Calls in Youtube API
  // Setups a click event to auto play video inline
  function youtubeInline() {
    // Inline video
    let tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    let firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    $('.inline-video').on('click', function(){

      let player;
      function onPlayerReady(event) {
       //event.target.playVideo();
      }
      function onPlayerStateChange(event) {
       if(event.data === YT.PlayerState.ENDED) {
         player.destroy();
       }
      }

      const youtubeVideoId = $(this).data('youtube-id');
      player = new YT.Player('player-inline', {
       width : '100%',
       height : '670',
       videoId : youtubeVideoId,
       playerVars: { 'autoplay': 1, 'vq': 'hd720', 'rel': 0 },
       events : {
         'onReady' : onPlayerReady,
         'onStateChange' : onPlayerStateChange
       }
      });

    });
  }

  var ellipsis = {
    enableEllipsis: function() {
      $(".ellipsis").dotdotdot({
        ellipsis: '... ',
        wrap: 'word',
        fallbackToLetter: true,
        after: null,
        watch: false,
        height: null,
        tolerance: 0,
        callback: function( isTruncated, orgContent ) {},
        lastCharacter	: {
          remove: [ ' ', ',', ';', '.', '!', '?' ],
          noEllipsis: []
        }
        });
    }
  };
  window.ellipsis = ellipsis;

  function lazyLoadProducts() {
    var page = 1; // What page we are on.
    var ppp = 25; // Post per page

    // If we change sort, reset pagination page
    $('#orderby').on('click', function() {
      page = 1;
    });

    // If we change filter, reset pagination page
    $('.products-filter__select').on('change', function() {
      page = 1;
    });

    $(".load-more-products").on("click",function(e) {
      var productIDs = $('#current-ids').attr('data-query-ids');
      e.preventDefault();
      $(this).hide();
      $('.load-more-products__spinner').show();
      $.ajax({
        method: 'get',
        url : wpAjax.ajax_url,
        data : {
          action: 'more_post_ajax',
          offset: (page * ppp),
          productIDs: productIDs,
          selectedSort: $('#orderby').val(),
          year: $('#year').val(),
          make: $('#make').val(),
          model: $('#model').val(),
          drivetype: $('#drivetype').val(),
          diffID: $('#diffid').val(),
          nonce: wpAjax.ajax_nonce
        }
      }).success(function(posts){
        page++;
        $('.product-results').append(posts);
        $('.load-more-products').show();
        $('.load-more-products__spinner').hide();
        if( page >= $('.total-pages').text() ) {
          $('.load-more-products').hide();
        }

        // Once products have loaded run supporting functions
        productsSlider.productsSlider();
        ellipsis.enableEllipsis();
      });
    });
  }

  function sortProducts() {
    // Sort by Ajax request
    // Used on all product archive results pages
    let source = '';
    if( window.location.pathname === '/diff-wizard/' ) {
      source = 'diff-wizard';
    } else {
      source = 'product-browsing';
    }

    $('.orderby').on('change', function() {
      $('.product-results').addClass('product-results--loading');
      $('.product-results').html('<div class="center-align"><div class="spinner"></div></div>');
      let diffID = urlParamChecker.getUrlParameter('diffid');
      let cat = 0 < $('#cat-category').length ? $('#cat-category').val() : $('.queried-category').val();
      let productIDs = $('#current-ids').attr('data-query-ids');
      let selectedVal = $(this).val();
      let diffYear = urlParamChecker.getUrlParameter('diffyear');
      let diffModel = urlParamChecker.getUrlParameter('model');
      let diffDriveType = urlParamChecker.getUrlParameter('drivetype');
      let parentID = $('.parent-id').val();
      let source = '';
      if( window.location.pathname === '/diff-wizard/' ) {
        source = 'diff-wizard';
      } else {
        source = 'product-browsing';
      }


      $.ajax({
        method: 'get',
        url : wpAjax.ajax_url,
        data : {
          action: 'get_product_sort',
          diffID: diffID,
          cat: cat,
          productIDs: productIDs,
          selectedval: selectedVal,
          diffYear: diffYear,
          diffModel: diffModel,
          diffDriveType: diffDriveType,
          parentID: parentID,
          source: source,
          paged: $('.page-numbers.current').text(),
          nonce: $('#sort_nonce').val()
        },
        success: function(response) {
          var data = JSON.parse(response);
          $('.product-results').removeClass('product-results--loading');
          $('.product-results').html(data.product);

          // Prepare product IDs for ability to sort
          if(data.productIDs) {
            var queryIDs = [];
            for (var idIndx = 0; idIndx < data.productIDs.length; idIndx++) {
              queryIDs.push(data.productIDs[idIndx]);
            }
            $('#current-ids').attr('data-query-ids', queryIDs);
          }

          // If we have more than 25 items add pagination button
          if( $('.total-pages').text() !== '1') {
            $('.load-more-products').show();
          } else {
            $('.load-more-products').hide();
          }

          // Once products have loaded run supporting functions
          productsSlider.productsSlider();
          ellipsis.enableEllipsis();
        }
      });
    });

    // If we have products to sort on load, trigger event
    if($('.product-results').length > 0 && source !== 'diff-wizard') {
      $('.orderby').trigger('change');
    }
  }

  /**
   * This function adds the attribute data-title to each table cell
   * it will look at the first row of the table to determine the values
   */
  function addTableCellTitle() {
    // find each table on page in .main
    $('body:not(.woocommerce-page) .main table:not(.gfield_list)').each(function() {
      let $table = $(this);
      // find the first row in table and get the heading for each column
      $(this).find('tr:first-of-type td').each(function(index) {
        let columnName = $(this)[0].innerText;
        // set to one-based index
        index = index+1;
        // set the attribute in each column with the matching index in each row
        $table.find('tr:gt(0)').each(function() {
          $(this).find('td:nth-child('+index+')').attr('data-title', columnName);
        });
      });
    });
  }

  // Swap out Gravity Forms list form field add and remove button images with real buttons
  // (Unable to find a gforms php hook that will do this)
  $('.gfield_list_container .add_list_item, .gfield_list_container .delete_list_item').each(function() {
    let attributes = {
      event: $(this).attr('onclick'),
      class: $(this).attr('class'),
      style: $(this).attr('style')
    };
    let btnText = 'add_list_item ' === $(this).attr('class') ? '<span>+ </span><span>Add</span><span> Part</span>' : '<span>- </span><span>Remove</span><span> Part</span>';
    $(this).replaceWith('<div class="button button--short gform-list-button '+attributes.class+'" style="'+attributes.style+'" onclick="'+attributes.event+'">'+btnText+'</div>');
  });

  // Dropdown timeout functions
  let timeoutID;

  function slowClose($this) {
    $this.toggleClass('open');
    $this.removeClass('closing');
  }

  function clearClose() {
    window.clearTimeout(timeoutID);
  }

  function delayedClose($this) {
    timeoutID = window.setTimeout(slowClose, 200, $this);
  }

  // Function to change phone numbers to clickable 'tel' links on mobile
  function telLinks() {
    // check to see if mobile styles are in place before running code
    if ( 0 === $('.menu-main-nav-container').height() ) {
      let regex = /(\d{1}[-. ])?((\(\d{3}\)[-. ]?)|(\d{3}[-. ]))?\d{3}[-. ]\d{4}/g;
      // find all text nodes in .main that have a phone number
      jQuery('.main').find(":not(iframe, form)").addBack().contents().filter(function() {
        return this.nodeType === 3 && this.textContent.match(regex);
      })
        .parent().each(function(){
          // with those text nodes, get the parent dom element and then change its html
          let $this = $(this);
          let textContent = $this.html();
          $this.html(textContent.replace(regex, '<a href=\"tel:$&\">$&</a>'));
        });
    }
  }


  // Use this variable to set up the common and page specific functions. If you
  // rename this variable, you will also need to rename the namespace below.
  let Sage = {
    // All pages
    'common': {
      init: function() {
        // JavaScript to be fired on all pages

        // Main nav dropdown toggle
        $('li.menu-item.dropdown')
          .mouseover(function() {
            if ( 0 < $('.menu-main-nav-container').height() ) {
              if ( $(this).hasClass('products-menu') && $(this).hasClass('closing') ) {
                clearClose();
                $(this).removeClass('closing');
              } else {
                $(this).addClass('open');
              }
            }
          })
          .mouseout(function() {
            if ( 0 < $('.menu-main-nav-container').height() ) {
              if ( $(this).hasClass('products-menu') && !$(this).hasClass('closing') && !$(this).hasClass('open') ) {
              } else if ( $(this).hasClass('products-menu') && !$(this).hasClass('closing') && $(this).hasClass('open') ) {
                $(this).addClass('closing');
                delayedClose($(this));
              } else {
                $(this).removeClass('open');
              }
            }
          });
        $('li.menu-item.dropdown span').on('click', function() {
          if ( 0 === $('.menu-main-nav-container').height() ) {
            $(this).parent().toggleClass('open');
          }
        });

        // Add data-title attribute to all tables for responsive layout
        addTableCellTitle();

        // Change phone numbers to clickable 'tel' links on mobile
        telLinks();

        // Convert svg images that were added to the page via the image tag
        // in the calculator buttons into inline svg images so we can control
        // the colors via css
        $('.button--calc img').each(function(){
          let $img = $(this);
          let imgID = $img.attr('id');
          let imgClass = $img.attr('class');
          let imgURL = $img.attr('src');

          jQuery.get(imgURL, function(data) {
            // Get the SVG tag, ignore the rest
            let $svg = jQuery(data).find('svg');

            // Add replaced image's ID to the new SVG
            if(typeof imgID !== 'undefined') {
                $svg = $svg.attr('id', imgID);
            }
            // Add replaced image's classes to the new SVG
            if(typeof imgClass !== 'undefined') {
                $svg = $svg.attr('class', imgClass+' replaced-svg');
            }

            // Remove any invalid XML tags as per http://validator.w3.org
            $svg = $svg.removeAttr('xmlns:a');

            // Replace image with new SVG
            $img.replaceWith($svg);

          }, 'xml');

        });

        // Init dotdotdot
        $(".ellipsis").dotdotdot({
          ellipsis	: '... ',
          watch: true
        });

        // Click event to open intercom app
        $('.enable-intercom').on('click', function(e) {
          e.preventDefault();
          let frameSelector = $('.intercom-launcher-frame').contents();
          if( ! frameSelector.find('.intercom-launcher').hasClass('intercom-launcher-active') ) {
            frameSelector.find('.intercom-launcher-open-icon').trigger( "click" );
          }
        });

      },
      finalize: function() {
        // JavaScript to be fired on all pages, after page specific JS is fired
      }
    },
    // Home page
    'home': {
      init: function() {
        // JavaScript to be fired on the home page
        $('.product-slider').slick({
          slidesToShow: 3,
          slidesToScroll: 1,
          infinite: true,
          prevArrow: '<div class="left-arrow slick-prev"><i class="fa fa-angle-left" aria-hidden="true"></i></div>',
          nextArrow: '<div class="right-arrow slick-next"><i class="fa fa-angle-right" aria-hidden="true"></i></div>',
          responsive: [
            {
              breakpoint: 768,
              settings: {
                slidesToShow: 2
              }
            },
            {
              breakpoint: 543,
              settings: {
                slidesToShow: 1
              }
            }
          ]
        });

        // init isotope
        $grid = jQuery('.tile-container').isotope({
          // options
          layoutMode: 'packery',
          itemSelector: '.tile',
          packery: {
            columnWidth: '.grid-sizer',
            gutter: '.gutter-sizer'
          }
        });

        youtubeModal();

      },
      finalize: function() {
        // JavaScript to be fired on the home page, after the init JS
      }
    },
    'page_template_template_pagebuilder': {
      init: function() {
        youtubeInline();
        sliderInit();

        // Open Specified FAQ if present in URL
        if(window.location.hash) {
          $(window.location.hash).collapse('show');
        }
      }
    },
    'post_type_archive_resource_center': {
      init: function() {

        // On load set active state for category
        // input search value from url params
        function findActiveStateOnLoad(catActive, searchParam) {
          $('.card-category-item[data-category-slug]').each(function() {
            if( $(this).data('category-slug') ===  catActive ) {
              $(this).addClass('active');
              const catName = $(this).text();

              $('.card__mobile-active-item .card-text').text(catName);
            }
          });

          $('.resource-category-search input').val(searchParam);
        }

        // Check if Search input has a value
        // hide/show rest button depending on result
        function searchValCheck() {
          if( !$('.resource-category-search input').val() ) {
            $('.resource-category-search__reset').css('display', 'none');
          } else {
            $('.resource-category-search__reset').css('display', 'block');
          }
        }

        // Check if Category list has an active item
        // hide/show rest button depending on result
        function categoryResetCheck() {
          if( $('.card-category-item').hasClass('active') ) {
            $('.resource-center-category__reset').css('display', 'inline-block');
          } else {
            $('.resource-center-category__reset').css('display', 'none');
          }
        }

        // Get results count and inject into markup
        function getResultsCount() {
          const resultsReturned = $('.facetwp-template .row').children().length;

          $('.archive-header__count span').text(resultsReturned);
        }

        // If no Facets returned present empty state
        function isResultsEmpty() {
          if ( $('.facetwp-template .row').children().length === 0 ) {
            $('.resource-center__no-results').css('display', 'block');
          } else{
            $('.resource-center__no-results').css('display', 'none');
          }
        }

        // Smooth scroll to resource-center section
        function scollToResources() {
          $('body,html').animate({
            'scrollTop': $('#resource-center').offset().top -100
          }, 1000);
        }

        // Conditional that activates
        // the resource-center loading state
        function isLoading(status) {
          const resourceSelector = $('.resource-center');
          if( status === true ) {
            $(resourceSelector).addClass('resource-center--is-loading');
            $('.resource-center__no-results').css('display', 'none');
          } else {
            $(resourceSelector).removeClass('resource-center--is-loading');
          }
        }

        // On Submit, prevent default action
        // and run Facet refresh event
        $('.resource-category-search').on('submit', function(e) {
          e.preventDefault();
          scollToResources();
          FWP.refresh();
        });

        // On click of reset, remove input value
        // and run Facet refresh event
        $('.resource-category-search__reset').on('click', function() {
          $('.resource-category-search input').val('');
          FWP.refresh();
        });

        // On Category Click remove current active /add new active class
        // and trigger facet refresh event
        $('.card-category-item').on('click', function() {
          $('.card-category-item').removeClass('active');
          $(this).addClass('active');
          scollToResources();
          FWP.refresh();
        });

        // On click of reset, remove active category
        // and run Facet refresh event
        $('.resource-center-category__reset').on('click', function() {
          $('.card-category-item').removeClass('active');
          $('.card__mobile-active-item .card-text').text('Select Category');
          FWP.refresh();
        });

        // Mobile Category dropdown click event
        $('.card__mobile-active-item').on('click', function() {
          $('.card-column').toggle();
        });

        // On facet refresh event get current active categorySlug
        // and pass it through to the facet
        $(document).on('facetwp-refresh', function() {
          const categorySlug = $('.card-category-item.active').data('category-slug');
          const searchVal = $('.resource-category-search input').val();

          isLoading(true);

          if( categorySlug != null ) {
            FWP.facets.resource_center_categories = [categorySlug];
          } else {
            FWP.facets.resource_center_categories = [];
          }
          FWP.facets.resource_center_search = [searchVal];

        });

        // On facet loaded event, remove loading state
        // check if and results returned
        let runCount = 0;
        $(document).on('facetwp-loaded', function() {
          const categoryParam = urlParamChecker.getUrlParameter('fwp_resource_center_categories');
          const searchParam = urlParamChecker.getUrlParameter('fwp_resource_center_search');

          // If we have parameters in URL on load, let's scroll to results section
          if( runCount === 0 && categoryParam || searchParam ) {
            runCount++;
            scollToResources();
          }

          findActiveStateOnLoad(categoryParam, searchParam);
          isLoading(false);
          isResultsEmpty();
          getResultsCount();
          categoryResetCheck();
          searchValCheck();
          youtubeModal();
          ellipsis.enableEllipsis();
        });

      }
    },
    'tax_product_cat': {
      init: function() {
        youtubeInline();
        sliderInit();
        lazyLoadProducts();
        sortProducts();
      }
    },
    'page_template_template_product_browsing_pagebuilder': {
      init: function() {
        lazyLoadProducts();
        sortProducts();
      }
    },
    'glossary': {
      init: function() {
        $('.glossary-section span.children').on('click', function() {
          $('.glossary-content .container').html('<div class="center-align"><div class="spinner"></div></div>');
          let $this = $(this);
          let pageId = parseInt($this.attr('data-page-id'));
          let row = parseInt($this.attr('data-row'));

          $.ajax({
            method: 'post',
            url : wpAjax.ajax_url,
            data : {
              action: 'get_glossary',
              row: row,
              page_id: pageId,
              nonce: $('#glossary_nonce').val()
            },
            success: function(response) {
              $('.glossary-content .container').html(response);
              $('.glossary-section span').removeClass('active');
              $this.addClass('active');
            }
          });
        });
      }
    },
    'diff_wizard': {
      init: function() {
        lazyLoadProducts();
        sortProducts();
      }
    },
    'search': {
      init: function() {

        // Search Sortby functionality
        $('#orderby-search').val(urlParamChecker.getUrlParameter('sortby'));
        $('#orderby-search').on('change', function() {
          var protocol = document.location.protocol;
          var RootURL = document.location.hostname;
          var searchParam = urlParamChecker.getUrlParameter('s');
          var sortByValue = $(this).val();
          window.location.href = protocol + '//' + RootURL + '?s=' + searchParam + '&sortby=' + sortByValue;
        });
      }
    },
    'checkout': {
      init: function() {
        // repositioning smarty checkboxes on checkout init
        var init = 0;
        $(document.body).on('updated_checkout', function() {
          if ( 0 === init ) {
            $('a.smarty-addr-billing').parent('.smarty-ui').addClass('smarty-billing').appendTo('.woocommerce-billing-fields');
            $('a.smarty-addr-shipping').parent('.smarty-ui').addClass('smarty-shipping').appendTo('.shipping_address');
          }
          init++;
        });

        function submitCoupon() {
          let coupon = $('#coupon_code_copy').val();
          $('#coupon_code').val(coupon);
          $('form.checkout_coupon').submit();
          setTimeout(function() {
            let error = $('.hidden-coupon-form .woocommerce-error');
            let message = $('.hidden-coupon-form .woocommerce-message');
            $(message).insertBefore('#order_review');
            $(error).insertBefore('#order_review');
            $('.coupon-form').toggleClass('active');
            $('#coupon_code_copy').val('');
          }, 1000);
        }

        $('.show-login-signup').on('click', function(e) {
          e.preventDefault();
          $('.login-signup, .show-login-signup').toggleClass('active');
        });

        $('#review_field input').on('click', function(e) {
          if ( $('#review_field input[value="No Thanks"]').is(':checked') ) {
            $('.vehicle-info').removeClass('active');
          } else {
            $('.vehicle-info').addClass('active');
          }
        });

        $('#show-coupon-form').on('click', function(e) {
          e.preventDefault();
          $('.coupon-form').toggleClass('active');
        });

        $('.coupon-form a[name="apply_coupon"]').on('click', function(e) {
          e.preventDefault();
          submitCoupon();
        });

        $('#coupon_code_copy').on('keyup', function(e) {
          if (e.keyCode === 13) {
            submitCoupon();
          }
        });

        $(document).on('change', '.shipping_methods', function(e) {
            $('body').trigger('update_checkout');
        });

        $(document).on('change', '#shipper_id', function(e) {
          $('body').trigger('update_checkout');
        });

        $(document).on('change', '#dropship_order_field input, #ship-to-different-address input', function(e) {
            $('body').trigger('update_checkout');
        });

        $(document).on('change', '.payment_methods .payment_method_choice input', function(e) {
          // update cart when changing payment types
          $('body').trigger('update_checkout');
        });
      }
    },
    'single_product': {
      init: function() {
        youtubeModal();
        productsSlider.productsSlider();
      }
    }
  };

  // The routing fires all common scripts, followed by the page specific scripts.
  // Add additional events for more control over timing e.g. a finalize event
  var UTIL = {
    fire: function(func, funcname, args) {
      var fire;
      var namespace = Sage;
      funcname = (funcname === undefined) ? 'init' : funcname;
      fire = func !== '';
      fire = fire && namespace[func];
      fire = fire && typeof namespace[func][funcname] === 'function';

      if (fire) {
        namespace[func][funcname](args);
      }
    },
    loadEvents: function() {
      // Fire common init JS
      UTIL.fire('common');

      // Fire page-specific init JS, and then finalize JS
      $.each(document.body.className.replace(/-/g, '_').split(/\s+/), function(i, classnm) {
        UTIL.fire(classnm);
        UTIL.fire(classnm, 'finalize');
      });

      // Fire common finalize JS
      UTIL.fire('common', 'finalize');
    }
  };

  // Load Events
  $(document).ready(UTIL.loadEvents);

})(jQuery); // Fully reference jQuery after this point.
