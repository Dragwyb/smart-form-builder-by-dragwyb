import { useSelector } from "react-redux";
import { useState, useEffect } from "react";

const StyleLoader = () => {
    const styleSelectors = useSelector((state) => state.styleSelectors);
    const formId = useSelector((state) => state.form.id);
    const [styleWrapper, setStyleWrapper] = useState(null);
    const iframeEle = useSelector(state => state?.iframeEle);

    const generateCssStrings = (cssSelectors) => {
        const cssCache = {};
        let cssString = "";

        Object.keys(cssSelectors).forEach(key => {
            const entry = cssSelectors[key];
            const selector = Object.keys(entry)[0];
            let rule = Object.values(entry)[0].trim();
            rule = rule.endsWith(';') ? rule : rule + ';';

            if (!cssCache[selector]) {
                cssCache[selector] = [];
            }
            cssCache[selector].push(rule);
        });

        for (const selector in cssCache) {
            if (cssCache.hasOwnProperty(selector)) {
                const rules = cssCache[selector].join(' ');
                cssString += `${selector} { ${rules} }\n`;
            }
        }
        return cssString;
    }

    useEffect(() => {

        if (iframeEle && styleSelectors && Object.keys(styleSelectors).length > 0) {
            const handler = setTimeout(() => {

                const deepClonsedStyleSelectors = { ...styleSelectors };
                const tableStyleSelectors = deepClonsedStyleSelectors['tablet'] || {};
                const mobileStyleSelectors = deepClonsedStyleSelectors['mobile'] || {};

                delete deepClonsedStyleSelectors['tablet']
                delete deepClonsedStyleSelectors['mobile']

                let cssString = generateCssStrings(deepClonsedStyleSelectors);

                if (Object.keys(tableStyleSelectors).length > 0) {
                    cssString += `@media (max-width: 768px) {
                        ${generateCssStrings(tableStyleSelectors)}
                    }`;
                }

                if (Object.keys(mobileStyleSelectors).length > 0) {
                    cssString += `@media (max-width: 480px) {
                        ${generateCssStrings(mobileStyleSelectors)}
                    }`;
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