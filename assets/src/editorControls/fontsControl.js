import SelectGroup from "../editor/components/Common/SelectGroup"; // Ensure this component is updated

export default class FontsControl extends DragwybEditor.editor.extends.ControlBase {
    constructor(props) {
        super(props);

        // Initialize state, preserving any existing state logic from ControlBase
        this.state = {
            ...(this.state || {}), // Defensive copy in case ControlBase sets state
            loadedFonts: new Set()
        };

        // Instance variables for Observer (doesn't need to be in state)
        this.observer = null;
    }

    controlName() {
        return "fonts";
    }

    onRender() {
        // If there is an initial value, load that font immediately
        const { value } = this.state;
        if (value) {
            this.loadFont(value);
        }
    }

    onUpdate(prevProps, prevState) {
        // If the user selects a new font, load it
        const { value } = this.state;
        if (value !== prevState.value) {
            if (value) {
                this.loadFont(value);
            }
        }
    }

    onDestroy() {
        // Clean up observer when control is removed
        if (this.observer) {
            this.observer.disconnect();
        }
    }

    // --- 1. Logic to Inject Google Fonts CSS ---
    loadFont = (fontName) => {
        if (!fontName || typeof fontName !== "string" || !this.settings || !this.settings.options || !this.settings.options[fontName] || this.settings.options[fontName] !== "google") return;

        // Optimization: Check if already loaded
        if (this.state.loadedFonts.has(fontName) || fontName === "Default") return;

        const link = document.createElement("link");
        link.href = `https://fonts.googleapis.com/css2?family=${fontName.replace(/\s+/g, '+')}&display=swap`;
        link.rel = "stylesheet";
        document.head.appendChild(link);

        // Update state to trigger re-render of font-family styles
        this.setState((prevState) => {
            const newSet = new Set(prevState.loadedFonts);
            newSet.add(fontName);
            return { loadedFonts: newSet };
        });
    };

    // --- 2. Initialize Observer (Passed to SelectGroup) ---
    initObserver = (listNode) => {
        if (!listNode) return;

        // Disconnect existing observer if any
        if (this.observer) this.observer.disconnect();


        this.observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const fontName = entry.target.getAttribute('data-font-family');
                    if (fontName) {
                        this.loadFont(fontName);
                        this.observer.unobserve(entry.target); // Stop watching this item
                    }
                }
            });
        }, {
            root: listNode, // Watch scroll inside the dropdown UL
            rootMargin: "100px 0px" // Load fonts 100px before they become visible
        });

        const items = listNode.querySelectorAll('[data-font-family]');
        items.forEach((item) => this.observer.observe(item));
    };

    // --- 3. Observe Individual Items ---
    observeElement = (element) => {
        if (element && this.observer) {
            this.observer.observe(element);
        }
    };

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value, loadedFonts } = this.state; // Get loadedFonts from state
        const { label, options, label_inline = false } = settings;

        // Transform options to Group format
        // Note: Logic moved here as requested, but ideally memoized
        const optionsByGroups = (opts) => {
            const groups = {};

            Object.keys(opts).forEach((opt) => {
                const groupName = opts[opt];
                if (!groups[groupName]) {
                    groups[groupName] = { label: groupName, options: [] };
                }
                groups[groupName].options.push({ value: opt, label: opt });
            });

            return Object.values(groups);
        };

        const currentValue = value || "Default";

        let wrapperCls = "dragwyb-control dragwyb-control--fonts";
        if (label_inline) {
            wrapperCls += " dragwyb-label-inline";
        }

        return (
            <div
                className={wrapperCls}
                data-control="fonts"
                id={`control-${id}`}
            >
                {/* Header */}
                <div className="dragwyb-fonts__header">
                    <this.RenderLabel
                        attr={
                            { htmlFor: id }
                        }
                    />
                </div>

                {/* Fields */}
                <SelectGroup
                    options={optionsByGroups(options)}
                    value={currentValue}
                    onChange={(option) => this.updateControlHandler(id, option.value || option)}
                    searchInput={true}
                    style={{ fontFamily: loadedFonts.has(currentValue) ? currentValue : 'inherit' }}
                    // 1. Pass the Observer Ref Setup
                    listRef={this.initObserver}

                    // 2. Custom Render for Lazy Loading & Styling
                    renderOption={({ option, className, onClick, onMouseEnter, isSelected, ref }) => {
                        const isLoaded = loadedFonts.has(option.value);

                        return (
                            <li
                                key={option.value}
                                className={className}
                                onClick={onClick}
                                onMouseEnter={onMouseEnter}
                                data-font-family={option.value} // Read by Observer

                                // Merge Refs: SelectGroup's scroll ref + Our Observer ref
                                ref={(el) => {
                                    if (ref) ref(el);
                                    this.observeElement(el);
                                }}

                                style={{
                                    fontFamily: isLoaded ? option.value : 'sans-serif',
                                    fontSize: '16px', // Slightly larger to see font detail
                                    padding: '8px 12px'
                                }}
                            >
                                {option.label}
                                {isSelected && <span className="dashicons dashicons-yes" style={{ marginLeft: 'auto' }}></span>}
                            </li>
                        );
                    }}
                />
            </div>
        );
    }
}