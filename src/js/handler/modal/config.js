import {
    standardConfig,
    videoConfig,
    mediaConfig,
    lottieConfig,
    ajaxConfig,
    htmlContentConfig,
    hubspotConfig,
} from "./variations/index.js";

export const MODAL_VARIATIONS = [
    { key: "standard",    enabled: true, selector: ".js--modal-button",             config: standardConfig },
    { key: "video",       enabled: true, selector: ".js--video-modal-button",       config: videoConfig },
    { key: "media",       enabled: true, selector: ".js--image-modal-button",       config: mediaConfig },
    { key: "lottie",      enabled: true, selector: ".js--lottie-modal-button",      config: lottieConfig },
    { key: "ajax",        enabled: true, selector: ".js--ajax-modal-button",        config: ajaxConfig },
    { key: "htmlContent", enabled: true, selector: ".js--htmlContent-modal-button", config: htmlContentConfig },
    { key: "hubspot",     enabled: true, selector: ".js--hubspot-modal-button",     config: hubspotConfig },
];
