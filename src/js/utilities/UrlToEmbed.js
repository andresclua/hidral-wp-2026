/**
 * Converts YouTube, Vimeo, and Wistia URLs to their embed equivalents and
 * builds provider-specific query parameters for autoplay/loop/muted/controls.
 *
 * Provider-specific behavior:
 *  - YouTube: emits `autoplay`, `mute`, `loop` (+ `playlist=<id>` when looping),
 *    `controls`, `playsinline`, `enablejsapi`. When `autoplay && !controls`
 *    (background mode), adds `modestbranding=1`, `rel=0`, `showinfo=0`,
 *    `iv_load_policy=3`, `disablekb=1`, `fs=0` to strip every piece of YouTube UI
 *    (logo, title, related videos, annotations, keyboard, fullscreen).
 *  - Vimeo: in autoplay + loop + muted + no-controls mode, collapses to a single
 *    `background=1` parameter. Otherwise emits the individual flags.
 *  - Wistia: maps to its named API params (`autoPlay`, `muted`, `endVideoBehavior`,
 *    `playbar`, `fullscreenButton`, `playButton`, `smallPlayButton`,
 *    `silentAutoPlay`).
 *
 * Accepts both the canonical watch URLs and the already-embedded forms.
 *
 * @param {string} url
 * @param {object} [opts]
 * @param {boolean} [opts.autoplay]
 * @param {boolean} [opts.loop]
 * @param {boolean} [opts.muted]
 * @param {boolean} [opts.controls]
 * @returns {{ provider: 'youtube'|'vimeo'|'wistia', id: string, embedUrl: string } | false}
 */
const PATTERNS = {
    youtube:      /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([^\s?&]+)/,
    youtubeEmbed: /(?:https?:\/\/)?(?:www\.)?youtube\.com\/embed\/([^\s?&]+)/,
    vimeo:        /(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(\d+)/,
    vimeoEmbed:   /(?:https?:\/\/)?player\.vimeo\.com\/video\/(\d+)/,
    wistia:       /(?:https?:\/\/)?(?:[\w-]+\.)?wistia\.(?:com|net)\/medias\/([\w\d]+)/,
    wistiaEmbed:  /(?:https?:\/\/)?(?:fast\.)?wistia\.(?:com|net)\/embed\/iframe\/([\w\d]+)/,
};

function detect(url) {
    let m;
    if ((m = url.match(PATTERNS.youtubeEmbed))) return { provider: "youtube", id: m[1] };
    if ((m = url.match(PATTERNS.vimeoEmbed)))   return { provider: "vimeo",   id: m[1] };
    if ((m = url.match(PATTERNS.wistiaEmbed)))  return { provider: "wistia",  id: m[1] };
    if ((m = url.match(PATTERNS.youtube)))      return { provider: "youtube", id: m[1] };
    if ((m = url.match(PATTERNS.vimeo)))        return { provider: "vimeo",   id: m[1] };
    if ((m = url.match(PATTERNS.wistia)))       return { provider: "wistia",  id: m[1] };
    return null;
}

function baseEmbedUrl({ provider, id }) {
    if (provider === "youtube") return `https://www.youtube.com/embed/${id}`;
    if (provider === "vimeo")   return `https://player.vimeo.com/video/${id}`;
    if (provider === "wistia")  return `https://fast.wistia.net/embed/iframe/${id}`;
    return null;
}

function paramsFor(provider, id, { autoplay = false, loop = false, muted = false, controls = true } = {}) {
    const flag = (v) => (v ? 1 : 0);

    if (provider === "youtube") {
        const base = {
            autoplay:    flag(autoplay),
            mute:        flag(muted),
            loop:        flag(loop),
            controls:    flag(controls),
            playsinline: 1,
            enablejsapi: 1,
            ...(loop ? { playlist: id } : {}),
        };
        // Background mode: strip every piece of YouTube UI.
        if (autoplay && !controls) {
            return {
                ...base,
                modestbranding: 1,
                rel:            0,
                showinfo:       0,
                iv_load_policy: 3,
                disablekb:      1,
                fs:             0,
            };
        }
        return base;
    }

    if (provider === "vimeo") {
        if (autoplay && loop && muted && !controls) return { background: 1 };
        return {
            autoplay: flag(autoplay),
            muted:    flag(muted),
            loop:     flag(loop),
            controls: flag(controls),
        };
    }

    if (provider === "wistia") {
        return {
            autoPlay:          autoplay,
            muted,
            endVideoBehavior:  loop ? "loop" : "default",
            playbar:           controls,
            fullscreenButton:  controls,
            playButton:        controls,
            smallPlayButton:   controls,
            silentAutoPlay:    autoplay && muted ? "allow" : false,
        };
    }

    return {};
}

export default function UrlToEmbed(url, opts = {}) {
    if (!url) return false;
    const info = detect(url);
    if (!info) return false;

    const base = baseEmbedUrl(info);
    if (!base) return false;

    const params = paramsFor(info.provider, info.id, opts);
    const u = new URL(base);
    Object.entries(params).forEach(([k, v]) => u.searchParams.set(k, String(v)));

    return {
        provider: info.provider,
        id:       info.id,
        embedUrl: u.toString(),
    };
}
