import Select from "../../editor/components/Common/Select";

const UnitSelector = ({ units, value, onChange }) => {
    return (
        <Select
            options={Object.fromEntries(units.map(key => [key, key]))}
            value={value}
            onChange={onChange}
            className="dragwyb-unit-selector"
        />
    );
};

export default UnitSelector;
