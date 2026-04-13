import { MdDesktopMac, MdTabletAndroid, MdPhoneAndroid } from 'react-icons/md';
import { CiMobile3 } from "react-icons/ci";
import { IoTvOutline } from "react-icons/io5";
import { IoIosTabletPortrait } from "react-icons/io";

import { useSelector } from 'react-redux';
import { __ } from '@wordpress/i18n';

const ResponsiveDevices = ({ Utils, style = 'default' }) => {
    const responsiveType = useSelector((state) => state.responsiveType);
    const iframeNode = useSelector((state) => state.iframeEle);

    const setWidth = (type) => {
        let width = iframeNode.defaultView.innerWidth;

        switch (type) {
            case 'tablet':
                width = 768;
                break;
            case 'mobile':
                width = 375;
                break;
            case 'desktop':
                width = 1024;
                break;
        }

        Utils.updateResponsiveType({ responsiveType: width });
    }

    return (
        <div className={`dragwyb-editor__responsive-devices responsive-devices-${style}`}>
            <button className={responsiveType >= 1024 ? 'active' : ''} onClick={() => setWidth('desktop')} title={__('Desktop', 'smart-form-builder-by-dragwyb')}><IoTvOutline /></button>
            <button className={responsiveType >= 768 && responsiveType < 1024 ? 'active' : ''} onClick={() => setWidth('tablet')} title={__('Tablet', 'smart-form-builder-by-dragwyb')}><IoIosTabletPortrait /></button>
            <button className={responsiveType < 768 ? 'active' : ''} onClick={() => setWidth('mobile')} title={__('Mobile', 'smart-form-builder-by-dragwyb')}><CiMobile3 /></button>
        </div>
    );
};

export default ResponsiveDevices;