/**
 * Flexible Components
 * 
 * Components listed here are exempt from:
 * - Strict element naming validation (can use any nested selectors)
 * - Max nesting depth limit (default: 5)
 * - ID selector restriction (can use #id selectors)
 * 
 * Add component names (without the c--/g-- prefix and -letter/-number suffix)
 * Example: 'content' matches c--content-a, g--content-01, etc.
 */
export const flexible = () => {
   return ['content', 'table', 'slider'];
}
