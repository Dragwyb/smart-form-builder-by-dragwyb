import Hooks from "./hooks";

const DragwybCore = () => {
    DragwybBuilder.Hooks = Hooks;
    jQuery(document).trigger('Dragwyb:init');
}

jQuery(window).on('load',()=>{
    DragwybCore();
})

