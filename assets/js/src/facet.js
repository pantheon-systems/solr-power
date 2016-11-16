jQuery(document).ready(function ($) {
    var the_body = $('body');
    the_body.on('change', '#solr_s', function (e) {
        $('#solr_facet input').prop('checked', false);
    });
    var search_results_div = $('#' + solr.search_results_id);
    if (0 === search_results_div.length) {
        search_results_div = $('#solr_search_results');
    }
    if (solr.allow_ajax && search_results_div.length) {
        var facets = $('#solr_facets');
        var search_form = $('#solr_facet');
        var solr_submit = function () {
            search_results_div.fadeOut();
            search_form.fadeOut();
            search_form.submit();
        };
        the_body.on('change', '.facet_check', function (e) {
            solr_submit();
        });

        the_body.on('click', '.facet_link', function (e) {
            var facet_id = $('#' + $(this).data('for'));
            if (facet_id.prop('checked')) {
                facet_id.prop('checked', false);
            } else {
                facet_id.prop('checked', true);
            }
            solr_submit();
            e.preventDefault();
        });
        the_body.on('click', '.solr_reset', function (e) {
            $('#' + $(this).data('for') + ' input').prop('checked', false);
            solr_submit();
            e.preventDefault();
        });
        search_form.submit(function (event) {
            event.preventDefault();
            var args = search_form.serializeArray();
            args.push({name: 'action', 'value': 'solr_search'});
            $.get(solr.ajaxurl, args, function (res) {
                var results = jQuery.parseJSON(res);

                $('#solr_facets').html(results.facets);
                search_results_div.html(results.posts).fadeIn();
                $("html, body").animate({scrollTop: 0}, "slow");
                $('#solr_facet').fadeIn();

            });
            return false;
        });

    }
});


