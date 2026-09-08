import { mountVideo, destroyVideo } from "@js/handler/video/utilities.js";

const VARIATION_MODIFIERS = {
    video: "c--modal-a--third-media",
    media: "c--modal-a--second-media",
    lottie: "c--modal-a--fourth-media",
    ajax: "c--modal-a--is-ajax",
    htmlContent: "c--modal-a--is-html",
    hubspot: "c--modal-a--is-hubspot",
};

function setVariation(modalEl, variation) {
    if (!modalEl) return;
    clearVariation(modalEl);
    const modifier = VARIATION_MODIFIERS[variation];
    if (modifier) modalEl.classList.add(modifier);
}

function clearVariation(modalEl) {
    if (!modalEl) return;
    Object.values(VARIATION_MODIFIERS).forEach((modifier) => modalEl.classList.remove(modifier));
}

export const standardConfig = ({ eventSystem }) => {
    return ({ element }) => {
        const content = document.querySelector(".js--modal-content");
        const source = element?.getAttribute("data-modal-target");

        return {
            selector: ".c--modal-a",
            openClass: "c--modal-a--is-active",
            beforeOpen: (modalEl) => {
                clearVariation(modalEl);
                if (!content) return;
                content.innerHTML = "";
                const tpl = source ? document.querySelector(source) : null;
                if (tpl) content.appendChild((tpl.content ?? tpl).cloneNode(true));
            },
            onClose: (modalEl) => {
                if (content) content.innerHTML = "";
                clearVariation(modalEl);
                try {
                    eventSystem?.destroyEvent({ library: "Modal", where: "Modal" });
                } catch (e) {
                    console.error("Error tearing down modal:", e);
                }
            },
        };
    };
};

export const videoConfig = ({ elements, eventSystem, boostify }) => {
    const triggers = prepareVideoTriggers(elements);

    return ({ element }) => {
        const trigger = triggers.find((t) => t.trigger === element);
        const content = document.querySelector('.js--modal-content');

        return {
            selector: ".c--modal-a",
            openClass: 'c--modal-a--is-active',
            beforeOpen: (modalEl) => {
                setVariation(modalEl, "video");
                if (!content) return;
                destroyVideo(content);
                if (trigger?.videoSrc) {
                    mountVideo(content, boostify, {
                        videoSrc: trigger.videoSrc,
                        poster: trigger.poster,
                        autoplay: trigger.autoplay,
                        forceplay: trigger.forceplay,
                    });
                }
            },
            onShow: (modalEl) => {

            },
            onClose: (modalEl) => {
                destroyVideo(content);
                clearVariation(modalEl);
                try {
                    eventSystem?.destroyEvent({ library: "Modal", where: "Modal" });
                } catch (e) {
                    console.error("Error tearing down modal:", e);
                }
            },
        };
    };
};

export const mediaConfig = ({ elements, eventSystem }) => {
    const triggers = prepareMediaTriggers(elements);

    return ({ element }) => {
        const trigger = triggers.find((t) => t.trigger === element);
        const content = document.querySelector('.js--modal-content');

        return {
            selector: ".c--modal-a",
            openClass: 'c--modal-a--is-active',
            beforeOpen: (modalEl) => {
                setVariation(modalEl, "media");
                if (!content) return;
                clearMedia(content);
                if (trigger?.src) mountMedia(content, trigger);
            },
            onShow: (modalEl) => {

            },
            onClose: (modalEl) => {
                clearMedia(content);
                clearVariation(modalEl);
                try {
                    eventSystem?.destroyEvent({ library: "Modal", where: "Modal" });
                } catch (e) {
                    console.error("Error tearing down modal:", e);
                }
            },
        };
    };
};

export const lottieConfig = ({ elements, eventSystem }) => {
    const triggers = prepareLottieTriggers(elements);

    return ({ element }) => {
        const trigger = triggers.find((t) => t.trigger === element);
        const content = document.querySelector('.js--modal-content');

        return {
            selector: ".c--modal-a",
            openClass: 'c--modal-a--is-active',
            beforeOpen: (modalEl) => {
                setVariation(modalEl, "lottie");
                if (!content) return;
                clearLottie(content, eventSystem);
                if (trigger?.path) mountLottie(content, trigger);
            },
            onShow: () => {
                const lotties = content?.querySelectorAll(".js--lottie-element");
                if (!lotties?.length) return;
                try {
                    eventSystem?.loadEvent({ library: "Lottie", where: "Modal", options: { elements: lotties } });
                } catch (e) {
                    console.error("Error initializing modal lottie:", e);
                }
            },
            onClose: (modalEl) => {
                clearLottie(content, eventSystem);
                clearVariation(modalEl);
                try {
                    eventSystem?.destroyEvent({ library: "Modal", where: "Modal" });
                } catch (e) {
                    console.error("Error tearing down modal:", e);
                }
            },
        };
    };
};

