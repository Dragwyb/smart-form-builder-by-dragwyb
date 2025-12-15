import React, { createRoot } from 'react';
import { __ } from '@wordpress/i18n';
import SearchInput from '../editor/components/Common/SearchInput';
import ReactDOM from "react-dom";
import { FaXmark } from "react-icons/fa6";

export default class IconControl extends DragwybEditor.editor.extends.ControlBase {

    controlName() {
        return 'icon';
    }

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            isOpen: false,
            search: '',
            activeTab: 'all' // 'all', 'solid', 'regular', 'brands'
        };

        this.wrapperRef = React.createRef();
        this.handleClickOutside = this.handleClickOutside.bind(this);

        // 1. Prefix Mapping
        this.prefixes = {
            solid: 'fas',
            regular: 'far',
            brands: 'fab'
        };

        // 2. Load Raw Data
        const globalIcons = DragwybEditor.faIconsList || {};

        this.libraries = {
            solid: globalIcons.solid || [],
            regular: globalIcons.regular || [],
            brands: globalIcons.brands || []
        };
    }

    componentDidMount() {
        super.componentDidMount();
        document.addEventListener('mousedown', this.handleClickOutside);
    }

    componentWillUnmount() {
        super.componentWillUnmount();
        document.removeEventListener('mousedown', this.handleClickOutside);
    }

    handleClickOutside(event) {
        if (this.wrapperRef.current && !this.wrapperRef.current.contains(event.target)) {
            this.setState({ isOpen: false });
        }
    }

    createIconClass(category, iconName) {
        const prefix = this.prefixes[category] || 'fas';
        return `${prefix} fa-${iconName}`;
    }

    /**
     * ✅ OPTIMIZED: Returns an OBJECT with filtered arrays, not a single flat array.
     */
    getFilteredIcons() {
        const { search } = this.state;
        const lowerSearch = search.toLowerCase();

        const solidIcons = DragwybEditor.faIconsList.solid;
        const regularIcons = DragwybEditor.faIconsList.regular;
        const brandsIcons = DragwybEditor.faIconsList.brands;

        const Icons = [];

        if (search === '') {
            Icons['solid'] = solidIcons;
            Icons['regular'] = regularIcons;
            Icons['brands'] = brandsIcons;
            return Icons;
        }

        solidIcons.forEach(icon => {
            if (icon.toLowerCase().includes(lowerSearch)) {
                if (Icons.hasOwnProperty('solid')) {
                    Icons['solid'].push(icon);
                } else {
                    Icons['solid'] = [icon];
                }
            }
        });

        regularIcons.forEach(icon => {
            if (icon.toLowerCase().includes(lowerSearch)) {
                if (Icons.hasOwnProperty('regular')) {
                    Icons['regular'].push(icon);
                } else {
                    Icons['regular'] = [icon];
                }
            }
        });

        brandsIcons.forEach(icon => {
            if (icon.toLowerCase().includes(lowerSearch)) {
                if (Icons.hasOwnProperty('brands')) {
                    Icons['brands'].push(icon);
                } else {
                    Icons['brands'] = [icon];
                }
            }
        });

        return Icons;

    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const value = this.state.value || settings.default || '';
        const { isOpen, search, activeTab } = this.state;

        // 1. Get the structured object containing all filtered lists
        const filteredGroups = this.getFilteredIcons();

        const displayValue = value.icon && value.type ? this.createIconClass(value.type, value.icon) : 'Select Icon';

        let iconCount = 0;

        if (activeTab === 'all') {
            const solidIcons = filteredGroups.solid ? filteredGroups.solid.length : 0;
            const regularIcons = filteredGroups.regular ? filteredGroups.regular.length : 0;
            const brandsIcons = filteredGroups.brands ? filteredGroups.brands.length : 0;
            iconCount = solidIcons + regularIcons + brandsIcons;
        } else {
            iconCount = filteredGroups[activeTab] ? filteredGroups[activeTab].length : 0;
        }

        const renderIcons = (type) => {
            return filteredGroups[type] ? filteredGroups[type].map((icon, index) => {
                const fullIconClass = this.createIconClass(type, icon);
                return <div
                    key={`${fullIconClass}-${index}`}
                    className={`dragwyb-icon-item ${value && value.type === type && value.icon === icon ? 'active' : ''}`}
                    onClick={() => {
                        this.updateControlHandler(id, { type, icon });
                        this.setState({ isOpen: false });
                    }}
                    title={fullIconClass}
                >
                    <div className="dragwyb-icon-inner">
                        <i className={fullIconClass}></i>
                    </div>
                </div>
            }) : null;
        }

        return (
            <div className="dragwyb-control dragwyb-control--icon" id={`control-${id}`} ref={this.wrapperRef}>
                {settings.label && (
                    <label className="dragwyb-control__label">{settings.label}</label>
                )}

                <div className="dragwyb-icon-selector">
                    {/* PREVIEW BUTTON */}
                    <div className="dragwyb-icon-preview" onClick={() => this.setState({ isOpen: !isOpen })}>
                        <div className="dragwyb-preview-left">
                            {value ? (
                                <div className="dragwyb-icon-box selected">
                                    <i className={this.createIconClass(value.type, value.icon)}></i>
                                </div>
                            ) : (
                                <div className="dragwyb-icon-box empty">
                                    <i className="fas fa-plus"></i>
                                </div>
                            )}
                            <span className="dragwyb-icon-name" style={{ textTransform: 'capitalize' }}>
                                {displayValue}
                            </span>
                        </div>

                        {value && (
                            <div className="dragwyb-preview-actions">
                                <i
                                    className="fas fa-times remove-icon"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        this.updateControlHandler(id, '');
                                    }}
                                    title="Remove"
                                ></i>
                            </div>
                        )}
                    </div>

                    {/* POPUP PANEL */}
                    {isOpen && (
                        ReactDOM.createPortal(
                            <div className="dragwyb-icon-panel">
                                <div className="dragwyb-icon-panel__inner">
                                    {/* Close Icon */}
                                    <span className="dragwyb-icon-panel__close" onClick={() => this.setState({ isOpen: false })} title={__('Close', 'dragwyb-form-builder')}>
                                        <FaXmark size={20} />
                                    </span>
                                    <h2 className="dragwyb-icon-panel__title">{__('Icon Library', 'dragwyb-form-builder')}</h2>
                                    {/* Search Bar */}
                                    <div className="dragwyb-icon-search-wrapper">
                                        <SearchInput
                                            onChange={(search) => this.setState({ search })}
                                            placeholder="Search fields..."
                                            id="dragwyb-controls__search"
                                        />
                                    </div>

                                    {/* Library Tabs */}
                                    <div className="dragwyb-icon-tabs">
                                        {['all', 'solid', 'regular', 'brands'].map(tab => (
                                            <button
                                                key={tab}
                                                type="button"
                                                className={`dragwyb-tab ${activeTab === tab ? 'active' : ''}`}
                                                onClick={() => this.setState({ activeTab: tab })}
                                            >
                                                {tab.charAt(0).toUpperCase() + tab.slice(1)}
                                            </button>
                                        ))}
                                    </div>

                                    {/* Icon Grid */}
                                    <div className="dragwyb-icon-grid">
                                        {iconCount > 0 ?
                                            <>
                                                {(activeTab === 'all' || activeTab === 'solid') && renderIcons('solid')}
                                                {(activeTab === 'all' || activeTab === 'regular') && renderIcons('regular')}
                                                {(activeTab === 'all' || activeTab === 'brands') && renderIcons('brands')}
                                            </>
                                            :
                                            <div className="dragwyb-icon-item empty">
                                                {__('No icons found', 'dragwyb-form-builder')}
                                            </div>
                                        }
                                    </div>
                                </div>
                            </div>,
                            document.body
                        )
                    )}
                </div>
            </div>
        );
    }
}