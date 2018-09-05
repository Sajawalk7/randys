"use strict";jQuery(function(t){function e(e){var r=JSON.parse(e);if(t(".products-filter__spinner").hide(),t(".products-filter__list").show(),t(".products-filter__list").css("visibility","visible"),t(".product-results").removeClass("product-results--loading"),t(".product-results").html(r.productList),t(".archive-header__count span").html(r.productCount),t(".products-filter__form-item").removeClass("unchanged"),t(".Parent, .Category, .GearRatio").addClass("unchanged"),t("."+r.changedDropdown).addClass("unchanged"),"Parent"===r.changedDropdown?t(".Category, .GearRatio").removeClass("unchanged"):"Category"===r.changedDropdown&&t(".GearRatio").removeClass("unchanged"),t(".products-filter__list > *:not('.products-filter__form-item.unchanged')").remove(),r.filters&&r.filters.length>=1){for(var s=r.filters.length-1;s>=0;s--){var i=r.filters[s].data,l=r.filters[s].label,d=r.filters[s].value;if("Parent"!==d&&d!==r.changedDropdown&&!t("."+d).hasClass("unchanged")){var o=t('<div class="products-filter__form-item '+d+'"><label class="products-filter__label">'+l+"</label></div>"),n=t("<select class='products-filter__select dynamic' data-label='"+l+"' id='"+d+"' name='"+d+"'></select>");t(n).append("<option value=''>ALL</option>");for(var c=i.length-1;c>=0;c--)1===i.length?t(n).append("<option value='"+i[c]+"' selected>"+i[c]+"</option>"):t(n).append("<option value='"+i[c]+"'>"+i[c]+"</option>");t(o).append(n),t(".products-filter__list").append(o)}}t(".products-filter__form-item").each(function(){if(!t(this).hasClass("unchanged")){var e=t(this).attr("class").split(" ")[1];t("."+e+" .products-filter__select").wrap('<div class="products-filter__select-wrap select"></div>')}}),t(".products-filter__select.dynamic").on("change",a)}if(r.productIDS){for(var u=[],p=0;p<r.productIDS.length;p++)u.push(r.productIDS[p]);t("#current-ids").attr("data-query-ids",u)}productsSlider.productsSlider(),ellipsis.enableEllipsis()}function r(r){t(".products-filter__spinner").show(),t(".products-filter__list").hide(),t(".product-results").addClass("product-results--loading"),t(".product-results").html('<div class="center-align"><div class="spinner"></div></div>');for(var a=["year","make","model","drivetype"],l=[],d=0;d<a.length;d++)l.push(a[d]+"="+encodeURIComponent(t("#"+a[d]).val()));for(var o=t("#current-filters .products-filter"),n=o.length-1;n>=0;n--){var c=t(o[n]).data("filter"),u=t(o[n]).data("value");l.push(c+"="+encodeURIComponent(u))}var p=s+"?"+l.join("&");t.ajax({url:p,type:"post",data:{action:"get_product_filter",diffIDSelected:i,changedDropdown:r,parentValue:t("#Parent").val(),categoryValue:t("#Category").val(),uJointTypeValue:t("#UJointType").val(),source:"diff-wizard",selectedSort:t("#orderby").val(),nonce:t("#product_filter_nonce").val()},success:e})}function a(){var e=t(this).attr("id");""===t(this).val()?t(".current-filter--"+t(this).attr("id")).remove():"Parent"===t(this).attr("id")||"Category"===t(this).attr("id")||"GearRatio"===t(this).attr("id")?(t("#current-filters .products-filter").remove(),t("#current-filters").append("<p class='products-filter current-filter--"+t(this).attr("id")+"' data-filter='"+t(this).attr("id")+"' data-value='"+t(this).val()+"'></p>")):t("#current-filters").append("<p class='products-filter current-filter--"+t(this).attr("id")+"' data-filter='"+t(this).attr("id")+"' data-value='"+t(this).val()+"'></p>"),r(e)}var s=wpAjax.ajax_url,i=urlParamChecker.getUrlParameter("diffid");t(document).ready(function(){i&&r(),t(".products-filter__select.static").on("change",a)}),t(".products-filter__reset").on("click",function(){t(".products-filter__form-item select").val(""),t("#current-filters").empty(),r()})});