export const ajaxConfig = ({ elements, eventSystem }) => {
    const triggers = prepareAjaxTriggers(elements);

    return ({ element }) => {
        const trigger = triggers.find((t) => t.trigger === element);
        const content = document.querySelector('.js--modal-content');

        return {
            selector: ".c--modal-a",
            openClass: 'c--modal-a--is-active',
            beforeOpen: (modalEl) => {
                setVariation(modalEl, "ajax");
                if (!content || !trigger) return;
                content.innerHTML = "";
                fetchModalContent(trigger)
                    .then((html) => { content.innerHTML = html || ""; })
                    .catch(() => { content.innerHTML = ""; });
            },
            onClose: (modalEl) => {
                if (content) content.innerHTML = "";
                clearVariation(modalEl);
                try {
                    eventSystem?.destroyEvent({ library: "Modal", where: "Modal" });
                } catch (e) {
                    console.error("Error tearing down modal:", e);
                }
            },
        };
    };
};

export const htmlContentConfig = ({ elements, eventSystem }) => {
    const triggers = prepareHtmlContentTriggers(elements);

    return ({ element }) => {
        const trigger = triggers.find((t) => t.trigger === element);
        const content = document.querySelector('.js--modal-content');

        return {
            selector: ".c--modal-a",
            openClass: 'c--modal-a--is-active',
            beforeOpen: (modalEl) => {
                setVariation(modalEl, "htmlContent");
                if (!content) return;
                content.innerHTML = trigger?.html || "";
            },
            onClose: (modalEl) => {
                if (content) content.innerHTML = "";
                clearVariation(modalEl);
                try {
                    eventSystem?.destroyEvent({ library: "Modal", where: "Modal" });
                } catch (e) {
                    console.error("Error tearing down modal:", e);
                }
            },
        };
    };
};

function prepareHtmlContentTriggers(triggers) {
    return Array.from(triggers).map((trigger) => ({
        trigger,
        html: trigger.getAttribute("data-modal-content") || "",
    }));
}

// --- HubSpot form variation ---------------------------------------------
let hubspotScriptPromise = null;
let hsFormCounter = 0;

// Inject the HubSpot embed script once; resolves when window.hbspt is ready.
function loadHubspotScript() {
    if (window.hbspt?.forms) return Promise.resolve();
    if (hubspotScriptPromise) return hubspotScriptPromise;

    hubspotScriptPromise = new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.src = "https://js.hsforms.net/forms/embed/v2.js";
        script.charset = "utf-8";
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => {
            hubspotScriptPromise = null; // allow a retry on the next open
            reject(new Error("HubSpot embed script failed to load"));
        };
        document.head.appendChild(script);
    });

    return hubspotScriptPromise;
}

function prepareHubspotTriggers(triggers) {
    return Array.from(triggers).map((trigger) => ({
        trigger,
        portalId: trigger.getAttribute("data-hs-portal-id") || "",
        formId: trigger.getAttribute("data-hs-form-id") || "",
        region: trigger.getAttribute("data-hs-region") || "",
    }));
}

export const hubspotConfig = ({ elements, eventSystem }) => {
    const triggers = prepareHubspotTriggers(elements);

    return ({ element }) => {
        const trigger = triggers.find((t) => t.trigger === element);
        const content = document.querySelector('.js--modal-content');

        return {
            selector: ".c--modal-a",
            openClass: 'c--modal-a--is-active',
            beforeOpen: (modalEl) => {
                setVariation(modalEl, "hubspot");
                if (!content || !trigger?.portalId || !trigger?.formId) return;

                // hbspt.forms.create targets a CSS selector, so give it a unique id.
                const targetId = `js--hs-form-${++hsFormCounter}`;
                content.innerHTML = `<div id="${targetId}" class="js--hs-form-target"></div>`;

                loadHubspotScript()
                    .then(() => {
                        if (!window.hbspt?.forms) return;
                        window.hbspt.forms.create({
                            portalId: trigger.portalId,
                            formId: trigger.formId,
                            ...(trigger.region ? { region: trigger.region } : {}),
                            target: `#${targetId}`,
                        });
                    })
                    .catch((e) => console.error(e));
            },
            onClose: (modalEl) => {
                if (content) content.innerHTML = "";
                clearVariation(modalEl);
                try {
                    eventSystem?.destroyEvent({ library: "Modal", where: "Modal" });
                } catch (e) {
                    console.error("Error tearing down modal:", e);
                }
            },
        };
    };
};

