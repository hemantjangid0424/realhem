import { ref } from 'vue';

const isVoiceModalOpen = ref(false);
const voiceCallback = ref(null);
const voiceSearchContext = ref({
    type: 'buy',
    city: 'Ahmedabad',
});

/**
 * City mappings with regex matching and canonical city names.
 */
const CITY_RULES = [
    { name: 'Ahmedabad', pattern: /\b(ahmedabad|amdavad|ahmadabad|vastral|bopal|gota|thandlodiya|motera|shela|science\s*city|sg\s*highway|prahlad\s*nagar|satellite|chandkheda|jagatpur|navrangpura|bodakdev|jodhpur)\b/i, isLocalityMatch: false },
    { name: 'Delhi NCR', pattern: /\b(delhi\s*ncr|delhi|new\s*delhi|noida|greater\s*noida|gurgaon|gurugram|ghaziabad|faridabad)\b/i },
    { name: 'Mumbai', pattern: /\b(mumbai|bombay|navi\s*mumbai|thane|bandra|andheri|borivali|kandivali|worli|juhu|powai)\b/i },
    { name: 'Bangalore', pattern: /\b(bangalore|bengaluru|whitefield|electronic\s*city|indiranagar|koramangala|bellandur|hsr\s*layout)\b/i },
    { name: 'Pune', pattern: /\b(pune|poona|hinjewadi|wakad|baner|kharadi|hadapsar|kothrud|viman\s*nagar)\b/i },
    { name: 'Hyderabad', pattern: /\b(hyderabad|secunderabad|gachibowli|hitech\s*city|madhapur|kondapur|kukatpally|jubilee\s*hills)\b/i },
    { name: 'Chennai', pattern: /\b(chennai|madras|omr|velachery|anna\s*nagar|porur|adyar|guindy)\b/i },
    { name: 'Kolkata', pattern: /\b(kolkata|calcutta|new\s*town|salt\s*lake|rajarhat|howrah|ballygunge)\b/i },
    { name: 'Surat', pattern: /\b(surat|vesu|adajan|pal|varachha)\b/i },
    { name: 'Vadodara', pattern: /\b(vadodara|baroda|alkapuri|gotri|vasna)\b/i },
    { name: 'Jaipur', pattern: /\b(jaipur|mansarovar|vaishali\s*nagar|jagatpura)\b/i },
    { name: 'Lucknow', pattern: /\b(lucknow|gomti\s*nagar|aliganj|indira\s*nagar)\b/i },
    { name: 'Chandigarh', pattern: /\b(chandigarh|mohali|panchkula|zirakpur)\b/i },
    { name: 'Kochi', pattern: /\b(kochi|cochin|kakkanad|edappally)\b/i },
    { name: 'Indore', pattern: /\b(indore|vijay\s*nagar|palasia)\b/i },
    { name: 'Goa', pattern: /\b(goa|panaji|margao|candolim)\b/i },
];

/**
 * Smart Real Estate Natural Language & Voice Search Query Parser.
 * Accurately extracts:
 * - City (e.g. 'Ahmedabad', 'Mumbai', 'Delhi NCR', etc.)
 * - BHK: ('1 BHK', '2 BHK', '3 BHK', '4+ BHK')
 * - Transaction Intent: ('buy', 'rent', 'commercial', 'plots', 'pg')
 * - Property Type: ('Residential Apartment', 'Independent House/Villa', 'Builder Floor', etc.)
 * - Cleaned residual locality / keyword with filler & stop-words removed.
 *
 * @param {string} rawQuery Voice transcript or user input query
 * @param {Object} options Context options (defaultType, defaultCity)
 * @returns {Object} Parsed search intent and parameters
 */
