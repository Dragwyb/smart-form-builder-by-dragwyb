(function() {
    'use strict';

    if (typeof dragwybTrackerCfg === 'undefined') return;

    var cfg = dragwybTrackerCfg;

    function getCookie(name) {
        var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return v ? v[2] : null;
    }

    function setCookie(name, value, days) {
        if (cfg.disableCookies === '1') return;
        var d = new Date();
        d.setTime(d.getTime() + 24 * 60 * 60 * 1000 * (days || 730));
        document.cookie = name + '=' + value + ';path=/;expires=' + d.toGMTString() + ';SameSite=Lax';
    }

    function generateUuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    var visitorUid = getCookie('dragwyb_uid');
    if (!visitorUid) {
        visitorUid = generateUuid();
        setCookie('dragwyb_uid', visitorUid, parseInt(cfg.cookieDuration, 10) || 730);
    }

    var sessionUid = getCookie('dragwyb_sid');
    if (!sessionUid) {
        sessionUid = generateUuid();
    }
    setCookie('dragwyb_sid', sessionUid, (parseInt(cfg.sessionTimeout, 10) || 30) / (24 * 60));

    function getDeviceType() {
        var ua = navigator.userAgent;
        if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) return 'tablet';
        if (/Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/i.test(ua)) return 'mobile';
        return 'desktop';
    }

    function getBrowser() {
        var ua = navigator.userAgent;
        if (ua.indexOf("Firefox") > -1) return "Firefox";
        if (ua.indexOf("Opera") > -1 || ua.indexOf("OPR") > -1) return "Opera";
        if (ua.indexOf("Trident") > -1) return "IE";
        if (ua.indexOf("Edge") > -1 || ua.indexOf("Edg") > -1) return "Edge";
        if (ua.indexOf("Chrome") > -1) return "Chrome";
        if (ua.indexOf("Safari") > -1) return "Safari";
        return "Unknown";
    }

    function getOs() {
        var ua = navigator.userAgent;
        if (ua.indexOf("Win") !== -1) return "Windows";
        if (ua.indexOf("Mac") !== -1) return "MacOS";
        if (ua.indexOf("Linux") !== -1) return "Linux";
        if (ua.indexOf("Android") !== -1) return "Android";
        if (ua.indexOf("like Mac") !== -1) return "iOS";
        return "Unknown";
    }

    function getUtmParams() {
        var params = new URLSearchParams(window.location.search);
        return {
            utm_source: params.get('utm_source') || '',
            utm_medium: params.get('utm_medium') || '',
            utm_campaign: params.get('utm_campaign') || '',
            utm_term: params.get('utm_term') || '',
            utm_content: params.get('utm_content') || ''
        };
    }

    function sendTracking(eventType, customData) {
        var utm = getUtmParams();
        var data = Object.assign({
            visitor_uid: visitorUid,
            session_uid: sessionUid,
            page_url: window.location.href,
            page_title: document.title,
            referrer: document.referrer || '',
            device_type: getDeviceType(),
            browser: getBrowser(),
            os: getOs(),
            screen_resolution: window.screen ? window.screen.width + 'x' + window.screen.height : '',
            language: navigator.language || ''
        }, utm, customData || {});

        var formData = new FormData();
        formData.append('action', 'dragwyb_track');
        formData.append('nonce', cfg.nonce);
        formData.append('event_type', eventType);
        formData.append('data', JSON.stringify(data));

        if (navigator.sendBeacon) {
            navigator.sendBeacon(cfg.ajaxUrl, formData);
        } else {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', cfg.ajaxUrl, true);
            xhr.send(formData);
        }
    }

    // Send pageview
    sendTracking('pageview');

    // Scroll Tracking
    var maxScroll = 0;
    var scrollTimer = null;
    window.addEventListener('scroll', function() {
        var h = document.documentElement, 
            b = document.body,
            st = 'scrollTop',
            sh = 'scrollHeight';
        var percent = Math.round((h[st]||b[st]) / ((h[sh]||b[sh]) - h.clientHeight) * 100);
        if (percent > maxScroll) {
            maxScroll = percent;
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function() {
                sendTracking('scroll', { scroll_depth: maxScroll });
            }, 1000);
        }
    });

    // Time on page Heartbeat
    var startTime = Date.now();
    setInterval(function() {
        var elapsedSeconds = Math.round((Date.now() - startTime) / 1000);
        sendTracking('heartbeat', { time_on_page: elapsedSeconds });
    }, 15000);

})();
