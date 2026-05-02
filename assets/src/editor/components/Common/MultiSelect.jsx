import React, { useState, useRef, useEffect } from "react";

const MultiSelect = ({
    options,
    value = [],
    onChange,
    placeholder = "Select...",
    className
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [dropdownPosition, setDropdownPosition] = useState("bottom");
    const [searchQuery, setSearchQuery] = useState("");
    const [highlightedIndex, setHighlightedIndex] = useState(-1);

    const containerRef = useRef(null);
    const inputRef = useRef(null);
    const itemRefs = useRef(new Map());

    const toggleDropdown = () => {
        if (isOpen) {
            setIsOpen(false);
            return;
        }

        if (containerRef.current) {
            const rect = containerRef.current.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const dropdownHeight = 250;

            if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                setDropdownPosition("top");
            } else {
                setDropdownPosition("bottom");
            }
        }
        setIsOpen(true);
    };

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (containerRef.current && !containerRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    useEffect(() => {
        if (isOpen && inputRef.current) {
            inputRef.current.focus();
        }
        if (!isOpen) {
            setSearchQuery("");
            setHighlightedIndex(-1);
        }
    }, [isOpen]);

    const handleSelect = (optionKey) => {
        let newValue = Array.isArray(value) ? [...value] : [];
        if (newValue.includes(optionKey)) {
            newValue = newValue.filter(item => item !== optionKey);
        } else {
            newValue.push(optionKey);
        }
        onChange(newValue);
    };

    const handleRemove = (e, optionKey) => {
        e.stopPropagation();
        let newValue = Array.isArray(value) ? [...value] : [];
        newValue = newValue.filter(item => item !== optionKey);
        onChange(newValue);
    };

    const getFilteredOptions = () => {
        if (!searchQuery) return options;
        const filterOptions = {};
        Object.keys(options).map((opt) => {
            if (opt.toLowerCase().includes(searchQuery.toLowerCase()) || options[opt].toLowerCase().includes(searchQuery.toLowerCase())) {
                filterOptions[opt] = options[opt];
            }
        });

        return filterOptions;
    };

    const scrollToHighlighted = (index) => {
        const item = itemRefs.current.get(index);

        if (index === -1) {
            const item0 = itemRefs.current.get(0);
            if (item0) {
                const dropdownWrapper = item0.closest('.dragwyb-multiselect-options');
                if (dropdownWrapper) {
                    dropdownWrapper.scrollTop = 0;
                    return;
                }
            }
        }

        if (item) {
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    };

    const handleKeyDown = (e) => {
        if (!isOpen) {
            if (e.key === "Enter" || e.key === "ArrowDown") {
                e.preventDefault();
                toggleDropdown();
            }
            return;
        }

        const filteredOptions = getFilteredOptions();
        const filteredKeys = Object.keys(filteredOptions);

        switch (e.key) {
            case "ArrowDown":
                e.preventDefault();
                const nextIndex = highlightedIndex < filteredKeys.length - 1 ? highlightedIndex + 1 : 0;
                setHighlightedIndex(nextIndex);
                scrollToHighlighted(nextIndex === 0 ? -1 : nextIndex);
                break;
            case "ArrowUp":
                e.preventDefault();
                const prevIndex = highlightedIndex > 0 ? highlightedIndex - 1 : filteredKeys.length - 1;
                setHighlightedIndex(prevIndex);
                scrollToHighlighted(highlightedIndex > prevIndex ? (prevIndex - 1) : prevIndex);
                break;
            case "Enter":
                e.preventDefault();
                if (filteredKeys[highlightedIndex]) handleSelect(filteredKeys[highlightedIndex]);
                break;
            case "Escape":
                setIsOpen(false);
                break;
            default: break;
        }
    };

    const filteredOptions = getFilteredOptions();
    const filteredOptionsCount = Object.keys(filteredOptions).length;

    const selectedItems = Array.isArray(value) ? value : [];

    return (
        <div className={`dragwyb-multiselect${isOpen ? " is-open" : ""} position-${dropdownPosition}${className ? " " + className : ""}`} ref={containerRef} onKeyDown={handleKeyDown}>
            <div className="dragwyb-multiselect-trigger" onClick={toggleDropdown}>
                <div className="dragwyb-multiselect-values">
                    {selectedItems.length > 0 ? (
                        selectedItems.map((item) => (
                            options[item] ?
                                <span key={item} className="dragwyb-multiselect-tag">
                                    {options[item]}
                                    <span className="dragwyb-multiselect-tag-remove dashicons dashicons-no-alt" onClick={(e) => handleRemove(e, item)}></span>
                                </span>
                                : ""
                        ))
                    ) : (
                        <span className="dragwyb-multiselect-placeholder is-placeholder">{placeholder}</span>
                    )}
                </div>
                <div className="dragwyb-multiselect-actions">
                    <i className="dashicons dashicons-arrow-down-alt2"></i>
                </div>
            </div>

            {isOpen && (
                <div className={`dragwyb-multiselect-dropdown position-${dropdownPosition}`}>
                    <div className="dragwyb-multiselect-search">
                        <input
                            ref={inputRef}
                            type="text"
                            value={searchQuery}
                            onChange={(e) => {
                                setSearchQuery(e.target.value);
                                setHighlightedIndex(0);
                            }}
                            placeholder="Search..."
                            onClick={(e) => e.stopPropagation()}
                            autoFocus
                        />
                    </div>

                    <ul className="dragwyb-multiselect-options">
                        {filteredOptionsCount > 0 ? (
                            Object.keys(filteredOptions).map((key, index) => {
                                const isSelected = selectedItems.includes(key);
                                const isHighlighted = index === highlightedIndex;

                                return (
                                    <li
                                        key={key}
                                        className={`dragwyb-option ${isSelected ? "selected" : ""} ${isHighlighted ? "highlighted" : ""}`}
                                        onClick={() => handleSelect(key)}
                                        onMouseMove={() => setHighlightedIndex(index)}
                                        ref={(el) => itemRefs.current.set(index, el)}
                                    >
                                        <div className="dragwyb-option-checkbox">
                                            {isSelected ? <span className="dashicons dashicons-yes"></span> : <span className="dragwyb-option-checkbox-empty"></span>}
                                        </div>
                                        {options[key]}
                                    </li>
                                );
                            })
                        ) : (
                            <li className="dragwyb-no-results">No results found</li>
                        )}
                    </ul>
                </div>
            )}
        </div>
    );
};

export default MultiSelect;