function prepareAjaxTriggers(triggers) {
    return Array.from(triggers).map((trigger) => ({
        trigger,
        template: trigger.getAttribute("data-modal-template") || "",
        id: trigger.getAttribute("data-modal-content-id") || "",
        nonce: trigger.getAttribute("data-modal-nonce") || "",
        html: null, // per-trigger cache so reopening doesn't refetch
    }));
}

async function fetchModalContent(t) {
    if (t.html != null) return t.html; // cached from a previous open
    if (!t.template || !t.id) return "";

    const formData = new FormData();
    formData.append("action", "modal_content");
    formData.append("template", t.template);
    formData.append("id", t.id);

    const nonce = t.nonce || window.base_wp_api?.nonces?.modal_content || "";
    if (nonce) formData.append("nonce", nonce);

    const response = await fetch(window.base_wp_api.ajax_url, { method: "POST", body: formData });
    const result = await response.json();

    t.html = result?.success ? (result.data?.html || "") : "";
    return t.html;
}

function prepareVideoTriggers(triggers) {
    return Array.from(triggers).map((trigger) => ({
        trigger,
        videoSrc: trigger.getAttribute("data-video-src") || "",
        poster: trigger.getAttribute("data-video-poster") || "",
        autoplay: trigger.getAttribute("data-video-autoplay") !== "false",
        forceplay: trigger.getAttribute("data-video-forceplay") !== "false",
    }));
}

function prepareMediaTriggers(triggers) {
    return Array.from(triggers).map((trigger) => ({
        trigger,
        src: trigger.getAttribute("data-media-src") || "",
        alt: trigger.getAttribute("data-media-alt") || "",
        caption: trigger.getAttribute("data-media-caption") || "",
    }));
}

function mountMedia(content, { src, alt = "", caption = "" } = {}) {
    if (!content || !src) return;

    const figure = document.createElement("figure");
    figure.className = "c--modal-a__wrapper__media-wrapper";

    const img = document.createElement("img");
    img.className = "c--modal-a__wrapper__media-wrapper__media";
    img.src = src;
    img.alt = alt;
    img.decoding = "async";
    figure.appendChild(img);

    if (caption) {
        const figcaption = document.createElement("figcaption");
        figcaption.className = "c--modal-a__wrapper__media-wrapper__subtitle";
        figcaption.textContent = caption;
        figure.appendChild(figcaption);
    }

    content.appendChild(figure);
}

function clearMedia(content) {
    if (!content) return;
    content.innerHTML = "";
}

function prepareLottieTriggers(triggers) {
    return Array.from(triggers).map((trigger) => ({
        trigger,
        path: trigger.getAttribute("data-lottie-path") || "",
        name: trigger.getAttribute("data-lottie-name") || "modal-lottie",
        renderer: trigger.getAttribute("data-lottie-renderer") || "svg",
        autoplay: trigger.getAttribute("data-lottie-autoplay") !== "false",
        loop: trigger.getAttribute("data-lottie-loop") !== "false",
    }));
}

function mountLottie(content, { path, name = "modal-lottie", renderer = "svg", autoplay = true, loop = true } = {}) {
    if (!content || !path) return;

    const el = document.createElement("div");
    el.className = "js--lottie-element";
    el.setAttribute("data-name", name);
    el.setAttribute("data-path", path);
    el.setAttribute("data-animType", renderer);
    el.setAttribute("data-autoplay", autoplay ? "true" : "false");
    el.setAttribute("data-loop", loop ? "true" : "false");
    el.style.width = "100%";
    el.style.aspectRatio = "1 / 1";

    content.appendChild(el);
}

function clearLottie(content, eventSystem) {
    if (!content) return;
    const lotties = content.querySelectorAll(".js--lottie-element");
    if (lotties.length) {
        try {
            eventSystem?.destroyEvent({ library: "Lottie", where: "Modal", options: { elements: lotties } });
        } catch (e) {
            /* noop */
        }
    }
    content.innerHTML = "";
}
