import stylelint from "stylelint";
import { experimental } from "./modifiers.mjs";
import { flexible } from "./flexible.mjs";

const ruleName = "terra/selector-naming";
const meta = { url: "https://github.com/terra/stylelint-terra" };

/* ============================================
 * MESSAGES
 * ============================================ */

const messages = stylelint.utils.ruleMessages(ruleName, {
  invalidComponent: (selector) =>
    `Invalid component name "${selector}". Must be "c--[name]-[letter]" or "g--[name]-[number]"`,
  invalidElement: (element) =>
    `Invalid element "__${element}". Must be a reserved word.`,
  invalidModifier: (modifier) =>
    `Invalid modifier "--${modifier}". Must be a reserved modifier.`,
  formModifierOutsideForm: (modifier) =>
    `Modifier "--${modifier}" is only valid in form components (c--form-*).`,
  missingOrdinalModifier: (found, missing) =>
    `Found "--${found}" but missing "--${missing}". Ordinal modifiers must be sequential.`,
  jsClassWithStyles: (selector) =>
    `JavaScript hook "${selector}" should not have styles. Use c-- or g-- components instead.`,
  invalidClassPrefix: (selector) =>
    `Invalid class "${selector}". All classes must use "c--" or "g--" prefix.`,
  invalidNestedSelector: (selector) =>
    `Invalid nested selector "${selector}". Use "__" for elements or "--" for modifiers.`,
  maxNestingDepth: (depth, max) =>
    `Expected nesting depth to be at most ${max}, but found ${depth}.`,
  noIdSelectors: (selector) =>
    `ID selector "${selector}" is not allowed. Use classes instead.`,
});

/* ============================================
 * RESERVED ELEMENTS (Terra Framework)
 * These are the only allowed BEM element names
 * ============================================ */

const reservedElements = new Set([
  // Structure
  "hd",           // header
  "bd",           // body
  "ft",           // footer
  "wrapper",
  "item",
  "list-item",
  "item-left",
  "item-right",
  "list-group",

  // Content
  "title",
  "subtitle",
  "content",
  "date",
  "category",
  "author",

  // Interactive
  "btn",
  "link",
  "icon",
  "badge",
  "pill",
  "logo",

  // Media
  "media",
  "media-wrapper",
  "video",
  "artwork",

  // Groups
  "ft-items",
  "bg-items",

  // UI Elements
  "dash",
  "overlay",
  "loader",
]);

/* ============================================
 * RESERVED MODIFIERS (Terra Framework)
 * ============================================ */

// Ordinal modifiers (must be sequential: second, third, fourth...)
const ordinalModifiers = [
  "second",
  "third",
  "fourth",
  "fifth",
  "sixth",
  "seventh",
  "eighth",
  "ninth",
  "tenth",
];

// State modifiers
const stateModifiers = new Set([
  "is-hidden",
  "is-active",
  "is-scroll",
  "is-loading",
  "is-loaded",
]);

// Animation modifiers
const animationModifiers = new Set([
  "fade",
  "fade-in",
  "fade-out",
  "slide-in",
  "slide-out",
]);

// Form-only modifiers (only valid inside c--form-* components)
const formModifiers = new Set([
  "valid",
  "error",
  "disabled",
  "focused",
  "required",
]);

// Combined base modifiers
const baseModifiers = new Set([
  ...stateModifiers,
  ...animationModifiers,
  ...ordinalModifiers,
]);

/* ============================================
 * PROJECT-SPECIFIC EXTENSIONS
 * Add custom elements and modifiers for this project
 * ============================================ */

// Experimental modifiers (testing, may be promoted to project or removed)
const experimentalModifiers = experimental();

/* ============================================
 * PATTERNS & REGEX
 * ============================================ */

// Component name formats
const customComponentPattern = /^\.c--[a-z]+(-[a-z]+)*-[a-z]$/;
const globalComponentPattern = /^\.g--[a-z]+(-[a-z]+)*-\d{2}$/;
const special404Pattern = /^\.c--404-[a-z]$/;
const special404GlobalPattern = /^\.g--404-\d{2}$/;

// Extract Terra components from selectors
const terraComponentInSelectorPattern = /\.c--[a-z]+(?:-[a-z]+)*-[a-z]|\.g--[a-z]+(?:-[a-z]+)*-\d{2}|\.c--404-[a-z]|\.g--404-\d{2}/g;

