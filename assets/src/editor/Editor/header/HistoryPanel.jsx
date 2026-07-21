import React from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { revertToHistory } from '../../store/actions';
import { __ } from '@wordpress/i18n';
import { FaCheck } from 'react-icons/fa';

const HistoryPanel = ({ onClose }) => {
    const history = useSelector(state => state.history || { past: [], currentIndex: -1 });
    const dispatch = useDispatch();

    const renderLabel = (label) => {
        if (!label) return '';
        const lastCommaIndex = label.lastIndexOf(',');
        if (lastCommaIndex !== -1) {
            const firstPart = label.substring(0, lastCommaIndex).trim();
            const secondPart = label.substring(lastCommaIndex + 1).trim();
            return (
                <>
                    <span className="label-bold">{firstPart}</span>{' '}
                    <span className="label-italic">{secondPart}</span>
                </>
            );
        }
        const colonIndex = label.indexOf(':');
        if (colonIndex !== -1) {
            const firstPart = label.substring(0, colonIndex + 1).trim();
            const secondPart = label.substring(colonIndex + 1).trim();
            return (
                <>
                    <span className="label-bold">{firstPart}</span>{' '}
                    <span className="label-italic">{secondPart}</span>
                </>
            );
        }
        return <span className="label-normal">{label}</span>;
    };

    return (
        <div className="dragwyb-panel dragwyb-history-panel-sidebar">
            <div className="dragwyb-panel__header">
                <h3>{__('History', 'smart-form-builder-by-dragwyb')}</h3>
                <button className="close-btn" onClick={onClose}>&times;</button>
            </div>
            <div className="dragwyb-panel__content">
                <div className="dragwyb-history-panel__list">
                    {/* Initial State entry */}
                    <div
                        className={`dragwyb-history-item ${history.currentIndex === -1 ? 'active' : ''}`}
                        onClick={() => dispatch(revertToHistory(-1))}
                    >
                        <div className="details">
                            <span className="label-bold">{__('Editing Started', 'smart-form-builder-by-dragwyb')}</span>
                        </div>
                        {history.currentIndex === -1 && (
                            <span className="check-icon"><FaCheck /></span>
                        )}
                    </div>

                    {/* Historic entries */}
                    {history.past.map((snapshot, index) => (
                        <div
                            key={index}
                            className={`dragwyb-history-item ${history.currentIndex === index ? 'active' : ''}`}
                            onClick={() => dispatch(revertToHistory(index))}
                        >
                            <div className="details">
                                {renderLabel(snapshot.label)}
                                <span>{snapshot.timestamp}</span>
                            </div>
                            {history.currentIndex === index && (
                                <span className="check-icon"><FaCheck /></span>
                            )}
                        </div>
                    ))}
                </div>
                <div className="dragwyb-history-panel__footer-text">
                    {__('Switch to Revisions tab for older versions', 'smart-form-builder-by-dragwyb')}
                </div>
            </div>
        </div>
    );
};

export default HistoryPanel;
