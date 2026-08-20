import React, { useState, useEffect } from 'react';
import {
    FaChartLine,
    FaFileAlt,
    FaCheckCircle,
    FaUser,
    FaDesktop,
    FaEye,
    FaLifeRing,
    FaBookOpen,
    FaHeadset,
    FaStar,
    FaArrowRight
} from 'react-icons/fa';

const SidebarDetails = () => {
    const {
        restUrl,
        nonce,
        analyticsData,
        extraPlugins = [],
        documentationUrl = 'https://dragwyb.com/docs',
        supportUrl = 'https://dragwyb.com/support',
        morePluginsUrl = 'https://dragwyb.com/plugins',
        totalForms = '0',
    } = window.DragwybSettingsData || {};

    const [timeRange, setTimeRange] = useState('1');
    const [isFetchingAnalytics, setIsFetchingAnalytics] = useState(false);

    // Key-value cache state for analytics: e.g. { all: {...}, '30days': {...}, '7days': {...}, 'today': {...} }
    const [analyticsCache, setAnalyticsCache] = useState(() => {
        if (analyticsData) {
            return { all: analyticsData };
        }
        return {};
    });

    useEffect(() => {
        // If data for the selected filter already exists in state cache, return early instead of fetching again!
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

    // Retrieve active stats from key-value state cache or fallback
    const currentStats = analyticsCache[timeRange] || analyticsCache['all'] || analyticsData || {};

    const stats = {
        totalSubmissions: currentStats.totalSubmissions || '0',
        uniqueVisitors: currentStats.uniqueVisitors || '0',
        totalSessions: currentStats.totalSessions || '0',
        totalPageViews: currentStats.totalPageViews || '0'
    };

    return (
        <aside className="dragwyb-settings-sidebar-details">
            {/* Card 1: Form Analytics Overview */}
            <div className="dragwyb-sidebar-card dragwyb-analytics-card">
                <div className="dragwyb-sidebar-card-header">
                    <div className="dragwyb-sidebar-header-title">
                        <FaChartLine className="pink-icon" />
                        <span>Form Analytics Overview</span>
                        {isFetchingAnalytics && (
                            <span className="dragwyb-analytics-loading-dot" title="Fetching latest stats..."></span>
                        )}
                    </div>
                    <select
                        className="dragwyb-analytics-filter"
                        value={timeRange}
                        onChange={(e) => setTimeRange(e.target.value)}
                    >
                        <option value="-1">All Time</option>
                        <option value="30">Last 30 Days</option>
                        <option value="7">Last 7 Days</option>
                        <option value="1">Today</option>
                    </select>
                </div>

                <div className="dragwyb-stats-grid">
                    {/* Stat 1: Total Forms */}
                    <div className="dragwyb-stat-box stat-pink">
                        <div className="dragwyb-stat-icon-wrap icon-pink">
                            <FaFileAlt />
                        </div>
                        <div className="dragwyb-stat-meta">
                            <div className="dragwyb-stat-value">{totalForms}</div>
                            <div className="dragwyb-stat-label">Total Forms</div>
                        </div>
                    </div>

                    {/* Stat 2: Total Form Submissions */}
                    <div className="dragwyb-stat-box stat-green">
                        <div className="dragwyb-stat-icon-wrap icon-green">
                            <FaCheckCircle />
                        </div>
                        <div className="dragwyb-stat-meta">
                            <div className="dragwyb-stat-value">{stats.totalSubmissions}</div>
                            <div className="dragwyb-stat-label">Total Form Submissions</div>
                        </div>
                    </div>

                    {/* Stat 3: Unique Visitors */}
                    <div className="dragwyb-stat-box stat-purple">
                        <div className="dragwyb-stat-icon-wrap icon-purple">
                            <FaUser />
                        </div>
                        <div className="dragwyb-stat-meta">
                            <div className="dragwyb-stat-value">{stats.uniqueVisitors}</div>
                            <div className="dragwyb-stat-label">Unique Visitors</div>
                        </div>
                    </div>

                    {/* Stat 4: Total Sessions */}
                    <div className="dragwyb-stat-box stat-blue">
                        <div className="dragwyb-stat-icon-wrap icon-blue">
                            <FaDesktop />
                        </div>
                        <div className="dragwyb-stat-meta">
                            <div className="dragwyb-stat-value">{stats.totalSessions}</div>
                            <div className="dragwyb-stat-label">Total Sessions</div>
                        </div>
                    </div>

                    {/* Stat 5: Total PageViews (Full Width) */}
                    <div className="dragwyb-stat-box stat-orange stat-full">
                        <div className="dragwyb-stat-icon-wrap icon-orange">
                            <FaEye />
                        </div>
                        <div className="dragwyb-stat-meta">
                            <div className="dragwyb-stat-value">{stats.totalPageViews}</div>
                            <div className="dragwyb-stat-label">Total PageViews</div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Card 2: Support */}
            <div className="dragwyb-sidebar-card dragwyb-support-card">
                <div className="dragwyb-support-badge-wrap">
                    <div className="dragwyb-support-circle-icon">
                        <FaLifeRing />
                    </div>
                </div>
                <h3 className="dragwyb-support-title">Support</h3>
                <p className="dragwyb-support-subtitle">Need help? We're here for you.</p>

                <div className="dragwyb-support-actions">
                    <a
                        href={documentationUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="dragwyb-btn-outline"
                    >
                        <FaBookOpen /> Documentation
                    </a>
                    <a
                        href={supportUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="dragwyb-btn-pink-solid"
                    >
                        <FaHeadset /> Premium Support
                    </a>
                </div>
            </div>

            {/* Card 3: More Plugins by Dragwyb */}
            <div className="dragwyb-sidebar-card dragwyb-more-plugins-card">
                <div className="dragwyb-sidebar-card-header align-start">
                    <div className="dragwyb-icon-badge-pink">
                        <FaStar />
                    </div>
                    <div className="dragwyb-sidebar-header-text">
                        <h4 className="dragwyb-more-title">More Plugins by Dragwyb</h4>
                        <p className="dragwyb-more-subtitle">Powerful plugins to extend your website.</p>
                    </div>
                </div>

                {extraPlugins.map((plugin, index) => (
                    <div key={index} className="dragwyb-featured-plugin-box">
                        <div className="dragwyb-plugin-icon-wrap">
                            <img src={plugin.iconUrl} alt={plugin.name} loading="lazy" width="48" height="48" />
                        </div>
                        <div className="dragwyb-plugin-info">
                            <h5>{plugin.name}</h5>
                            <p>{plugin.description}</p>
                        </div>
                        <a
                            href={plugin.url}
                            className="dragwyb-btn-install"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Install
                        </a>
                    </div>
                ))}

                <div className="dragwyb-more-plugins-footer">
                    <a
                        href={morePluginsUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="dragwyb-view-all-link"
                    >
                        View All Plugins <FaArrowRight />
                    </a>
                </div>
            </div>
        </aside>
    );
};

export default SidebarDetails;
