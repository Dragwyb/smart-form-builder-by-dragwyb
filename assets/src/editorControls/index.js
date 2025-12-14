
import '../../sass/editorControls.scss';
import TextControl from './textControl';
import SelectControl from './selectControl';
import TextareaControl from './textareaControl';
import SwitcherControl from './switcherControl';
import DimensionsControl from './dimensionsControl';
import RadioControl from './RadioControl';
import SliderControl from './SliderControl';
import NumberControl from './numberControl';
import ColorControl from './colorControl';
import TabsControl from './tabsControl';
import SectionControl from './sectionControl';
import RepeaterControl from './repeaterControl/index';
import PopoverToggleControl from './popoverToogleControl';
import FontsControl from './fontsControl';
import ChooseControl from './chooseControl';
import UrlControl from './urlControl';
import GalleryControl from './galleryControl';
import IconControl from './iconControl';

const initializeControls = () => {
    const defaultControls = {
        'text': TextControl,
        'select': SelectControl,
        'textarea': TextareaControl,
        'switcher': SwitcherControl,
        'dimensions': DimensionsControl,
        'radio': RadioControl,
        'slider': SliderControl,
        'number': NumberControl,
        'color': ColorControl,
        'tabs': TabsControl,
        'section': SectionControl,
        'repeater': RepeaterControl,
        'popover-toggle': PopoverToggleControl,
        'fonts': FontsControl,
        'choose': ChooseControl,
        'url': UrlControl,
        'gallery': GalleryControl,
        'icon': IconControl
    }

    Object.keys(defaultControls).map(key => DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/ControlRender/' + key, () => { return defaultControls[key] }))
}

jQuery(document).on('Dragwyb:editorInit', () => {
    initializeControls();
});