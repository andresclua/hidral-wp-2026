import { u_is_touch } from '@andresclua/jsutil';

class DebugManager {
    constructor() {
        this.logs = { import: [], instantiate: [], manager: [], events: [], error: [], timing: [] };
        this.panels = {};
        this.isTouch = u_is_touch();
        this.activePanel = null;
        this.activePanels = {}; // Track active state for each panel (desktop)
        
        if (this.isTouch) {
            this.initMobileUI();
        } else {
            this.initDesktopUI();
        }
    }

    makeDraggable(handle, panel) {
        let offsetX,
            offsetY,
            isDragging = false;

        handle.addEventListener("mousedown", (e) => {
            isDragging = true;
            offsetX = e.clientX - panel.offsetLeft;
            offsetY = e.clientY - panel.offsetTop;
            panel.style.bottom = "auto";
            panel.style.right = "auto";
            document.body.style.userSelect = "none";
        });

        document.addEventListener("mouseup", () => {
            isDragging = false;
            document.body.style.userSelect = "";
        });

        document.addEventListener("mousemove", (e) => {
            if (!isDragging) return;
            panel.style.left = e.clientX - offsetX + "px";
            panel.style.top = e.clientY - offsetY + "px";
        });
    }

    // === PUBLIC API ===
    import(name, opts = {}) {
        this.log("import", `${name}`, { color: opts.color });
    }

    instance(name, opts = {}) {
        this.log("instantiate", `${name}`, { color: opts.color });
    }

    manager(name, opts = {}) {
        this.log("manager", `${name}`, { color: opts.color });
    }

    events(name, opts = {}) {
        this.log("events", `${name}`, { color: opts.color });
    }

    timing(name, opts = {}) {
        this.log("timing", `${name}`, { color: opts.color });
    }

    error(message, data = {}) {
        const targetPanel = data.panel;

        this.log("error", message, { ...data, color: "red" });

        if (targetPanel && this.logs[targetPanel]) {
            this.log(targetPanel, message, { ...data, color: "red" });
        }

        this.openPanel("error");
    }

    openPanel(type) {
        const panel = this.panels[type];
        if (!panel) return;
        
        if (this.isTouch) {
            // Auto-expand if collapsed (especially for errors)
            if (!this.mobileExpanded) {
                this.toggleMobileExpanded();
            }
            // Close any other open panel first
            if (this.activePanel && this.activePanel !== type) {
                this.closePanel(this.activePanel);
            }
            this.activePanel = type;
        } else {
            // Desktop: track active panels
            this.activePanels[type] = true;
        }
        
        panel.style.display = "flex";
        this.updateButtonState(type, true);
    }

    closePanel(type) {
        const panel = this.panels[type];
        if (!panel) return;
        panel.style.display = "none";
        this.updateButtonState(type, false);
        
        if (this.isTouch) {
            if (this.activePanel === type) {
                this.activePanel = null;
            }
        } else {
            // Desktop: track active panels
            this.activePanels[type] = false;
        }
    }

    updateButtonState(type, isActive) {
        if (!this.controlBar) return;
        const btn = this.controlBar.querySelector(`button[data-panel="${type}"]`);
        if (!btn) return;
        
        if (isActive) {
            btn.style.background = "#0f0";
            btn.style.color = "#000";
            btn.style.borderColor = "#0f0";
        } else {
            btn.style.background = "#222";
            btn.style.color = "#fff";
            btn.style.borderColor = "#555";
        }
    }

    // === CORE LOGGING ===
    log(type, message, data = {}) {
        const entry = {
            type,
            message,
            data,
            time: new Date().toLocaleTimeString(),
        };

        this.logs[type].push(entry);
        this.renderEntry(type, entry);
    }

