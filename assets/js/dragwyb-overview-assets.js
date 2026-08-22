jQuery(document).ready(function ($) {
    (function ($) {

        /**
         * Moves all elements matching the noticeSelector to directly after the headerSelector,
         * and sets their display to 'block'.
         * 
         * @param {string} noticeSelector - The CSS selector for the notice elements (e.g., '.notice')
         * @param {string} headerSelector - The CSS selector for the header element (e.g., '.dragwyb-dashboard-header')
         */
        function moveNoticesAfterHeader(noticeSelector, headerSelector) {
            // 1. Find the target header element
            const header = document.querySelector(headerSelector);

            if (!header) {
                console.warn(`Could not find header: ${headerSelector}`);
                return;
            }

            // 2. Find all notice elements
            const notices = document.querySelectorAll(noticeSelector);

            if (notices.length === 0) {
                return;
            }

            // 3. Move all notices immediately after the header
            header.after(...notices);

            // 4. Set display to 'block' for each notice
            notices.forEach(notice => {
                notice.style.display = 'block';
            });
        }

        moveNoticesAfterHeader('.notice', '.dragwyb-dashboard-header');
        // Clean cache button handler
        const cacheBtn = $('table.table-view-list.dragwyb-forms tbody tr td.clean_cache.column-clean_cache button');

        cacheBtn.on('click', function (e) {
            e.preventDefault();
            let formId = $(this).attr('id').trim();
            if (!formId || !formId.startsWith('clean-cache-')) {
                return;
            }

            formId = formId.replace('clean-cache-', '');

            if (!formId || '' === formId.trim()) {
                return;
            }

            formId = parseInt(formId);

            if (isNaN(formId)) {
                return;
            }

            const nonce = $(this).data('key');
            const cleanCacheNonce = $(this).data('clean-key');

            const data = {
                action: 'dragwyb_clean_form_cache',
                form_id: formId,
                nonce: nonce,
                delete_cache_nonce: cleanCacheNonce,
            };

            $.ajax({
                url: DragwybOverviewPage.ajaxurl,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.success === true) {
                        const formId = response.data.form_id;
                        if (formId && formId > 0) {
                            const button = document.getElementById('clean-cache-' + formId);
                            if (button) {
                                button.disabled = true;
                                button.classList.add('button', 'button-small');
                            }
                            alert(response.data.message);
                        } else {
                            console.log(response);
                            alert('Invalid form id');
                        }
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function (response) {
                    console.log(response);
                    alert(response.data.message);
                },
            });
        });

        // Direct click-to-copy shortcode functionality without text selection highlight
        $(document).on('click', '.dragwyb-shortcode, .dragwyb-shortcode-value', function (e) {
            e.preventDefault();
            const $el = $(this);
            const shortcode = $el.data('shortcode') || $el.text().trim();

            if (!shortcode) {
                return;
            }

            const copyTextToClipboard = function (text) {
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(text);
                } else {
                    const $temp = $('<input>');
                    $('body').append($temp);
                    $temp.val(text).select();
                    document.execCommand('copy');
                    $temp.remove();
                    return Promise.resolve();
                }
            };

            copyTextToClipboard(shortcode).then(function () {
                if (window.getSelection) {
                    window.getSelection().removeAllRanges();
                }
                if (document.selection) {
                    document.selection.empty();
                }
                $el.blur();

                const originalTitle = $el.attr('title') || 'Click to copy shortcode';
                $el.addClass('copied').attr('title', 'Copied to clipboard!');

                setTimeout(function () {
                    $el.removeClass('copied').attr('title', originalTitle);
                }, 2000);
            });
        });
    })(jQuery);
});