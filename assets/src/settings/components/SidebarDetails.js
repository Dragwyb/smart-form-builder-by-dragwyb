import React, { useState, useEffect } from 'react';

const SidebarDetails = () => {
    const {
        restUrl,
        nonce,
        analyticsData,
        extraPlugins = [],
        freeSupportUrl = 'https://wordpress.org/support/plugin/smart-form-builder-by-dragwyb/',
        supportUrl = 'https://dragwyb.com/contact/?utm_source=settings&utm_medium=contact&utm_campaign=form-builder',
        morePluginsUrl = 'https://dragwyb.com/products/?utm_source=settings&utm_medium=plugin&utm_campaign=form-builder',
        totalForms = '0',
        puginUrl
    } = window.DragwybSettingsData || {};

    const [timeRange, setTimeRange] = useState('1');
    const [isFetchingAnalytics, setIsFetchingAnalytics] = useState(false);

    // Key-value cache state for analytics
    const [analyticsCache, setAnalyticsCache] = useState(() => {
        if (analyticsData) {
            return { all: analyticsData };
        }
        return {};
    });

    useEffect(() => {
        if (analyticsCache[timeRange]) {
            return;
        }

        const fetchAnalyticsData = async () => {
            setIsFetchingAnalytics(true);
            try {
                const apiBase = restUrl ? restUrl.replace(/\/settings\/?$/, '') : '/wp-json/dragwyb/v1';
                const response = await fetch(`${apiBase}/analytics?range=${timeRange}`, {
                    headers: {
                        'X-WP-Nonce': nonce
                    }
                });
                const result = await response.json();
                if (result.status === 'success' && result.data) {
                    setAnalyticsCache(prevCache => ({
                        ...prevCache,
                        [timeRange]: result.data
                    }));
                }
            } catch (error) {
                console.error('Failed to fetch analytics for range:', timeRange, error);
            } finally {
                setIsFetchingAnalytics(false);
            }
        };

        fetchAnalyticsData();
    }, [timeRange, analyticsCache, restUrl, nonce]);

    const currentStats = analyticsCache[timeRange] || analyticsCache['all'] || analyticsData || {};

    const stats = {
        totalSubmissions: currentStats.totalSubmissions || '0',
        uniqueVisitors: currentStats.uniqueVisitors || '0',
        totalSessions: currentStats.totalSessions || '0',
        totalPageViews: currentStats.totalPageViews || '0'
    };

    return (
        <aside className="dragwyb-db-col-right dragwyb-settings-sidebar-col">
            {/* Card 1: Analytics Overview */}
            <div className="dragwyb-db-card dragwyb-analytics-card">
                <div className="dragwyb-db-card-header">
                    <div className="dragwyb-db-card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                        <h2>Analytics Overview</h2>
                    </div>
                    <select
                        id="dragwyb-db-stats-timeframe"
                        className="dragwyb-db-select-sm"
                        value={timeRange}
                        onChange={(e) => setTimeRange(e.target.value)}
                    >
                        <option value="1">Today</option>
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="-1">All Time</option>
                    </select>
                </div>

                <div className="dragwyb-db-stats-grid">
                    {/* Tile 1: Total Forms */}
                    <div className="dragwyb-db-stat-tile tile-pink">
                        <div className="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                        <div className="stat-content">
                            <div className="stat-number">{totalForms}</div>
                            <div className="stat-label">Total Forms</div>
                        </div>
                    </div>

                    {/* Tile 2: Total Form Submissions */}
                    <div className="dragwyb-db-stat-tile tile-green">
                        <div className="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div className="stat-content">
                            <div className="stat-number">{stats.totalSubmissions}</div>
                            <div className="stat-label">Total Form Submissions</div>
                        </div>
                    </div>

                    {/* Tile 3: Unique Visitors */}
                    <div className="dragwyb-db-stat-tile tile-purple">
                        <div className="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div className="stat-content">
                            <div className="stat-number">{stats.uniqueVisitors}</div>
                            <div className="stat-label">Unique Visitors</div>
                        </div>
                    </div>

                    {/* Tile 4: Total Sessions */}
                    <div className="dragwyb-db-stat-tile tile-blue">
                        <div className="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                        </div>
                        <div className="stat-content">
                            <div className="stat-number">{stats.totalSessions}</div>
                            <div className="stat-label">Total Sessions</div>
                        </div>
                    </div>

                    {/* Tile 5: Total PageViews */}
                    <div className="dragwyb-db-stat-tile tile-orange">
                        <div className="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                        <div className="stat-content">
                            <div className="stat-number">{stats.totalPageViews}</div>
                            <div className="stat-label">Total PageViews</div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Card 2: Support */}
            <div className="dragwyb-db-card dragwyb-support-card">
                <div className="dragwyb-support-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                        <path d="M320 128C241 128 175.3 185.3 162.3 260.7C171.6 257.7 181.6 256 192 256L208 256C234.5 256 256 277.5 256 304L256 400C256 426.5 234.5 448 208 448L192 448C139 448 96 405 96 352L96 288C96 164.3 196.3 64 320 64C443.7 64 544 164.3 544 288L544 456.1C544 522.4 490.2 576.1 423.9 576.1L336 576L304 576C277.5 576 256 554.5 256 528C256 501.5 277.5 480 304 480L336 480C362.5 480 384 501.5 384 528L384 528L424 528C463.8 528 496 495.8 496 456L496 435.1C481.9 443.3 465.5 447.9 448 447.9L432 447.9C405.5 447.9 384 426.4 384 399.9L384 303.9C384 277.4 405.5 255.9 432 255.9L448 255.9C458.4 255.9 468.3 257.5 477.7 260.6C464.7 185.3 399.1 127.9 320 127.9z" />
                    </svg>
                </div>
                <h3 className="dragwyb-support-title">Support</h3>
                <p className="dragwyb-support-desc">Need help? We're here for you.</p>
                <div className="dragwyb-support-actions">
                    <a href={freeSupportUrl} target="_blank" rel="noopener noreferrer" className="dragwyb-btn-doc">
                        Free Support
                    </a>
                    <a href={supportUrl} target="_blank" rel="noopener noreferrer" className="dragwyb-btn-premium">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                        </svg>
                        Premium Support
                    </a>
                </div>
            </div>

            {/* Card 3: More Plugins */}
            <div className="dragwyb-db-card dragwyb-promo-card">
                <div className="dragwyb-promo-header">
                    <div className="dragwyb-star-badge">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#f43f5e" stroke="none">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </div>
                    <div>
                        <h3 className="dragwyb-promo-title">More Plugins by Dragwyb</h3>
                        <p className="dragwyb-promo-subtitle">Powerful plugins to extend your website.</p>
                    </div>
                </div>

                {extraPlugins.map((plugin, index) =>
                    <div className="dragwyb-plugin-feature-box" key={index}>
                        <div className="plugin-icon-bubble">
                            <img src={`${puginUrl}assets/images/${plugin.icon}`} loading="lazy" alt="" />
                        </div>
                        <div className="plugin-info">
                            <h4 className="plugin-name">{plugin.name}</h4>
                            <p className="plugin-desc">{plugin.description}</p>
                        </div>
                        <div className="dragwyb-plugin-btn-wrapper">
                            <a href={plugin.url} className="dragwyb-btn-install" target="_blank" rel="noopener noreferrer">
                                Install Now
                            </a>
                        </div>
                    </div>
                )}

                <div className="dragwyb-promo-footer">
                    <a href={morePluginsUrl} target="_blank" rel="noopener noreferrer" className="dragwyb-link-arrow">
                        View All Plugins &rarr;
                    </a>
                </div>
            </div>
        </aside>
    );
};

export default SidebarDetails;