// Pattern checks
const hasTerraComponentPattern = /\.(c--|g--)/;
const hasFormComponentPattern = /\.c--form-/;

// JavaScript hooks (should not have styles)
const jsHookPattern = /\.js--[a-z0-9-]+/gi;

// Modifiers: match `--x` but NOT when preceded by `_` or `.c` or `.g`
const modifierPattern = /(?<![_.cg])--([a-z]+(?:-[a-z]+)*)/g;

// Elements: match `__x`
const elementPattern = /__([a-z]+(?:-[a-z]+)*)/g;

// Form sub-component modifiers (e.g., --form-radio-a)
const formSubComponentModifierPattern = /^form-[a-z]+(-[a-z]+)*-[a-z]$/;

// Invalid nested selector: single underscore
const invalidSingleUnderscorePattern = /&_(?!_)([a-z]+(?:-[a-z]+)*)/g;

// ID selectors pattern
const idSelectorPattern = /#[a-zA-Z][a-zA-Z0-9_-]*/g;

/* ============================================
 * FLEXIBLE COMPONENTS (No strict validation)
 * These component types allow any nested selectors:
 * - c--content-{x} / g--content-{xx}: CMS content
 * - c--table-{x} / g--table-{xx}: Table structures
 * ============================================ */

const escapeRegex = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

const flexibleComponentFilePattern = new RegExp(
  String.raw`[/\\]_?[cg]--(${flexible().map(escapeRegex).join('|')})-[a-z0-9]+\.scss$`
);

/* ============================================
 * HELPER FUNCTIONS
 * ============================================ */

/**
 * Remove content inside :has(), :not(), :is(), :where()
 */
function stripFunctionalPseudoContent(selector) {
  return selector.replace(/:(has|not|is|where)\([^)]*\)/gi, ":$1()");
}

/**
 * Split selector list by commas, respecting parentheses
 */
function splitSelectorList(selector) {
  const parts = [];
  let current = "";
  let depth = 0;

  for (const char of selector) {
    if (char === "(") depth++;
    if (char === ")") depth--;

    if (char === "," && depth === 0) {
      parts.push(current.trim());
      current = "";
    } else {
      current += char;
    }
  }

  if (current.trim()) {
    parts.push(current.trim());
  }

  return parts;
}

function containsTerraComponent(selectorPart) {
  return hasTerraComponentPattern.test(selectorPart);
}

function containsFormComponent(selectorPart) {
  return hasFormComponentPattern.test(selectorPart);
}

function extractTerraComponents(selector) {
  return selector.match(terraComponentInSelectorPattern) || [];
}

function isValidComponentName(className) {
  return (
    customComponentPattern.test(className) ||
    globalComponentPattern.test(className) ||
    special404Pattern.test(className) ||
    special404GlobalPattern.test(className)
  );
}

/* ============================================
 * AST HELPERS
 * ============================================ */

function isInsideTerraComponent(rule) {
  return Boolean(findNearestComponentRule(rule));
}

function isInsideFormComponent(rule) {
  const componentRule = findNearestComponentRule(rule);
  if (!componentRule) return false;
  return containsFormComponent(componentRule.selector);
}

function findNearestComponentRule(rule) {
  let parent = rule.parent;
  while (parent) {
    if (parent.type === "rule") {
      const sel = parent.selector;
      if (sel && containsTerraComponent(sel)) return parent;
    }
    parent = parent.parent;
  }
  return null;
}

/* ============================================
 * ORDINAL VALIDATION
 * ============================================ */

function collectOrdinalModifiers(componentRule) {
  const found = new Map();

  const walk = (node) => {
    if (node.type === "rule" && node.selector) {
      for (const match of node.selector.matchAll(modifierPattern)) {
        const modifier = match[1];
        if (ordinalModifiers.includes(modifier) && !found.has(modifier)) {
          found.set(modifier, node);
        }
      }
    }

    if (node.nodes) {
      for (const child of node.nodes) walk(child);
    }
  };

  walk(componentRule);
  return found;
}

function validateOrdinalSequence(foundOrdinals, result) {
  if (!foundOrdinals.size) return;

  let highestIndex = -1;
  let highestModifier = null;
  let highestNode = null;

  for (const [modifier, node] of foundOrdinals) {
    const idx = ordinalModifiers.indexOf(modifier);
    if (idx > highestIndex) {
      highestIndex = idx;
      highestModifier = modifier;
      highestNode = node;
    }
  }

  for (let i = 0; i < highestIndex; i++) {
    const required = ordinalModifiers[i];
    if (!foundOrdinals.has(required)) {
      stylelint.utils.report({
        message: messages.missingOrdinalModifier(highestModifier, required),
        node: highestNode,
        result,
        ruleName,
      });
    }
  }
}

