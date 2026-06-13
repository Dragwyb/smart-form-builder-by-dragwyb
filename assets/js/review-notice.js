(
    function ($) {
        class SMFBD_Review_Form {
            constructor() {
                this.init();
            }

            init() {
                const $notice = $('.smfbd-review-notice .smfbd-review-notice-buttons button');
                $notice.on('click', this.smfbd_review_dismiss);
            }

            smfbd_review_dismiss(e) {
                e.preventDefault();
                e.stopPropagation();
                const nonce = window.smfbd_review_obj.nonce;
                const noticeWrp = jQuery(this).closest('.smfbd-review-notice');
                jQuery.ajax({
                    url: window.smfbd_review_obj.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'smfbd_review_dismiss',
                        smfbd_review_dismiss: true,
                        nonce: nonce,
                    },
                    success: function (response) {
                        noticeWrp.fadeOut(500);
                    },
                })
            }
        }

        new SMFBD_Review_Form();
    }
)(jQuery)