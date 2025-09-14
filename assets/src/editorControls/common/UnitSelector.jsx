import React, { useState, useRef, useEffect } from "react";
import { RiArrowDownSLine } from "react-icons/ri";

const UnitSelector = ({ units, value, onChange }) => {
    const [open, setOpen] = useState(false);
    const wrapperRef = useRef(null);

    // Close dropdown on outside click
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
                setOpen(false);
            }
        };
        document.addEventListener("mousedown", handleClickOutside);
        return () => {
            document.removeEventListener("mousedown", handleClickOutside);
        };
    }, []);

    return (
        <div
            className={`dragwyb-unit-selector ${open ? "is-open" : ""}`}
            ref={wrapperRef}
        >
            <button
                type="button"
                className="dragwyb-unit-selector__trigger"
                onClick={() => setOpen(!open)}
            >
                <span>{value}</span>
                <RiArrowDownSLine className="dragwyb-unit-selector__icon" />
            </button>

            {open && (
                <ul className="dragwyb-unit-selector__list">
                    {units.map((unit) => (
                        <li
                            key={unit}
                            className={`dragwyb-unit-selector__item ${
                                unit === value ? "is-active" : ""
                            }`}
                            onClick={() => {
                                onChange(unit);
                                setOpen(false);
                            }}
                        >
                            {unit}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
};

export default UnitSelector;
