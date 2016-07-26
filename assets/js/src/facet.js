var Facet_Widget = {
    init: function () {
        var search_box = document.getElementById('solr_s');
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
