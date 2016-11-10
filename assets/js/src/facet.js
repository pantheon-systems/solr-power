var Facet_Widget = {
    init: function () {
        var search_box = document.getElementById('solr_s');
        // Let's not worry about the search box if it doesn't exist.
        if (null !== search_box) {
            search_box.addEventListener('change', this.uncheckall);
        }

    },
    uncheckall: function () {
        var checkboxes = [];
        checkboxes = document.getElementById('solr_facet').getElementsByTagName('input');

        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].type == 'checkbox') {
                checkboxes[i].checked = false;
            }
        }
    }
};

window.onload = function () {
    Facet_Widget.init();
};

jQuery(document).ready(function ($) {
    var facets = $('#solr_facets');
    var search_form = $('#solr_facet');
    $('body').on('change', '.facet_check', function (e) {
        /* var checkboxes = [];
         checkboxes = document.getElementById('solr_facet').getElementsByTagName('input');

         for (var i = 0; i < checkboxes.length; i++) {
         if (checkboxes[i].type == 'checkbox') {
         checkboxes[i].disabled = true;
         }
         }*/
        $('#content').fadeOut();
        search_form.fadeOut();
        search_form.submit();
    });

    $('body').on('click', '.facet_link', function (e) {
        var facet_id=$('#f_' + $(this).data('for'));
        if (facet_id.is(':checked')){
            facet_id.prop('checked',false);
        }else{
            facet_id.prop('checked',true);
        }
     //   e.preventDefault();
    });

    search_form.submit(function (event) {
        event.preventDefault();
        var args = search_form.serializeArray();
        args.push({name: 'action', 'value': 'solr_search'});
        $.get('/wp-admin/admin-ajax.php', args, function (res) {
            var results = jQuery.parseJSON(res);

            $('#solr_facets').html(results.facets);
            $('#content').html(results.posts).fadeIn();
            $("html, body").animate({scrollTop: 0}, "slow");
            $('#solr_facet').fadeIn();
        });
        return false;
    });
});