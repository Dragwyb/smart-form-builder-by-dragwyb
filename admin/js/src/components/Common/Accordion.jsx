import React, { useState } from 'react';
import { Dashicon } from '@wordpress/components';

const Accordion = ({ items }) => {
    const [openItems, setOpenItems] = useState(new Set());

    const toggleItem = (index) => {
        const newOpenItems = new Set(openItems);
        if (newOpenItems.has(index)) {
            newOpenItems.delete(index);
        } else {
            newOpenItems.add(index);
        }
        setOpenItems(newOpenItems);
    };

    return (
        <div className="dragwyb-accordion">
            {items.map((item, index) => (
                <div
                    key={index}
                    className={`dragwyb-accordion__item ${openItems.has(index) ? 'is-open' : ''}`}
                >
                    <button
                        className="dragwyb-accordion__header"
                        onClick={() => toggleItem(index)}
                    >
                        <span className="dragwyb-accordion__title">{item.title}</span>
                        <Dashicon icon={openItems.has(index) ? 'arrow-up-alt2' : 'arrow-down-alt2'} />
                    </button>
                    {openItems.has(index) && (
                        <div className="dragwyb-accordion__content">
                            {item.content}
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
};

export default Accordion; 