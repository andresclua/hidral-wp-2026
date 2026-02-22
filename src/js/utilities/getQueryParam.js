/**
 * Retrieves a query parameter from the current URL.
 * This function checks if a specific query parameter exists in the URL's search string (the part after the "?").
 * 
 * @param {string} param - The name of the query parameter you want to check in the URL.
 * @returns {boolean} - Returns `true` if the query parameter is present, otherwise returns `false`.
 * Example usage:
 * const isPageParamPresent = getQueryParam('debug'); // Returns true because 'debug' is in the URL
 * const isLimitParamPresent = getQueryParam('limit'); // Returns false because 'limit' is not in the URL
*/
export const getQueryParam = (param) => {
    // Create a new instance of URLSearchParams, passing in the search string of the current URL (i.e., the part after the "?").
    const urlParams = new URLSearchParams(window.location.search);
    
    // Check if the parameter exists in the URL query string and return true if found.
    // URLSearchParams.has() checks if the provided key exists in the query string.
    return urlParams.has(param); // If the parameter is found, it returns true, otherwise false
}
