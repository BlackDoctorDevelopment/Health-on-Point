(function ($) {
    'use strict';

    // Copy shortcode to clipboard on click
    $(document).on('click', '.column-hop_embed code', function () {
        var text = $(this).text();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () {
                var $el = $(this);
                $el.css('background', '#d4edda');
                setTimeout(function () { $el.css('background', ''); }, 1200);
            }.bind(this));
        }
    });

})(jQuery);
