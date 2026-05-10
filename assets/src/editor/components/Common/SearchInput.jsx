import React, { useState } from 'react';
import { useDebouncedCallback } from '../../utils/helpers';
import { FaMagnifyingGlass, FaXmark } from "react-icons/fa6";
import { __ } from '@wordpress/i18n';

const SearchInput = ({
    value,
    onChange,
    placeholder = 'Search...',
    debounceTime = 100,
    className = '',
    id = Math.random(99999)
}) => {
    const [searchValue, setSearchValue] = useState(value || '');
    const debouncedSearch = useDebouncedCallback((val) => {
        onChange(val);
    }, debounceTime);

    const onChangeHandler = (value) => {
        const newValue = value;
        setSearchValue(newValue);
        debouncedSearch(newValue);
    }

    return (
        <div className={`dragwyb-search-input ${className}`}>
            <label htmlFor={id} title={__('Search', 'smart-form-builder-by-dragwyb')}>
                <span className="search-icon">
                    <FaMagnifyingGlass />
                </span>
            </label>
            <input
                id={id}
                type="text"
                value={searchValue}
                onChange={(e) => { onChangeHandler(e.target.value) }}
                placeholder={placeholder}
            />
            {searchValue && (
                <button
                    className="dragwyb-search-input__clear"
                    onClick={() => { onChangeHandler('') }}
                    type="button"
                    title={__('Clear', 'smart-form-builder-by-dragwyb')}
                >
                    <span><FaXmark /></span>
                </button>
            )}
        </div>
    );
};

export default SearchInput;