export function parseVoiceQuery(rawQuery, options = {}) {
    if (!rawQuery || typeof rawQuery !== 'string') {
        return {
            transcript: '',
            city: options.defaultCity || 'Ahmedabad',
            hasCity: false,
            bhk: null,
            bhks: [],
            hasBhk: false,
            type: options.defaultType || 'buy',
            hasType: false,
            propertyTypes: [],
            keyword: '',
            rawKeyword: '',
        };
    }

    const original = rawQuery.trim();
    let workingText = ` ${original.toLowerCase()} `;

    // ── 1. BHK / Bedroom Extraction ──────────────────────────────────────────
    const detectedBhks = new Set();
    const bhkPhrasesToRemove = [];

    // Pattern: "3 bhk", "3bhk", "3-bhk", "3 bed", "3 bedroom", "3 bedrooms", "3rk"
    const digitBhkRegex = /\b([1-9]|10)\s*(?:[-_ ]?)(?:bhk|b\.h\.k|b\s*h\s*k|beds?|bedrooms?|rk)\b/gi;
    let match;
    while ((match = digitBhkRegex.exec(workingText)) !== null) {
        const num = parseInt(match[1], 10);
        bhkPhrasesToRemove.push(match[0]);
        if (num === 1) detectedBhks.add('1 BHK');
        else if (num === 2) detectedBhks.add('2 BHK');
        else if (num === 3) detectedBhks.add('3 BHK');
        else if (num >= 4) detectedBhks.add('4+ BHK');
    }

    // Word patterns: "one bhk", "two bedroom", "three bhk", "four bedroom", "studio"
    const wordBhkMap = [
        { word: /\b(?:one|1)\s*(?:bhk|b\.h\.k|bed|beds|bedroom|bedrooms|rk)\b|\bstudio\b/gi, label: '1 BHK' },
        { word: /\b(?:two|2)\s*(?:bhk|b\.h\.k|bed|beds|bedroom|bedrooms)\b/gi, label: '2 BHK' },
        { word: /\b(?:three|3)\s*(?:bhk|b\.h\.k|bed|beds|bedroom|bedrooms)\b/gi, label: '3 BHK' },
        { word: /\b(?:four|five|six|4|5|6)\s*(?:bhk|b\.h\.k|bed|beds|bedroom|bedrooms)\b/gi, label: '4+ BHK' },
    ];

    wordBhkMap.forEach(({ word, label }) => {
        let wMatch;
        while ((wMatch = word.exec(workingText)) !== null) {
            bhkPhrasesToRemove.push(wMatch[0]);
            detectedBhks.add(label);
        }
    });

    // Clean BHK phrases from working text
    bhkPhrasesToRemove.forEach(phrase => {
        workingText = workingText.replace(new RegExp(`\\b${escapeRegExp(phrase.trim())}\\b`, 'gi'), ' ');
    });

    const bhksArray = Array.from(detectedBhks);
    const hasBhk = bhksArray.length > 0;
    const primaryBhk = hasBhk ? bhksArray[0] : null;

    // ── 2. City & Location Extraction ────────────────────────────────────────
    let matchedCity = null;
    let matchedCityPhrase = null;

    for (const rule of CITY_RULES) {
        const cityMatch = workingText.match(rule.pattern);
        if (cityMatch) {
            matchedCity = rule.name;
            matchedCityPhrase = cityMatch[0];
            break;
        }
    }

    if (matchedCityPhrase) {
        workingText = workingText.replace(new RegExp(`\\b${escapeRegExp(matchedCityPhrase)}\\b`, 'gi'), ' ');
    }

    const hasCity = !!matchedCity;
    const finalCity = matchedCity || options.defaultCity || 'Ahmedabad';

    // ── 3. Transaction Intent (Buy / Rent / Commercial / Plots / PG) ─────────
    let detectedType = null;
    let hasType = false;

    if (/\b(?:for\s+rent|on\s+rent|to\s+rent|rent|rental|lease|renting|for\s+lease)\b/i.test(workingText)) {
        detectedType = 'rent';
        hasType = true;
        workingText = workingText.replace(/\b(?:for\s+rent|on\s+rent|to\s+rent|rent|rental|lease|renting|for\s+lease)\b/gi, ' ');
    } else if (/\b(?:for\s+sale|to\s+buy|buy|sale|purchase|buying|resale|for\s+purchase)\b/i.test(workingText)) {
        detectedType = 'buy';
        hasType = true;
        workingText = workingText.replace(/\b(?:for\s+sale|to\s+buy|buy|sale|purchase|buying|resale|for\s+purchase)\b/gi, ' ');
    } else if (/\b(?:commercial|office|offices|shop|shops|showroom|showrooms|warehouse)\b/i.test(workingText)) {
        detectedType = 'commercial';
        hasType = true;
        workingText = workingText.replace(/\b(?:commercial|office|offices|shop|shops|showroom|showrooms|warehouse)\b/gi, ' ');
    } else if (/\b(?:plots?|lands?|agricultural\s*land)\b/i.test(workingText)) {
        detectedType = 'plots';
        hasType = true;
        workingText = workingText.replace(/\b(?:plots?|lands?|agricultural\s*land)\b/gi, ' ');
    } else if (/\b(?:pg|paying\s*guest|co-living|hostel)\b/i.test(workingText)) {
        detectedType = 'pg';
        hasType = true;
        workingText = workingText.replace(/\b(?:pg|paying\s*guest|co-living|hostel)\b/gi, ' ');
    }

    const finalType = detectedType || options.defaultType || 'buy';

    // ── 4. Property Category (Apartment / Villa / Floor) ─────────────────────
    const propertyTypes = [];
    if (/\b(?:flats?|apartments?|penthouses?)\b/i.test(workingText)) {
        propertyTypes.push('Residential Apartment');
        workingText = workingText.replace(/\b(?:flats?|apartments?|penthouses?)\b/gi, ' ');
    }
    if (/\b(?:villas?|bungalows?|row\s*houses?|independent\s*houses?|duplex|houses?)\b/i.test(workingText)) {
        propertyTypes.push('Independent House/Villa');
        workingText = workingText.replace(/\b(?:villas?|bungalows?|row\s*houses?|independent\s*houses?|duplex|houses?)\b/gi, ' ');
    }
    if (/\b(?:builder\s*floors?)\b/i.test(workingText)) {
        propertyTypes.push('Builder Floor');
        workingText = workingText.replace(/\b(?:builder\s*floors?)\b/gi, ' ');
    }

    // ── 5. Clean Residual Keyword (Locality / Landmark / Project) ─────────────
    // Remove filler and preposition stop-words
    const stopWordsRegex = /\b(in|at|near|around|for|to|on|with|under|by|the|a|an|me|find|showing|show|search|searching|looking\s+for|look\s+for|want|need|properties|property|please|available|ready|ready\s+to\s+move|new\s+launch|verified|from|within|of|and|or)\b/gi;
    let cleaned = workingText.replace(stopWordsRegex, ' ');

    // Remove stray punctuation and excessive whitespace
    cleaned = cleaned.replace(/[^a-zA-Z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();

    return {
        transcript: original,
        city: finalCity,
        hasCity,
        bhk: primaryBhk,
        bhks: bhksArray,
        hasBhk,
        type: finalType,
        hasType,
        propertyTypes,
        keyword: cleaned,
        rawKeyword: original,
    };
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

export function useVoiceSearch() {
    /**
     * Open voice search popup modal.
     * @param {Function} onResult Callback with (transcript, parsedQuery)
     * @param {Object} context Optional context (e.g. current property type, city)
     */
    const openVoiceModal = (onResult, context = {}) => {
        voiceCallback.value = onResult;
        voiceSearchContext.value = {
            type: context.type || 'buy',
            city: context.city || 'Ahmedabad',
        };
        isVoiceModalOpen.value = true;
    };

    const closeVoiceModal = () => {
        isVoiceModalOpen.value = false;
        voiceCallback.value = null;
    };

    const deliverResult = (transcript) => {
        if (voiceCallback.value && typeof voiceCallback.value === 'function') {
            const parsed = parseVoiceQuery(transcript, {
                defaultType: voiceSearchContext.value?.type || 'buy',
                defaultCity: voiceSearchContext.value?.city || 'Ahmedabad',
            });
            voiceCallback.value(transcript, parsed);
        }
        closeVoiceModal();
    };

    return {
        isVoiceModalOpen,
        voiceSearchContext,
        openVoiceModal,
        closeVoiceModal,
        deliverResult,
        parseVoiceQuery,
    };
}
