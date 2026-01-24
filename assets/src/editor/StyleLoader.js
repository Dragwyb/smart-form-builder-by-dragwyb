import { useSelector } from "react-redux";
import { useState, useEffect } from "react";

const StyleLoader = () => {
    const styleSelectors = useSelector((state) => state.styleSelectors);
    const formId = useSelector((state) => state.form.id);
    const [styleWrapper, setStyleWrapper] = useState(null);
    const iframeEle = useSelector(state => state?.iframeEle);


    useEffect(() => {

        if (iframeEle) {
            const handler = setTimeout(() => {

                const cssCache = {};
                let cssString = "";

                Object.keys(styleSelectors).forEach(key => {
                    const entry = styleSelectors[key];
                    const selector = Object.keys(entry)[0];
                    const rule = Object.values(entry)[0];

                    if (!cssCache[selector]) {
                        cssCache[selector] = [];
                    }
                    cssCache[selector].push(rule);
                });

                for (const selector in cssCache) {
                    if (cssCache.hasOwnProperty(selector)) {
                        const rules = cssCache[selector].join(';');
                        cssString += `${selector} { ${rules} }\n`;
                    }
                }

                if (!styleWrapper && '' !== cssString) {
                    setStyleWrapper(iframeEle.getElementById('dragwyb-form-' + formId));
                }

                if (styleWrapper) {
                    styleWrapper.innerHTML = cssString;
                }

            }, 5);

            return () => {
                clearTimeout(handler);
            };
        }


    }, [styleSelectors, styleWrapper, iframeEle]);

    return null;
};

export default StyleLoader;