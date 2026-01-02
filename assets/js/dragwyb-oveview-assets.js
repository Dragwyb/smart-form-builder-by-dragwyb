jQuery(document).ready(function ($) {
    (function ($) {
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

            const data = {
                action: 'dragwyb_clean_form_cache',
                form_id: formId,
                nonce: nonce,
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
                            const button = document.querySelector('table.table-view-list.dragwyb-forms tbody tr td.clean_cache.column-clean_cache button[id="clean-cache-' + formId + '"]');
                            button.disabled = true;
                            button.classList.add('button');
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
                    console.log(response)
                    alert(response.data.message);
                },
            });
        });
    })(jQuery);
});