/**
 * Objects to group
 * @param {Array} items
 * @param {string} key
 * @param {string} defaultValue
 * Useage:
 * const groupedObjects = groupObjects(items, 'type' , 'other');
 */
export const groupObjects = (items, key, defaultValue = 'other' ) => {
    return items.reduce((acc, item) => {
        const groupKey = item[key] || defaultValue;
        if (!acc[groupKey]) {
            acc[groupKey] = [];
        }
        acc[groupKey].push(item);
        return acc;
    }, {});
};


/**
 * Filter and group objects
 * @param {Array} items - Array of objects
 * @param {Object} filters - Filter criteria
 * @param {string} groupKey - Key to group by
 * @returns {Object} - Filtered and grouped objects
 * Usage:
 * Filter and group in one step
 * const filteredAndGrouped = filterAndGroupObjects(items, 
 *    { isPro: false }, 
 *    'type'
 * );
 */
export const filterAndGroupObjects = (items, filters = {}, groupKey) => {
    const filteredItems = filterObjects(items, filters);
    if (groupKey) {
        return groupObjects(filteredItems, groupKey);
    }
    return filteredItems;
};

/**
 * Filter objects based on provided criteria
 * @param {Array} items - Array of objects to filter
 * @param {Object} filters - Filter criteria
 * @returns {Array} - Filtered array of objects
 * Usage:
 * const freeElements = filterObjects(items, { 
 *      type: 'element',
 *      isPro: false 
 *  });
 * 
 * Multiple types:
 * const multipleTypes = filterObjects(items, { 
 *    type: ['element', 'select'] 
 * });
 * 
 * Filter with custom predicate
 * const customFilter = filterObjects(items, {
 *    name: (name) => name && name.includes('Product')
 * });
 */
export const filterObjects = (items, filters = {}) => {
    return items.filter(item => {
        return Object.entries(filters).every(([key, value]) => {
            // If filter value is array, check if item value is in array
            if (Array.isArray(value)) {
                return value.includes(item[key]);
            }
            
            // If filter value is function, use it as predicate
            if (typeof value === 'function') {
                return value(item[key]);
            }

            // Handle special case for pro/free status
            if (key === 'isPro') {
                return value === Boolean(item.is_pro);
            }

            // Default exact match
            return item[key] === value;
        });
    });
};

export const trimWords = (text, wordLimit = 23, ellipsis = '....') => {
    if (!text) return '';
    
    const words = text.split(/\s+/); // Split text by whitespace
    if (words.length > wordLimit) {
        return words.slice(0, wordLimit).join(' ') + ' ' + ellipsis;
    }
    return text;
};

/**
 * Get text content from HTML
 * @param {*} html 
 * @returns 
 */
export const getTextFromHtml = (html) => {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    return tempDiv.textContent || tempDiv.innerText || '';
}

export const sanitizeHtml = (html, allowedTags = ['a', 'b', 'br', 'em', 'i', 'p', 'span', 'strong', 'u']) => {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;

    const elements = tempDiv.querySelectorAll('*');
    elements.forEach((el) => {
        if (!allowedTags.includes(el.tagName.toLowerCase())) {
            el.replaceWith(...el.childNodes); // Remove tag but keep inner content
        }
    });

    return tempDiv.innerHTML;
}

/**
 * Number to absolute integer.
 */
const numberToAbsInt = ( num = 0 ) => {
    if ( ! isNaN( num ) ) {
        num = parseFloat( num );
        num = Math.abs( num );
        num = Math.floor( num );
    } else {
        num = 0;
    }

    return num;
};

/**
 * Get random absolute integer.
 */
const getRandomAbsInt = ( min = 0, max = 9 ) => {
    min = numberToAbsInt( min );
    max = numberToAbsInt( max );

    return ( Math.floor( Math.random() * ( max - min + 1 ) + min ) );
};

/**
 * Get unique Id.
 */
export const getUniqueId = ( uniqueId = 0 ) => {
    uniqueId = numberToAbsInt( uniqueId );
    uniqueId = ( ( 0 < uniqueId ) ? uniqueId : ( Date.now()  + '' + getRandomAbsInt( 1, 9 ) ) );

    return numberToAbsInt( uniqueId );
};

/**
 * Absolute number to string.
 */
export const absoluteNumberToString = ( input ) => {
    // Check if the input is an array
    if ( Array.isArray( input ) ) {
        // Map through the array and convert each number
        return input.map( num => Math.abs( num ).toString() );
    } else {
        // Handle single number input
        return Math.abs( input ).toString();
    }
}

/**
 * Convert string to absolute number.
 */
export const stringToAbsoluteNumber = ( input ) => {
    // Check if the input is an array
    if ( Array.isArray( input ) ) {
        // Map through the array and convert each string to an absolute number
        return input.map( str => Math.abs( Number( str ) ) );
    } else {
        // Handle single string input
        return Math.abs(Number(input));
    }
}