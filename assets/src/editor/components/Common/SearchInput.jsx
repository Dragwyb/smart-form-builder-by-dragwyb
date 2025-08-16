import React, { useState } from 'react';
import { Dashicon } from '@wordpress/components';
import { useDebouncedCallback } from '../../utils/helpers';

const SearchInput = ({
    value,
    onChange,
    placeholder = 'Search...',
    debounceTime = 100,
    className = '',
    id = Math.random(99999)
}) => {
    const [searchValue, setSearchValue] = useState(value);
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
            <label for={id}>
                <i className='fa-solid fa-magnifying-glass' />
            </label>
            <input
                id={id}
                type="text"
                value={searchValue}
                onChange={(e)=>{onChangeHandler(e.target.value)}}
                placeholder={placeholder}
            />
            {searchValue && (
                <button
                    className="dragwyb-search-input__clear"
                    onClick={() => {onChangeHandler('')}}
                    type="button"
                >
                    <i className='fa-solid fa-xmark' />
                </button>
            )}
        </div>
    );
};

export default SearchInput;
