"use strict";jQuery(function(e){function a(){for(var a=["cat-make","cat-model","cat-drivetype","cat-category"],t=0;t<a.length;t++)e("#"+a[t]).val()&&(e("#"+a[t]).prop("disabled",!1),e("#"+a[t]).parent(".products-filter__select").removeClass("select--disabled"))}function t(a){var t=JSON.parse(a);loadingState.isLoading(!1),e("#cat-"+t.dropdown).addClass("active"),t.diffdata&&1===t.diffdata.length&&urlParamChecker.getUrlParameter("cat-make")&&!m?(e(".products-filter__form").append('<input id="diffid" type="hidden" name="diffid"  value="" />'),e("#diffid").val(t.diffdata[0].diffid),e(".products-filter__form").trigger("submit")):t.diffdata&&1===t.diffdata.length&&(e(".products-filter__form").append('<input id="diffid" type="hidden" name="diffid"  value="" />'),e("#diffid").val(t.diffdata[0].diffid));var r=this.dropdownIndex,d=this.fields;if(t.success!==!0)return void alert("There was an error with your request, please try again.");if(t.dropdown){for(;"cat-"+t.dropdown!==d[r];)e("#"+d[r]+' option[value!=""]').remove(),e("#"+d[r]).append("<option value='"+t.input[d[r]]+"'>"+t.input[d[r]]+"</option>"),e("#"+d[r]).val(t.input[d[r]]),e("#"+d[r]).prop("disabled",!1),e("#"+d[r]).parent(".products-filter__select").removeClass("select--disabled"),r++;for(e("#cat-"+t.dropdown+' option[value!=""]').remove(),r=t.dropdowndata.length-1;r>=0;r--)e("#cat-"+t.dropdown).append("<option value='"+t.dropdowndata[r]+"'>"+t.dropdowndata[r]+"</option>");1===t.dropdowndata.length?e("#cat-"+t.dropdown).val(t.dropdowndata[0]):0===t.dropdowndata.length&&(e(".products-filter-dropdown").prop("disabled",!1),e(".products-filter__select").removeClass("select--disabled"),e(".products-filter__submit").prop("disabled",!1)),e("#cat-"+t.dropdown).prop("disabled",!1),e("#cat-"+t.dropdown).parent(".products-filter__select").removeClass("select--disabled")}if(t.diffdata&&(e(".products-filter-dropdown").removeClass("active"),e(".products-filter__submit").prop("disabled",!1)),e("#extra-filters").empty(),e("#differential-list").empty(),t.filters&&t.filters.length>=1){e("#extra-filters").show(),e(".diffwizard-results").addClass("diffwizard-results--multiple-diffs"),e("#extra-filters").append('<h3 class="diffwizard-results__title">Filter Your results</h3>');for(var s=t.filters.length-1;s>=0;s--){var f=t.filters[s].data,o=t.filters[s].label,n=t.filters[s].value,p=e("<select class='diffwizard-results__select' data-label='"+o+"' id='"+n+"'></select>");for(e(p).append("<option value=''>ALL</option>"),r=f.length-1;r>=0;r--)e(p).append("<option value='"+f[r]+"'>"+f[r]+"</option>");e("#extra-filters").append("<div class='diffwizard-results__label'>"+o+"</div>"),e("#extra-filters").append(p)}e(".diffwizard-results__select").wrap("<div class='diffwizard-results__select-wrap select'></div>"),e(".diffwizard-results__select").on("change",i)}else e(".extra-filters-results").remove();if(t.diffdata){var c=window.location.pathname;for(e("#differential-list").append('<h3 class="diffwizard-results__title">Choose Your Differential</h3>'),r=t.diffdata.length-1;r>=0;r--){imageChecker.checkDiffImage("/wp-content/uploads/differential-images/"+t.diffdata[r].FullImage,"differential-item__image-"+r);var u=e("<div class='differential-item m-b-3 row' id='"+t.diffdata[r].diffid+"'></div>");u.append("<div class='col-sm-6'><img src='' class='differential-item__image differential-item__image-"+r+" img-fluid m-b-2 mx-auto' /></div>"),u.append("<div class='col-sm-6'><p class='differential-item__diffname'>"+t.diffdata[r].diffname+"</p><p class='diffwizard__diffdescription'>"+t.diffdata[r].diffdescription+"</p><a href='"+c+"/"+l+"&diffid="+t.diffdata[r].diffid+"' class='button button--slim differential-item__diff-select'>Select Differential</a></div>"),e("#differential-list").append(u)}}}function r(){for(var a=["cat-year","cat-make","cat-model","cat-drivetype","cat-category"],r=[],i=0;i<a.length;i++)r.push(a[i]+"="+encodeURIComponent(e("#"+a[i]).val()));for(var d=e(".diffwizard--current-filter"),l=d.length-1;l>=0;l--){var f=e(d[l]).data("filter"),o=e(d[l]).data("value");r.push(f+"="+o)}var n=s+"?"+r.join("&");n+="&parent-id="+e("#parent-id").val(),e.ajax({url:n,type:"get",data:{action:"product_browsing_ajax_request",source:"product-browsing",nonce:browsing.ajax_nonce},dropdownIndex:5,fields:a,success:t})}function i(){""!==e(this).val()&&(e("#extra-filters").hide(),e("#current-filters").show().append("<p class='diffwizard--current-filter current-filter--"+e(this).attr("id")+"' data-filter='"+e(this).attr("id")+"' data-value='"+e(this).val()+"'>"+e(this).data("label")+": "+e(this).val()+"<br /></p>"),e(".diffwizard--current-filter--remove").click(function(){e("#current-filters").remove(),r(),e("#extra-filters").show(),e("#current-filters").empty().hide()}),r())}function d(){var a=["cat-year","cat-make","cat-model","cat-drivetype","cat-category"];e("#extra-filters").empty(),e("#differential-list").empty(),e("#current-filters").empty();var r="";if(""===e("#cat-year").val())for(r=1;r<a.length;r++)e("#"+a[r]).val(""),e("#"+a[r]).prop("disabled",!0);var i=[],d=0;for(r=0;r<a.length;r++){if(!e("#"+a[r]).val()){d=r;break}if(i.push(a[r]+"="+encodeURIComponent(e("#"+a[r]).val())),e(this).attr("id")===a[r]){d=r+1,e(".products-filter__pre-populated #"+a[d]).val()&&(i.push(a[d]+"="+encodeURIComponent(e("#"+a[d]).val())),d++);break}}loadingState.isLoading(!0,e(".products-filter-dropdown"),e("#"+a[d]));var l=s+"?"+i.join("&");l+="&parent-id="+e("#parent-id").val();for(var f=d;f<a.length;f++)e("#"+a[f]).prop("disabled",!0),e("#"+a[f]).val(""),e(".products-filter__submit").prop("disabled",!0),e("#"+a[f]).parent(".products-filter__select").addClass("select--disabled");e.ajax({url:l,type:"get",data:{action:"product_browsing_ajax_request",source:"product-browsing",nonce:browsing.ajax_nonce},dropdownIndex:d,fields:a,success:t})}var l,s=browsing.ajax_url,f="cat-year="+urlParamChecker.getUrlParameter("cat-year"),o="cat-category="+urlParamChecker.getUrlParameter("cat-category"),n="cat-make="+urlParamChecker.getUrlParameter("cat-make"),p="cat-model="+urlParamChecker.getUrlParameter("cat-model"),c="cat-drivetype="+urlParamChecker.getUrlParameter("cat-drivetype"),u="parent-id="+urlParamChecker.getUrlParameter("parent-id"),m=urlParamChecker.getUrlParameter("diffid");l=urlParamChecker.getUrlParameter("parent-id")?"?"+f+"&"+n+"&"+p+"&"+c+"&"+o+"&&"+u:"?"+f+"&"+n+"&"+p+"&"+c+"&"+o,e(document).ready(function(){a(),urlParamChecker.getUrlParameter("cat-make")&&!urlParamChecker.getUrlParameter("diffid")&&r(),e(".products-filter-dropdown").on("change",d)})});