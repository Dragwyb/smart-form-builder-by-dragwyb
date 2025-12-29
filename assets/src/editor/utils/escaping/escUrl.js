/**
 * Default allowed protocols (mirrors WordPress wp_allowed_protocols)
 */
const ALLOWED_PROTOCOLS = ['http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'irc6', 'ircs', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'sms', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn'];

/**
 * Helper: Recursively removes hex encoded CR/LF characters to prevent injection.
 * mimics _deep_replace
 */
const deepCleanCRLF = (url) => {
    const stripRegex = /%0d|%0a|%0D|%0A/g;
    let currentUrl = url;
    let previousUrl = '';

    while (previousUrl !== currentUrl) {
        previousUrl = currentUrl;
        currentUrl = currentUrl.replace(stripRegex, '');
    }
    return currentUrl;
};

/**
 * Checks and cleans a URL.
 *
 * @param {string} url The URL to be cleaned.
 * @param {string[]} [protocols=null] Array of allowed protocols.
 * @param {string} [context='display'] 'display' handles entities, other contexts do not.
 * @return {string} The cleaned URL.
 */
export const escUrl = (url, protocols = null, context = 'display') => {
    if (!url || typeof url !== 'string') {
        return '';
    }

    // 1. Trim and encode spaces
    let cleanUrl = url.trim().replace(/ /g, '%20');

    // 2. Remove invalid characters (preserving extended ASCII)
    cleanUrl = cleanUrl.replace(/[^a-z0-9\-~+_.?#=!&;,/:%@$|*'()\[\]\x80-\xff]/gi, '');

    if (cleanUrl === '') return cleanUrl;

    // 3. Deep replace CRLF (unless it's mailto)
    if (!/^mailto:/i.test(cleanUrl)) {
        cleanUrl = deepCleanCRLF(cleanUrl);
    }

    // 4. Fix common protocol typos
    cleanUrl = cleanUrl.replace(';//', '://');

    // 5. Prepend http:// if missing scheme
    // Checks: No colon, not relative (/, #, ?), and not a PHP file
    const hasScheme = cleanUrl.includes(':');
    const isRelative = ['/', '#', '?'].includes(cleanUrl.charAt(0));
    const isScript = /^[a-z0-9-]+\.php/i.test(cleanUrl);

    if (!hasScheme && !isRelative && !isScript) {
        cleanUrl = `http://${cleanUrl}`;
    }

    // 6. Handle Context: Display (Normalize entities)
    if (context === 'display') {
        cleanUrl = cleanUrl.replace(/&amp;/g, '&#038;').replace(/'/g, '&#039;');
    }

    // 7. Handle Square Brackets []
    // We must separate the authority (user:pass@host:port) from the path/query
    if (cleanUrl.includes('[') || cleanUrl.includes(']')) {
        let front = '';
        let end = cleanUrl;

        // Try to match scheme://authority vs the rest
        const match = cleanUrl.match(/^([a-z0-9+.-]+:\/\/)([^/]+)(.*)$/i);
        const matchRel = cleanUrl.match(/^(\/\/)([^/]+)(.*)$/); // Protocol relative

        if (match) {
            front = match[1] + match[2];
            end = match[3] || '';
        } else if (matchRel) {
            front = matchRel[1] + matchRel[2];
            end = matchRel[3] || '';
        }

        const cleanEnd = end.replace(/\[/g, '%5B').replace(/\]/g, '%5D');
        cleanUrl = `${front}${cleanEnd}`;
    }

    // 8. Protocol Validation
    if (cleanUrl.startsWith('/')) {
        return cleanUrl;
    }

    const checkProtocols = protocols || ALLOWED_PROTOCOLS;
    const colonIndex = cleanUrl.indexOf(':');

    if (colonIndex > 0) {
        const scheme = cleanUrl.substring(0, colonIndex).toLowerCase();
        if (!checkProtocols.includes(scheme)) {
            return ''; // Invalid protocol
        }
    }

    return cleanUrl;
};