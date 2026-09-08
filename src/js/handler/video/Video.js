import UrlToEmbed from "@js/utilities/UrlToEmbed";

/**
 * Video — unified player for mp4 files and YouTube/Vimeo/Wistia embeds.
 *
 * The handler queries `.js--boostify-player` containers and instantiates one
 * Video per element. Behaviour is driven by data attributes on the container:
 *
 *   data-video-src           required — file URL or provider URL
 *   data-video-autoplay      "true" → autoplay + muted + loop + no controls
 *                            anything else → user-driven playback (controls on)
 *   data-video-class         class applied to the injected <video> / <iframe>
 *   data-video-poster        URL of the poster image (used by mp4 path only)
 *   data-video-forceplay     "false" → load paused (embed autoplay=0, no forcePlay);
 *                            default starts playback once the player is ready
 *
 * Rendering path:
 *  - If UrlToEmbed recognises the src (YouTube/Vimeo/Wistia), appendIframe()
 *    drops an <iframe> with provider-specific query params (autoplay, UI
 *    stripping, loop, mute) built by UrlToEmbed.
 *  - Otherwise (raw mp4) appendVideoFile() delegates to `boostify.videoPlayer()`,
 *    which injects a <video> element and applies the right HTML attributes.
 *
 * Autoplay control:
 *  - mp4 → `video.play()` + a `pause` listener that re-calls play (so accidental
 *    UA pauses don't break the loop).
 *  - Vimeo → lockAutoplay() listens for the iframe "pause" postMessage and
 *    re-issues play.
 *  - YouTube → background-mode URL params keep the embed playing without UI.
 *
 * For non-autoplay videos, forcePlay() sends a play postMessage once the iframe
 * is ready (Vimeo `{"method":"play"}` / YouTube `playVideo` command). Useful
 * when the embed is appended after a user gesture and we want it to start
 * immediately without waiting for the user to hit the provider's play UI.
 *
 * The optional poster button overlay (`.c--video-a__btn`) is removed by
 * removePoster() once the embedded player is ready to show — for Vimeo this is
 * driven by `bufferend`/`timeupdate` when the video will play, or by `loaded`
 * when it loads paused (see removePosterWhenReady), not the iframe `load`
 * event, so the modal background never flashes through.
 */
class Video {
    constructor(payload) {
        this.DOM = {
            videoContainer: payload.videoContainer,
        };

        this.boostify = payload.boostify;
        this._cleanups = [];
        this.init();
    }

    init() {
        const el = this.DOM.videoContainer;
        if (!el) return;

        const src = el.getAttribute("data-video-src");
        if (!src) return;

        const isAutoplay      = el.getAttribute("data-video-autoplay") === "true";
        const shouldForcePlay = el.getAttribute("data-video-forceplay") !== "false";
        const className       = el.getAttribute("data-video-class") || "";
        const poster          = el.getAttribute("data-video-poster") || null;

        const opts = isAutoplay
            ? { autoplay: true,            muted: true,  loop: true,  controls: false, poster, className }
            : { autoplay: shouldForcePlay, muted: false, loop: false, controls: true,  poster, className };

        const embed = UrlToEmbed(src, opts);

        if (embed) {
            const iframe = this.appendIframe(el, embed.embedUrl, className);
            if (!iframe) return;
            const willPlay = isAutoplay || shouldForcePlay;
            this.removePosterWhenReady(el, iframe, embed.provider, willPlay);
            if (isAutoplay)           this.lockAutoplay(iframe, embed.provider);
            else if (shouldForcePlay) this.forcePlay(iframe, embed.provider);
            // else: paused — embed loads with autoplay=0 and shows its own play button.
        } else {
            this.appendVideoFile(el, src, opts, isAutoplay);
        }
    }

    appendIframe(el, url, className) {
        const iframe = document.createElement("iframe");
        iframe.setAttribute("frameborder", "0");
        iframe.setAttribute("allowfullscreen", "");
        iframe.setAttribute("allow", "autoplay; fullscreen; picture-in-picture; encrypted-media; clipboard-write");
        if (className) iframe.className = className;
        iframe.src = url;
        el.appendChild(iframe);
        return iframe;
    }

    appendVideoFile(el, src, { autoplay, loop, muted, controls, poster, className }, lockOnAutoplay = true) {
        this.boostify.videoPlayer({
            url: { mp4: src },
            attributes: {
                class: className,
                loop,
                muted,
                controls,
                playsInline: true,
                preload: "metadata",
                ...(autoplay ? { autoplay: true } : {}),
                ...(poster ? { poster } : {}),
            },
            appendTo: el,
        });

        this.whenVideoReady(el, (video) => {
            video.load();
            video.addEventListener("playing", () => this.removePoster(el), { once: true });
            if (!autoplay) {
                // Paused: lift the cover once ready so the native poster/controls are reachable.
                video.addEventListener("loadeddata", () => this.removePoster(el), { once: true });
                return;
            }
            video.play().catch(() => {});
            if (!lockOnAutoplay) return;
            const onPause = () => video.play().catch(() => {});
            video.addEventListener("pause", onPause);
            this._cleanups.push(() => video.removeEventListener("pause", onPause));
        });
    }