    // === MOBILE UI ===
    initMobileUI() {
        this.mobileExpanded = false;
        this.mobileButtons = {};
        
        // Create badge (collapsed state)
        const badge = document.createElement("div");
        badge.id = "debug-badge";
        badge.style.cssText = `
            position: fixed;
            bottom: 10px;
            left: 10px;
            width: 40px;
            height: 40px;
            background: rgba(0,0,0,0.85);
            border-radius: 50%;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-family: monospace;
            font-size: 16px;
            color: #0f0;
            border: 1px solid #333;
            transition: all 0.2s ease;
        `;
        badge.textContent = "🐛";
        this.badge = badge;
        
        badge.addEventListener("click", () => {
            this.toggleMobileExpanded();
        });
        
        document.body.appendChild(badge);

        // Create container (expanded state)
        const container = document.createElement("div");
        container.id = "debug-mobile-container";
        container.style.cssText = `
            position: fixed;
            bottom: 10px;
            left: 10px;
            right: 10px;
            z-index: 99999;
            font-family: monospace;
            display: none;
            flex-direction: column;
            gap: 8px;
        `;
        this.mobileContainer = container;

        // Create single panel (reused for all types)
        const panelTypes = ["import", "instantiate", "manager", "events", "error", "timing"];
        
        panelTypes.forEach((type) => {
            const panel = document.createElement("div");
            panel.id = `debug-panel-${type}`;
            panel.style.cssText = `
                background: rgba(0,0,0,0.95);
                color: #0f0;
                font-size: 11px;
                padding: 8px;
                border-radius: 6px;
                border: 1px solid #333;
                height: 200px;
                overflow: hidden;
                box-sizing: border-box;
                display: none;
                flex-direction: column;
            `;
            panel.innerHTML = `
                <div class="debug-header" style="
                    background: #222;
                    color: #fff;
                    padding: 6px 8px;
                    font-weight: bold;
                    border-radius: 4px;
                    margin-bottom: 6px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-shrink: 0;
                ">
                    <span>${type.charAt(0).toUpperCase() + type.slice(1)}</span>
                    <button class="debug-close" data-panel="${type}" style="
                        background: transparent;
                        border: none;
                        color: #888;
                        cursor: pointer;
                        font-size: 18px;
                        padding: 0 4px;
                        line-height: 1;
                    ">✕</button>
                </div>
                <div class="debug-log" style="
                    overflow-y: auto;
                    flex: 1;
                    min-height: 0;
                "></div>
            `;
            container.appendChild(panel);
            this.panels[type] = panel;
        });

        // Create control bar with buttons
        const bar = document.createElement("div");
        bar.id = "debug-controls";
        bar.style.cssText = `
            background: rgba(0,0,0,0.85);
            color: #fff;
            padding: 8px;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        `;
        
        // Top row with close button
        const topRow = document.createElement("div");
        topRow.style.cssText = `
            display: flex;
            align-items: center;
            gap: 8px;
        `;
        
        // Close button to collapse back to badge
        const closeBtn = document.createElement("button");
        closeBtn.style.cssText = `
            background: transparent;
            border: none;
            color: #888;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            line-height: 1;
        `;
        closeBtn.textContent = "✕";
        closeBtn.addEventListener("click", () => {
            this.toggleMobileExpanded();
        });
        
        const label = document.createElement("span");
        label.textContent = "Debug Panels";
        label.style.cssText = "font-size: 14px; color: #fff; font-weight: bold;";
        
        topRow.appendChild(closeBtn);
        topRow.appendChild(label);
        bar.appendChild(topRow);
        
        // Button grid
        const buttonGrid = document.createElement("div");
        buttonGrid.style.cssText = `
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        `;
        
        const panelLabels = {
            import: "Imports",
            instantiate: "Instances",
            manager: "Manager",
            events: "Events",
            error: "Errors",
            timing: "Timings"
        };
        
        panelTypes.forEach((type) => {
            const btn = document.createElement("button");
            btn.dataset.panel = type;
            btn.textContent = panelLabels[type];
            btn.style.cssText = `
                background: #222;
                color: #fff;
                border: 1px solid #555;
                border-radius: 4px;
                padding: 10px 14px;
                font-family: monospace;
                font-size: 13px;
                cursor: pointer;
                flex: 1;
                min-width: calc(50% - 3px);
                transition: all 0.15s ease;
            `;
            
            btn.addEventListener("click", () => {
                // Close current panel if different
                if (this.activePanel && this.activePanel !== type) {
                    this.closePanel(this.activePanel);
                }
                
                // Toggle this panel
                if (this.activePanel === type) {
                    this.closePanel(type);
                } else {
                    this.openPanel(type);
                }
            });
            
            this.mobileButtons[type] = btn;
            buttonGrid.appendChild(btn);
        });
        
        bar.appendChild(buttonGrid);
        container.appendChild(bar);

        document.body.appendChild(container);
        this.controlBar = bar;

        // Handle close button clicks on panels
        container.querySelectorAll(".debug-close").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                e.stopPropagation();
                this.closePanel(btn.dataset.panel);
            });
        });
    }

    toggleMobileExpanded() {
        this.mobileExpanded = !this.mobileExpanded;
        
        if (this.mobileExpanded) {
            this.badge.style.display = "none";
            this.mobileContainer.style.display = "flex";
        } else {
            this.badge.style.display = "flex";
            this.mobileContainer.style.display = "none";
            // Close any open panel when collapsing
            if (this.activePanel) {
                this.closePanel(this.activePanel);
            }
        }
    }

    // === DESKTOP UI ===
    initDesktopUI() {
        // Create control bar
        const bar = document.createElement("div");
        bar.id = "debug-controls";
        bar.style.cssText = `
            position: fixed; bottom: 10px; left: 10px;
            background: rgba(0,0,0,0.85); color: #fff;
            font-family: monospace; font-size: 14px;
            padding: 6px 8px; border-radius: 6px;
            z-index: 99999;
            display: flex;
            align-items: center;
            gap: 4px;
        `;
        bar.innerHTML = `
            <span class="debug-drag-handle" style="
                color: #666;
                margin-right: 4px;
                font-size: 12px;
                user-select: none;
                cursor: move;
            ">⋮⋮</span>
            <button data-panel="import">Imports</button>
            <button data-panel="instantiate">Instances</button>
            <button data-panel="manager">Manager</button>
            <button data-panel="events">Events</button>
            <button data-panel="error">Errors</button>
            <button data-panel="timing">Timings</button>
        `;

        document.body.appendChild(bar);

        // Make control bar draggable
        const dragHandle = bar.querySelector(".debug-drag-handle");
        this.makeDraggable(dragHandle, bar);

        // Layout constants
        const PANEL_WIDTH = 360;
        const PANEL_HEIGHT = 240;
        const H_GAP = 20;
        const V_GAP = 20;
        const PANELS_PER_ROW = 3;
        const START_LEFT = 120;
        const START_BOTTOM = 10;

        // Store bar reference for button updates BEFORE creating panels
        this.controlBar = bar;

        // Create the panels
        ["import", "instantiate", "manager", "events", "error", "timing"].forEach((type, i) => {
            const row = Math.floor(i / PANELS_PER_ROW);
            const col = i % PANELS_PER_ROW;

            const left = START_LEFT + col * (PANEL_WIDTH + H_GAP);
            const bottom = START_BOTTOM + row * (PANEL_HEIGHT + V_GAP);

            const panel = document.createElement("div");
            panel.id = `debug-panel-${type}`;
            panel.style.cssText = `
                position: fixed;
                left: ${left}px;
                bottom: ${bottom}px;
                width: ${PANEL_WIDTH}px;
                height: ${PANEL_HEIGHT}px;
                background: rgba(0,0,0,0.9); color: #0f0;
                font-family: monospace; font-size: 12px;
                z-index: 99998;
                padding: 8px; border-radius: 6px;
                display: none; border: 1px solid #333;
                resize: both;
                overflow: hidden;
                box-sizing: border-box;
            `;
            panel.innerHTML = `
                <div class="debug-header" style="
                    background: #222;
                    color: #fff;
                    padding: 4px 8px;
                    cursor: move;
                    font-weight: bold;
                    border-radius: 4px 4px 0 0;
                    user-select: none;
                    flex-shrink: 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <span>${type.charAt(0).toUpperCase() + type.slice(1)}</span>
                    <button class="debug-close" data-panel="${type}" style="
                        background: transparent;
                        border: none;
                        color: #888;
                        cursor: pointer;
                        font-size: 16px;
                        padding: 0 4px;
                        line-height: 1;
                    ">✕</button>
                </div>
                <div class="debug-log" style="
                    margin-top: 4px;
                    overflow-y: auto;
                    flex: 1;
                    min-height: 0;
                "></div>
            `;
            panel.style.display = "none";
            panel.style.flexDirection = "column";

            document.body.appendChild(panel);
            
            const header = panel.querySelector(".debug-header");
            this.makeDraggable(header, panel);
            this.panels[type] = panel;
            
            // Attach close button handler directly for this panel
            // Use captured 'type' variable to ensure correct panel type
            const closeBtn = panel.querySelector(".debug-close");
            const panelType = type; // Capture type in closure
            
            closeBtn.addEventListener("mousedown", (e) => {
                e.stopPropagation(); // Prevent drag from starting
            });
            
            closeBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                e.preventDefault();
                this.closePanel(panelType);
            });
            
            closeBtn.addEventListener("mouseenter", () => {
                closeBtn.style.color = "#fff";
            });
            
            closeBtn.addEventListener("mouseleave", () => {
                closeBtn.style.color = "#888";
            });
        });

        // Handle toggle clicks on control bar buttons
        bar.querySelectorAll("button").forEach((btn) => {
            btn.style.cssText = `
                background: #222; color: #fff; border: 1px solid #555;
                border-radius: 4px; margin-right: 4px; cursor: pointer;
                padding: 4px 8px; transition: all 0.15s ease;
            `;
            btn.addEventListener("click", () => this.togglePanel(btn.dataset.panel));
        });
    }

    togglePanel(type) {
        const panel = this.panels[type];
        if (!panel) return;
        
        // Use activePanels tracking for desktop, which is more reliable
        const isActive = this.activePanels[type] === true;
        
        if (isActive) {
            this.closePanel(type);
        } else {
            this.openPanel(type);
        }
    }

    renderEntry(type, entry) {
        const panel = this.panels[type];
        if (!panel) return;

        const COLOR_MAP = {
            red: "#f55",
            blue: "#0ff",
            pink: "#ff00ff",
            white: "#fff",
            green: "#0f0",
            yellow: "#ff0",
            orange: '#FF8800'
        };

        const log = panel.querySelector(".debug-log");
        const div = document.createElement("div");

        const typeColor = entry.data.color ?? entry.color;
        const color = entry.data.localError ? "#f55" : (COLOR_MAP[typeColor] ?? COLOR_MAP.yellow);

        div.innerHTML = `<span style="color:${color}">[${entry.time}] ${entry.message}</span>`;
        log.appendChild(div);
        log.scrollTop = log.scrollHeight;
    }
}

export const debug = new DebugManager();
