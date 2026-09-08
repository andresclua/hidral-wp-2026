import Video from "@js/handler/video/Video.js";

export function destroyVideo(content) {
    if (!content) return;
    if (content._videoInstance) {
        try { content._videoInstance.destroy(); } catch (e) { /* noop */ }
        content._videoInstance = null;
    }
    // Always clear: a click-to-play video may have a poster cover mounted with no
    // Video instance yet, and it still needs to be wiped on close / before reopen.
    content.innerHTML = "";
}

export function mountVideo(content, boostify, { videoSrc = "", poster = "", autoplay = true, forceplay = true } = {}) {
    if (!content || !videoSrc) return;

    const container = document.createElement("div");
    container.className = "c--video-a js--boostify-player";
    container.setAttribute("data-video-src", videoSrc);
    container.setAttribute("data-video-autoplay", autoplay ? "true" : "false");
    container.setAttribute("data-video-forceplay", forceplay ? "true" : "false");
    container.setAttribute("data-video-class", "c--video-a__media");
    if (poster) container.setAttribute("data-video-poster", poster);

    content.appendChild(container);

    // Autoplay / force-play: build the player now (Video lifts the poster when ready).
    if (autoplay || forceplay) {
        if (poster) container.appendChild(buildPosterCover(poster, false));
        content._videoInstance = new Video({ videoContainer: container, boostify });
        return;
    }

    // Paused (click-to-play, like core-media inline): show poster + play button and
    // only mount the player on click — so the embed never replaces the poster on load.
    const cover = buildPosterCover(poster, true);
    container.appendChild(cover);

    cover.addEventListener("click", () => {
        container.setAttribute("data-video-forceplay", "true"); // start right after the gesture
        content._videoInstance = new Video({ videoContainer: container, boostify });
    }, { once: true });
}

function buildPosterCover(poster, withPlayButton = false) {
    const cover = document.createElement("button");
    cover.type = "button";
    cover.className = "c--video-a__btn";
    cover.setAttribute("aria-label", "Play video");

    if (withPlayButton) {
        const artwork = document.createElement("div");
        artwork.className = "c--video-a__btn__artwork";
        artwork.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="15" viewBox="0 0 13 15" fill="none"><path d="M12.375 7.31285C12.3755 7.50384 12.3265 7.6917 12.2329 7.85815C12.1392 8.02461 12.0041 8.164 11.8406 8.26277L1.71 14.4601C1.5392 14.5647 1.34358 14.6218 1.14334 14.6255C0.943094 14.6292 0.745492 14.5794 0.570938 14.4812C0.398046 14.3845 0.254022 14.2436 0.153678 14.0728C0.053333 13.902 0.000288884 13.7076 0 13.5095V1.11621C0.000288884 0.91813 0.053333 0.7237 0.153678 0.552917C0.254022 0.382133 0.398046 0.241159 0.570938 0.144492C0.745492 0.0462991 0.943094 -0.00351529 1.14334 0.0001929C1.34358 0.00390109 1.5392 0.0609976 1.71 0.165585L11.8406 6.36293C12.0041 6.4617 12.1392 6.60109 12.2329 6.76755C12.3265 6.93401 12.3755 7.12186 12.375 7.31285Z" fill="currentColor"/></svg>';
        cover.appendChild(artwork);
    }

    if (poster) {
        const img = document.createElement("img");
        img.className = "c--video-a__btn__media";
        img.src = poster;
        img.alt = "";
        img.decoding = "async";
        cover.appendChild(img);
    }

    return cover;
}
