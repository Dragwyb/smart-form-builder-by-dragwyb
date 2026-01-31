import React, { useState, useEffect, useRef, useCallback } from 'react';
import { useSelector } from 'react-redux';

const Scrollbar = ({ children, className = '' }) => {
    const contentRef = useRef(null);
    const scrollTrackRef = useRef(null);
    const scrollThumbRef = useRef(null);
    const iframeEle = useSelector(state => state?.iframeEle);

    if (!iframeEle) return null;

    const [thumbHeight, setThumbHeight] = useState(20);
    const [isDragging, setIsDragging] = useState(false);
    const [activeDocumentType, setActiveDocumentType] = useState('');

    const dragInfo = useRef({
        startY: 0,
        startScrollTop: 0,
    });

    const handleResize = useCallback(() => {
        if (!contentRef.current || !scrollThumbRef.current) return;

        const { clientHeight, scrollHeight } = contentRef.current;

        if (scrollHeight <= clientHeight) {
            scrollTrackRef.current.style.display = 'none';
            scrollThumbRef.current.style.display = 'none';
            return;
        }

        scrollTrackRef.current.style.display = 'block';
        scrollThumbRef.current.style.display = 'block';

        const scrollRatio = clientHeight / scrollHeight;
        const rawHeight = scrollRatio * clientHeight;

        const newHeight = Math.max(rawHeight, 30);

        setThumbHeight(newHeight);
        scrollThumbRef.current.style.height = `${newHeight}px`;
    }, []);

    const handleScroll = useCallback(() => {
        if (!contentRef.current || !scrollThumbRef.current) return;

        const { scrollTop, scrollHeight, clientHeight } = contentRef.current;
        const currentThumbHeight = parseFloat(scrollThumbRef.current.style.height) || 30;

        const maxThumbMove = clientHeight - currentThumbHeight;
        const maxScroll = scrollHeight - clientHeight;
        const scrollRatio = scrollTop / maxScroll;

        const newTop = scrollRatio * maxThumbMove;

        requestAnimationFrame(() => {
            if (scrollThumbRef.current) {
                scrollThumbRef.current.style.transform = `translateY(${newTop}px)`;
            }
        });
    }, []);

    const handleThumbMouseDown = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();

        const targetDocument = e?.target?.ownerDocument;

        if (targetDocument === iframeEle) {
            setActiveDocumentType('iframe');
        } else if (activeDocumentType !== 'parent') {
            setActiveDocumentType('parent');
        }

        setIsDragging(true);

        dragInfo.current = {
            startY: e.clientY,
            startScrollTop: contentRef.current.scrollTop
        };

        targetDocument.body.style.userSelect = 'none';
        targetDocument.body.style.cursor = 'grabbing';
    }, []);

    const handleTrackClick = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();

        if (!e.target || !e.target.classList.contains('dragwyb-scrollbar-track')) return;

        if (e.target === scrollThumbRef.current) return;

        const content = contentRef.current;
        const thumb = scrollThumbRef.current;

        if (!content || !thumb) return;

        const thumbRect = thumb.getBoundingClientRect();
        const clickY = e.clientY;

        const stepAmount = content.clientHeight;

        if (clickY < thumbRect.top) {
            content.scrollBy({
                top: -stepAmount,
                behavior: 'smooth'
            });
        } else if (clickY > thumbRect.bottom) {
            content.scrollBy({
                top: stepAmount,
                behavior: 'smooth'
            });
        }
    }, []);

    const handleThumbMouseMove = useCallback((e) => {
        if (!isDragging || !contentRef.current) return;

        e.preventDefault();
        e.stopPropagation();

        const { scrollHeight, clientHeight } = contentRef.current;
        const { startY, startScrollTop } = dragInfo.current;
        const currentThumbHeight = thumbHeight;

        const deltaY = e.clientY - startY;

        const maxThumbMove = clientHeight - currentThumbHeight;
        const maxScroll = scrollHeight - clientHeight;

        if (maxThumbMove <= 0) return;

        const ratio = maxScroll / maxThumbMove;

        const newScrollTop = startScrollTop + (deltaY * ratio);

        contentRef.current.scrollTop = newScrollTop;

    }, [isDragging, thumbHeight]);

    const handleThumbMouseUp = useCallback(() => {
        if (isDragging) {
            const targetDoument = activeDocumentType === 'iframe' ? iframeEle : document;

            setIsDragging(false);
            targetDoument.body.style.userSelect = '';
            targetDoument.body.style.cursor = '';
        }
    }, [isDragging]);

    useEffect(() => {

        if ('' === activeDocumentType) return;

        const targetDoument = activeDocumentType === 'iframe' ? iframeEle : document;

        if (isDragging) {
            targetDoument.addEventListener('mousemove', handleThumbMouseMove);
            targetDoument.addEventListener('mouseup', handleThumbMouseUp);
            targetDoument.addEventListener('mouseleave', handleThumbMouseUp);
        } else {
            targetDoument.removeEventListener('mousemove', handleThumbMouseMove);
            targetDoument.removeEventListener('mouseup', handleThumbMouseUp);
            targetDoument.removeEventListener('mouseleave', handleThumbMouseUp);
        }

        return () => {
            targetDoument.removeEventListener('mousemove', handleThumbMouseMove);
            targetDoument.removeEventListener('mouseup', handleThumbMouseUp);
            targetDoument.removeEventListener('mouseleave', handleThumbMouseUp);
        };
    }, [activeDocumentType, handleThumbMouseMove, handleThumbMouseUp]);

    useEffect(() => {
        const content = contentRef.current;
        if (!content) return;
        const resizeObserver = new ResizeObserver(() => handleResize());
        const mutationObserver = new MutationObserver(() => handleResize());

        resizeObserver.observe(content);
        mutationObserver.observe(content, { childList: true, subtree: true });
        handleResize();

        return () => {
            resizeObserver.disconnect();
            mutationObserver.disconnect();
        };
    }, [handleResize]);

    return (
        <div className={`dragwyb-scrollbar-container ${className}`}>
            <div
                className="dragwyb-scrollbar-content"
                ref={contentRef}
                onScroll={handleScroll}
            >
                {children}
            </div>
            <div className="dragwyb-scrollbar-track" ref={scrollTrackRef} onClick={handleTrackClick}>
                <div
                    className={`dragwyb-scrollbar-thumb ${isDragging ? 'active' : ''}`}
                    ref={scrollThumbRef}
                    onMouseDown={handleThumbMouseDown}
                />
            </div>
        </div>
    );
};

export default Scrollbar;