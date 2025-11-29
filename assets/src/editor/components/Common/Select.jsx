import React, { Component, createRef } from "react";

class Select extends Component {
    constructor(props) {
        super(props);
        this.state = {
            isOpen: false,
            dropdownPosition: "bottom",
            searchQuery: "",
            highlightedIndex: 0,
        };

        this.containerRef = createRef();
        this.listRef = createRef();
        if (this.props.searchInput) this.searchInputRef = createRef();
        this.optionsRef = []; // Array of refs for individual options
    }

    componentDidMount() {
        document.addEventListener("mousedown", this.handleClickOutside);
    }

    componentWillUnmount() {
        document.removeEventListener("mousedown", this.handleClickOutside);
    }

    componentDidUpdate(prevProps, prevState) {
        // When opening, focus the search input and reset highlight
        if (this.state.isOpen && !prevState.isOpen && this.props.searchInput) {
            if (this.searchInputRef.current) {
                this.searchInputRef.current.focus();
            }
            this.resetHighlight();
        }
    }

    handleClickOutside = (event) => {
        if (this.containerRef.current && !this.containerRef.current.contains(event.target)) {
            this.setState({ isOpen: false });
        }
    };

    toggleDropdown = () => {
        if (this.state.isOpen) {
            this.setState({ isOpen: false });
            return;
        }

        // Calculate Position before opening
        if (this.containerRef.current) {
            const rect = this.containerRef.current.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const dropdownHeight = 250; // Should match max-height in CSS

            // If space below is tight (< 250px) and space above is larger, open upwards
            if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                this.setState({ dropdownPosition: "top" });
            } else {
                this.setState({ dropdownPosition: "bottom" });
            }
        }
        this.setState({ isOpen: true });
    };

    handleSearchChange = (e) => {
        this.setState({ searchQuery: e.target.value, highlightedIndex: 0 });
    };

    getFilteredOptions = () => {
        const { options } = this.props;
        const { searchQuery } = this.state;
        if (!searchQuery || !this.props.searchInput) return options;

        const filterOptions = {};

        Object.keys(options).map((opt) => {
            if (opt.toLowerCase().includes(searchQuery.toLowerCase()) || options[opt].toLowerCase().includes(searchQuery.toLowerCase())) {
                filterOptions[opt] = options[opt];
            }
        });

        return filterOptions;
    };

    resetHighlight = () => {
        const { options, value } = this.props;
        // Try to find the currently selected index, otherwise 0
        const selectedIndex = Object.keys(options).findIndex(opt => options[opt] === value);

        this.setState({
            highlightedIndex: selectedIndex >= 0 ? selectedIndex : 0,
            searchQuery: "" // Optional: Clear search on re-open
        });
    }

    handleKeyDown = (e) => {
        const { isOpen, highlightedIndex } = this.state;
        const filteredOptions = this.getFilteredOptions();

        // If closed and user hits Enter/Space/Down, open it
        if (!isOpen) {
            if (e.key === "Enter" || e.key === " " || e.key === "ArrowDown") {
                e.preventDefault();
                this.setState({ isOpen: true });
            }
            return;
        }

        switch (e.key) {
            case "ArrowDown":
                e.preventDefault();
                const nextIndex =
                    highlightedIndex === filteredOptions.length - 1 ? 0 : highlightedIndex + 1;
                this.setState({ highlightedIndex: nextIndex }, this.scrollToHighlighted(nextIndex === 0 ? -1 : nextIndex));
                break;

            case "ArrowUp":
                e.preventDefault();
                const prevIndex =
                    highlightedIndex === 0 ? filteredOptions.length - 1 : highlightedIndex - 1;
                this.setState({ highlightedIndex: prevIndex }, this.scrollToHighlighted(prevIndex === 0 ? -1 : prevIndex));
                break;

            case "Enter":
                e.preventDefault();
                const optionKeys = Object.keys(filteredOptions);
                if (optionKeys[highlightedIndex]) {
                    this.handleSelect(optionKeys[highlightedIndex]);
                }
                break;

            case "Escape":
                this.setState({ isOpen: false });
                break;

            case "Tab":
                this.setState({ isOpen: false });
                break;

            default:
                break;
        }
    };

    // Logic to ensure the highlighted item stays visible in the scrollable UL
    scrollToHighlighted = (index) => {
        const list = this.listRef.current;
        const highlightedItem = list.children[index];

        if (index === -1) {
            const highlightedItem = list.children[0];

            if (highlightedItem) {
                const dropdownWrapper = highlightedItem.closest('.dragwyb-select-options')

                if (dropdownWrapper) {
                    dropdownWrapper.scrollTop = 0;
                    return;
                }
            }
        }

        if (highlightedItem && list) {
            const itemTop = highlightedItem.offsetTop;
            const itemBottom = itemTop + highlightedItem.clientHeight;
            const listTop = list.scrollTop;
            const listBottom = listTop + list.clientHeight;

            if (itemBottom > listBottom) {
                list.scrollTop = itemBottom - list.clientHeight;
            } else if (itemTop < listTop) {
                list.scrollTop = itemTop;
            }
        }
    };

    handleSelect = (option) => {
        this.props.onChange(option);
        this.setState({ isOpen: false, searchQuery: "" });
    };

    render() {
        const { options, value, placeholder, searchInput = false } = this.props;
        const { isOpen, searchQuery, highlightedIndex, dropdownPosition } = this.state;

        const filteredOptions = this.getFilteredOptions();
        const filteredOptionsCount = Object.keys(filteredOptions).length;

        const selectedOption = Object.keys(options).find((opt) => opt === value);

        return (
            <div
                className={`dragwyb-select ${isOpen ? "is-open" : ""} position-${dropdownPosition}`}
                ref={this.containerRef}
                onKeyDown={this.handleKeyDown}
            >
                {/* 1. The Trigger Box */}
                <div className="dragwyb-select-trigger" onClick={this.toggleDropdown}>
                    <span className={`dragwyb-select-value ${!selectedOption ? 'is-placeholder' : ''}`}>
                        {selectedOption ? options[selectedOption] : (placeholder || "Select...")}
                    </span>
                    <i className="dashicons dashicons-arrow-down-alt2"></i>
                </div>

                {/* 2. The Dropdown Menu */}
                {isOpen && (
                    <div className={`dragwyb-select-dropdown position-${dropdownPosition}`}>
                        {/* Search Input */}
                        {searchInput && <div className="dragwyb-select-search">
                            <input
                                ref={this.searchInputRef}
                                type="text"
                                value={searchQuery}
                                onChange={this.handleSearchChange}
                                placeholder="Search..."
                                onClick={(e) => e.stopPropagation()} // Prevent triggering close
                            />
                        </div>}

                        {/* Options List */}
                        <ul className="dragwyb-select-options" ref={this.listRef}>
                            {filteredOptionsCount > 0 ? (
                                Object.keys(filteredOptions).map((key, index) => {
                                    const isSelected = filteredOptions[key] === value;
                                    const isHighlighted = index === highlightedIndex;

                                    return (
                                        <li
                                            key={key}
                                            className={`dragwyb-option ${isSelected ? "selected" : ""} ${isHighlighted ? "highlighted" : ""}`}
                                            onClick={() => this.handleSelect(filteredOptions[key])}
                                            onMouseMove={() => this.setState({ highlightedIndex: index })}
                                        >
                                            {filteredOptions[key]}
                                            {isSelected && <i className="dashicons dashicons-yes"></i>}
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
    }
}

export default Select;