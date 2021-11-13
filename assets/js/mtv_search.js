jQuery(document).ready(function() {

    liveSearch();
    mtv_auto_trigger();

    jQuery(document).on('click', '#more-results-button', function() {
        jQuery('#search-full-screen form#mtv-form #submit').trigger('click');
    });
    if (mtv_search_vars.trigger !== '') {
        jQuery(document).on('click', mtv_search_vars.trigger, function() {
            jQuery('body').toggleClass('full-screen-open');
            jQuery('body').toggleClass('full-screen-open-left');
        });
    }

});

function mtv_close_search() {
    jQuery('body').toggleClass('full-screen-open');
    jQuery('body').toggleClass('full-screen-open-left');
}

function liveSearch() {
    jQuery(document).on('keypress', 'input[name="searchtext"]', function(e) {

        if (e.which == 13) {
            e.preventDefault();
            mtv_search();
        }

    });

    /**
     * code for live search
     */
    var typingTimer; //timer identifier
    var doneTypingInterval = 250; //time in ms, 5 second for example
    var $input = jQuery('input[name="searchtext"]');

    //on keyup, start the countdown
    $input.on('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(mtv_search, doneTypingInterval);
    });

    //on keydown, clear the countdown 
    $input.on('keydown', function() {
        clearTimeout(typingTimer);
    });
}

function changeSearchContainer(wrap) {
    var id = jQuery(wrap).attr('id');
    var fullScreen = jQuery('body').hasClass('full-screen-open');
    var container = '#search-full-screen';
    if (!fullScreen) {
        container = '#page';
    }
    jQuery(container + ' #' + id).toggleClass('active');
    switch (id) {
        case 'filter-trigger':
            jQuery(container + ' #search_form_filter').toggleClass('active');
            jQuery(container + ' #search_form_resutls').toggleClass('active');

            break;
    }
}

function mtv_auto_trigger() {
    if (jQuery('#page-main-content #search_form').attr('data-trigger') == 1) {
        mtv_search();
    }
}

function mtv_search() {
    var fullScreen = jQuery('body').hasClass('full-screen-open');
    var container = '#search-full-screen';

    if (!fullScreen) {
        container = '#page';


    }

    if (jQuery(container + ' #search_form_filter').hasClass('active')) {
        changeSearchContainer(jQuery(container + ' #filter-trigger'));
    }
    mtv_search_query(container);

}

function mtv_search_query(container) {

    var loading = container + ' #search-results';
    mtv_loading(loading, true),
        jQuery.ajax({
            type: "GET",
            async: true,
            cache: false,
            data: jQuery(container + ' #mtv-form').serializeArray(),
            url: awmGlobals.url + "/wp-json/mtv-search/search/",
            success: function(response) {
                mtv_loading(loading, false);
                jQuery(container + ' #search-results').html(response);
                jQuery(document).trigger('mtv_search_results');
            }
        });
}

function mtv_loading(div, action) {

    if (action) {
        jQuery(div).addClass('mtv-on-load');
        var html = jQuery("#mtv-loading").html();
        jQuery(div).html(html);
    } else {
        jQuery(div).removeClass('mtv-on-load');
        jQuery(div + " .loading-wrapper").fadeOut('slow');
    }
}

function disableCheckboxes() {
    var fullScreen = jQuery('body').hasClass('full-screen-open');
    var container = '#search-full-screen';
    if (!fullScreen) {
        container = '#page-main-content';
    }
    jQuery(container + ' #mtv-form input[type="checkbox"]').prop("checked", false);
    changeSearchContainer(jQuery(container + ' #filter-trigger'));
    mtv_search();
}

function newSearch() {
    var fullScreen = jQuery('body').hasClass('full-screen-open');
    var container = '#search-full-screen';
    if (!fullScreen) {
        container = '#page-main-content';
    }
    changeSearchContainer(jQuery(container + ' #filter-trigger'));
    mtv_search();
}