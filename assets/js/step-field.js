/**
 * Frontend Step Fields Navigation Class
 */
class DragwybStepFields extends DragwybBuilder.DragwybFormFrontendBase {
    bindElements() {
        this.elements.$form = this.$container.is('form') ? this.$container : this.$container.find('form.dragwyb-form');
        this.elements.$stepFields = this.elements.$form.find('.dragwyb-step-field');
    }

    init() {
        if (this.elements.$stepFields.length === 0) {
            return;
        }

        this.steps = this.elements.$stepFields;
        this.stepsCount = this.steps.length;

        // Find initial active step index (usually 0)
        this.currentStepIdx = 0;
        this.steps.each((idx, el) => {
            if (!jQuery(el).hasClass('dragwyb-step-field_hidden')) {
                this.currentStepIdx = idx;
            }
        });

        // Hide the next button on the final step page
        const $finalStep = this.steps.eq(this.stepsCount - 1);
        $finalStep.find('.dragwyb-step-next').hide();

        this.bindEvents();
        this.updateIndicators();
    }

    bindEvents() {
        // Next button click handler
        this.elements.$form.on('click', '.dragwyb-button-next', (e) => {
            e.preventDefault();
            const currentStep = this.steps.eq(this.currentStepIdx);

            if (this.validateStepFields(currentStep)) {
                this.goToStep(this.currentStepIdx + 1);
            }
        });

        // Previous button click handler
        this.elements.$form.on('click', '.dragwyb-button-prev', (e) => {
            e.preventDefault();
            this.goToStep(this.currentStepIdx - 1);
        });

        // Navigation indicator click handler (optional, allowed for completed/active steps)
        this.elements.$form.on('click', '.dragwyb-step-item', (e) => {
            const stepId = jQuery(e.currentTarget).attr('data-step');
            const $targetStepField = this.elements.$form.find(`#dragwyb-step-${stepId}`);
            if ($targetStepField.length > 0) {
                const targetIdx = this.steps.index($targetStepField);

                // Allow jumping to steps that are already completed or the next logical step
                if (targetIdx !== -1 && targetIdx <= this.currentStepIdx) {
                    this.goToStep(targetIdx);
                } else if (targetIdx === this.currentStepIdx + 1) {
                    const currentStep = this.steps.eq(this.currentStepIdx);
                    if (this.validateStepFields(currentStep)) {
                        this.goToStep(targetIdx);
                    }
                }
            }
        });

        // Intercept validation failures from standard submit
        this.elements.$form.on('submit', () => {
            setTimeout(() => {
                const $firstError = this.elements.$form.find('.dragwyb-error').first();
                if ($firstError.length > 0) {
                    const $stepPage = $firstError.closest('.dragwyb-step-field');
                    if ($stepPage.length > 0) {
                        const targetStepIdx = this.steps.index($stepPage);
                        if (targetStepIdx !== -1 && targetStepIdx !== this.currentStepIdx) {
                            this.goToStep(targetStepIdx);
                            $firstError.focus();
                        }
                    }
                }
            }, 10);
        });
    }

    validateStepFields($step) {
        const $fields = $step.find('input, select, textarea').not('[type="submit"], [type="button"], [type="hidden"]');

        // Trigger standard validation checks by simulating blur events
        $fields.each((_, el) => {
            jQuery(el).trigger('blur');
        });

        // If any error exists in this step, do not proceed
        const hasErrors = $step.find('.dragwyb-error').length > 0;

        if (hasErrors) {
            $step.find('.dragwyb-error').first().focus();
            return false;
        }

        return true;
    }

    goToStep(stepIdx) {
        if (stepIdx < 0 || stepIdx >= this.stepsCount) {
            return;
        }

        const prevStep = this.steps.eq(this.currentStepIdx);
        const nextStep = this.steps.eq(stepIdx);

        // Transition classes
        prevStep.addClass('dragwyb-step-field_hidden');
        nextStep.removeClass('dragwyb-step-field_hidden');

        this.currentStepIdx = stepIdx;

        // Scroll to form header
        jQuery('html, body').animate({
            scrollTop: this.elements.$form.offset().top - 100
        }, 300);

        this.updateIndicators();
    }

    updateIndicators() {
        const indicatorType = this.elements.$form.attr('data-step-indicator') || 'numbers';
        const activeIdx = this.currentStepIdx;

        if (indicatorType === 'progress') {
            const $fill = this.elements.$form.find('#progress-fill');
            const $text = this.elements.$form.find('#progress-text');
            const percentage = ((activeIdx + 1) / this.stepsCount) * 100;
            $fill.css('width', `${percentage}%`);
            $text.text(`Step ${activeIdx + 1} of ${this.stepsCount}`);
        } else if (indicatorType === 'numbers' || indicatorType === 'dots') {
            const $indicators = this.elements.$form.find('.dragwyb-step-indicator .dragwyb-step-item');
            $indicators.each((idx, el) => {
                const $item = jQuery(el);
                if (idx === activeIdx) {
                    $item.removeClass('completed').addClass('active');
                } else if (idx < activeIdx) {
                    $item.removeClass('active').addClass('completed');
                } else {
                    $item.removeClass('active completed');
                }
            });
        }
    }
}

// Hook into frontend form ready action
jQuery(document).on('Dragwyb:frontendInit', () => {
    DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', (container, formId) => {
        new DragwybStepFields(container, formId);
    });
});
