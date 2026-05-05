import React, { useEffect, useRef } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { hideNotice } from '../../store/actions';

const Notice = () => {
    const notices = useSelector(state => state.notices);
    const dispatch = useDispatch();
    // Track all active timers to clean them up properly
    const timersRef = useRef(new Map());

    useEffect(() => {
        // Set timers for new notices
        notices.forEach(notice => {
            if (!timersRef.current.has(notice.id)) {
                const timer = setTimeout(() => {
                    dispatch(hideNotice(notice.id));
                    timersRef.current.delete(notice.id);
                }, 3000);
                timersRef.current.set(notice.id, timer);
            }
        });

        // Clean up timers for notices that have been removed
        const currentIds = new Set(notices.map(n => n.id));
        for (const [id, timer] of timersRef.current.entries()) {
            if (!currentIds.has(id)) {
                clearTimeout(timer);
                timersRef.current.delete(id);
            }
        }

        // Cleanup on unmount
        return () => {
            for (const timer of timersRef.current.values()) {
                clearTimeout(timer);
            }
            timersRef.current.clear();
        };
    }, [notices, dispatch]);

    return (
        <div className="dragwyb-notices">
            {notices.map(notice => (
                <div
                    key={notice.id}
                    className={`dragwyb-notice ${notice.type}`}
                >
                    {notice.message}
                    <span
                        className="dragwyb-notice-dismiss"
                        onClick={() => dispatch(hideNotice(notice.id))}
                    >
                    </span>
                </div>
            ))}
        </div>
    );
};

export default Notice;