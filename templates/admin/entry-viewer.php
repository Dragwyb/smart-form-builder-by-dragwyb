<?php if (!defined('ABSPATH')) exit; ?>

<div class="dragwyb-entry-viewer" style="display: none;">
    <div class="entry-viewer-overlay"></div>
    <div class="entry-viewer-content">
        <div class="entry-viewer-header">
            <h2><?php esc_html_e('Entry Details', 'dragwyb-form-builder'); ?></h2>
            <button type="button" class="close-viewer">×</button>
        </div>

        <div class="entry-viewer-body">
            <div class="entry-info">
                <div class="entry-meta">
                    <span class="entry-id"></span>
                    <span class="entry-date"></span>
                    <span class="entry-status"></span>
                </div>

                <div class="entry-actions">
                    <button type="button" class="button mark-read">
                        <?php esc_html_e('Mark as Read', 'dragwyb-form-builder'); ?>
                    </button>
                    <button type="button" class="button mark-spam">
                        <?php esc_html_e('Mark as Spam', 'dragwyb-form-builder'); ?>
                    </button>
                    <button type="button" class="button delete-entry">
                        <?php esc_html_e('Delete', 'dragwyb-form-builder'); ?>
                    </button>
                </div>
            </div>

            <div class="entry-tabs">
                <button type="button" data-tab="fields" class="active">
                    <?php esc_html_e('Fields', 'dragwyb-form-builder'); ?>
                </button>
                <button type="button" data-tab="files">
                    <?php esc_html_e('Files', 'dragwyb-form-builder'); ?>
                </button>
                <button type="button" data-tab="notes">
                    <?php esc_html_e('Notes', 'dragwyb-form-builder'); ?>
                </button>
                <button type="button" data-tab="submitter">
                    <?php esc_html_e('Submitter', 'dragwyb-form-builder'); ?>
                </button>
            </div>

            <div class="entry-tab-content">
                <!-- Fields Tab -->
                <div data-tab-content="fields" class="active">
                    <div class="entry-fields"></div>
                </div>

                <!-- Files Tab -->
                <div data-tab-content="files">
                    <div class="entry-files"></div>
                </div>

                <!-- Notes Tab -->
                <div data-tab-content="notes">
                    <div class="entry-notes">
                        <div class="notes-list"></div>
                        <div class="add-note">
                            <textarea placeholder="<?php esc_attr_e('Add a note...', 'dragwyb-form-builder'); ?>"></textarea>
                            <button type="button" class="button button-primary save-note">
                                <?php esc_html_e('Add Note', 'dragwyb-form-builder'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submitter Tab -->
                <div data-tab-content="submitter">
                    <div class="submitter-info"></div>
                </div>
            </div>
        </div>
    </div>
</div> 