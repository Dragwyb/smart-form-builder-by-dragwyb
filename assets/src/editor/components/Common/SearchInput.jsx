import React from 'react';
import { Dashicon } from '@wordpress/components';
import { debounce } from '../../utils/helpers';

const SearchInput = ({
    value,
    onChange,
    placeholder = 'Search...',
    debounceTime = 300,
    className = ''
}) => {
    const debouncedOnChange = React.useMemo(
        () => debounce(onChange, debounceTime),
        [onChange, debounceTime]
    );

    return (
        <div className={`dragwyb-search-input ${className}`}>
            <Dashicon icon="search" />
            <input
                type="text"
                value={value}
                onChange={(e) => {
                    const newValue = e.target.value;
                    onChange(newValue); // Immediate update for controlled input
                    debouncedOnChange(newValue); // Debounced callback
                }}
                placeholder={placeholder}
            />
            {value && (
                <button
                    className="dragwyb-search-input__clear"
                    onClick={() => onChange('')}
                >
                    <Dashicon icon="no-alt" />
                </button>
            )}
        </div>
    );
};

export default SearchInput; 