/**
 * Individual Form Handler Class
 */
class DragwybRangeSlider extends DragwybBuilder.DragwybFormFrontendBase {
    bindElements() {
        this.elements.$form = this.$container.is('form') ? this.$container : this.$container.find('form.dragwyb-form');
    }

    init() {
        const $sliders = this.$container.find('.dragwyb-custom-range-container');

        $sliders.each((_, el) => {
            this.initSlider(jQuery(el));
        });
    }

    initSlider($slider) {
        const $input = $slider.find('input[type="range"]');
        const $progress = $slider.find('.dragwyb-range-progress');
        const $thumb = $slider.find('.dragwyb-range-thumb');

        if (!$input.length) return;

        const updateVisuals = () => {
            const min = parseFloat($input.attr('min')) || 0;
            const max = parseFloat($input.attr('max')) || 100;
            const val = parseFloat($input.val()) || 0;

            let percentage = ((val - min) / (max - min)) * 100;
            if (percentage < 0) percentage = 0;
            if (percentage > 100) percentage = 100;

            $progress.css('width', `${percentage}%`);
            $thumb.css('left', `${percentage}%`);
        };

        // Initial update
        updateVisuals();

        // Bind input event for real-time dragging updates
        $input.on('input', () => {
            updateVisuals();
        });

        // Ensure thumb gets focus styling when input is focused
        $input.on('focus', () => {
            $slider.addClass('dragwyb-range-focused');
        }).on('blur', () => {
            $slider.removeClass('dragwyb-range-focused');
        });
    }
}


export default DragwybRangeSlider;