/* ============================================
 * NESTING DEPTH VALIDATION
 * ============================================ */

const MAX_NESTING_DEPTH = 5;

// Pattern to detect pseudo-class/pseudo-element selectors
// Matches selectors like &:hover, &:focus, &::before, &:nth-child(2), &:hover &, etc.
const pseudoSelectorPattern = /^&[:]{1,2}[a-z-]+(\([^)]*\))?(\s+&)?$/i;

/**
 * Check if a selector is primarily a pseudo-class/pseudo-element selector
 * These don't count towards nesting depth (same as stylelint's max-nesting-depth with ignore: pseudo-classes)
 */
function isPseudoOnlySelector(selector) {
  if (!selector) return false;
  // Handle comma-separated selectors - check if ALL parts are pseudo selectors
  const parts = selector.split(',').map(s => s.trim());
  return parts.every(part => pseudoSelectorPattern.test(part));
}


/**
 * Calculate the nesting depth of a rule
 * Ignores blockless at-rules, media queries, and pseudo-classes (same as stylelint's max-nesting-depth)
 */
function calculateNestingDepth(rule) {
  let depth = 0;
  let parent = rule.parent;

  while (parent && parent.type !== "root") {
    if (parent.type === "rule") {
      // Don't count pseudo-class/pseudo-element only selectors
      if (!isPseudoOnlySelector(parent.selector)) {
        depth++;
      }
    } else if (parent.type === "atrule") {
      // Ignore blockless at-rules (like @include, @extend) and @media queries
      // This matches native stylelint max-nesting-depth behavior
      const ignoredAtRules = [
        // Blockless at-rules
        "include", "extend", "import", "use", "forward", "mixin", "function", "return", "warn", "debug", "error", "at-root",
        // At-rules that should not count towards nesting depth (like native stylelint)
        "media", "supports", "document", "layer"
      ];
      if (!ignoredAtRules.includes(parent.name)) {
        depth++;
      }
    }
    parent = parent.parent;
  }

  return depth;
}

/* ============================================
 * ID SELECTOR VALIDATION
 * ============================================ */

/**
 * Validate that no ID selectors are used
 */
function validateNoIdSelectors(selector, rule, result) {
  const parts = splitSelectorList(selector);

  for (const part of parts) {
    const strippedPart = stripFunctionalPseudoContent(part);
    const idMatches = strippedPart.match(idSelectorPattern);
    if (idMatches) {
      for (const id of idMatches) {
        report(result, rule, messages.noIdSelectors(id));
      }
    }
  }
}

/* ============================================
 * VALIDATION FUNCTIONS
 * ============================================ */

function report(result, node, message) {
  stylelint.utils.report({ message, node, result, ruleName });
}

/**
 * Check if element is valid (reserved or project-specific)
 */
function isValidElement(element) {
  return reservedElements.has(element);
}

/**
 * Check if modifier is valid (base, project, or experimental)
 */
function isValidModifier(modifier) {
  return (
    baseModifiers.has(modifier) ||
    experimentalModifiers.has(modifier)
  );
}

function validateRootComponentSelector(rule, result) {
  const selector = rule.selector;
  const parts = splitSelectorList(selector);

  for (const part of parts) {
    const strippedPart = stripFunctionalPseudoContent(part);

    // Check for JS hooks
    const jsHooks = strippedPart.match(jsHookPattern);
    if (jsHooks) {
      for (const hook of jsHooks) {
        report(result, rule, messages.jsClassWithStyles(hook));
      }
    }

    // Extract and validate classes
    const classMatches = [...strippedPart.matchAll(/\.([a-z][a-z0-9-]*)/gi)];

    for (const match of classMatches) {
      const className = `.${match[1]}`;

      if (className.startsWith(".js--")) continue;
      if (className.startsWith(".u--") || className.startsWith(".f--")) continue;

      if (!className.startsWith(".c--") && !className.startsWith(".g--")) {
        report(result, rule, messages.invalidClassPrefix(className));
        continue;
      }

      if (!isValidComponentName(className)) {
        const components = extractTerraComponents(className);
        if (components.length === 0) {
          report(result, rule, messages.invalidComponent(className));
        } else {
          for (const component of components) {
            if (!isValidComponentName(component)) {
              report(result, rule, messages.invalidComponent(component));
            }
          }
        }
      }
    }
  }
}

