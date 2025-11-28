import React, { useState, useRef, useEffect, useMemo } from "react";

const SelectGroup = ({
    options,
    value,
    onChange,
    placeholder = "Select..."
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [dropdownPosition, setDropdownPosition] = useState("bottom"); // 'top' or 'bottom'
    const [searchQuery, setSearchQuery] = useState("");
    const [highlightedIndex, setHighlightedIndex] = useState(-1);

    const containerRef = useRef(null);
    const inputRef = useRef(null);
    const listRef = useRef(null);
    const itemRefs = useRef(new Map());

    // --- 1. Filter Logic (Same as before) ---
    const filteredGroups = useMemo(() => {
        if (!searchQuery) return options;
        return options.map(group => {
            const matchingOptions = group.options.filter(opt =>
                opt.label.toLowerCase().includes(searchQuery.toLowerCase())
            );
            return { ...group, options: matchingOptions };
        }).filter(group => group.options.length > 0);
    }, [options, searchQuery]);

    const flatVisibleOptions = useMemo(() => {
        return filteredGroups.flatMap(group => group.options);
    }, [filteredGroups]);

    // --- 2. Positioning Logic ---
    const toggleDropdown = () => {
        if (isOpen) {
            setIsOpen(false);
            return;
        }

        // Calculate Position before opening
        if (containerRef.current) {
            const rect = containerRef.current.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const dropdownHeight = 250; // Should match max-height in CSS

            // If space below is tight (< 250px) and space above is larger, open upwards
            if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                setDropdownPosition("top");
            } else {
                setDropdownPosition("bottom");
            }
        }
        setIsOpen(true);
    };

    // --- 3. Effects ---
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (containerRef.current && !containerRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };
        // Use 'mousedown' or 'click' depending on preference
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

    // --- 4. Handlers ---
    const handleSelect = (option) => {
        onChange(option);
        setIsOpen(false);
    };

    const scrollToHighlighted = (index) => {
        const item = itemRefs.current.get(index);
        if (item) {
            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    };

    const handleKeyDown = (e) => {
        if (!isOpen) {
            if (e.key === "Enter" || e.key === "ArrowDown") {
                e.preventDefault();
                toggleDropdown(); // Use toggle to trigger position calc
            }
            return;
        }

        switch (e.key) {
            case "ArrowDown":
                e.preventDefault();
                const nextIndex = highlightedIndex < flatVisibleOptions.length - 1 ? highlightedIndex + 1 : 0;
                setHighlightedIndex(nextIndex);
                scrollToHighlighted(nextIndex);
                break;
            case "ArrowUp":
                e.preventDefault();
                const prevIndex = highlightedIndex > 0 ? highlightedIndex - 1 : flatVisibleOptions.length - 1;
                setHighlightedIndex(prevIndex);
                scrollToHighlighted(prevIndex);
                break;
            case "Enter":
                e.preventDefault();
                if (flatVisibleOptions[highlightedIndex]) handleSelect(flatVisibleOptions[highlightedIndex]);
                break;
            case "Escape":
                setIsOpen(false);
                break;
            default: break;
        }
    };

    const getDisplayLabel = () => {
        if (!value) return placeholder;
        for (const group of options) {
            const found = group.options.find(o => o.value === value);
            if (found) return found.label;
        }
        return value;
    };

    return (
        <div
            className={`dragwyb-select ${isOpen ? "is-open" : ""} position-${dropdownPosition}`}
            ref={containerRef}
            onKeyDown={handleKeyDown}
        >
            <div className="dragwyb-select-trigger" onClick={toggleDropdown}>
                <span className={`dragwyb-select-value ${!value ? 'is-placeholder' : ''}`}>
                    {getDisplayLabel()}
                </span>
                <i className="dashicons dashicons-arrow-down-alt2"></i>
            </div>

            {isOpen && (
                <div className={`dragwyb-select-dropdown position-${dropdownPosition}`}>
                    <div className="dragwyb-select-search">
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
                        />
                    </div>

                    <ul className="dragwyb-select-options" ref={listRef}>
                        {filteredGroups.length > 0 ? (
                            filteredGroups.map((group, groupIndex) => (
                                <React.Fragment key={group.label || groupIndex}>
                                    <li className="dragwyb-group-label">{group.label}</li>
                                    {group.options.map((option) => {
                                        const flatIndex = flatVisibleOptions.indexOf(option);
                                        const isHighlighted = flatIndex === highlightedIndex;
                                        const isSelected = value === option.value;
                                        return (
                                            <li
                                                key={option.value}
                                                ref={(el) => itemRefs.current.set(flatIndex, el)}
                                                className={`dragwyb-option ${isSelected ? "selected" : ""} ${isHighlighted ? "highlighted" : ""}`}
                                                onClick={() => handleSelect(option)}
                                                onMouseEnter={() => setHighlightedIndex(flatIndex)}
                                            >
                                                {option.label}
                                                {isSelected && <span className="dashicons dashicons-yes"></span>}
                                            </li>
                                        );
                                    })}
                                </React.Fragment>
                            ))
                        ) : (
                            <li className="dragwyb-no-results">No results found</li>
                        )}
                    </ul>
                </div>
            )}
        </div>
    );
};

export default SelectGroup;