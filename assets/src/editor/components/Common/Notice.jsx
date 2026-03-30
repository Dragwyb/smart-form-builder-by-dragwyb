import React, { useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { hideNotice } from '../../store/actions';

const Notice = () => {
    const notices = useSelector(state => state.notices);
    const dispatch = useDispatch();

    useEffect(() => {
        notices.forEach(notice => {
            const timer = setTimeout(() => {
                dispatch(hideNotice(notice.id));
            }, 3000);
            return () => clearTimeout(timer);
        });
    }, [notices]);

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