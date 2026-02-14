import React from 'react';

export default class IconsControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'icons';
    }

    addItem = () => {
        const current = this.state.value || [];
        const newItem = { icon: 'fas fa-star', link: '', text: 'Item' };
        this.updateControlHandler(this.id, [...current, newItem]);
    }

    removeItem = (index) => {
        const current = this.state.value || [];
        const newItems = current.filter((_, i) => i !== index);
        this.updateControlHandler(this.id, newItems);
    }

    updateItem = (index, key, val) => {
        const current = [...(this.state.value || [])];
        current[index] = { ...current[index], [key]: val };
        this.updateControlHandler(this.id, current);
    }

    bind() {
        if (!this.shouldRender()) return <></>;
        const { settings, id } = this;
        const items = this.state.value || [];

        return (
            <div className="dragwyb-control dragwyb-control--icons-list" id={`control-${id}`}>
                <this.RenderLabel />

                <div className="dragwyb-repeater-list">
                    {items.map((item, index) => (
                        <div key={index} className="dragwyb-repeater-item">
                            <div className="dragwyb-repeater-header">
                                <span>Item #{index + 1}</span>
                                <button className="dragwyb-btn-icon" onClick={() => this.removeItem(index)}>trash</button>
                            </div>
                            <div className="dragwyb-repeater-content">
                                <input
                                    type="text" placeholder="Icon Class" value={item.icon}
                                    onChange={(e) => this.updateItem(index, 'icon', e.target.value)}
                                />
                                <input
                                    type="text" placeholder="Link" value={item.link}
                                    onChange={(e) => this.updateItem(index, 'link', e.target.value)}
                                />
                            </div>
                        </div>
                    ))}
                </div>

                <button type="button" className="dragwyb-btn dragwyb-btn-add" onClick={this.addItem}>
                    + Add Icon
                </button>
            </div>
        );
    }
}