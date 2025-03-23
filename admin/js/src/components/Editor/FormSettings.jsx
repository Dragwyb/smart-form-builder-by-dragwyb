import React from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { Tabs, Panel } from '../Common';

const FormSettings = () => {
    const settings = useSelector(state => state.form.settings);
    const dispatch = useDispatch();

    const handleChange = (section, setting, value) => {
        dispatch({
            type: 'UPDATE_FORM_SETTINGS',
            payload: {
                ...settings,
                [section]: {
                    ...settings[section],
                    [setting]: value
                }
            }
        });
    };

    return (
        <div className="form-settings">
            <Tabs>
                {Object.entries(DragwybEditor.settings).map(([key, section]) => (
                    <Tabs.Tab key={key} title={section.label}>
                        <Panel>
                            {Object.entries(section.settings).map(([settingKey, setting]) => (
                                <div key={settingKey} className="setting-row">
                                    <label>{setting.label}</label>
                                    <input
                                        type={setting.type}
                                        value={settings[key]?.[settingKey] || ''}
                                        onChange={e => handleChange(key, settingKey, e.target.value)}
                                    />
                                </div>
                            ))}
                        </Panel>
                    </Tabs.Tab>
                ))}
            </Tabs>
        </div>
    );
};

export default FormSettings; 