function validateNestedElements(selector, rule, result) {
  const parts = splitSelectorList(selector);

  for (const part of parts) {
    const strippedPart = stripFunctionalPseudoContent(part);
    for (const match of strippedPart.matchAll(elementPattern)) {
      const element = match[1];
      if (!isValidElement(element)) {
        report(result, rule, messages.invalidElement(element));
      }
    }
  }
}

function validateNestedSelectorSyntax(selector, rule, result) {
  const parts = splitSelectorList(selector);

  for (const part of parts) {
    const strippedPart = stripFunctionalPseudoContent(part);
    for (const match of strippedPart.matchAll(invalidSingleUnderscorePattern)) {
      const fullMatch = `&_${match[1]}`;
      report(result, rule, messages.invalidNestedSelector(fullMatch));
    }
  }
}

function validateNestedModifiers(selector, rule, result) {
  const isInForm = isInsideFormComponent(rule);
  const parts = splitSelectorList(selector);

  for (const part of parts) {
    const partIsInForm = isInForm || containsFormComponent(part);
    const strippedPart = stripFunctionalPseudoContent(part);

    for (const match of strippedPart.matchAll(modifierPattern)) {
      const modifier = match[1];

      // Form-only modifiers
      if (formModifiers.has(modifier) || formSubComponentModifierPattern.test(modifier)) {
        if (!partIsInForm) {
          report(result, rule, messages.formModifierOutsideForm(modifier));
        }
        continue;
      }

      // Check all modifier sources
      if (!isValidModifier(modifier)) {
        report(result, rule, messages.invalidModifier(modifier));
      }
    }
  }
}

/* ============================================
 * MAIN RULE FUNCTION
 * ============================================ */

/** @type {import('stylelint').Rule} */
const ruleFunction = (primary) => {
  return (root, result) => {
    const validOptions = stylelint.utils.validateOptions(result, ruleName, {
      actual: primary,
      possible: [true],
    });
    if (!validOptions) return;

    // Check if this is a content component file (skip strict validation)
    const filePath = root.source?.input?.file || "";
    const isFlexibleComponentFile = flexibleComponentFilePattern.test(filePath);

    const processedComponentBases = new Set();

    root.walkRules((rule) => {
      // Skip keyframes
      if (rule.parent?.type === "atrule" && rule.parent?.name === "keyframes") {
        return;
      }

      const selector = rule.selector;
      if (!selector) return;

      // Skip utility/foundation only selectors
      const hasOnlyUtilityOrFoundation =
        (selector.includes(".u--") || selector.includes(".f--")) &&
        !selector.match(/\.(c--|g--|js--)/);
      if (hasOnlyUtilityOrFoundation) return;

      // Validations that skip flexible components
      if (!isFlexibleComponentFile) {
        // Nesting depth validation
        const depth = calculateNestingDepth(rule);
        if (depth > MAX_NESTING_DEPTH) {
          report(result, rule, messages.maxNestingDepth(depth, MAX_NESTING_DEPTH));
        }

        // ID selector validation
        validateNoIdSelectors(selector, rule, result);
      }

      const isNested = selector.startsWith("&");

      // Root selectors (not nested)
      if (!isNested) {
        if (isFlexibleComponentFile) return;

        validateRootComponentSelector(rule, result);

        // Ordinal checks
        const components = extractTerraComponents(selector);
        for (const component of components) {
          if (!processedComponentBases.has(component)) {
            processedComponentBases.add(component);
            const foundOrdinals = collectOrdinalModifiers(rule);
            validateOrdinalSequence(foundOrdinals, result);
          }
        }

        return;
      }

      // Nested selectors
      if (isNested) {
        if (!isInsideTerraComponent(rule)) return;
        if (isFlexibleComponentFile) return;

        validateNestedSelectorSyntax(selector, rule, result);
        validateNestedElements(selector, rule, result);
        validateNestedModifiers(selector, rule, result);
      }
    });
  };
};

ruleFunction.ruleName = ruleName;
ruleFunction.messages = messages;
ruleFunction.meta = meta;

export default stylelint.createPlugin(ruleName, ruleFunction);
