import React, { useState, useEffect, useRef, useCallback, useMemo } from "react";

const Slider = ({
    value = "",
    min = 0,
    max = 100,
    step = 1,
    numberInput = true,
    onChange,
    className = ""
}) => {
    const [localValue, setLocalValue] = useState(value);
    const [isDragging, setIsDragging] = useState(false);
    const trackRef = useRef(null);

    // Sync with parent props
    useEffect(() => {
        setLocalValue(value);
    }, [value]);

    // --- MATH HELPER: Pixels to Value ---
    const calculateValueFromPointer = useCallback((clientX) => {
        if (!trackRef.current) return localValue;

        const rect = trackRef.current.getBoundingClientRect();
        const width = rect.width;
        const left = rect.left;

        // 1. Calculate percentage (0 to 1) based on mouse X position
        let percent = (clientX - left) / width;

        // 2. Clamp between 0 and 1
        percent = Math.max(0, Math.min(1, percent));

        // 3. Convert percent to value range
        let rawValue = min + (percent * (max - min));

        // 4. Snap to Step
        let steppedValue = Math.round(rawValue / step) * step;

        // 5. Clamp final value to ensure precision didn't drift
        return Math.min(Math.max(steppedValue, min), max);
    }, [min, max, step, localValue]);

    // --- MOUSE HANDLERS ---
    const handleMouseDown = (e) => {
        // Only left click
        if (e.button !== 0) return;

        setIsDragging(true);

        // Update value immediately on click
        const newValue = calculateValueFromPointer(e.clientX);
        updateValue(newValue);

        // Prevent text selection while dragging
        e.preventDefault();
    };

    const handleMouseMove = useCallback((e) => {
        if (!isDragging) return;
        const newValue = calculateValueFromPointer(e.clientX);
        updateValue(newValue);
    }, [isDragging, calculateValueFromPointer]);

    const handleMouseUp = useCallback(() => {
        setIsDragging(false);
    }, []);

    // Attach global listeners when dragging starts
    useEffect(() => {
        if (isDragging) {
            window.addEventListener("mousemove", handleMouseMove);
            window.addEventListener("mouseup", handleMouseUp);
        } else {
            window.removeEventListener("mousemove", handleMouseMove);
            window.removeEventListener("mouseup", handleMouseUp);
        }
        return () => {
            window.removeEventListener("mousemove", handleMouseMove);
            window.removeEventListener("mouseup", handleMouseUp);
        };
    }, [isDragging, handleMouseMove, handleMouseUp]);

    // --- KEYBOARD ACCESSIBILITY ---
    const handleKeyDown = (e) => {
        let newValue = Number(localValue);

        switch (e.key) {
            case "ArrowRight":
            case "ArrowUp":
                newValue = Math.min(newValue + step, max);
                break;
            case "ArrowLeft":
            case "ArrowDown":
                newValue = Math.max(newValue - step, min);
                break;
            default:
                return;
        }

        e.preventDefault(); // Prevent page scroll
        updateValue(newValue);
    };

    // --- UPDATE LOGIC ---
    const updateValue = (val) => {
        // Handle empty string from number input
        if (val === "") {
            setLocalValue("");
            if (onChange) onChange("");
            return;
        }

        if (val < min) {
            setLocalValue(min);
            if (onChange) onChange(min);
            return;
        }

        if (val > max) {
            setLocalValue(max);
            if (onChange) onChange(max);
            return;
        }

        // Ensure precision (fix floating point errors like 0.300000004)
        const cleanVal = Number(Number(val).toFixed(2)); // Adjust decimals as needed
        setLocalValue(cleanVal);

        if (onChange) onChange(cleanVal);
    };

    // --- RENDER HELPERS ---
    const percentage = useMemo(() => {
        if (localValue === "") return 0;
        const val = Number(localValue);
        return ((val - min) * 100) / (max - min);
    }, [localValue, min, max]);

    return (
        <div className={`dragwyb-slider ${className}`}>

            {/* 1. Custom Track Area */}
            <div
                className={`dragwyb-slider__track-area ${isDragging ? "is-dragging" : ""}`}
                onMouseDown={handleMouseDown}
                ref={trackRef}
            >
                {/* Background Rail */}
                <div className="dragwyb-slider__rail"></div>

                {/* Colored Fill */}
                <div
                    className="dragwyb-slider__fill"
                    style={{ width: `${percentage}%` }}
                ></div>

                {/* Draggable Thumb */}
                <div
                    className="dragwyb-slider__thumb"
                    style={{ left: `${percentage}%` }}
                    tabIndex={0} // Make focusable
                    onKeyDown={handleKeyDown}
                    role="slider"
                    aria-valuenow={localValue}
                    aria-valuemin={min}
                    aria-valuemax={max}
                ></div>
            </div>

            {/* 2. Number Input */}
            {numberInput && (
                <div className="dragwyb-slider__input-area">
                    <input
                        type="number"
                        value={localValue}
                        onChange={(e) => updateValue(e.target.value)}
                        className="dragwyb-slider__number"
                        min={min}
                        max={max}
                        step={step}
                    />
                </div>
            )}
        </div>
    );
};

export default Slider;