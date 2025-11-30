import React, { useState, useRef, useEffect, useMemo } from "react";

const GroupedSelect = ({
    options,
    value,
    onChange,
    placeholder = "Select...",
    searchInput = false,
    renderOption,
    listRef,
    className
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [dropdownPosition, setDropdownPosition] = useState("bottom");
    const [searchQuery, setSearchQuery] = useState("");
    const [highlightedIndex, setHighlightedIndex] = useState(-1);

    const containerRef = useRef(null);
    const inputRef = useRef(null);
    const internalListRef = useRef(null); // Fallback if no external listRef provided
    const itemRefs = useRef(new Map());

    // Use external ref if provided, otherwise internal
    const activeListRef = listRef || internalListRef;

    const filteredGroups = useMemo(() => {
        if (!searchQuery || !searchInput) return options;
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

    const handleSelect = (option) => {
        onChange(option);
        setIsOpen(false);
    };

    const scrollToHighlighted = (index) => {
        const item = itemRefs.current.get(index);

        if (index === -1) {
            const item = itemRefs.current.get(0);

            if (item) {
                const dropdownWrapper = item.closest('.dragwyb-select-options')

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
        console.log(e);
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
                scrollToHighlighted(nextIndex === 0 ? -1 : nextIndex);
                break;
            case "ArrowUp":
                e.preventDefault();
                const prevIndex = highlightedIndex > 0 ? highlightedIndex - 1 : flatVisibleOptions.length - 1;
                setHighlightedIndex(prevIndex);
                scrollToHighlighted(highlightedIndex > prevIndex ? (prevIndex - 1) : prevIndex);
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
    }

    return (
        <div className={`dragwyb-select dragwyb-select-group${isOpen ? " is-open" : ""} position-${dropdownPosition}${className ? " " + className : ""}`} ref={containerRef} onKeyDown={handleKeyDown}>
            <div className="dragwyb-select-trigger" onClick={toggleDropdown}>
                <span className={`dragwyb-select-value ${!value ? 'is-placeholder' : ''}`}>
                    {getDisplayLabel()}
                </span>
                <i className="dashicons dashicons-arrow-down-alt2"></i>
            </div>

            {/* Dropdown */}
            {isOpen && (
                <div className={`dragwyb-select-dropdown position-${dropdownPosition}`}>
                    {searchInput && (
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
                                autoFocus
                            />
                        </div>
                    )}

                    {/* We pass the ref here so Parent can observe scrolling */}
                    <ul className="dragwyb-select-options" ref={activeListRef}>
                        {filteredGroups.length > 0 ? (
                            filteredGroups.map((group, groupIndex) => (
                                <React.Fragment key={group.label || groupIndex}>
                                    <li className="dragwyb-group-label">{group.label}</li>

                                    {group.options.map((option) => {
                                        const flatIndex = flatVisibleOptions.indexOf(option);
                                        const isHighlighted = flatIndex === highlightedIndex;
                                        const isSelected = value === option.value;

                                        // Common props for the option
                                        const optionProps = {
                                            key: option.value,
                                            className: `dragwyb-option ${isSelected ? "selected" : ""} ${isHighlighted ? "highlighted" : ""}`,
                                            onClick: () => handleSelect(option),
                                            onMouseMove: () => setHighlightedIndex(flatIndex),
                                            // Pass the ref function so we can scroll to item
                                            ref: (el) => itemRefs.current.set(flatIndex, el)
                                        };

                                        // 1. If Parent provided a custom renderer, use it!
                                        if (renderOption) {
                                            return renderOption({
                                                option,
                                                ...optionProps,
                                                isSelected,
                                                isHighlighted
                                            });
                                        }

                                        // 2. Otherwise, render default LI
                                        return (
                                            <li {...optionProps}>
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

export default GroupedSelect;