jQuery(function ($) {
    var $body = $('body'),
        $backdrop = $('<div class="slidePanel-wrapper" style="display:none"/>');

    var is_rtl = jQuery('html').attr('dir') == 'rtl';
    $(document).on("click", "[data-toggle=slidePanel]", function (e) {
        e.stopPropagation();

        $btn = $(this);
        $.slidePanel.show({url: $(this).data("url"), settings: {cache: false}}, {
            direction: is_rtl ? 'left' : 'right',
            template: function (options) {
                return '<div class="' + options.classes.base + " " + options.classes.base + "-" + options.direction + '"><div class="' + options.classes.base + '-scrollable"><div><div class="' + options.classes.content + '"></div></div></div><div class="' + options.classes.base + '-handler"></div></div>'
            }, afterLoad: function () {
                this.$panel.find('.preloader').hide();
                var call = $btn.attr('data-event');
                if (call != undefined) {
                    var fn = window[call];
                    fn(this.$panel);
                } else {
                    if (typeof bookmeSidePanelLoaded != "undefined") {
                        bookmeSidePanelLoaded(this.$panel);
                    }
                }

            }, beforeLoad: function () {
                this.$panel.find('.preloader').show();
                $body.css('overflow-y', 'hidden');
                $body.append($backdrop);
                $('.slidePanel-wrapper').fadeIn();
            }, afterHide: function () {
                $body.css('overflow-y', 'auto');
            }, beforeHide: function () {
                $('.slidePanel-wrapper').fadeOut(300, function () {
                    $(this).remove();
                });
            },
            closeSelector: ".slidePanel-close",
            mouseDragHandler: ".slidePanel-handler",
            loading: {
                template: function (options) {
                    return '<div class="' + options.classes.loading + '"><div class="cssload-speeding-wheel"></div></div>'
                }, showCallback: function (options) {
                    this.$el.addClass(options.classes.loading + "-show")
                }, hideCallback: function (options) {
                    this.$el.removeClass(options.classes.loading + "-show")
                }
            }
        });
    }).on("click", ".slidePanel-wrapper", function (e) {
        $.slidePanel.hide();
    });
});
