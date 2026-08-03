
import '../../sass/editorControls.scss';
import TextControl from './textControl';
import SelectControl from './selectControl';
import MultiSelectControl from './multiSelectControl';
import TextareaControl from './textareaControl';
import DateControl from './dateControl';
import SwitcherControl from './switcherControl';
import DimensionsControl from './dimensionsControl';
import RadioControl from './radioControl';
import SliderControl from './sliderControl';
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
import HeadingControl from './headingControl';
import RawHtmlControl from './rawHtmlControl';
import ImageControl from './imageControl';
import WysiwygControl from './wysiwygControl';

const initializeControls = () => {
    const defaultControls = {
        'text': TextControl,
        'select': SelectControl,
        'multiselect': MultiSelectControl,
        'textarea': TextareaControl,
        'date': DateControl,
        'wysiwyg': WysiwygControl,
        'switcher': SwitcherControl,
        'dimensions': DimensionsControl,
        'radio': RadioControl,
        'raw_html': RawHtmlControl,
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
        'image': ImageControl,
        'icon': IconControl,
        'heading': HeadingControl
    }

    Object.keys(defaultControls).map(key => DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/ControlRender/' + key, () => { return defaultControls[key] }))
}

jQuery(document).on('Dragwyb:editorInit', () => {
    initializeControls();
});