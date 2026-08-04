import React from 'react';
import { __ } from '@wordpress/i18n';
import Select from '../editor/components/Common/Select';

export default class PresetStyleControl extends DragwybEditor.editor.extends.ControlBase {
  controlName() {
    return 'preset_style';
  }

  handlePresetChange = (presetKey) => {
    const { id, Utils } = this;
    const store = window.DragwybStore || {};
    const dispatch = store.dispatch || (({ type, payload }) => { });
    const storeState = store.getState ? store.getState() : {};
    const storeStyleSelectors = storeState.styleSelectors || {};

    this.updateControlHandler(id, presetKey);

    const presetStyles = window.DragwybEditor?.presetStyle || DragwybEditor?.presetStyle || {};
    if (!presetKey || !presetStyles[presetKey]) {
      return;
    }

    const newPresetStyle = presetStyles[presetKey];

    // 1. Remove existing style selectors for style toolbar only (excluding fields styles)
    const deleteStyleKeys = (selectorsObj, responsiveType = 'desktop') => {
      if (!selectorsObj) return;
      Object.keys(selectorsObj).forEach((key) => {
        if (key.startsWith('style_')) {
          Utils.deleteStyleSelectors({ key, responsiveType });
        }
      });
    };

    deleteStyleKeys(storeStyleSelectors, 'desktop');
    if (storeStyleSelectors.tablet) {
      deleteStyleKeys(storeStyleSelectors.tablet, 'tablet');
    }
    if (storeStyleSelectors.mobile) {
      deleteStyleKeys(storeStyleSelectors.mobile, 'mobile');
    }

    // 2. Update Redux store for style toolbar
    Utils.updateToolbarSetting({ id: 'style', value: newPresetStyle });

    // 3. Apply style selectors for the new preset style controls
    const styleControls = DragwybEditor?.style?.controls || {};
    Object.keys(styleControls).forEach((controlKey) => {
      const controlConfig = styleControls[controlKey];
      const val = newPresetStyle[controlKey] !== undefined ? newPresetStyle[controlKey] : controlConfig?.default;

      if (
        val !== undefined &&
        val !== null &&
        val !== '' &&
        controlConfig?.selectors &&
        controlConfig?.selectors_placeholders
      ) {
        const uniqueSelector = `style_${controlKey}`;
        const styleSelectorsData = {
          key: uniqueSelector,
          value: val,
          selectors: controlConfig.selectors,
          placeholders: controlConfig.selectors_placeholders,
          toolbarType: 'style',
          itemId: false,
        };

        if (controlConfig.responsive_control && controlConfig.responsive_type) {
          styleSelectorsData.responsiveType = controlConfig.responsive_type;
        }

        Utils.updateStyleSelectors(styleSelectorsData);
      }
    });

    // 4. Trigger preview iframe update
    Utils.editorFormReady();

    // 5. Add history snapshot
    const formatPresetName = (key) => key.replace('-', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    if (dispatch) {
      dispatch({
        type: 'ADD_HISTORY_SNAPSHOT',
        payload: { label: `Preset Style: ${formatPresetName(presetKey)}` },
      });
    }
  };

  bind() {
    if (!this.shouldRender()) return <></>;

    const { id, settings } = this;
    const options = settings.options || {};
    const labelInline = settings.label_inline || false;
    const value = this.state.value || '';

    return (
      <div className="dragwyb-control dragwyb-control--preset-style" id={`control-${id}`}>
        <div className={`dragwyb-preset-style-row dragwyb-label-${labelInline ? 'inline' : 'block'}`}>
          <this.RenderLabel attr={{ htmlFor: id }} />
          <div className="dragwyb-preset-style-field">
            <Select
              options={options}
              value={value}
              onChange={(selectedVal) => this.handlePresetChange(selectedVal)}
              placeholder={__('Select Preset Style', 'smart-form-builder-by-dragwyb')}
            />
          </div>
        </div>
        <div className="dragwyb-preset-style-warning">
          <div className="dragwyb-preset-style-warning-icon-wrapper">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M8 1.5C4.41 1.5 1.5 4.41 1.5 8C1.5 11.59 4.41 14.5 8 14.5C11.59 14.5 14.5 11.59 14.5 8C14.5 4.41 11.59 1.5 8 1.5ZM8 13C5.24 13 3 10.76 3 8C3 5.24 5.24 3 8 3C10.76 3 13 5.24 13 8C13 10.76 10.76 13 8 13ZM7.25 4.5H8.75V8.5H7.25V4.5ZM7.25 9.5H8.75V11H7.25V9.5Z" fill="currentColor" />
            </svg>
          </div>
          <p className="dragwyb-preset-style-warning-text">
            {__(
              'This style replace all your existing styling excluding fields style only remove existing style from style toolbar and apply new styling.',
              'smart-form-builder-by-dragwyb'
            )}
          </p>
        </div>
      </div>
    );
  }

  resetControl() {
    const value = this.state.value;

    if (value && value !== 'default') {
      this.handlePresetChange('default');
    }

    this.setState({ value: undefined });
    this.updateControls(this.id, undefined);
  }
}