    forcePlay(iframe, provider) {
        const playMsg = provider === "vimeo"
            ? '{"method":"play"}'
            : provider === "youtube"
            ? '{"event":"command","func":"playVideo","args":""}'
            : null;
        if (!playMsg) return;

        let done = false;
        const send = () => {
            if (done) return;
            done = true;
            try { iframe.contentWindow.postMessage(playMsg, "*"); } catch (e) { /* noop */ }
        };

        this.onIframeMessage(iframe, (data) => {
            if (data.event === "ready" || data.method === "ready" || data.event === "onReady") send();
        });
        iframe.addEventListener("load", send, { once: true });
    }

    lockAutoplay(iframe, provider) {
        if (provider !== "vimeo") return;
        const play = () => {
            try { iframe.contentWindow.postMessage('{"method":"play"}', "*"); } catch (e) { /* noop */ }
        };
        this.onIframeMessage(iframe, (data) => {
            if (data.event === "ready" || data.method === "ready") {
                try { iframe.contentWindow.postMessage('{"method":"addEventListener","value":"pause"}', "*"); } catch (e) { /* noop */ }
                play();
            }
            if (data.event === "pause") play();
        });
    }

    /**
     * Keep the poster cover up until the embedded player is actually rendering
     * a frame, then lift it. Picking the right Vimeo signal is fiddly because
     * the early ones all fire before anything is on screen:
     *
     *  - the iframe `load` event and Vimeo's `ready` event fire before the
     *    player has a frame → lifting then flashes the modal background through;
     *  - Vimeo's `loaded` event (video *metadata* loaded) also fires before the
     *    first frame is painted → the cover lifts but the player is still blank;
     *  - `play` alone is unreliable: Vimeo emits a single `play` event that can
     *    be missed in the ready/subscribe race, and unmuted autoplay is often
     *    blocked outright.
     *
     * So which signal we gate on depends on whether the video will play:
     *
     *  - willPlay (autoplay or forcePlay): gate on the first signal that means a
     *    frame is genuinely on screen — `bufferend` (buffered and ready to show)
     *    or `timeupdate` (playback position advanced; fires continuously, so a
     *    missed `play` can't stall us).
     *  - paused (`data-video-forceplay="false"`): no playback event will ever
     *    fire, so gate on `loaded` instead — by then the player has its metadata
     *    and shows its own poster frame + play button, which is what we want the
     *    user to reach. (Mirrors the mp4 paused branch, which lifts on
     *    `loadeddata` rather than `playing`.)
     *
     * Either way a short timeout backstops the case where no event arrives
     * (blocked autoplay, missed `loaded`): by then the player's own poster has
     * painted, so revealing it is safe.
     *
     *  - YouTube / Wistia / unknown: their postMessage event channels are not
     *             reliable enough to gate on, so keep the original behaviour and
     *             lift on the iframe `load` event.
     */
    removePosterWhenReady(el, iframe, provider, willPlay) {
        if (provider !== "vimeo") {
            iframe.addEventListener("load", () => this.removePoster(el), { once: true });
            return;
        }

        const removeOn = willPlay ? ["bufferend", "timeupdate", "play"] : ["loaded"];
        const subscribe = () => {
            removeOn.forEach((evt) => {
                try { iframe.contentWindow.postMessage(`{"method":"addEventListener","value":"${evt}"}`, "*"); } catch (e) { /* noop */ }
            });
        };

        this.onIframeMessage(iframe, (data) => {
            if (data.event === "ready" || data.method === "ready") subscribe();
            if (removeOn.includes(data.event)) this.removePoster(el);
        });
        iframe.addEventListener("load", subscribe, { once: true });

        const safety = setTimeout(() => this.removePoster(el), 2500);
        this._cleanups.push(() => clearTimeout(safety));
    }

    removePoster(el) {
        const btn = el.querySelector(".c--video-a__btn");
        if (btn) btn.remove();
    }

    onIframeMessage(iframe, callback) {
        const handler = (e) => {
            if (!iframe.contentWindow || e.source !== iframe.contentWindow) return;
            let data;
            try {
                data = typeof e.data === "string" ? JSON.parse(e.data) : e.data;
            } catch (err) { return; }
            if (data) callback(data);
        };
        window.addEventListener("message", handler);
        this._cleanups.push(() => window.removeEventListener("message", handler));
    }

    whenVideoReady(el, callback) {
        const existing = el.querySelector("video");
        if (existing) {
            callback(existing);
            return;
        }
        const observer = new MutationObserver(() => {
            const video = el.querySelector("video");
            if (!video) return;
            observer.disconnect();
            callback(video);
        });
        observer.observe(el, { childList: true, subtree: true });
        this._cleanups.push(() => observer.disconnect());
    }

    destroy() {
        this._cleanups.forEach((fn) => { try { fn(); } catch (e) { /* noop */ } });
        this._cleanups = [];
        if (this.DOM?.videoContainer) this.DOM.videoContainer.innerHTML = "";
        this.DOM = null;
    }
}

export default Video;
