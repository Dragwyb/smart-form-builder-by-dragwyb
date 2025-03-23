<div class="dragwyb-visual-editor">
    <h3><?php esc_html_e('Visual Style Editor', 'dragwyb-form-builder'); ?></h3>
    
    <div class="dragwyb-visual-editor-tabs">
        <button type="button" class="active" data-tab="spacing">
            <?php esc_html_e('Spacing', 'dragwyb-form-builder'); ?>
        </button>
        <button type="button" data-tab="typography">
            <?php esc_html_e('Typography', 'dragwyb-form-builder'); ?>
        </button>
        <button type="button" data-tab="colors">
            <?php esc_html_e('Colors', 'dragwyb-form-builder'); ?>
        </button>
        <button type="button" data-tab="effects">
            <?php esc_html_e('Effects', 'dragwyb-form-builder'); ?>
        </button>
    </div>

    <div class="dragwyb-visual-editor-content">
        <!-- Spacing Tab -->
        <div class="dragwyb-visual-editor-tab active" data-tab="spacing">
            <div class="dragwyb-visual-spacing">
                <div class="dragwyb-spacing-control">
                    <label><?php esc_html_e('Padding', 'dragwyb-form-builder'); ?></label>
                    <div class="dragwyb-spacing-inputs">
                        <input type="number" data-spacing="padding-top" placeholder="Top">
                        <input type="number" data-spacing="padding-right" placeholder="Right">
                        <input type="number" data-spacing="padding-bottom" placeholder="Bottom">
                        <input type="number" data-spacing="padding-left" placeholder="Left">
                    </div>
                </div>
                
                <div class="dragwyb-spacing-control">
                    <label><?php esc_html_e('Margin', 'dragwyb-form-builder'); ?></label>
                    <div class="dragwyb-spacing-inputs">
                        <input type="number" data-spacing="margin-top" placeholder="Top">
                        <input type="number" data-spacing="margin-right" placeholder="Right">
                        <input type="number" data-spacing="margin-bottom" placeholder="Bottom">
                        <input type="number" data-spacing="margin-left" placeholder="Left">
                    </div>
                </div>
            </div>
        </div>

        <!-- Typography Tab -->
        <div class="dragwyb-visual-editor-tab" data-tab="typography">
            <div class="dragwyb-typography-control">
                <label><?php esc_html_e('Font Family', 'dragwyb-form-builder'); ?></label>
                <select data-typography="font-family">
                    <option value="inherit"><?php esc_html_e('Default', 'dragwyb-form-builder'); ?></option>
                    <option value="Arial, sans-serif">Arial</option>
                    <option value="'Helvetica Neue', Helvetica, sans-serif">Helvetica</option>
                    <option value="Georgia, serif">Georgia</option>
                    <option value="'Times New Roman', Times, serif">Times New Roman</option>
                </select>
            </div>

            <div class="dragwyb-typography-control">
                <label><?php esc_html_e('Font Size', 'dragwyb-form-builder'); ?></label>
                <input type="number" data-typography="font-size" min="8" max="72">
                <select data-typography="font-size-unit">
                    <option value="px">px</option>
                    <option value="em">em</option>
                    <option value="rem">rem</option>
                </select>
            </div>

            <div class="dragwyb-typography-control">
                <label><?php esc_html_e('Font Weight', 'dragwyb-form-builder'); ?></label>
                <select data-typography="font-weight">
                    <option value="normal">Normal</option>
                    <option value="bold">Bold</option>
                    <option value="300">Light</option>
                    <option value="500">Medium</option>
                    <option value="600">Semi Bold</option>
                    <option value="700">Bold</option>
                </select>
            </div>
        </div>

        <!-- Colors Tab -->
        <div class="dragwyb-visual-editor-tab" data-tab="colors">
            <div class="dragwyb-color-control">
                <label><?php esc_html_e('Text Color', 'dragwyb-form-builder'); ?></label>
                <input type="text" class="dragwyb-color-picker" data-color="color">
            </div>

            <div class="dragwyb-color-control">
                <label><?php esc_html_e('Background Color', 'dragwyb-form-builder'); ?></label>
                <input type="text" class="dragwyb-color-picker" data-color="background-color">
            </div>

            <div class="dragwyb-color-control">
                <label><?php esc_html_e('Border Color', 'dragwyb-form-builder'); ?></label>
                <input type="text" class="dragwyb-color-picker" data-color="border-color">
            </div>
        </div>

        <!-- Effects Tab -->
        <div class="dragwyb-visual-editor-tab" data-tab="effects">
            <div class="dragwyb-effects-control">
                <label><?php esc_html_e('Border Radius', 'dragwyb-form-builder'); ?></label>
                <input type="number" data-effects="border-radius" min="0">
                <span>px</span>
            </div>

            <div class="dragwyb-effects-control">
                <label><?php esc_html_e('Box Shadow', 'dragwyb-form-builder'); ?></label>
                <div class="dragwyb-shadow-inputs">
                    <input type="number" data-shadow="h-offset" placeholder="H">
                    <input type="number" data-shadow="v-offset" placeholder="V">
                    <input type="number" data-shadow="blur" placeholder="Blur">
                    <input type="number" data-shadow="spread" placeholder="Spread">
                    <input type="text" class="dragwyb-color-picker" data-shadow="color">
                </div>
            </div>

            <div class="dragwyb-effects-control">
                <label><?php esc_html_e('Opacity', 'dragwyb-form-builder'); ?></label>
                <input type="range" data-effects="opacity" min="0" max="100" step="1">
                <span class="opacity-value">100%</span>
            </div>
        </div>
    </div>

    <div class="dragwyb-visual-editor-target">
        <label><?php esc_html_e('Apply styles to:', 'dragwyb-form-builder'); ?></label>
        <select id="dragwyb-style-target">
            <option value="form"><?php esc_html_e('Entire Form', 'dragwyb-form-builder'); ?></option>
            <option value="labels"><?php esc_html_e('Labels', 'dragwyb-form-builder'); ?></option>
            <option value="inputs"><?php esc_html_e('Input Fields', 'dragwyb-form-builder'); ?></option>
            <option value="button"><?php esc_html_e('Submit Button', 'dragwyb-form-builder'); ?></option>
        </select>
    </div>
</div> 