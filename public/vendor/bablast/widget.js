!(function () {
    "use strict";
    const n =
            document.currentScript ||
            (function () {
                const n = document.getElementsByTagName("script");
                return n[n.length - 1];
            })(),
        t = window.BablastLiveChat,
        e = [];
    (Array.isArray(window.BablastLiveChatQueue) &&
        e.push(...window.BablastLiveChatQueue),
        t && Array.isArray(t.q) && e.push(...t.q));
    const a =
        n.getAttribute("data-sender-key") ||
        (window.BablastLiveChat && window.BablastLiveChat.key) ||
        window.BablastChatSenderKey ||
        "";
    let o =
            n.getAttribute("data-api-base") ||
            n.getAttribute("data-base-url") ||
            (window.BablastLiveChat &&
                (window.BablastLiveChat.apiBase ||
                    window.BablastLiveChat.baseUrl)) ||
            window.BablastChatBaseURL ||
            "",
        l = "";
    o && o.includes("/v1/livechat")
        ? (l = o.replace(/\/$/, ""))
        : ((!o ||
              o.includes("dash.bablast.id") ||
              o.includes("app.bablast.id")) &&
              (o =
                  "undefined" != typeof window &&
                  ["localhost", "127.0.0.1"].includes(window.location.hostname)
                      ? "http://localhost:3001"
                      : "https://si-api.bablast.id"),
          (l = o.replace(/\/$/, "") + "/v1/livechat"));
    const i =
            n.getAttribute("data-user-id") ||
            n.getAttribute("data-external-id") ||
            "",
        s = n.getAttribute("data-user-name") || "",
        r = n.getAttribute("data-user-email") || "",
        c = n.getAttribute("data-user-phone") || "",
        p = (n) => (n ? a + "_uid_" + n : a);
    let d = p(i);
    const b = () => "bablast_lc_" + d,
        u = () => "bablast_lc_prof_" + d,
        m = () => "bablast_lc_sessions_" + d,
        g = () => "bablast_lc_agent_" + d + "_" + f,
        v = () => {
            try {
                const n = localStorage.getItem(m()),
                    t = n ? JSON.parse(n) : [];
                return Array.isArray(t) ? t : [];
            } catch (n) {
                return [];
            }
        },
        h = (n, t, e = "open") => {
            if (n)
                try {
                    const a = v(),
                        o = a.findIndex((t) => t.sessionId === n),
                        l = Date.now(),
                        i = new Date(l).toLocaleDateString([], {
                            month: "short",
                            day: "numeric",
                            hour: "2-digit",
                            minute: "2-digit",
                        }),
                        s = {
                            sessionId: n,
                            lastMessage: t
                                ? String(t).slice(0, 120)
                                : "Percakapan baru",
                            timeStr: i,
                            ts: l,
                            status: e || "open",
                        };
                    o >= 0
                        ? (a[o] = { ...a[o], ...s, status: e || a[o].status })
                        : a.unshift(s);
                    const r = a.slice(0, 30);
                    localStorage.setItem(m(), JSON.stringify(r));
                } catch (n) {}
        };
    let f = localStorage.getItem(b());
    (f ||
        ((f =
            "vis_" +
            Math.random().toString(36).substring(2, 10) +
            Date.now().toString(36)),
        localStorage.setItem(b(), f)),
        h(f, "Percakapan aktif", "open"));
    let x = null;
    try {
        const n = localStorage.getItem(u());
        n && (x = JSON.parse(n));
    } catch (n) {}
    const y = (n) => {
            if (!n || "object" != typeof n || Array.isArray(n)) return {};
            const t = {};
            return (
                Object.entries(n)
                    .slice(0, 50)
                    .forEach(([n, e]) => {
                        const a = String(n || "")
                            .trim()
                            .slice(0, 80);
                        if (
                            a &&
                            !["__proto__", "prototype", "constructor"].includes(
                                a,
                            )
                        )
                            if (
                                null === e ||
                                ["string", "number", "boolean"].includes(
                                    typeof e,
                                )
                            )
                                t[a] =
                                    "string" == typeof e ? e.slice(0, 2e3) : e;
                            else
                                try {
                                    const n = JSON.stringify(e);
                                    n &&
                                        n.length <= 4e3 &&
                                        (t[a] = JSON.parse(n));
                                } catch (n) {}
                    }),
                t
            );
        },
        w = (n, t = null) => {
            if (!n || "object" != typeof n || Array.isArray(n)) return null;
            const e = String(n.external_id ?? n.user_id ?? t?.external_id ?? "")
                    .trim()
                    .slice(0, 191),
                a = Boolean(t?.external_id && e && t.external_id !== e)
                    ? {}
                    : t || {};
            return {
                ...a,
                external_id: e,
                name: String(n.name ?? a.name ?? "")
                    .trim()
                    .slice(0, 255),
                email: String(n.email ?? a.email ?? "")
                    .trim()
                    .toLowerCase()
                    .slice(0, 255),
                phone: String(n.phone ?? a.phone ?? "")
                    .trim()
                    .slice(0, 40),
                custom_attributes: {
                    ...(a.custom_attributes || {}),
                    ...y(n.custom_attributes || {}),
                },
            };
        },
        k = () => Boolean(x && (x.external_id || x.name || x.email || x.phone));
    if ((x && (x = w(x)), i && (!x || x.external_id !== i))) {
        x = w({ external_id: i, name: s, email: r, phone: c }, x);
        try {
            localStorage.setItem(u(), JSON.stringify(x));
        } catch (n) {}
    }
    let F = {
            widget_name: "Live Chat Support",
            header_title: "Chat dengan Kami",
            header_subtitle: "Kami siap membantu 24/7",
            welcome_message: "Halo! Ada yang bisa kami bantu hari ini?",
            primary_color: "#1E40AF",
            widget_position: "bottom-right",
            avatar_url: "",
            is_active: !0,
            is_prechat_form_enabled: !1,
            require_name: !0,
            require_email: !1,
            require_phone: !1,
            prechat_message:
                "Silakan isi data diri Anda sebelum memulai percakapan",
            is_proactive_enabled: !1,
            proactive_delay_seconds: 15,
            proactive_message:
                "Halo! Ada yang bisa kami bantu? Tanyakan apa saja di sini ya 😊",
        },
        _ = !1,
        E = null,
        B = !1,
        I = null,
        C = 0,
        S = null,
        L = null,
        z = 0,
        $ = !1,
        A = null,
        T = !1,
        j = null;
    try {
        const n = localStorage.getItem(g());
        if (n) {
            const t = JSON.parse(n),
                e = String(t?.name || "").trim();
            e && !/^(ai assistant|bablast ai|bot|system)$/i.test(e)
                ? (j = t)
                : localStorage.removeItem(g());
        }
    } catch (n) {}
    try {
        "1" === sessionStorage.getItem("bablast_lc_dismiss_card_" + f) &&
            (T = !0);
    } catch (n) {}
    const M = document.title,
        D = [
            "image/png",
            "image/jpeg",
            "image/webp",
            "image/gif",
            "application/pdf",
        ],
        P = () =>
            dn().some(
                (n) =>
                    "agent" === n.role ||
                    "assistant" === n.role ||
                    "outbound" === n.direction ||
                    "outgoing" === n.type,
            ),
        N = (n, t) => {
            const e = "bottom-left" === t;
            let a = document.getElementById("bablast-lc-styles");
            (a ||
                ((a = document.createElement("style")),
                (a.id = "bablast-lc-styles"),
                document.head.appendChild(a)),
                (a.innerHTML = `\n            #bablast-lc-widget {\n                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Inter, Helvetica, Arial, sans-serif;\n                position: fixed;\n                bottom: 24px;\n                ${e ? "left: 24px;" : "right: 24px;"}\n                z-index: 999999;\n                box-sizing: border-box;\n                -webkit-font-smoothing: antialiased;\n            }\n            #bablast-lc-widget * {\n                box-sizing: border-box;\n            }\n            #bablast-lc-bubble {\n                width: 60px;\n                height: 60px;\n                border-radius: 30px;\n                background: ${n};\n                box-shadow: 0 8px 24px -4px rgba(0,0,0,0.22), 0 4px 10px rgba(0,0,0,0.1);\n                cursor: pointer;\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                transition: transform 0.2s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.2s ease;\n                position: relative;\n                color: #FFFFFF;\n                user-select: none;\n                border: none;\n                padding: 0;\n            }\n            #bablast-lc-bubble:hover {\n                transform: scale(1.06);\n                box-shadow: 0 12px 30px -4px rgba(0,0,0,0.28), 0 6px 14px rgba(0,0,0,0.12);\n            }\n            #bablast-lc-bubble:active {\n                transform: scale(0.95);\n            }\n            #bablast-lc-bubble svg {\n                fill: currentColor;\n                width: 28px;\n                height: 28px;\n                transition: transform 0.3s ease, opacity 0.2s ease;\n            }\n            #bablast-lc-bubble .bablast-lc-icon-open {\n                display: block;\n            }\n            #bablast-lc-bubble .bablast-lc-icon-close {\n                display: none;\n            }\n            #bablast-lc-bubble.active .bablast-lc-icon-open {\n                display: none;\n            }\n            #bablast-lc-bubble.active .bablast-lc-icon-close {\n                display: block;\n            }\n            .bablast-lc-badge {\n                position: absolute;\n                top: -5px;\n                right: -5px;\n                min-width: 22px;\n                height: 22px;\n                padding: 0 6px;\n                border-radius: 999px;\n                background: #DC2626;\n                color: #FFFFFF;\n                border: 2px solid #FFFFFF;\n                display: none;\n                align-items: center;\n                justify-content: center;\n                font-size: 11px;\n                line-height: 1;\n                font-weight: 700;\n                font-variant-numeric: tabular-nums;\n            }\n            #bablast-lc-proactive-invite {\n                position: absolute;\n                bottom: 76px;\n                ${e ? "left: 0;" : "right: 0;"}\n                width: min(316px, calc(100vw - 32px));\n                display: none;\n                align-items: flex-start;\n                gap: 10px;\n                padding: 13px;\n                background: #FFFFFF;\n                color: #1F2937;\n                border: 1px solid #D1D5DB;\n                border-radius: 14px;\n                box-shadow: 0 6px 8px rgba(15, 23, 42, 0.14);\n            }\n            #bablast-lc-proactive-invite.open {\n                display: flex;\n                animation: bablast-lc-invite-in 0.24s cubic-bezier(0.22, 1, 0.36, 1);\n            }\n            .bablast-lc-proactive-avatar {\n                width: 34px;\n                height: 34px;\n                flex: 0 0 34px;\n                border-radius: 50%;\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                overflow: hidden;\n                background: ${n};\n                color: #FFFFFF;\n                font-size: 13px;\n                font-weight: 700;\n            }\n            .bablast-lc-proactive-avatar img { width: 100%; height: 100%; object-fit: cover; }\n            .bablast-lc-proactive-content { flex: 1; min-width: 0; }\n            .bablast-lc-proactive-agent { margin: 0 0 3px; color: #374151; font-size: 12px; font-weight: 700; }\n            .bablast-lc-proactive-message { margin: 0; color: #111827; font-size: 13px; line-height: 1.45; overflow-wrap: anywhere; }\n            .bablast-lc-proactive-actions { display: flex; align-items: center; gap: 8px; margin-top: 9px; }\n            #bablast-lc-proactive-reply {\n                border: none;\n                border-radius: 999px;\n                padding: 7px 13px;\n                background: ${n};\n                color: #FFFFFF;\n                cursor: pointer;\n                font-size: 12px;\n                font-weight: 700;\n            }\n            #bablast-lc-proactive-dismiss {\n                border: none;\n                background: transparent;\n                color: #4B5563;\n                cursor: pointer;\n                font-size: 12px;\n                font-weight: 600;\n                padding: 7px 4px;\n            }\n            @keyframes bablast-lc-invite-in {\n                from { opacity: 0; transform: translateY(8px) scale(0.98); }\n                to { opacity: 1; transform: translateY(0) scale(1); }\n            }\n\n            #bablast-lc-window {\n                position: fixed;\n                bottom: 96px;\n                ${e ? "left: 24px;" : "right: 24px;"}\n                width: 380px;\n                height: 580px;\n                max-height: calc(100vh - 120px);\n                max-width: calc(100vw - 32px);\n                background: #FFFFFF;\n                border-radius: 20px;\n                box-shadow: 0 16px 40px -10px rgba(0,0,0,0.18), 0 0 0 1px rgba(0,0,0,0.06);\n                display: none;\n                flex-direction: column;\n                overflow: hidden;\n                opacity: 0;\n                transform: translateY(16px) scale(0.96);\n                transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1), transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);\n                z-index: 999998;\n            }\n            #bablast-lc-window.open {\n                display: flex !important;\n                opacity: 1;\n                transform: translateY(0) scale(1);\n            }\n\n            #bablast-lc-header {\n                background: ${n};\n                padding: 16px 18px;\n                color: #FFFFFF;\n                display: flex;\n                justify-content: space-between;\n                align-items: center;\n                flex-shrink: 0;\n                position: relative;\n                z-index: 10;\n                box-shadow: 0 2px 10px rgba(0,0,0,0.08);\n            }\n            .bablast-lc-header-info {\n                display: flex;\n                align-items: center;\n                gap: 12px;\n                min-width: 0;\n            }\n            .bablast-lc-avatar-container {\n                position: relative;\n                flex-shrink: 0;\n            }\n            .bablast-lc-avatar {\n                width: 40px;\n                height: 40px;\n                border-radius: 50%;\n                background: rgba(255,255,255,0.22);\n                border: 2px solid rgba(255,255,255,0.4);\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                font-weight: 700;\n                font-size: 16px;\n                overflow: hidden;\n                color: #FFFFFF;\n            }\n            .bablast-lc-avatar img {\n                width: 100%;\n                height: 100%;\n                object-fit: cover;\n            }\n            .bablast-lc-status-dot {\n                position: absolute;\n                bottom: 0;\n                right: 0;\n                width: 11px;\n                height: 11px;\n                background: #22C55E;\n                border: 2px solid #FFFFFF;\n                border-radius: 50%;\n            }\n            .bablast-lc-titles {\n                display: flex;\n                flex-direction: column;\n                min-width: 0;\n            }\n            .bablast-lc-titles h3 {\n                margin: 0;\n                font-size: 15px;\n                font-weight: 700;\n                line-height: 1.25;\n                color: #FFFFFF;\n                white-space: nowrap;\n                overflow: hidden;\n                text-overflow: ellipsis;\n            }\n            .bablast-lc-titles p {\n                margin: 3px 0 0 0;\n                font-size: 12px;\n                opacity: 0.9;\n                line-height: 1.2;\n                white-space: nowrap;\n                overflow: hidden;\n                text-overflow: ellipsis;\n            }\n            #bablast-lc-subtitle.bablast-lc-agent-active {\n                animation: bablastAgentIdentityIn 0.28s ease-out;\n                font-weight: 600;\n            }\n            @keyframes bablastAgentIdentityIn {\n                from { opacity: 0; transform: translateY(3px); }\n                to { opacity: 0.9; transform: translateY(0); }\n            }\n            .bablast-lc-header-actions {\n                display: flex;\n                align-items: center;\n                gap: 6px;\n                flex-shrink: 0;\n            }\n            .bablast-lc-header-btn {\n                cursor: pointer;\n                background: rgba(255,255,255,0.18);\n                width: 32px;\n                height: 32px;\n                border-radius: 16px;\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                color: #FFFFFF;\n                transition: background-color 0.15s ease, transform 0.15s ease;\n                border: none;\n                padding: 0;\n            }\n            .bablast-lc-header-btn:hover {\n                background: rgba(255,255,255,0.3);\n                transform: scale(1.05);\n            }\n            .bablast-lc-header-btn svg {\n                width: 17px;\n                height: 17px;\n                fill: currentColor;\n            }\n            .bablast-lc-menu-wrapper {\n                position: relative;\n                display: inline-block;\n            }\n            .bablast-lc-dropdown-menu {\n                position: absolute;\n                top: calc(100% + 6px);\n                right: 0;\n                background: #FFFFFF;\n                min-width: 170px;\n                border-radius: 10px;\n                box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);\n                padding: 4px;\n                z-index: 50;\n                animation: bablastDropIn 0.15s ease-out;\n            }\n            @keyframes bablastDropIn {\n                from { opacity: 0; transform: translateY(-4px) scale(0.96); }\n                to { opacity: 1; transform: translateY(0) scale(1); }\n            }\n            .bablast-lc-dropdown-item {\n                display: flex;\n                align-items: center;\n                gap: 8px;\n                width: 100%;\n                padding: 8px 12px;\n                font-size: 13px;\n                font-weight: 500;\n                color: #374151;\n                border: none;\n                background: transparent;\n                border-radius: 6px;\n                cursor: pointer;\n                text-align: left;\n                transition: background-color 0.12s ease;\n            }\n            .bablast-lc-dropdown-item:hover {\n                background: #F3F4F6;\n            }\n            .bablast-lc-dropdown-item.text-danger {\n                color: #DC2626;\n            }\n            .bablast-lc-dropdown-item.text-danger:hover {\n                background: #FEE2E2;\n            }\n            .bablast-lc-modal-overlay {\n                position: absolute;\n                top: 0;\n                left: 0;\n                right: 0;\n                bottom: 0;\n                background: rgba(0,0,0,0.45);\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                z-index: 99;\n                padding: 20px;\n                backdrop-filter: blur(2px);\n                animation: bablastFadeIn 0.15s ease-out;\n            }\n            @keyframes bablastFadeIn {\n                from { opacity: 0; }\n                to { opacity: 1; }\n            }\n            .bablast-lc-modal-card {\n                background: #FFFFFF;\n                border-radius: 16px;\n                padding: 20px;\n                max-width: 320px;\n                width: 100%;\n                box-shadow: 0 20px 25px -5px rgba(0,0,0,0.18);\n                text-align: center;\n                animation: bablastModalIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);\n            }\n            @keyframes bablastModalIn {\n                from { opacity: 0; transform: scale(0.92); }\n                to { opacity: 1; transform: scale(1); }\n            }\n            .bablast-lc-modal-title {\n                font-size: 16px;\n                font-weight: 700;\n                margin: 0 0 8px 0;\n                color: #111827;\n            }\n            .bablast-lc-modal-desc {\n                font-size: 13px;\n                color: #6B7280;\n                margin: 0 0 18px 0;\n                line-height: 1.45;\n            }\n            .bablast-lc-modal-actions {\n                display: flex;\n                gap: 8px;\n            }\n            .bablast-lc-modal-btn {\n                flex: 1;\n                padding: 9px 14px;\n                border-radius: 8px;\n                font-size: 13px;\n                font-weight: 600;\n                cursor: pointer;\n                border: none;\n                transition: opacity 0.15s ease;\n            }\n            .bablast-lc-modal-btn:hover {\n                opacity: 0.9;\n            }\n            .bablast-lc-modal-btn.cancel {\n                background: #F3F4F6;\n                color: #374151;\n            }\n            .bablast-lc-modal-btn.confirm {\n                background: #DC2626;\n                color: #FFFFFF;\n            }\n            #bablast-lc-close {\n                cursor: pointer;\n                background: rgba(255,255,255,0.18);\n                width: 32px;\n                height: 32px;\n                border-radius: 16px;\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                color: #FFFFFF;\n                transition: background-color 0.15s ease, transform 0.15s ease;\n                flex-shrink: 0;\n                border: none;\n            }\n            #bablast-lc-close:hover {\n                background: rgba(255,255,255,0.3);\n                transform: scale(1.05);\n            }\n            #bablast-lc-close svg {\n                width: 16px;\n                height: 16px;\n                fill: currentColor;\n            }\n\n            /* History Container Styles */\n            #bablast-lc-history-container {\n                flex: 1;\n                display: flex;\n                flex-direction: column;\n                background: #F9FAFB;\n                overflow: hidden;\n            }\n            .bablast-lc-history-header {\n                padding: 12px 18px;\n                background: #FFFFFF;\n                border-bottom: 1px solid #E5E7EB;\n                display: flex;\n                justify-content: space-between;\n                align-items: center;\n                flex-shrink: 0;\n            }\n            .bablast-lc-history-title {\n                font-size: 13px;\n                font-weight: 700;\n                color: #1F2937;\n            }\n            .bablast-lc-history-back-btn {\n                background: none;\n                border: none;\n                color: #4B5563;\n                font-size: 12px;\n                font-weight: 600;\n                cursor: pointer;\n                padding: 4px 8px;\n                border-radius: 6px;\n            }\n            .bablast-lc-history-back-btn:hover {\n                background: #F3F4F6;\n                color: #111827;\n            }\n            .bablast-lc-history-list {\n                flex: 1;\n                overflow-y: auto;\n                padding: 12px 16px;\n                display: flex;\n                flex-direction: column;\n                gap: 8px;\n            }\n            .bablast-lc-history-card {\n                background: #FFFFFF;\n                border: 1px solid #E5E7EB;\n                border-radius: 12px;\n                padding: 12px 14px;\n                cursor: pointer;\n                transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;\n                display: flex;\n                flex-direction: column;\n                gap: 6px;\n                text-align: left;\n            }\n            .bablast-lc-history-card:hover {\n                transform: translateY(-1px);\n                box-shadow: 0 4px 12px -2px rgba(0,0,0,0.06);\n                border-color: #D1D5DB;\n            }\n            .bablast-lc-history-card.active-session {\n                border-color: ${n};\n                background: #F8FAFC;\n            }\n            .bablast-lc-history-card-top {\n                display: flex;\n                justify-content: space-between;\n                align-items: center;\n            }\n            .bablast-lc-history-date {\n                font-size: 11px;\n                color: #9CA3AF;\n                font-weight: 500;\n            }\n            .bablast-lc-history-status {\n                font-size: 10px;\n                font-weight: 700;\n                padding: 2px 8px;\n                border-radius: 999px;\n                text-transform: uppercase;\n                letter-spacing: 0.5px;\n            }\n            .bablast-lc-history-status.open {\n                background: #DCFCE7;\n                color: #166534;\n            }\n            .bablast-lc-history-status.closed,\n            .bablast-lc-history-status.resolved {\n                background: #F3F4F6;\n                color: #6B7280;\n            }\n            .bablast-lc-history-card-msg {\n                font-size: 13px;\n                color: #374151;\n                line-height: 1.35;\n                white-space: nowrap;\n                overflow: hidden;\n                text-overflow: ellipsis;\n            }\n            .bablast-lc-history-footer {\n                padding: 12px 16px;\n                background: #FFFFFF;\n                border-top: 1px solid #E5E7EB;\n                flex-shrink: 0;\n            }\n            .bablast-lc-history-empty {\n                display: flex;\n                flex-direction: column;\n                align-items: center;\n                justify-content: center;\n                padding: 40px 16px;\n                color: #9CA3AF;\n                font-size: 13px;\n                text-align: center;\n                gap: 8px;\n            }\n\n            /* Pre-Chat Form Styles */\n            #bablast-lc-prechat-container {\n                flex: 1;\n                display: flex;\n                flex-direction: column;\n                padding: 24px;\n                background: #FFFFFF;\n                overflow-y: auto;\n            }\n            .bablast-lc-prechat-intro {\n                font-size: 13.5px;\n                color: #475569;\n                line-height: 1.5;\n                margin: 0 0 20px 0;\n                text-align: center;\n            }\n            .bablast-lc-form-group {\n                margin-bottom: 16px;\n            }\n            .bablast-lc-form-label {\n                display: block;\n                font-size: 12px;\n                font-weight: 600;\n                color: #334155;\n                margin-bottom: 6px;\n            }\n            .bablast-lc-form-label .req {\n                color: #EF4444;\n                margin-left: 2px;\n            }\n            .bablast-lc-form-input {\n                width: 100%;\n                padding: 10px 14px;\n                border: 1.5px solid #CBD5E1;\n                border-radius: 12px;\n                font-size: 13.5px;\n                outline: none;\n                transition: border-color 0.15s, box-shadow 0.15s;\n                background: #F8FAFC;\n            }\n            .bablast-lc-form-input:focus {\n                border-color: ${n};\n                background: #FFFFFF;\n                box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.12);\n            }\n            .bablast-lc-phone-field {\n                display: flex;\n                align-items: stretch;\n                width: 100%;\n                border: 1.5px solid #CBD5E1;\n                border-radius: 12px;\n                background: #F8FAFC;\n                transition: border-color 0.15s, box-shadow 0.15s, background-color 0.15s;\n                overflow: hidden;\n            }\n            .bablast-lc-phone-field:focus-within {\n                border-color: ${n};\n                background: #FFFFFF;\n                box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.12);\n            }\n            #bablast-lc-country-code {\n                width: 112px;\n                flex: 0 0 112px;\n                padding: 10px 34px 10px 10px;\n                border: none;\n                border-right: 1px solid #CBD5E1;\n                border-radius: 0;\n                background-color: transparent;\n                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='m4 6 4 4 4-4' stroke='%23475569' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");\n                background-repeat: no-repeat;\n                background-position: right 11px center;\n                background-size: 16px 16px;\n                color: #334155;\n                font-size: 13px;\n                font-weight: 600;\n                outline: none;\n                cursor: pointer;\n                appearance: none;\n                -webkit-appearance: none;\n                text-overflow: ellipsis;\n            }\n            #bablast-lc-input-phone {\n                min-width: 0;\n                flex: 1;\n                padding: 10px 12px;\n                border: none;\n                background: transparent;\n                color: #1E293B;\n                font-size: 13.5px;\n                outline: none;\n            }\n            .bablast-lc-form-hint {\n                margin: 5px 0 0;\n                color: #64748B;\n                font-size: 11px;\n                line-height: 1.35;\n            }\n            .bablast-lc-form-error {\n                color: #EF4444;\n                font-size: 11.5px;\n                margin-top: 4px;\n                display: none;\n            }\n            .bablast-lc-btn-start {\n                width: 100%;\n                background: ${n};\n                color: #FFFFFF;\n                border: none;\n                border-radius: 12px;\n                padding: 12px 20px;\n                font-size: 14px;\n                font-weight: 700;\n                cursor: pointer;\n                margin-top: 8px;\n                transition: opacity 0.15s, transform 0.15s;\n                box-shadow: 0 4px 12px rgba(0,0,0,0.1);\n            }\n            .bablast-lc-btn-start:hover {\n                opacity: 0.94;\n                transform: translateY(-1px);\n            }\n\n            /* Post-session CSAT */\n            #bablast-lc-closed-bar {\n                display: none;\n                padding: 16px;\n                background: #FFFFFF;\n                border-top: 1px solid #E2E8F0;\n                text-align: center;\n                max-height: 270px;\n                overflow-y: auto;\n            }\n            .bablast-lc-closed-label { margin: 0 0 12px; color: #64748B; font-size: 12.5px; font-weight: 600; }\n            .bablast-lc-feedback-title { margin: 0; color: #1E293B; font-size: 14px; font-weight: 700; }\n            .bablast-lc-feedback-help { margin: 4px 0 10px; color: #64748B; font-size: 11.5px; }\n            .bablast-lc-rating { display: flex; justify-content: center; gap: 4px; margin-bottom: 8px; }\n            .bablast-lc-rating-btn {\n                width: 36px;\n                height: 36px;\n                padding: 0;\n                border: none;\n                border-radius: 8px;\n                background: transparent;\n                color: #CBD5E1;\n                font-size: 26px;\n                line-height: 1;\n                cursor: pointer;\n                transition: color 0.15s ease, background-color 0.15s ease, transform 0.15s ease;\n            }\n            .bablast-lc-rating-btn:hover { color: #F59E0B; background: #FFF7ED; transform: translateY(-1px); }\n            .bablast-lc-rating-btn.selected { color: #F59E0B; }\n            .bablast-lc-rating-btn:focus-visible,\n            #bablast-lc-feedback-comment:focus-visible,\n            #bablast-lc-feedback-submit:focus-visible,\n            #bablast-lc-btn-new-session:focus-visible { outline: 3px solid rgba(59, 130, 246, 0.35); outline-offset: 2px; }\n            #bablast-lc-feedback-comment {\n                width: 100%;\n                min-height: 58px;\n                max-height: 90px;\n                resize: vertical;\n                padding: 8px 10px;\n                border: 1px solid #CBD5E1;\n                border-radius: 10px;\n                color: #1E293B;\n                background: #F8FAFC;\n                font: inherit;\n                font-size: 12px;\n            }\n            .bablast-lc-feedback-error { min-height: 16px; margin: 3px 0; color: #B91C1C; font-size: 11px; }\n            #bablast-lc-feedback-submit { width: 100%; margin: 0 0 10px; padding: 9px 14px; }\n            #bablast-lc-feedback-submit[disabled] { cursor: not-allowed; opacity: 0.58; transform: none; }\n            .bablast-lc-feedback-thanks { display: none; padding: 6px 0 12px; color: #166534; font-size: 13px; font-weight: 650; }\n            #bablast-lc-btn-new-session { width: auto; display: inline-block; margin: 0; padding: 9px 18px; border-radius: 20px; font-size: 12.5px; }\n\n            /* Chat Messages Container */\n            #bablast-lc-chat-container {\n                flex: 1;\n                min-height: 0;\n                display: flex;\n                flex-direction: column;\n                overflow: hidden;\n                background: #F8FAFC;\n            }\n            #bablast-lc-body {\n                flex: 1;\n                min-height: 0;\n                padding: 16px;\n                overflow-y: auto;\n                overflow-x: hidden;\n                display: flex;\n                flex-direction: column;\n                gap: 12px;\n            }\n            #bablast-lc-body::-webkit-scrollbar {\n                width: 5px;\n            }\n            #bablast-lc-body::-webkit-scrollbar-track {\n                background: transparent;\n            }\n            #bablast-lc-body::-webkit-scrollbar-thumb {\n                background: rgba(148, 163, 184, 0.4);\n                border-radius: 4px;\n            }\n            #bablast-lc-body::-webkit-scrollbar-thumb:hover {\n                background: rgba(148, 163, 184, 0.7);\n            }\n\n            .bablast-lc-divider {\n                text-align: center;\n                margin: 4px 0 8px 0;\n                position: relative;\n            }\n            .bablast-lc-divider span {\n                background: #E2E8F0;\n                color: #64748B;\n                font-size: 11px;\n                font-weight: 600;\n                padding: 4px 12px;\n                border-radius: 12px;\n                letter-spacing: 0.02em;\n                display: inline-block;\n                max-width: 90%;\n            }\n\n            /* Message Bubbles */\n            .bablast-lc-msg {\n                max-width: 85%;\n                padding: 11px 14px;\n                border-radius: 16px;\n                font-size: 13.5px;\n                line-height: 1.55;\n                word-wrap: break-word;\n                word-break: break-word;\n                animation: bablastFadeIn 0.2s ease-out;\n                position: relative;\n            }\n            @keyframes bablastFadeIn {\n                from { opacity: 0; transform: translateY(6px); }\n                to { opacity: 1; transform: translateY(0); }\n            }\n            .bablast-lc-msg-content {\n                white-space: pre-wrap;\n            }\n            .bablast-lc-msg-content strong {\n                font-weight: 700;\n            }\n            .bablast-lc-msg-content a {\n                color: inherit;\n                text-decoration: underline;\n                font-weight: 500;\n            }\n            .bablast-lc-media-image {\n                display: block;\n                width: 240px;\n                max-width: 100%;\n                max-width: 260px;\n                max-height: 260px;\n                margin-bottom: 8px;\n                border-radius: 10px;\n                object-fit: cover;\n                background: #E2E8F0;\n                cursor: zoom-in;\n            }\n            .bablast-lc-media-link { display: block; }\n            .bablast-lc-document {\n                display: flex;\n                align-items: center;\n                gap: 10px;\n                min-width: 220px;\n                margin-bottom: 7px;\n                padding: 10px;\n                border-radius: 10px;\n                background: rgba(15, 23, 42, 0.07);\n                color: inherit;\n                text-decoration: none !important;\n            }\n            .bablast-lc-document-icon {\n                width: 34px;\n                height: 40px;\n                border-radius: 6px;\n                background: #DC2626;\n                color: #FFFFFF;\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                font-size: 10px;\n                font-weight: 800;\n                flex-shrink: 0;\n            }\n            .bablast-lc-document-info { min-width: 0; flex: 1; }\n            .bablast-lc-document-name {\n                display: block;\n                overflow: hidden;\n                text-overflow: ellipsis;\n                white-space: nowrap;\n                font-size: 12px;\n                font-weight: 650;\n            }\n            .bablast-lc-document-meta { display: block; margin-top: 2px; font-size: 10.5px; opacity: 0.72; }\n            .bablast-lc-msg-agent {\n                align-self: flex-start;\n                background: #FFFFFF;\n                color: #1E293B;\n                border-bottom-left-radius: 4px;\n                box-shadow: 0 1px 4px rgba(0,0,0,0.05), 0 0 0 1px rgba(0,0,0,0.04);\n            }\n            .bablast-lc-msg-user {\n                align-self: flex-end;\n                background: ${n};\n                color: #FFFFFF;\n                border-bottom-right-radius: 4px;\n                box-shadow: 0 2px 8px rgba(0,0,0,0.12);\n            }\n            .bablast-lc-msg-meta {\n                display: flex;\n                align-items: center;\n                gap: 4px;\n                margin-top: 4px;\n            }\n            .bablast-lc-msg-agent .bablast-lc-msg-meta {\n                justify-content: flex-start;\n            }\n            .bablast-lc-msg-user .bablast-lc-msg-meta {\n                justify-content: flex-end;\n            }\n            .bablast-lc-time {\n                font-size: 10px;\n                line-height: 1;\n            }\n            .bablast-lc-msg-agent .bablast-lc-time {\n                color: #94A3B8;\n            }\n            .bablast-lc-msg-user .bablast-lc-time {\n                color: rgba(255,255,255,0.78);\n            }\n            .bablast-lc-msg-status {\n                display: inline-flex;\n                align-items: center;\n                justify-content: center;\n                line-height: 1;\n                font-size: 10px;\n                user-select: none;\n            }\n            .bablast-lc-msg-status svg {\n                display: block;\n            }\n            .bablast-lc-msg-status.status-sending {\n                color: rgba(255,255,255,0.65);\n            }\n            .bablast-lc-icon-sending {\n                animation: bablastSpin 1.8s linear infinite;\n            }\n            @keyframes bablastSpin {\n                0% { transform: rotate(0deg); }\n                100% { transform: rotate(360deg); }\n            }\n            .bablast-lc-msg-status.status-sent {\n                color: rgba(255,255,255,0.75);\n            }\n            .bablast-lc-msg-status.status-delivered {\n                color: rgba(255,255,255,0.9);\n            }\n            .bablast-lc-msg-status.status-read {\n                color: #38BDF8;\n            }\n            .bablast-lc-msg-status.status-failed {\n                color: #FCA5A5;\n                cursor: pointer;\n            }\n            .bablast-lc-failed-indicator {\n                display: inline-flex;\n                align-items: center;\n                gap: 3px;\n                background: rgba(239, 68, 68, 0.25);\n                padding: 1px 6px 1px 4px;\n                border-radius: 999px;\n                color: #FEE2E2;\n                font-size: 9.5px;\n                font-weight: 600;\n                transition: background-color 0.15s ease;\n            }\n            .bablast-lc-failed-indicator:hover {\n                background: rgba(239, 68, 68, 0.45);\n            }\n            .bablast-lc-retry-text {\n                text-decoration: underline;\n            }\n            .bablast-lc-agent-label {\n                display: flex;\n                align-items: center;\n                gap: 5px;\n                margin: 0 0 6px;\n                color: #64748B;\n                font-size: 11px;\n                font-weight: 600;\n                line-height: 1.2;\n            }\n            .bablast-lc-agent-label-icon {\n                font-size: 11px;\n                line-height: 1;\n            }\n\n            /* Modern Animated Typing Bubble */\n            .bablast-lc-typing-bubble {\n                align-self: flex-start;\n                background: #FFFFFF;\n                padding: 12px 18px;\n                border-radius: 18px 18px 18px 4px;\n                box-shadow: 0 1px 4px rgba(0,0,0,0.05), 0 0 0 1px rgba(0,0,0,0.04);\n                display: inline-flex;\n                flex-direction: column;\n                align-items: flex-start;\n                gap: 5px;\n                min-width: 52px;\n                animation: bablastFadeIn 0.2s ease-out;\n            }\n            .bablast-lc-typing-label {\n                color: #64748B;\n                font-size: 11px;\n                font-weight: 600;\n                line-height: 1.25;\n            }\n            .bablast-lc-typing-dots {\n                display: inline-flex;\n                align-items: center;\n                gap: 5px;\n                min-height: 8px;\n            }\n            .bablast-lc-dot {\n                width: 6px;\n                height: 6px;\n                background: #94A3B8;\n                border-radius: 50%;\n                display: inline-block;\n                animation: bablastDotPulse 1.3s infinite ease-in-out;\n            }\n            .bablast-lc-dot:nth-child(1) { animation-delay: 0s; }\n            .bablast-lc-dot:nth-child(2) { animation-delay: 0.2s; }\n            .bablast-lc-dot:nth-child(3) { animation-delay: 0.4s; }\n\n            @keyframes bablastDotPulse {\n                0%, 60%, 100% {\n                    opacity: 0.4;\n                }\n                30% {\n                    opacity: 1;\n                    background: ${n};\n                }\n            }\n\n            /* Footer & Input Box */\n            #bablast-lc-footer {\n                padding: 10px 14px;\n                background: #FFFFFF;\n                border-top: 1px solid #E2E8F0;\n                display: flex;\n                align-items: stretch;\n                flex-direction: column;\n                gap: 8px;\n                flex-shrink: 0;\n            }\n            .bablast-lc-input-row { display: flex; align-items: center; gap: 8px; }\n            #bablast-lc-attach {\n                width: 36px;\n                height: 36px;\n                border: none;\n                border-radius: 18px;\n                background: #F1F5F9;\n                color: #475569;\n                cursor: pointer;\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                flex-shrink: 0;\n            }\n            #bablast-lc-attach:hover { background: #E2E8F0; color: #1E293B; }\n            #bablast-lc-attach:focus-visible,\n            #bablast-lc-send:focus-visible,\n            #bablast-lc-bubble:focus-visible,\n            #bablast-lc-close:focus-visible { outline: 3px solid rgba(59, 130, 246, 0.45); outline-offset: 2px; }\n            #bablast-lc-attach svg { width: 18px; height: 18px; fill: currentColor; }\n            .bablast-lc-attachment-preview {\n                display: none;\n                align-items: center;\n                gap: 10px;\n                padding: 9px 10px;\n                border-radius: 10px;\n                background: #F1F5F9;\n                color: #1E293B;\n            }\n            .bablast-lc-attachment-preview.visible { display: flex; }\n            .bablast-lc-preview-thumb {\n                width: 42px;\n                height: 42px;\n                border-radius: 8px;\n                background: #E2E8F0;\n                object-fit: cover;\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                color: #B91C1C;\n                font-size: 11px;\n                font-weight: 800;\n                flex-shrink: 0;\n            }\n            .bablast-lc-preview-info { min-width: 0; flex: 1; }\n            .bablast-lc-preview-name { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; font-weight: 650; }\n            .bablast-lc-preview-size { display: block; margin-top: 2px; font-size: 10.5px; color: #64748B; }\n            #bablast-lc-attachment-remove { border: none; background: transparent; color: #64748B; cursor: pointer; font-size: 18px; line-height: 1; padding: 5px; }\n            .bablast-lc-upload-error { display: none; margin: 0; color: #B91C1C; font-size: 11px; }\n            .bablast-lc-upload-error.visible { display: block; }\n            .bablast-lc-drop-overlay {\n                position: absolute;\n                inset: 72px 12px 76px;\n                z-index: 20;\n                border: 2px dashed ${n};\n                border-radius: 12px;\n                background: rgba(255,255,255,0.96);\n                color: #334155;\n                display: none;\n                align-items: center;\n                justify-content: center;\n                text-align: center;\n                padding: 24px;\n                font-size: 13px;\n                font-weight: 650;\n                pointer-events: none;\n            }\n            #bablast-lc-window.dragging .bablast-lc-drop-overlay { display: flex; }\n            #bablast-lc-send[disabled], #bablast-lc-attach[disabled] { cursor: not-allowed; opacity: 0.55; transform: none; }\n            #bablast-lc-input {\n                flex: 1;\n                border: 1.5px solid #E2E8F0;\n                background: #F8FAFC;\n                border-radius: 24px;\n                padding: 9px 16px;\n                font-size: 13.5px;\n                outline: none;\n                color: #1E293B;\n                transition: border-color 0.15s, background-color 0.15s;\n            }\n            #bablast-lc-input:focus {\n                border-color: ${n};\n                background: #FFFFFF;\n            }\n            #bablast-lc-send {\n                width: 36px;\n                height: 36px;\n                border-radius: 18px;\n                background: ${n};\n                color: #FFFFFF;\n                border: none;\n                cursor: pointer;\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                transition: transform 0.15s, opacity 0.15s;\n                flex-shrink: 0;\n            }\n            #bablast-lc-send:hover {\n                transform: scale(1.08);\n            }\n            #bablast-lc-send:active {\n                transform: scale(0.92);\n            }\n            #bablast-lc-send svg {\n                width: 16px;\n                height: 16px;\n                fill: currentColor;\n                margin-left: 2px;\n            }\n            .bablast-lc-brand {\n                text-align: center;\n                font-size: 10.5px;\n                color: #94A3B8;\n                padding: 6px 0 8px 0;\n                background: #FFFFFF;\n                flex-shrink: 0;\n            }\n\n            /* In-Chat Follow-Up Card */\n            .bablast-lc-followup-card {\n                background: #FFFFFF;\n                border: 1.5px solid #E2E8F0;\n                border-radius: 16px;\n                padding: 14px 16px;\n                margin: 6px 0;\n                box-shadow: 0 4px 14px -2px rgba(0, 0, 0, 0.06);\n                animation: bablastFollowUpSlide 0.3s cubic-bezier(0.16, 1, 0.3, 1);\n            }\n            @keyframes bablastFollowUpSlide {\n                from { opacity: 0; transform: translateY(12px) scale(0.97); }\n                to { opacity: 1; transform: translateY(0) scale(1); }\n            }\n            .bablast-lc-followup-header {\n                display: flex;\n                align-items: flex-start;\n                gap: 10px;\n                margin-bottom: 12px;\n            }\n            .bablast-lc-followup-icon {\n                width: 32px;\n                height: 32px;\n                border-radius: 10px;\n                background: #EEF2FF;\n                color: ${n};\n                display: flex;\n                align-items: center;\n                justify-content: center;\n                flex-shrink: 0;\n            }\n            .bablast-lc-followup-icon svg {\n                width: 18px;\n                height: 18px;\n                fill: currentColor;\n            }\n            .bablast-lc-followup-title {\n                font-size: 13px;\n                font-weight: 700;\n                color: #1E293B;\n                margin: 0 0 3px 0;\n                line-height: 1.3;\n            }\n            .bablast-lc-followup-desc {\n                font-size: 11.5px;\n                color: #64748B;\n                margin: 0;\n                line-height: 1.4;\n            }\n            .bablast-lc-followup-inputs {\n                display: flex;\n                flex-direction: column;\n                gap: 8px;\n                margin-bottom: 10px;\n            }\n            .bablast-lc-followup-input {\n                width: 100%;\n                padding: 8px 12px;\n                border: 1px solid #CBD5E1;\n                border-radius: 10px;\n                font-size: 12.5px;\n                outline: none;\n                background: #F8FAFC;\n                transition: border-color 0.15s;\n            }\n            .bablast-lc-followup-input:focus {\n                border-color: ${n};\n                background: #FFFFFF;\n            }\n            .bablast-lc-followup-phone-wrap {\n                display: flex;\n                align-items: stretch;\n                border: 1px solid #CBD5E1;\n                border-radius: 10px;\n                background: #F8FAFC;\n                overflow: hidden;\n                transition: border-color 0.15s;\n            }\n            .bablast-lc-followup-phone-wrap:focus-within {\n                border-color: ${n};\n                background: #FFFFFF;\n            }\n            .bablast-lc-followup-prefix {\n                padding: 8px 10px;\n                background: #F1F5F9;\n                font-size: 12px;\n                font-weight: 700;\n                color: #475569;\n                border-right: 1px solid #CBD5E1;\n                display: flex;\n                align-items: center;\n            }\n            .bablast-lc-followup-phone {\n                flex: 1;\n                min-width: 0;\n                padding: 8px 10px;\n                border: none;\n                background: transparent;\n                font-size: 12.5px;\n                outline: none;\n                color: #1E293B;\n            }\n            .bablast-lc-followup-actions {\n                display: flex;\n                align-items: center;\n                gap: 8px;\n            }\n            .bablast-lc-followup-btn-submit {\n                flex: 1;\n                background: ${n};\n                color: #FFFFFF;\n                border: none;\n                border-radius: 10px;\n                padding: 8px 14px;\n                font-size: 12px;\n                font-weight: 700;\n                cursor: pointer;\n                transition: opacity 0.15s;\n            }\n            .bablast-lc-followup-btn-submit:hover {\n                opacity: 0.9;\n            }\n            .bablast-lc-followup-btn-dismiss {\n                background: transparent;\n                border: 1px solid #E2E8F0;\n                border-radius: 10px;\n                padding: 8px 12px;\n                font-size: 12px;\n                font-weight: 600;\n                color: #64748B;\n                cursor: pointer;\n            }\n            .bablast-lc-followup-btn-dismiss:hover {\n                background: #F1F5F9;\n                color: #334155;\n            }\n            .bablast-lc-followup-success {\n                display: flex;\n                align-items: center;\n                gap: 8px;\n                padding: 10px 14px;\n                background: #F0FDF4;\n                border: 1px solid #BBF7D0;\n                border-radius: 12px;\n                color: #166534;\n                font-size: 12px;\n                font-weight: 600;\n                animation: bablastFollowUpSlide 0.2s ease-out;\n            }\n\n            @media (max-width: 480px) {\n                #bablast-lc-window {\n                    bottom: 0 !important;\n                    left: 0 !important;\n                    right: 0 !important;\n                    width: 100vw !important;\n                    height: 100vh !important;\n                    max-height: 100vh !important;\n                    max-width: 100vw !important;\n                    border-radius: 0 !important;\n                }\n            }\n            @media (prefers-reduced-motion: reduce) {\n                #bablast-lc-widget *, #bablast-lc-widget *::before, #bablast-lc-widget *::after {\n                    animation-duration: 0.01ms !important;\n                    animation-iteration-count: 1 !important;\n                    transition-duration: 0.01ms !important;\n                }\n            }\n        `));
        },
        H = (n, t, e) => {
            if (_) return;
            const a = document.getElementById("bablast-lc-proactive-invite");
            if (!a) return;
            const o = document.getElementById("bablast-lc-proactive-agent"),
                l = document.getElementById("bablast-lc-proactive-message"),
                i = document.getElementById("bablast-lc-proactive-avatar");
            if (
                (o && (o.textContent = n || F.header_title || "Tim Support"),
                l &&
                    (l.textContent =
                        t ||
                        F.proactive_message ||
                        "Halo! Ada yang bisa kami bantu?"),
                i)
            ) {
                const t = e || F.avatar_url;
                t
                    ? (i.innerHTML = `<img src="${Cn(t)}" alt="${Cn(n || "Avatar")}">`)
                    : (i.textContent = (n || F.header_title || "B")
                          .charAt(0)
                          .toUpperCase());
            }
            (a.classList.add("open"), zn());
        },
        O = () => {
            A && (clearTimeout(A), (A = null));
            const n = document.getElementById("bablast-lc-proactive-invite");
            n && n.classList.remove("open");
        },
        q = () => "bablast_lc_feedback_" + d + "_" + f,
        J = ["", "Sangat Buruk", "Kurang", "Cukup", "Puas", "Sangat Puas"],
        U = (n) => {
            ((z = n),
                document
                    .querySelectorAll(".bablast-lc-rating-btn")
                    .forEach((t) => {
                        const e = Number(t.dataset.rating) <= n;
                        (t.classList.toggle("selected", e),
                            t.setAttribute(
                                "aria-pressed",
                                Number(t.dataset.rating) === n
                                    ? "true"
                                    : "false",
                            ));
                    }));
            const t = document.getElementById("bablast-lc-rating-label");
            t &&
                (t.textContent = n
                    ? `${n} dari 5 — ${J[n]}`
                    : "Pilih nilai 1–5 bintang");
            const e = document.getElementById("bablast-lc-feedback-error");
            e && (e.textContent = "");
        },
        R = () => {
            const n = "1" === localStorage.getItem(q()),
                t = document.getElementById("bablast-lc-feedback-form"),
                e = document.getElementById("bablast-lc-feedback-thanks");
            if (
                (t && (t.style.display = n ? "none" : "block"),
                e && (e.style.display = n ? "block" : "none"),
                !n)
            ) {
                ((z = 0), U(0));
                const n = document.getElementById(
                    "bablast-lc-feedback-comment",
                );
                n && (n.value = "");
            }
        },
        K = async (n) => {
            if ((n.preventDefault(), $)) return;
            const t = document.getElementById("bablast-lc-feedback-error");
            if (!z)
                return void (
                    t && (t.textContent = "Pilih rating terlebih dahulu.")
                );
            $ = !0;
            const e = document.getElementById("bablast-lc-feedback-submit");
            e && ((e.disabled = !0), (e.textContent = "Mengirim…"));
            try {
                const n = await fetch(`${l}/feedback/${a}`, {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            session_id: f,
                            rating: z,
                            comment:
                                document
                                    .getElementById(
                                        "bablast-lc-feedback-comment",
                                    )
                                    ?.value.trim() || "",
                            visitor_name: x?.name || "",
                            visitor_phone: x?.phone || "",
                            visitor_email: x?.email || "",
                        }),
                    }),
                    t = await n.json().catch(() => ({}));
                if (!n.ok || !t.success)
                    throw new Error(t.message || "Feedback gagal dikirim.");
                (localStorage.setItem(q(), "1"), R());
            } catch (n) {
                t &&
                    (t.textContent =
                        n.message ||
                        "Feedback gagal dikirim. Silakan coba lagi.");
            } finally {
                (($ = !1),
                    e &&
                        ((e.disabled = !1),
                        (e.textContent = "Kirim Penilaian")));
            }
        },
        V = (n) => {
            B = !!n;
            const t = document.getElementById("bablast-lc-footer"),
                e = document.getElementById("bablast-lc-closed-bar"),
                a = document.getElementById("bablast-lc-input"),
                o = document.getElementById("bablast-lc-menu-wrapper");
            B
                ? (t && (t.style.display = "none"),
                  e && (e.style.display = "block"),
                  a && (a.disabled = !0),
                  o && (o.style.display = "none"),
                  localStorage.setItem("bablast_lc_closed_" + d + "_" + f, "1"),
                  R())
                : (t && (t.style.display = "flex"),
                  e && (e.style.display = "none"),
                  a && (a.disabled = !1),
                  o && !Y && (o.style.display = "inline-block"),
                  localStorage.removeItem("bablast_lc_closed_" + d + "_" + f));
        };
    let Y = !1;
    const W = () => v().find((n) => "open" === n.status || !n.status) || null,
        G = (n) => {
            const t = document.getElementById("bablast-lc-history-list"),
                e = document.getElementById("bablast-lc-btn-new-chat"),
                a = n || v(),
                o = W();
            (e &&
                (e.textContent = o
                    ? "Lanjutkan Percakapan Aktif"
                    : "+ Mulai Percakapan Baru"),
                t &&
                    (a && 0 !== a.length
                        ? ((t.innerHTML = a
                              .map((n) => {
                                  const t = n.sessionId === f,
                                      e =
                                          "resolved" === n.status ||
                                          "closed" === n.status,
                                      a = e ? "Selesai" : "Aktif",
                                      o = e ? "resolved" : "open";
                                  return `\n                <div class="bablast-lc-history-card ${t ? "active-session" : ""}" data-session-id="${Cn(n.sessionId)}">\n                    <div class="bablast-lc-history-card-top">\n                        <span class="bablast-lc-history-date">${Cn(n.timeStr || "")}</span>\n                        <span class="bablast-lc-history-status ${o}">${a}</span>\n                    </div>\n                    <div class="bablast-lc-history-card-msg">\n                        ${t ? '<strong style="color:inherit">▶ </strong>' : ""}${Cn(n.lastMessage || "Percakapan")}\n                    </div>\n                </div>\n            `;
                              })
                              .join("")),
                          t
                              .querySelectorAll(".bablast-lc-history-card")
                              .forEach((n) => {
                                  n.addEventListener("click", () => {
                                      const t = n.dataset.sessionId;
                                      t && Q(t);
                                  });
                              }))
                        : (t.innerHTML =
                              '\n                <div class="bablast-lc-history-empty">\n                    <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>\n                    <span>Belum ada riwayat percakapan sebelumnya</span>\n                </div>\n            ')));
        },
        Q = (n) => {
            (E && (E.close(), (E = null)),
                (f = n),
                localStorage.setItem(b(), f),
                (Y = !1),
                nn(),
                Sn(),
                Tn());
        },
        X = () => {
            ((Y = !Y),
                nn(),
                Y &&
                    (G(),
                    (async () => {
                        try {
                            const n = v()
                                    .map((n) => n.sessionId)
                                    .filter(Boolean),
                                t = new URLSearchParams();
                            (n.length > 0 && t.set("sessions", n.join(",")),
                                x?.email && t.set("email", x.email),
                                x?.phone && t.set("phone", x.phone),
                                x?.external_id &&
                                    t.set("external_id", x.external_id));
                            const e = await fetch(
                                `${l}/conversations/${a}?${t.toString()}`,
                            );
                            if (e.ok) {
                                const n = (await e.json()).data || [];
                                if (n.length > 0) {
                                    const t = v(),
                                        e = new Map();
                                    (t.forEach((n) => e.set(n.sessionId, n)),
                                        n.forEach((n) => {
                                            const t = e.get(n.session_id) || {},
                                                a = n.last_activity_at
                                                    ? new Date(
                                                          n.last_activity_at,
                                                      )
                                                    : new Date(
                                                          n.created_at ||
                                                              Date.now(),
                                                      ),
                                                o = a.toLocaleDateString([], {
                                                    month: "short",
                                                    day: "numeric",
                                                    hour: "2-digit",
                                                    minute: "2-digit",
                                                });
                                            e.set(n.session_id, {
                                                ...t,
                                                sessionId: n.session_id,
                                                lastMessage:
                                                    n.last_message ||
                                                    t.lastMessage ||
                                                    "Percakapan baru",
                                                timeStr: o,
                                                ts: a.getTime(),
                                                status: n.status || "open",
                                            });
                                        }));
                                    const a = Array.from(e.values())
                                        .sort(
                                            (n, t) => (t.ts || 0) - (n.ts || 0),
                                        )
                                        .slice(0, 30);
                                    (localStorage.setItem(
                                        m(),
                                        JSON.stringify(a),
                                    ),
                                        Y && G(a));
                                }
                            }
                        } catch (n) {
                            console.warn(
                                "[Bablast LiveChat] Fetch conversations list error:",
                                n,
                            );
                        }
                    })()));
        },
        Z = (n = !1) => {
            if (!0 !== n) {
                const n = W();
                if (n && n.sessionId !== f) return void Q(n.sessionId);
                if (n && n.sessionId === f && !B)
                    return (
                        (Y = !1),
                        nn(),
                        void document
                            .getElementById("bablast-lc-input")
                            ?.focus()
                    );
            }
            (E && (E.close(), (E = null)),
                (f =
                    "vis_" +
                    Math.random().toString(36).substring(2, 10) +
                    Date.now().toString(36)),
                localStorage.setItem(b(), f),
                h(f, "Percakapan baru", "open"),
                (j = null),
                cn(null),
                vn([]),
                (z = 0),
                ($ = !1),
                V(!1),
                (Y = !1),
                nn(),
                (F.is_prechat_form_enabled && !k() && !P()) ||
                    (Tn(),
                    En([]),
                    xn(),
                    setTimeout(() => {
                        document.getElementById("bablast-lc-input")?.focus();
                    }, 100)));
        },
        nn = () => {
            const n = document.getElementById("bablast-lc-prechat-container"),
                t = document.getElementById("bablast-lc-chat-container"),
                e = document.getElementById("bablast-lc-history-container"),
                a = document.getElementById("bablast-lc-btn-history"),
                o = document.getElementById("bablast-lc-title"),
                l = document.getElementById("bablast-lc-subtitle");
            if (!n || !t) return;
            if (Y) {
                ((n.style.display = "none"), (t.style.display = "none"));
                const i = document.getElementById("bablast-lc-menu-wrapper");
                return (
                    i && (i.style.display = "none"),
                    e && (e.style.display = "flex"),
                    o && (o.textContent = "Riwayat Percakapan"),
                    l && (l.textContent = "Percakapan Anda sebelumnya"),
                    void (
                        a &&
                        ((a.innerHTML =
                            '<svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>'),
                        (a.title = "Ke Ruang Chat"))
                    )
                );
            }
            (e && (e.style.display = "none"),
                o && (o.textContent = F.header_title),
                l && (l.textContent = F.header_subtitle),
                a &&
                    ((a.innerHTML =
                        '<svg viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>'),
                    (a.title = "Riwayat Percakapan")));
            const i = document.getElementById("bablast-lc-menu-wrapper");
            !F.is_prechat_form_enabled || k() || P()
                ? ((n.style.display = "none"),
                  (t.style.display = "flex"),
                  i && (i.style.display = B ? "none" : "inline-block"))
                : ((n.style.display = "flex"),
                  (t.style.display = "none"),
                  i && (i.style.display = "none"),
                  (document.getElementById(
                      "bablast-lc-req-name",
                  ).style.display = F.require_name ? "inline" : "none"),
                  (document.getElementById(
                      "bablast-lc-req-phone",
                  ).style.display = F.require_phone ? "inline" : "none"),
                  (document.getElementById(
                      "bablast-lc-req-email",
                  ).style.display = F.require_email ? "inline" : "none"),
                  (document.getElementById(
                      "bablast-lc-prechat-intro-text",
                  ).textContent =
                      F.prechat_message ||
                      "Silakan isi data diri Anda sebelum memulai percakapan"));
        },
        tn = (n) => {
            n.preventDefault();
            const t = document.getElementById("bablast-lc-input-name"),
                e = document.getElementById("bablast-lc-input-phone"),
                a = document.getElementById("bablast-lc-country-code"),
                o = document.getElementById("bablast-lc-input-email"),
                l = t ? t.value.trim() : "",
                i = e ? e.value.trim() : "",
                s = a ? a.value.replace(/\D/g, "") : "",
                r = i.replace(/\D/g, "").replace(/^0+/, ""),
                c = i && s ? `${s}${r}` : "",
                p = o ? o.value.trim() : "";
            ((document.getElementById("bablast-lc-err-name").style.display =
                "none"),
                (document.getElementById("bablast-lc-err-phone").style.display =
                    "none"),
                (document.getElementById("bablast-lc-err-email").style.display =
                    "none"));
            let d = !1;
            (F.require_name &&
                !l &&
                ((document.getElementById("bablast-lc-err-name").style.display =
                    "block"),
                (d = !0)),
                F.require_phone && !i
                    ? ((document.getElementById(
                          "bablast-lc-err-phone",
                      ).textContent = "Nomor WhatsApp wajib diisi"),
                      (document.getElementById(
                          "bablast-lc-err-phone",
                      ).style.display = "block"),
                      (d = !0))
                    : i &&
                      (!s || r.length < 6 || c.length < 8 || c.length > 15) &&
                      ((document.getElementById(
                          "bablast-lc-err-phone",
                      ).textContent =
                          "Pilih kode negara dan masukkan nomor yang valid"),
                      (document.getElementById(
                          "bablast-lc-err-phone",
                      ).style.display = "block"),
                      (d = !0)),
                F.require_email && !p
                    ? ((document.getElementById(
                          "bablast-lc-err-email",
                      ).textContent = "Alamat email wajib diisi"),
                      (document.getElementById(
                          "bablast-lc-err-email",
                      ).style.display = "block"),
                      (d = !0))
                    : p &&
                      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(p) &&
                      ((document.getElementById(
                          "bablast-lc-err-email",
                      ).textContent = "Format email tidak valid"),
                      (document.getElementById(
                          "bablast-lc-err-email",
                      ).style.display = "block"),
                      (d = !0)),
                d ||
                    ((x = { name: l, phone: c, email: p }),
                    localStorage.setItem(u(), JSON.stringify(x)),
                    On(),
                    nn(),
                    Tn(),
                    En(dn())));
        },
        en = () => "bablast_lc_msgs_" + d + "_" + f,
        an = (n = {}) => {
            const t = String(
                    n.name ||
                        n.agent_name ||
                        n.sender_name ||
                        n.from_name ||
                        "",
                )
                    .trim()
                    .slice(0, 100),
                e = String(n.role || n.agent_role || n.role_name || "")
                    .trim()
                    .slice(0, 60);
            return t ? { name: t, role: e } : null;
        },
        on = (n = {}) => {
            if (!n.metadata) return {};
            if ("object" == typeof n.metadata) return n.metadata;
            try {
                return JSON.parse(n.metadata);
            } catch (n) {
                return {};
            }
        },
        ln = (n = {}, t = "") => {
            const e = on(n),
                a = String(
                    n.sub_type || n.subType || e.sub_type || e.subType || "",
                ).toLowerCase(),
                o = String(n.sent_by || e.sent_by || "").toLowerCase(),
                l = String(
                    n.agent_name ||
                        n.sender_name ||
                        n.from_name ||
                        e.agent_name ||
                        "",
                ).toLowerCase(),
                i = String(n.agent_id || e.agent_id || "").toLowerCase();
            return (
                "assistant" === t ||
                "ai" === t ||
                !0 === n.is_bot ||
                !0 === n.is_ai ||
                !0 === n.is_automated ||
                "ai_reply" === a ||
                "ai" === o ||
                "ai_bot" === i ||
                "ai" === i ||
                /^(ai assistant|bablast ai|bot|system)$/i.test(l)
            );
        },
        sn = () =>
            F.ai_agent_name ||
            F.bot_name ||
            F.support_name ||
            F.widget_name ||
            "Tim Support",
        rn = (n = {}, t = "") => {
            const e = on(n),
                a = ln(n, t),
                o = n.agent_name || e.agent_name || "",
                l = an({
                    name: o || n.sender_name || n.from_name,
                    role:
                        n.agent_role ||
                        n.role_name ||
                        e.agent_role ||
                        e.role_name,
                }),
                i =
                    !0 === n.is_fallback_identity ||
                    !l?.name ||
                    (!o && l.name === sn());
            return {
                name: l?.name || sn(),
                role: l?.role || "",
                automated: a,
                fallback: i,
                human: Boolean(l?.name) && !a && !i,
            };
        },
        cn = (n = j) => {
            const t = document.getElementById("bablast-lc-subtitle");
            if (!t) return;
            if (!n?.name)
                return (
                    (t.textContent = F.header_subtitle),
                    void t.classList.remove("bablast-lc-agent-active")
                );
            const e = n.role ? ` (${n.role})` : "";
            ((t.textContent = `🟢 ${n.name}${e} siap membantu`),
                t.classList.remove("bablast-lc-agent-active"),
                t.offsetWidth,
                t.classList.add("bablast-lc-agent-active"));
        },
        pn = (n) => {
            const t = an(n);
            if (t) {
                j = t;
                try {
                    localStorage.setItem(g(), JSON.stringify(t));
                } catch (n) {}
                cn(t);
            }
        },
        dn = () => {
            try {
                const n = localStorage.getItem(en());
                return n ? JSON.parse(n) : [];
            } catch (n) {
                return [];
            }
        },
        bn = (n) => {
            const t = Number(n || 0);
            return t
                ? t < 1024
                    ? `${t} B`
                    : t < 1048576
                      ? `${(t / 1024).toFixed(t < 10240 ? 1 : 0)} KB`
                      : `${(t / 1048576).toFixed(1)} MB`
                : "";
        },
        un = (n = "") => {
            const t = document.getElementById("bablast-lc-upload-error");
            t && ((t.textContent = n), t.classList.toggle("visible", !!n));
        },
        mn = () => {
            (I && I.previewURL && URL.revokeObjectURL(I.previewURL),
                (I = null));
            const n = document.getElementById("bablast-lc-file-input"),
                t = document.getElementById("bablast-lc-attachment-preview"),
                e = document.getElementById("bablast-lc-preview-thumb");
            (n && (n.value = ""),
                t && t.classList.remove("visible"),
                e && ((e.textContent = "FILE"), (e.style.backgroundImage = "")),
                un(""));
        },
        gn = (n) => {
            if (!n) return;
            if (!D.includes(n.type))
                return (
                    mn(),
                    void un(
                        "Format tidak didukung. Pilih PNG, JPG, WebP, GIF, atau PDF.",
                    )
                );
            if (n.size > 5242880)
                return (mn(), void un("Ukuran file maksimal 5 MB."));
            (mn(),
                (I = {
                    file: n,
                    previewURL: n.type.startsWith("image/")
                        ? URL.createObjectURL(n)
                        : "",
                }),
                (document.getElementById(
                    "bablast-lc-preview-name",
                ).textContent = n.name),
                (document.getElementById(
                    "bablast-lc-preview-size",
                ).textContent = bn(n.size)));
            const t = document.getElementById("bablast-lc-preview-thumb");
            (I.previewURL
                ? ((t.textContent = ""),
                  (t.style.backgroundImage = `url("${I.previewURL.replace(/"/g, "%22")}")`),
                  (t.style.backgroundSize = "cover"),
                  (t.style.backgroundPosition = "center"))
                : (t.textContent = "PDF"),
                document
                    .getElementById("bablast-lc-attachment-preview")
                    .classList.add("visible"));
        },
        vn = (n) => {
            try {
                if (
                    (localStorage.setItem(en(), JSON.stringify(n)),
                    n && n.length > 0)
                ) {
                    const t = n[n.length - 1];
                    h(
                        f,
                        t.text || (t.media_url ? "[Lampiran]" : ""),
                        B ? "resolved" : "open",
                    );
                }
            } catch (n) {
                console.warn("[Bablast LiveChat] Failed to set cache:", n);
            }
        };
    let hn = null;
    const fn = (n = !0) => {
            n && hn && (clearTimeout(hn), (hn = null));
            const t = document.getElementById("bablast-lc-active-typing");
            t && t.remove();
        },
        xn = () => {
            const n = document.getElementById("bablast-lc-body");
            n && (n.scrollTop = n.scrollHeight);
        },
        yn = (n = "delivered") => {
            switch (n) {
                case "sending":
                    return '<svg class="bablast-lc-icon-sending" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 15"></polyline></svg>';
                case "sent":
                    return '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                case "read":
                    return '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#38BDF8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L7 17l-5-5"/><path d="M22 10l-7.5 7.5-2-2"/></svg>';
                case "failed":
                    return '<span class="bablast-lc-failed-indicator" title="Gagal terkirim. Klik untuk coba lagi."><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="#EF4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span class="bablast-lc-retry-text">Ulangi</span></span>';
                default:
                    return '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L7 17l-5-5"/><path d="M22 10l-7.5 7.5-2-2"/></svg>';
            }
        },
        wn = (n, t, e = null, a = "") => {
            if (!n) return;
            const o = document.getElementById("bablast-lc-body");
            if (o) {
                const a = o.querySelector(
                    `[data-temp-id="${n}"], [data-msg-id="${n}"]`,
                );
                if (a) {
                    e && a.setAttribute("data-msg-id", e);
                    const o = a.querySelector(".bablast-lc-msg-status");
                    o &&
                        ((o.className = `bablast-lc-msg-status status-${t}`),
                        (o.innerHTML = yn(t)),
                        (o.onclick =
                            "failed" === t
                                ? (t) => {
                                      (t.stopPropagation(), Fn(n));
                                  }
                                : null));
                }
            }
            try {
                const o = dn();
                let l = !1;
                for (let i = 0; i < o.length; i++)
                    if (
                        o[i].temp_id === n ||
                        o[i].message_id === n ||
                        o[i].id === n
                    ) {
                        ((o[i].status = t),
                            e && (o[i].message_id = e),
                            a && (o[i].error_message = a),
                            (l = !0));
                        break;
                    }
                l && localStorage.setItem(en(), JSON.stringify(o));
            } catch (n) {}
        },
        kn = async (n, t, e) => {
            try {
                let o = {};
                if (e && e.file) {
                    const n = new FormData();
                    n.append("file", e.file, e.file.name);
                    const t = await fetch(`${l}/upload/${a}`, {
                            method: "POST",
                            body: n,
                        }),
                        i = await t.json().catch(() => ({}));
                    if (!t.ok || !i.success || !i.data?.url_file)
                        throw new Error(i.message || "File gagal diunggah.");
                    o = i.data;
                }
                const i = await fetch(`${l}/message`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        sender_key: a,
                        session_id: f,
                        contact_name: x ? x.name : "",
                        contact_phone: x ? x.phone : "",
                        contact_email: x ? x.email : "",
                        external_id: x?.external_id || "",
                        custom_attributes: x?.custom_attributes || {},
                        content: t,
                        media_url: o.url_file || e?.media_url || "",
                    }),
                });
                if (!i.ok) {
                    const n = await i.json().catch(() => ({}));
                    if (
                        400 === i.status ||
                        (n.message && n.message.includes("selesai"))
                    ) {
                        V(!0);
                        const n = document.getElementById("bablast-lc-body");
                        if (n) {
                            const t = document.createElement("div");
                            ((t.className = "bablast-lc-divider"),
                                (t.style.marginTop = "12px"),
                                (t.innerHTML =
                                    "<span>Sesi percakapan telah berakhir. Silakan mulai percakapan baru</span>"),
                                n.appendChild(t),
                                (n.scrollTop = n.scrollHeight));
                        }
                    }
                    throw new Error(n.message || "Pesan gagal dikirim.");
                }
                const s = await i.json().catch(() => ({})),
                    r = s.data?.message_id || s.data?.id || "",
                    c = s.data?.status || "delivered";
                (wn(n, c, r), Bn());
            } catch (t) {
                (console.error(
                    "[Bablast LiveChat] Send message background error:",
                    t,
                ),
                    wn(n, "failed", null, t.message));
            }
        },
        Fn = (n) => {
            const t = dn().find(
                (t) => t.temp_id === n || t.message_id === n || t.id === n,
            );
            t &&
                (wn(n, "sending"),
                kn(
                    t.temp_id || n,
                    t.text || t.content || "",
                    t.media_url
                        ? { media_url: t.media_url, nama_file: t.nama_file }
                        : null,
                ));
        },
        _n = (n, t, e, a = !0, o = {}, l = {}) => {
            const i = document.getElementById("bablast-lc-body");
            if (!i) return;
            "user" !== n && fn();
            const s = "user" === n,
                r = s ? null : rn({ ...o, ...l }, n);
            r?.human && pn(r);
            const c = i.scrollHeight - i.scrollTop - i.clientHeight < 220,
                p = l?.temp_id || o?.temp_id || "",
                d = l?.message_id || l?.id || o?.message_id || o?.id || "",
                b = s ? l?.status || o?.status || "delivered" : "",
                u = document.createElement("div");
            ((u.className =
                "bablast-lc-msg " +
                (s ? "bablast-lc-msg-user" : "bablast-lc-msg-agent")),
                p && u.setAttribute("data-temp-id", p),
                d && u.setAttribute("data-msg-id", d));
            const m =
                    e ||
                    new Date().toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit",
                    }),
                g = o.media_url || o.url_file || o.mediaURL || "",
                v = o.mime_type || o.mimeType || o.message_type || "",
                x =
                    o.nama_file ||
                    o.original_name ||
                    o.file_name ||
                    ((n) => {
                        if (!n) return "Lampiran";
                        const t =
                            n.split("?")[0].split("/").pop() || "Lampiran";
                        try {
                            return decodeURIComponent(t);
                        } catch (n) {
                            return t;
                        }
                    })(g),
                y =
                    "application/pdf" === v ||
                    "document" === v ||
                    /\.pdf(?:$|\?)/i.test(g);
            let w = "";
            g && y
                ? (w = `<a class="bablast-lc-document" href="${Cn(g)}" target="_blank" rel="noopener noreferrer" download><span class="bablast-lc-document-icon">PDF</span><span class="bablast-lc-document-info"><span class="bablast-lc-document-name">${Cn(x)}</span><span class="bablast-lc-document-meta">${Cn(bn(o.size_file || o.size) || "Buka dokumen")}</span></span></a>`)
                : g &&
                  (w = `<a class="bablast-lc-media-link" href="${Cn(g)}" target="_blank" rel="noopener noreferrer" aria-label="Buka gambar"><img class="bablast-lc-media-image" src="${Cn(g)}" alt="${Cn(x)}" loading="lazy"></a>`);
            const k = s
                ? `<span class="bablast-lc-msg-status status-${b}">${yn(b)}</span>`
                : "";
            if (
                ((u.innerHTML = `\n            ${!s && r?.name ? `<div class="bablast-lc-agent-label"><span class="bablast-lc-agent-label-icon" aria-hidden="true">${r.automated ? "✨" : "👤"}</span><span>${Cn(r.name)}</span></div>` : ""}\n            ${w}\n            ${
                    t
                        ? `<div class="bablast-lc-msg-content">${((n) => {
                              if (!n) return "";
                              let t = Cn(n);
                              return (
                                  (t = t.replace(
                                      /(https?:\/\/[^\s<]+[^<.,:;"')\]\s])/g,
                                      (n) =>
                                          `<a href="${n}" target="_blank" rel="noopener noreferrer">${n}</a>`,
                                  )),
                                  (t = t.replace(
                                      /(?:\*([^*]+)\*)/g,
                                      "<strong>$1</strong>",
                                  )),
                                  (t = t.replace(
                                      /(?:_([^_]+)_)/g,
                                      "<em>$1</em>",
                                  )),
                                  (t = t.replace(
                                      /(?:~([^~]+)~)/g,
                                      "<del>$1</del>",
                                  )),
                                  (t = t.replace(
                                      /(?:`([^`]+)`)/g,
                                      '<code style="background:rgba(0,0,0,0.06);padding:2px 4px;border-radius:4px;font-size:12px;">$1</code>',
                                  )),
                                  t
                              );
                          })(t)}</div>`
                        : ""
                }\n            <div class="bablast-lc-msg-meta">\n                <span class="bablast-lc-time">${m}</span>\n                ${k}\n            </div>\n        `),
                s && "failed" === b)
            ) {
                const n = u.querySelector(".bablast-lc-msg-status");
                n?.addEventListener("click", (n) => {
                    (n.stopPropagation(), Fn(p || d));
                });
            }
            (i.appendChild(u),
                (s || c) && (i.scrollTop = i.scrollHeight),
                a &&
                    ((n, t, e, a = {}, o = {}) => {
                        try {
                            const l = dn(),
                                i = Date.now();
                            l.push({
                                role: n,
                                text: t,
                                timeStr: e,
                                ts: i,
                                ...a,
                                ...o,
                            });
                            const s = l.slice(-100);
                            (localStorage.setItem(en(), JSON.stringify(s)),
                                h(
                                    f,
                                    t || (a.media_url ? "[Lampiran]" : ""),
                                    B ? "resolved" : "open",
                                ));
                        } catch (n) {
                            console.warn(
                                "[Bablast LiveChat] Failed to cache message locally:",
                                n,
                            );
                        }
                    })(
                        n,
                        t,
                        m,
                        o,
                        s
                            ? {
                                  temp_id: p,
                                  message_id: d,
                                  status: b || "delivered",
                              }
                            : {
                                  sender_name: r?.name || "",
                                  agent_name: r?.human ? r.name : "",
                                  agent_role: r?.role || "",
                                  is_automated: r?.automated || !1,
                                  is_fallback_identity: r?.fallback || !1,
                              },
                    ));
        },
        En = (n) => {
            const t = document.getElementById("bablast-lc-body");
            if (!t) return;
            ((t.innerHTML = ""), fn());
            const e = document.createElement("div");
            ((e.className = "bablast-lc-divider"),
                (e.innerHTML = "<span>Hari Ini</span>"),
                t.appendChild(e),
                n && 0 !== n.length
                    ? (n.forEach((n) => {
                          const t =
                                  "user" === n.role ||
                                  "incoming" === n.type ||
                                  "inbound" === n.type ||
                                  "inbound" === n.direction
                                      ? "user"
                                      : "agent",
                              e = n.text || n.content || "",
                              a =
                                  n.timeStr ||
                                  (n.timestamp
                                      ? new Date(
                                            1e3 * n.timestamp,
                                        ).toLocaleTimeString([], {
                                            hour: "2-digit",
                                            minute: "2-digit",
                                        })
                                      : ""),
                              o = {
                                  media_url: n.media_url || n.mediaURL || "",
                                  mime_type:
                                      n.mime_type ||
                                      n.mimeType ||
                                      n.message_type ||
                                      "",
                                  nama_file:
                                      n.nama_file ||
                                      n.original_name ||
                                      n.file_name ||
                                      "",
                                  size_file: n.size_file || n.size || 0,
                              };
                          (e || o.media_url) && _n(t, e, a, !1, o, n);
                      }),
                      requestAnimationFrame(() => {
                          (xn(),
                              setTimeout(() => xn(), 50),
                              setTimeout(() => xn(), 200));
                      }))
                    : F.welcome_message &&
                      _n(
                          "agent",
                          F.welcome_message,
                          "",
                          !1,
                          {},
                          { is_automated: !0 },
                      ));
        },
        Bn = () => {
            k() ||
                T ||
                document.getElementById("bablast-lc-followup-card") ||
                setTimeout(() => {
                    In();
                }, 400);
        },
        In = () => {
            const n = document.getElementById("bablast-lc-body");
            if (!n || document.getElementById("bablast-lc-followup-card"))
                return;
            if (k() || T) return;
            const t = document.createElement("div");
            ((t.id = "bablast-lc-followup-card"),
                (t.className = "bablast-lc-followup-card"),
                (t.innerHTML =
                    '\n            <div class="bablast-lc-followup-header">\n                <div class="bablast-lc-followup-icon">\n                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5L2 22l5.22-1.31C8.61 21.49 10.26 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm.04 16.5c-1.52 0-2.96-.44-4.18-1.2l-.3-.19-3.1.78.83-3.02-.2-.32A7.95 7.95 0 014 12c0-4.41 3.59-8 8.04-8 4.43 0 8.04 3.59 8.04 8 0 4.41-3.61 8.5-8.04 8.5z"/></svg>\n                </div>\n                <div>\n                    <p class="bablast-lc-followup-title">Ingin kami follow-up ke WhatsApp?</p>\n                    <p class="bablast-lc-followup-desc">Tinggalkan kontak agar obrolan ini dapat kami teruskan jika Anda meninggalkan halaman.</p>\n                </div>\n            </div>\n            <form id="bablast-lc-followup-form" class="bablast-lc-followup-form">\n                <div class="bablast-lc-followup-inputs">\n                    <input type="text" id="bablast-lc-followup-name" placeholder="Nama Anda (opsional)" class="bablast-lc-followup-input" />\n                    <div class="bablast-lc-followup-phone-wrap">\n                        <span class="bablast-lc-followup-prefix">🇮🇩 +62</span>\n                        <input type="tel" id="bablast-lc-followup-phone" placeholder="812-3456-7890" class="bablast-lc-followup-phone" required />\n                    </div>\n                </div>\n                <div class="bablast-lc-followup-actions">\n                    <button type="submit" id="bablast-lc-followup-submit" class="bablast-lc-followup-btn-submit">Simpan Kontak</button>\n                    <button type="button" id="bablast-lc-followup-dismiss" class="bablast-lc-followup-btn-dismiss">Nanti Saja</button>\n                </div>\n            </form>\n        '),
                n.appendChild(t),
                requestAnimationFrame(() => xn()));
            const e = document.getElementById("bablast-lc-followup-form");
            e &&
                e.addEventListener("submit", async (n) => {
                    n.preventDefault();
                    const e = document.getElementById(
                            "bablast-lc-followup-name",
                        ),
                        o = document.getElementById(
                            "bablast-lc-followup-phone",
                        ),
                        i = document.getElementById(
                            "bablast-lc-followup-submit",
                        ),
                        s = e ? e.value.trim() : "",
                        r = o
                            ? o.value
                                  .trim()
                                  .replace(/\D/g, "")
                                  .replace(/^0+/, "")
                            : "";
                    if (!r) return;
                    const c = "62" + r,
                        p = s || `Pengunjung #${f.slice(-4)}`;
                    i && ((i.disabled = !0), (i.textContent = "Menyimpan..."));
                    try {
                        (await fetch(`${l}/lead/${a}`, {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({
                                session_id: f,
                                name: p,
                                phone: c,
                            }),
                        }),
                            (x = w({ ...(x || {}), name: p, phone: c })));
                        try {
                            localStorage.setItem(
                                "bablast_lc_visitor_" + d,
                                JSON.stringify(x),
                            );
                        } catch (n) {}
                        t.innerHTML =
                            '\n                        <div class="bablast-lc-followup-success">\n                            <span style="font-size:16px;">✅</span>\n                            <span>Nomor WhatsApp Anda tersimpan! Obrolan ini dapat kami teruskan ke WhatsApp.</span>\n                        </div>\n                    ';
                    } catch (n) {
                        (console.warn(
                            "[Bablast LiveChat] Follow-up submit error:",
                            n,
                        ),
                            i &&
                                ((i.disabled = !1),
                                (i.textContent = "Simpan Kontak")));
                    }
                });
            const o = document.getElementById("bablast-lc-followup-dismiss");
            o &&
                o.addEventListener("click", () => {
                    T = !0;
                    try {
                        sessionStorage.setItem(
                            "bablast_lc_dismiss_card_" + f,
                            "1",
                        );
                    } catch (n) {}
                    ((t.style.transition = "opacity 0.2s, transform 0.2s"),
                        (t.style.opacity = "0"),
                        (t.style.transform = "scale(0.95)"),
                        setTimeout(() => t.remove(), 200));
                });
        },
        Cn = (n) =>
            n
                ? n.replace(
                      /[&<>'"]/g,
                      (n) =>
                          ({
                              "&": "&amp;",
                              "<": "&lt;",
                              ">": "&gt;",
                              "'": "&#39;",
                              '"': "&quot;",
                          })[n] || n,
                  )
                : "",
        Sn = async () => {
            const n = dn(),
                t = JSON.stringify(n || []);
            (n && n.length > 0 ? En(n) : F.welcome_message && En([]),
                "1" ===
                    localStorage.getItem("bablast_lc_closed_" + d + "_" + f) &&
                    V(!0));
            try {
                const e = await fetch(`${l}/history/${a}/${f}`);
                if (e.ok) {
                    const a = await e.json(),
                        o = (a.data && a.data.messages) || [],
                        l = (a.data && a.data.status) || "open";
                    if (
                        ("resolved" === l || "closed" === l
                            ? (V(!0), h(f, void 0, l))
                            : (V(!1), h(f, void 0, "open")),
                        o.length > 0)
                    ) {
                        const n = o
                            .map((n) => ({
                                role:
                                    "incoming" === n.type ||
                                    "inbound" === n.type ||
                                    "inbound" === n.direction ||
                                    "user" === n.role
                                        ? "user"
                                        : "agent",
                                text: n.content || "",
                                media_url: n.media_url || "",
                                message_type: n.message_type || "",
                                nama_file: n.nama_file || "",
                                size_file: n.size_file || 0,
                                sender_name:
                                    n.sender_name ||
                                    n.agent_name ||
                                    n.from_name ||
                                    "",
                                agent_name:
                                    n.agent_name ||
                                    n.metadata?.agent_name ||
                                    "",
                                agent_role:
                                    n.agent_role ||
                                    n.role_name ||
                                    n.metadata?.agent_role ||
                                    n.metadata?.role_name ||
                                    "",
                                agent_id: n.agent_id || "",
                                metadata: n.metadata || {},
                                is_automated: ln(n, n.role || ""),
                                timeStr: n.timestamp
                                    ? new Date(
                                          1e3 * n.timestamp,
                                      ).toLocaleTimeString([], {
                                          hour: "2-digit",
                                          minute: "2-digit",
                                      })
                                    : "",
                                ts: n.timestamp
                                    ? 1e3 * n.timestamp
                                    : Date.now(),
                            }))
                            .filter((n) => !!n.text || !!n.media_url);
                        (JSON.stringify(dn()) !== t || (vn(n), En(n)),
                            nn(),
                            n.some((n) => "user" === n.role) && P() && Bn());
                    } else
                        (n && 0 !== n.length) ||
                            JSON.stringify(dn()) !== t ||
                            En([]);
                }
            } catch (n) {
                console.warn(
                    "[Bablast LiveChat] Server history sync error (using local cache):",
                    n,
                );
            }
        },
        Ln = () => {
            const n = window.AudioContext || window.webkitAudioContext;
            return n
                ? (S || (S = new n()),
                  "suspended" === S.state && S.resume().catch(() => {}),
                  S)
                : null;
        },
        zn = () => {
            const n = Ln();
            if (n && "running" === n.state)
                try {
                    const t = n.currentTime,
                        e = n.createOscillator(),
                        a = n.createGain();
                    ((e.type = "sine"),
                        e.frequency.setValueAtTime(587.33, t),
                        e.frequency.setValueAtTime(880, t + 0.1),
                        a.gain.setValueAtTime(0.01, t),
                        a.gain.exponentialRampToValueAtTime(0.18, t + 0.05),
                        a.gain.exponentialRampToValueAtTime(0.001, t + 0.35),
                        e.connect(a),
                        a.connect(n.destination),
                        e.start(t),
                        e.stop(t + 0.35));
                } catch (n) {
                    console.warn("[Bablast LiveChat] Audio chime error:", n);
                }
        },
        $n = () => {
            const n = document.getElementById("bablast-lc-badge");
            n &&
                ((n.textContent = C > 99 ? "99+" : String(C)),
                n.setAttribute("aria-label", `${C} pesan belum dibaca`),
                (n.style.display = C > 0 ? "flex" : "none"));
        },
        An = () => {
            L && (clearInterval(L), (L = null), (document.title = M));
        };
    (window.addEventListener("visibilitychange", () => {
        document.hidden || An();
    }),
        window.addEventListener("focus", An));
    const Tn = () => {
            E ||
                ((E = new EventSource(`${l}/events/${a}/${f}`)),
                (E.onmessage = (n) => {
                    try {
                        const a = JSON.parse(n.data);
                        if (
                            ("message" !== a.type &&
                                "proactive_greeting" !== a.type) ||
                            (!a.content && !a.media_url)
                        ) {
                            if ("typing" === a.type)
                                if ("typing" === a.state) {
                                    const n = rn(a, a.role || "agent"),
                                        t = Boolean(
                                            a.agent_name ||
                                            a.sender_name ||
                                            a.from_name,
                                        );
                                    ((n = {}) => {
                                        const t =
                                            document.getElementById(
                                                "bablast-lc-body",
                                            );
                                        if (!t) return;
                                        const e = an(n),
                                            a = e ||
                                                j || { name: sn(), role: "" };
                                        (e &&
                                            !1 !== n.human &&
                                            !0 !== n.automated &&
                                            pn(a),
                                            fn(!1));
                                        const o = document.createElement("div");
                                        ((o.id = "bablast-lc-active-typing"),
                                            (o.className =
                                                "bablast-lc-typing-bubble"),
                                            o.setAttribute(
                                                "title",
                                                a.name + " sedang mengetik...",
                                            ),
                                            (o.innerHTML = `\n            <div class="bablast-lc-typing-label">${Cn(a.name)} sedang mengetik...</div>\n            <div class="bablast-lc-typing-dots">\n                <span class="bablast-lc-dot"></span>\n                <span class="bablast-lc-dot"></span>\n                <span class="bablast-lc-dot"></span>\n            </div>\n        `),
                                            t.appendChild(o),
                                            requestAnimationFrame(() => {
                                                t.scrollTop = t.scrollHeight;
                                            }),
                                            hn && clearTimeout(hn),
                                            (hn = setTimeout(() => {
                                                fn();
                                            }, 2e4)));
                                    })(
                                        n.automated &&
                                            /^(ai assistant|bablast ai)$/i.test(
                                                n.name,
                                            ) &&
                                            j
                                            ? j
                                            : n.human || t
                                              ? n
                                              : j || n,
                                    );
                                } else fn();
                            else if (
                                "read_receipt" === a.type ||
                                "read" === a.type
                            )
                                (() => {
                                    const n =
                                        document.getElementById(
                                            "bablast-lc-body",
                                        );
                                    n &&
                                        n
                                            .querySelectorAll(
                                                ".bablast-lc-msg-user .bablast-lc-msg-status",
                                            )
                                            .forEach((n) => {
                                                n.classList.contains(
                                                    "status-failed",
                                                ) ||
                                                    n.classList.contains(
                                                        "status-sending",
                                                    ) ||
                                                    ((n.className =
                                                        "bablast-lc-msg-status status-read"),
                                                    (n.innerHTML = yn("read")));
                                            });
                                    try {
                                        const n = dn();
                                        let t = !1;
                                        (n.forEach((n) => {
                                            ("user" === n.role ||
                                                "incoming" === n.type ||
                                                "inbound" === n.type) &&
                                                "failed" !== n.status &&
                                                "sending" !== n.status &&
                                                "read" !== n.status &&
                                                ((n.status = "read"), (t = !0));
                                        }),
                                            t &&
                                                localStorage.setItem(
                                                    en(),
                                                    JSON.stringify(n),
                                                ));
                                    } catch (n) {}
                                })();
                            else if (
                                "message_status" === a.type &&
                                a.message_id
                            )
                                wn(a.message_id, a.status || "delivered");
                            else if ("session_closed" === a.type) {
                                fn();
                                const n =
                                    document.getElementById("bablast-lc-body");
                                if (n) {
                                    const t = document.createElement("div");
                                    ((t.className = "bablast-lc-divider"),
                                        (t.style.marginTop = "12px"),
                                        (t.innerHTML =
                                            "<span>Sesi percakapan telah berakhir</span>"),
                                        n.appendChild(t),
                                        (n.scrollTop = n.scrollHeight));
                                }
                                V(!0);
                            }
                        } else {
                            fn();
                            const n = a.role || "agent";
                            if (
                                (_n(
                                    n,
                                    a.content || "",
                                    "",
                                    !0,
                                    {
                                        media_url: a.media_url || "",
                                        message_type: a.message_type || "",
                                        nama_file: a.nama_file || "",
                                        size_file: a.size_file || 0,
                                    },
                                    a,
                                ),
                                nn(),
                                "agent" === n || "assistant" === n)
                            ) {
                                const o = rn(a, n);
                                ((t = o.name),
                                    (e = a.content || ""),
                                    _ || ((C += 1), $n(), zn(), H(t, e)),
                                    document.hidden &&
                                        ((n) => {
                                            if (L) return;
                                            let t = !1;
                                            L = setInterval(() => {
                                                ((document.title = t
                                                    ? `(1) Pesan Baru dari ${n} - ${M}`
                                                    : M),
                                                    (t = !t));
                                            }, 1200);
                                        })(t));
                            }
                        }
                    } catch (n) {
                        console.error("[Bablast LiveChat] SSE parse error:", n);
                    }
                    var t, e;
                }),
                (E.onerror = (n) => {
                    (console.warn(
                        "[Bablast LiveChat] SSE connection error, reconnecting...",
                        n,
                    ),
                        E && (E.close(), (E = null), setTimeout(Tn, 3e3)));
                }));
        },
        jn = () => {
            _ = !_;
            const n = document.getElementById("bablast-lc-window"),
                t = document.getElementById("bablast-lc-bubble");
            _
                ? (O(),
                  Ln(),
                  (C = 0),
                  $n(),
                  An(),
                  nn(),
                  n.classList.add("open"),
                  t.classList.add("active"),
                  t.setAttribute("aria-expanded", "true"),
                  !F.is_prechat_form_enabled || k() || P()
                      ? (Tn(),
                        requestAnimationFrame(() => xn()),
                        setTimeout(() => {
                            (xn(),
                                B ||
                                    document
                                        .getElementById("bablast-lc-input")
                                        ?.focus());
                        }, 100))
                      : setTimeout(() => {
                            document
                                .getElementById("bablast-lc-input-name")
                                ?.focus();
                        }, 100))
                : (n.classList.remove("open"),
                  t.classList.remove("active"),
                  t.setAttribute("aria-expanded", "false"));
        },
        Mn = async (n) => {
            if ((n.preventDefault(), B)) return;
            const t = document.getElementById("bablast-lc-input"),
                e = t ? t.value.trim() : "",
                a = I;
            if (!e && !a) return;
            un("");
            const o =
                "tmp_" +
                Date.now() +
                "_" +
                Math.random().toString(36).substring(2, 7);
            let l = {};
            (a &&
                (l = {
                    file: a.file,
                    media_url:
                        a.previewURL ||
                        (a.isImage && a.file
                            ? URL.createObjectURL(a.file)
                            : ""),
                    mime_type: a.file ? a.file.type : "",
                    nama_file: a.file ? a.file.name : "Lampiran",
                    size_file: a.file ? a.file.size : 0,
                }),
                _n("user", e, "", !0, l, { temp_id: o, status: "sending" }),
                t && (t.value = ""),
                a && mn(),
                kn(o, e, a));
        };
    let Dn = "active",
        Pn = null;
    const Nn = (n) => {
        if (!a || !f) return;
        Dn = n;
        const t = JSON.stringify({
            sender_key: a,
            session_id: f,
            presence: n,
            url: window.location.href,
            user_agent: navigator.userAgent,
            contact_name: x ? x.name : "",
            contact_phone: x ? x.phone : "",
            contact_email: x ? x.email : "",
            external_id: x?.external_id || "",
            custom_attributes: x?.custom_attributes || {},
        });
        if ("offline" === n && navigator.sendBeacon) {
            const n = new Blob([t], { type: "application/json" });
            return void navigator.sendBeacon(`${l}/presence`, n);
        }
        fetch(`${l}/presence`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: t,
            keepalive: !0,
        }).catch(() => {});
    };
    let Hn = !1;
    function On() {
        return x && (x.phone || x.email)
            ? fetch(`${l}/sync-lead/${a}`, {
                  method: "POST",
                  headers: { "Content-Type": "application/json" },
                  body: JSON.stringify(x),
                  keepalive: !0,
              }).catch(() => {})
            : Promise.resolve();
    }
    const qn = (n) => {
            const t = w(n, x);
            if (!t || !Jn(t))
                return (
                    console.warn(
                        "[Bablast LiveChat] identify(profile) memerlukan user_id/external_id, nama, email, atau nomor telepon.",
                    ),
                    null
                );
            const e = t.external_id,
                a = x?.external_id,
                o = Boolean(e && a && e !== a) || Boolean(e && !a);
            return (
                (x = t),
                o &&
                    e &&
                    ((d = p(e)),
                    E && (E.close(), (E = null)),
                    (f =
                        localStorage.getItem(b()) ||
                        "vis_" +
                            Math.random().toString(36).substring(2, 10) +
                            Date.now().toString(36)),
                    localStorage.setItem(b(), f),
                    (B = !1),
                    (j = null)),
                localStorage.setItem(u(), JSON.stringify(x)),
                On(),
                Hn && (nn(), Tn(), Nn(Dn || "active"), o && Sn()),
                { ...x }
            );
        },
        Jn = (n) =>
            Boolean(n && (n.external_id || n.name || n.email || n.phone)),
        Un = () => {
            ((x = null),
                localStorage.removeItem(u()),
                (d = p("")),
                Hn
                    ? (Z(!0), Nn("active"))
                    : ((f =
                          "vis_" +
                          Math.random().toString(36).substring(2, 10) +
                          Date.now().toString(36)),
                      localStorage.setItem(b(), f)));
        },
        Rn = function (n, ...t) {
            if ("function" == typeof Rn[n]) return Rn[n](...t);
            console.warn(`[Bablast LiveChat] Method "${n}" tidak dikenal.`);
        };
    (Object.assign(Rn, {
        key: a,
        baseUrl: o,
        identify: qn,
        setUser: qn,
        setCustomAttributes: (n) => {
            const t = y(n);
            return (
                (x = {
                    ...(x || {}),
                    custom_attributes: {
                        ...(x?.custom_attributes || {}),
                        ...t,
                    },
                }),
                localStorage.setItem(u(), JSON.stringify(x)),
                Hn && Nn(Dn || "active"),
                { ...x.custom_attributes }
            );
        },
        clearIdentity: Un,
        logout: Un,
        reset: Z,
    }),
        (window.BablastLiveChat = Rn));
    // ---- Fallback polling (patch) ----
    let pollSeeded = null;
    const pollSeen = new Set();

    const pollNewMessages = async () => {
        const sid = f;
        if (!sid || B) return;
        try {
            const resp = await fetch(`${l}/history/${a}/${sid}`);
            if (!resp.ok || sid !== f) return;
            const msgs = ((await resp.json()).data || {}).messages || [];

            const seedOnly = pollSeeded !== sid;
            pollSeeded = sid;

            msgs.forEach((msg) => {
                const id = msg.id || msg.message_id;
                if (!id || pollSeen.has(id)) return;
                pollSeen.add(id);
                if (seedOnly) return;

                if (
                    msg.type === "incoming" ||
                    msg.type === "inbound" ||
                    msg.direction === "inbound"
                )
                    return;

                const text = msg.content || "";
                if (!text && !msg.media_url) return;

                const dup = dn()
                    .slice(-10)
                    .some(
                        (m) =>
                            m.role !== "user" &&
                            (m.text || "") === text &&
                            Date.now() - (m.ts || 0) < 60000,
                    );
                if (dup) return;

                _n(
                    "agent",
                    text,
                    "",
                    true,
                    {
                        media_url: msg.media_url || "",
                        message_type: msg.message_type || "",
                        nama_file: msg.nama_file || "",
                        size_file: msg.size_file || 0,
                    },
                    msg,
                );

                const ident = rn(msg, "agent");
                if (!_) {
                    C += 1;
                    $n();
                    zn();
                    H(ident.name, text);
                }
            });
        } catch (e) {}
    };

    const schedulePoll = () => {
        setTimeout(
            async () => {
                await pollNewMessages();
                schedulePoll();
            },
            document.hidden ? 15000 : _ ? 4000 : 12000,
        );
    };

    document.addEventListener("visibilitychange", () => {
        if (!document.hidden) pollNewMessages();
    });

    setTimeout(() => {
        pollNewMessages();
        schedulePoll();
    }, 2000);
    const Kn = async () => {
        (N(F.primary_color, F.widget_position),
            (() => {
                const n = document.getElementById("bablast-lc-widget");
                n && n.remove();
                const t = document.createElement("div");
                ((t.id = "bablast-lc-widget"),
                    (t.innerHTML = `\n            <div id="bablast-lc-proactive-invite" role="status" aria-live="polite">\n                <div class="bablast-lc-proactive-avatar" id="bablast-lc-proactive-avatar">B</div>\n                <div class="bablast-lc-proactive-content">\n                    <p class="bablast-lc-proactive-agent" id="bablast-lc-proactive-agent">Tim kami</p>\n                    <p class="bablast-lc-proactive-message" id="bablast-lc-proactive-message"></p>\n                    <div class="bablast-lc-proactive-actions">\n                        <button type="button" id="bablast-lc-proactive-reply">Balas</button>\n                        <button type="button" id="bablast-lc-proactive-dismiss">Nanti</button>\n                    </div>\n                </div>\n            </div>\n            <button type="button" id="bablast-lc-bubble" title="Chat dengan Kami" aria-label="Buka chat" aria-expanded="false">\n                <svg class="bablast-lc-icon-open" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>\n                <svg class="bablast-lc-icon-close" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>\n                <div id="bablast-lc-badge" class="bablast-lc-badge" aria-label="0 pesan belum dibaca">0</div>\n            </button>\n            <div id="bablast-lc-window" role="dialog" aria-label="${Cn(F.header_title)}">\n                <div id="bablast-lc-header">\n                    <div class="bablast-lc-header-info">\n                        <div class="bablast-lc-avatar-container">\n                            <div class="bablast-lc-avatar" id="bablast-lc-avatar-box">\n                                <span id="bablast-lc-avatar-text">B</span>\n                            </div>\n                            <div class="bablast-lc-status-dot"></div>\n                        </div>\n                        <div class="bablast-lc-titles">\n                            <h3 id="bablast-lc-title">${F.header_title}</h3>\n                            <p id="bablast-lc-subtitle">${F.header_subtitle}</p>\n                        </div>\n                    </div>\n                    <div class="bablast-lc-header-actions">\n                        <button type="button" id="bablast-lc-btn-history" class="bablast-lc-header-btn" title="Riwayat Percakapan" aria-label="Riwayat percakapan">\n                            <svg viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>\n                        </button>\n                        <div class="bablast-lc-menu-wrapper" id="bablast-lc-menu-wrapper">\n                            <button type="button" id="bablast-lc-btn-menu" class="bablast-lc-header-btn" title="Opsi Percakapan" aria-label="Menu Opsi">\n                                <svg viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>\n                            </button>\n                            <div id="bablast-lc-header-dropdown" class="bablast-lc-dropdown-menu" style="display:none;">\n                                <button type="button" id="bablast-lc-opt-end-chat" class="bablast-lc-dropdown-item text-danger">\n                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>\n                                    <span>Akhiri Percakapan</span>\n                                </button>\n                            </div>\n                        </div>\n                        <button type="button" id="bablast-lc-close" title="Tutup Chat" aria-label="Tutup chat">\n                            <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>\n                        </button>\n                    </div>\n                </div>\n\n                \x3c!-- Pre-Chat Lead Capture Form --\x3e\n                <div id="bablast-lc-prechat-container" style="display:none;">\n                    <p class="bablast-lc-prechat-intro" id="bablast-lc-prechat-intro-text">\n                        ${F.prechat_message || "Silakan isi data diri Anda sebelum memulai percakapan"}\n                    </p>\n                    <form id="bablast-lc-prechat-form">\n                        <div class="bablast-lc-form-group" id="bablast-lc-group-name">\n                            <label class="bablast-lc-form-label">\n                                Nama Lengkap <span class="req" id="bablast-lc-req-name">*</span>\n                            </label>\n                            <input type="text" class="bablast-lc-form-input" id="bablast-lc-input-name" placeholder="Nama Anda" autocomplete="name">\n                            <div class="bablast-lc-form-error" id="bablast-lc-err-name">Nama lengkap wajib diisi</div>\n                        </div>\n\n                        <div class="bablast-lc-form-group" id="bablast-lc-group-phone">\n                            <label class="bablast-lc-form-label">\n                                Nomor WhatsApp / HP <span class="req" id="bablast-lc-req-phone" style="display:none;">*</span>\n                            </label>\n                            <div class="bablast-lc-phone-field">\n                                <select id="bablast-lc-country-code" aria-label="Kode negara" autocomplete="tel-country-code">\n                                    <option value="62" selected>🇮🇩 +62</option>\n                                    <option value="93">🇦🇫 +93</option>\n                                    <option value="27">🇿🇦 +27</option>\n                                    <option value="355">🇦🇱 +355</option>\n                                    <option value="213">🇩🇿 +213</option>\n                                    <option value="1">🇺🇸 +1</option>\n                                    <option value="376">🇦🇩 +376</option>\n                                    <option value="244">🇦🇴 +244</option>\n                                    <option value="1">🇦🇮 +1</option>\n                                    <option value="1">🇦🇬 +1</option>\n                                    <option value="966">🇸🇦 +966</option>\n                                    <option value="54">🇦🇷 +54</option>\n                                    <option value="374">🇦🇲 +374</option>\n                                    <option value="297">🇦🇼 +297</option>\n                                    <option value="61">🇦🇺 +61</option>\n                                    <option value="43">🇦🇹 +43</option>\n                                    <option value="994">🇦🇿 +994</option>\n                                    <option value="1">🇧🇸 +1</option>\n                                    <option value="973">🇧🇭 +973</option>\n                                    <option value="880">🇧🇩 +880</option>\n                                    <option value="1">🇧🇧 +1</option>\n                                    <option value="31">🇳🇱 +31</option>\n                                    <option value="599">🇧🇶 +599</option>\n                                    <option value="375">🇧🇾 +375</option>\n                                    <option value="32">🇧🇪 +32</option>\n                                    <option value="501">🇧🇿 +501</option>\n                                    <option value="229">🇧🇯 +229</option>\n                                    <option value="1">🇧🇲 +1</option>\n                                    <option value="975">🇧🇹 +975</option>\n                                    <option value="591">🇧🇴 +591</option>\n                                    <option value="387">🇧🇦 +387</option>\n                                    <option value="267">🇧🇼 +267</option>\n                                    <option value="55">🇧🇷 +55</option>\n                                    <option value="673">🇧🇳 +673</option>\n                                    <option value="359">🇧🇬 +359</option>\n                                    <option value="226">🇧🇫 +226</option>\n                                    <option value="257">🇧🇮 +257</option>\n                                    <option value="420">🇨🇿 +420</option>\n                                    <option value="235">🇹🇩 +235</option>\n                                    <option value="56">🇨🇱 +56</option>\n                                    <option value="225">🇨🇮 +225</option>\n                                    <option value="599">🇨🇼 +599</option>\n                                    <option value="45">🇩🇰 +45</option>\n                                    <option value="1">🇩🇲 +1</option>\n                                    <option value="593">🇪🇨 +593</option>\n                                    <option value="503">🇸🇻 +503</option>\n                                    <option value="291">🇪🇷 +291</option>\n                                    <option value="372">🇪🇪 +372</option>\n                                    <option value="268">🇸🇿 +268</option>\n                                    <option value="251">🇪🇹 +251</option>\n                                    <option value="679">🇫🇯 +679</option>\n                                    <option value="63">🇵🇭 +63</option>\n                                    <option value="358">🇫🇮 +358</option>\n                                    <option value="241">🇬🇦 +241</option>\n                                    <option value="220">🇬🇲 +220</option>\n                                    <option value="995">🇬🇪 +995</option>\n                                    <option value="233">🇬🇭 +233</option>\n                                    <option value="350">🇬🇮 +350</option>\n                                    <option value="299">🇬🇱 +299</option>\n                                    <option value="1">🇬🇩 +1</option>\n                                    <option value="590">🇬🇵 +590</option>\n                                    <option value="1">🇬🇺 +1</option>\n                                    <option value="502">🇬🇹 +502</option>\n                                    <option value="44">🇬🇬 +44</option>\n                                    <option value="224">🇬🇳 +224</option>\n                                    <option value="240">🇬🇶 +240</option>\n                                    <option value="245">🇬🇼 +245</option>\n                                    <option value="592">🇬🇾 +592</option>\n                                    <option value="594">🇬🇫 +594</option>\n                                    <option value="509">🇭🇹 +509</option>\n                                    <option value="504">🇭🇳 +504</option>\n                                    <option value="852">🇭🇰 +852</option>\n                                    <option value="36">🇭🇺 +36</option>\n                                    <option value="91">🇮🇳 +91</option>\n                                    <option value="44">🇬🇧 +44</option>\n                                    <option value="964">🇮🇶 +964</option>\n                                    <option value="98">🇮🇷 +98</option>\n                                    <option value="353">🇮🇪 +353</option>\n                                    <option value="354">🇮🇸 +354</option>\n                                    <option value="972">🇮🇱 +972</option>\n                                    <option value="39">🇮🇹 +39</option>\n                                    <option value="1">🇯🇲 +1</option>\n                                    <option value="81">🇯🇵 +81</option>\n                                    <option value="49">🇩🇪 +49</option>\n                                    <option value="44">🇯🇪 +44</option>\n                                    <option value="253">🇩🇯 +253</option>\n                                    <option value="687">🇳🇨 +687</option>\n                                    <option value="855">🇰🇭 +855</option>\n                                    <option value="237">🇨🇲 +237</option>\n                                    <option value="1">🇨🇦 +1</option>\n                                    <option value="7">🇰🇿 +7</option>\n                                    <option value="254">🇰🇪 +254</option>\n                                    <option value="358">🇦🇽 +358</option>\n                                    <option value="1">🇰🇾 +1</option>\n                                    <option value="61">🇨🇨 +61</option>\n                                    <option value="682">🇨🇰 +682</option>\n                                    <option value="500">🇫🇰 +500</option>\n                                    <option value="298">🇫🇴 +298</option>\n                                    <option value="1">🇲🇵 +1</option>\n                                    <option value="692">🇲🇭 +692</option>\n                                    <option value="672">🇳🇫 +672</option>\n                                    <option value="677">🇸🇧 +677</option>\n                                    <option value="47">🇸🇯 +47</option>\n                                    <option value="1">🇹🇨 +1</option>\n                                    <option value="1">🇻🇮 +1</option>\n                                    <option value="1">🇻🇬 +1</option>\n                                    <option value="681">🇼🇫 +681</option>\n                                    <option value="996">🇰🇬 +996</option>\n                                    <option value="686">🇰🇮 +686</option>\n                                    <option value="57">🇨🇴 +57</option>\n                                    <option value="269">🇰🇲 +269</option>\n                                    <option value="242">🇨🇬 +242</option>\n                                    <option value="243">🇨🇩 +243</option>\n                                    <option value="82">🇰🇷 +82</option>\n                                    <option value="850">🇰🇵 +850</option>\n                                    <option value="383">🇽🇰 +383</option>\n                                    <option value="506">🇨🇷 +506</option>\n                                    <option value="385">🇭🇷 +385</option>\n                                    <option value="53">🇨🇺 +53</option>\n                                    <option value="965">🇰🇼 +965</option>\n                                    <option value="856">🇱🇦 +856</option>\n                                    <option value="371">🇱🇻 +371</option>\n                                    <option value="961">🇱🇧 +961</option>\n                                    <option value="266">🇱🇸 +266</option>\n                                    <option value="231">🇱🇷 +231</option>\n                                    <option value="218">🇱🇾 +218</option>\n                                    <option value="423">🇱🇮 +423</option>\n                                    <option value="370">🇱🇹 +370</option>\n                                    <option value="352">🇱🇺 +352</option>\n                                    <option value="261">🇲🇬 +261</option>\n                                    <option value="853">🇲🇴 +853</option>\n                                    <option value="389">🇲🇰 +389</option>\n                                    <option value="960">🇲🇻 +960</option>\n                                    <option value="265">🇲🇼 +265</option>\n                                    <option value="60">🇲🇾 +60</option>\n                                    <option value="223">🇲🇱 +223</option>\n                                    <option value="356">🇲🇹 +356</option>\n                                    <option value="212">🇲🇦 +212</option>\n                                    <option value="596">🇲🇶 +596</option>\n                                    <option value="222">🇲🇷 +222</option>\n                                    <option value="230">🇲🇺 +230</option>\n                                    <option value="262">🇾🇹 +262</option>\n                                    <option value="52">🇲🇽 +52</option>\n                                    <option value="20">🇪🇬 +20</option>\n                                    <option value="691">🇫🇲 +691</option>\n                                    <option value="373">🇲🇩 +373</option>\n                                    <option value="377">🇲🇨 +377</option>\n                                    <option value="976">🇲🇳 +976</option>\n                                    <option value="382">🇲🇪 +382</option>\n                                    <option value="1">🇲🇸 +1</option>\n                                    <option value="258">🇲🇿 +258</option>\n                                    <option value="95">🇲🇲 +95</option>\n                                    <option value="264">🇳🇦 +264</option>\n                                    <option value="674">🇳🇷 +674</option>\n                                    <option value="977">🇳🇵 +977</option>\n                                    <option value="227">🇳🇪 +227</option>\n                                    <option value="234">🇳🇬 +234</option>\n                                    <option value="505">🇳🇮 +505</option>\n                                    <option value="683">🇳🇺 +683</option>\n                                    <option value="47">🇳🇴 +47</option>\n                                    <option value="968">🇴🇲 +968</option>\n                                    <option value="92">🇵🇰 +92</option>\n                                    <option value="680">🇵🇼 +680</option>\n                                    <option value="507">🇵🇦 +507</option>\n                                    <option value="675">🇵🇬 +675</option>\n                                    <option value="595">🇵🇾 +595</option>\n                                    <option value="51">🇵🇪 +51</option>\n                                    <option value="48">🇵🇱 +48</option>\n                                    <option value="689">🇵🇫 +689</option>\n                                    <option value="351">🇵🇹 +351</option>\n                                    <option value="33">🇫🇷 +33</option>\n                                    <option value="1">🇵🇷 +1</option>\n                                    <option value="247">🇦🇨 +247</option>\n                                    <option value="44">🇮🇲 +44</option>\n                                    <option value="61">🇨🇽 +61</option>\n                                    <option value="974">🇶🇦 +974</option>\n                                    <option value="236">🇨🇫 +236</option>\n                                    <option value="1">🇩🇴 +1</option>\n                                    <option value="262">🇷🇪 +262</option>\n                                    <option value="40">🇷🇴 +40</option>\n                                    <option value="7">🇷🇺 +7</option>\n                                    <option value="250">🇷🇼 +250</option>\n                                    <option value="212">🇪🇭 +212</option>\n                                    <option value="590">🇧🇱 +590</option>\n                                    <option value="290">🇸🇭 +290</option>\n                                    <option value="1">🇰🇳 +1</option>\n                                    <option value="1">🇱🇨 +1</option>\n                                    <option value="590">🇲🇫 +590</option>\n                                    <option value="508">🇵🇲 +508</option>\n                                    <option value="1">🇻🇨 +1</option>\n                                    <option value="685">🇼🇸 +685</option>\n                                    <option value="1">🇦🇸 +1</option>\n                                    <option value="378">🇸🇲 +378</option>\n                                    <option value="239">🇸🇹 +239</option>\n                                    <option value="64">🇳🇿 +64</option>\n                                    <option value="221">🇸🇳 +221</option>\n                                    <option value="381">🇷🇸 +381</option>\n                                    <option value="248">🇸🇨 +248</option>\n                                    <option value="232">🇸🇱 +232</option>\n                                    <option value="65">🇸🇬 +65</option>\n                                    <option value="1">🇸🇽 +1</option>\n                                    <option value="357">🇨🇾 +357</option>\n                                    <option value="421">🇸🇰 +421</option>\n                                    <option value="386">🇸🇮 +386</option>\n                                    <option value="252">🇸🇴 +252</option>\n                                    <option value="34">🇪🇸 +34</option>\n                                    <option value="94">🇱🇰 +94</option>\n                                    <option value="249">🇸🇩 +249</option>\n                                    <option value="211">🇸🇸 +211</option>\n                                    <option value="963">🇸🇾 +963</option>\n                                    <option value="597">🇸🇷 +597</option>\n                                    <option value="46">🇸🇪 +46</option>\n                                    <option value="41">🇨🇭 +41</option>\n                                    <option value="886">🇹🇼 +886</option>\n                                    <option value="992">🇹🇯 +992</option>\n                                    <option value="238">🇨🇻 +238</option>\n                                    <option value="255">🇹🇿 +255</option>\n                                    <option value="66">🇹🇭 +66</option>\n                                    <option value="670">🇹🇱 +670</option>\n                                    <option value="86">🇨🇳 +86</option>\n                                    <option value="228">🇹🇬 +228</option>\n                                    <option value="690">🇹🇰 +690</option>\n                                    <option value="676">🇹🇴 +676</option>\n                                    <option value="1">🇹🇹 +1</option>\n                                    <option value="290">🇹🇦 +290</option>\n                                    <option value="216">🇹🇳 +216</option>\n                                    <option value="90">🇹🇷 +90</option>\n                                    <option value="993">🇹🇲 +993</option>\n                                    <option value="688">🇹🇻 +688</option>\n                                    <option value="256">🇺🇬 +256</option>\n                                    <option value="380">🇺🇦 +380</option>\n                                    <option value="971">🇦🇪 +971</option>\n                                    <option value="598">🇺🇾 +598</option>\n                                    <option value="998">🇺🇿 +998</option>\n                                    <option value="678">🇻🇺 +678</option>\n                                    <option value="39">🇻🇦 +39</option>\n                                    <option value="58">🇻🇪 +58</option>\n                                    <option value="84">🇻🇳 +84</option>\n                                    <option value="246">🇮🇴 +246</option>\n                                    <option value="970">🇵🇸 +970</option>\n                                    <option value="967">🇾🇪 +967</option>\n                                    <option value="962">🇯🇴 +962</option>\n                                    <option value="30">🇬🇷 +30</option>\n                                    <option value="260">🇿🇲 +260</option>\n                                    <option value="263">🇿🇼 +263</option>\n                                </select>\n                                <input type="tel" id="bablast-lc-input-phone" placeholder="81234567890" autocomplete="tel-national" inputmode="tel" aria-describedby="bablast-lc-phone-hint bablast-lc-err-phone">\n                            </div>\n                            <p class="bablast-lc-form-hint" id="bablast-lc-phone-hint">Pilih kode negara, lalu masukkan nomor tanpa kode negara.</p>\n                            <div class="bablast-lc-form-error" id="bablast-lc-err-phone">Nomor WhatsApp wajib diisi</div>\n                        </div>\n\n                        <div class="bablast-lc-form-group" id="bablast-lc-group-email">\n                            <label class="bablast-lc-form-label">\n                                Alamat Email <span class="req" id="bablast-lc-req-email" style="display:none;">*</span>\n                            </label>\n                            <input type="email" class="bablast-lc-form-input" id="bablast-lc-input-email" placeholder="email@domain.com" autocomplete="email">\n                            <div class="bablast-lc-form-error" id="bablast-lc-err-email">Format email tidak valid</div>\n                        </div>\n\n                        <button type="submit" class="bablast-lc-btn-start" id="bablast-lc-btn-start">\n                            Mulai Percakapan\n                        </button>\n                    </form>\n                </div>\n\n                \x3c!-- Chat Messages View --\x3e\n                <div id="bablast-lc-chat-container">\n                    <div class="bablast-lc-drop-overlay" aria-hidden="true">Lepaskan file di sini untuk melampirkan</div>\n                    <div id="bablast-lc-body"></div>\n\n                    \x3c!-- Session Closed Footer Action --\x3e\n                    <div id="bablast-lc-closed-bar">\n                        <p class="bablast-lc-closed-label">Sesi percakapan ini telah berakhir</p>\n                        <form id="bablast-lc-feedback-form">\n                            <h4 class="bablast-lc-feedback-title">Bagaimana pengalaman chat Anda?</h4>\n                            <p class="bablast-lc-feedback-help" id="bablast-lc-rating-label">Pilih nilai 1–5 bintang</p>\n                            <div class="bablast-lc-rating" role="group" aria-label="Rating pengalaman chat">\n                                ${[1, 2, 3, 4, 5].map((n) => `<button type="button" class="bablast-lc-rating-btn" data-rating="${n}" aria-label="${n} bintang" aria-pressed="false">★</button>`).join("")}\n                            </div>\n                            <textarea id="bablast-lc-feedback-comment" maxlength="1000" placeholder="Ceritakan sedikit pengalaman Anda (opsional)" aria-label="Komentar feedback"></textarea>\n                            <p id="bablast-lc-feedback-error" class="bablast-lc-feedback-error" role="alert"></p>\n                            <button type="submit" id="bablast-lc-feedback-submit" class="bablast-lc-btn-start">Kirim Penilaian</button>\n                        </form>\n                        <div id="bablast-lc-feedback-thanks" class="bablast-lc-feedback-thanks" role="status">Terima kasih atas penilaian Anda! 🙌</div>\n                        <button type="button" id="bablast-lc-btn-new-session" class="bablast-lc-btn-start">\n                            + Mulai Percakapan Baru\n                        </button>\n                    </div>\n\n                    <form id="bablast-lc-footer">\n                        <div id="bablast-lc-attachment-preview" class="bablast-lc-attachment-preview">\n                            <div id="bablast-lc-preview-thumb" class="bablast-lc-preview-thumb">FILE</div>\n                            <div class="bablast-lc-preview-info">\n                                <span id="bablast-lc-preview-name" class="bablast-lc-preview-name"></span>\n                                <span id="bablast-lc-preview-size" class="bablast-lc-preview-size"></span>\n                            </div>\n                            <button type="button" id="bablast-lc-attachment-remove" aria-label="Hapus lampiran">×</button>\n                        </div>\n                        <p id="bablast-lc-upload-error" class="bablast-lc-upload-error" role="alert"></p>\n                        <div class="bablast-lc-input-row">\n                            <input type="file" id="bablast-lc-file-input" accept="image/png,image/jpeg,image/webp,image/gif,application/pdf" hidden>\n                            <button type="button" id="bablast-lc-attach" title="Lampirkan file" aria-label="Lampirkan gambar atau PDF">\n                                <svg viewBox="0 0 24 24"><path d="M16.5 6.5v10.25a4.25 4.25 0 0 1-8.5 0V5.5a3 3 0 0 1 6 0v10.75a1.75 1.75 0 0 1-3.5 0V6.5H12v9.75a.25.25 0 0 0 .5 0V5.5a1.5 1.5 0 0 0-3 0v11.25a2.75 2.75 0 0 0 5.5 0V6.5h1.5z"/></svg>\n                            </button>\n                            <input type="text" id="bablast-lc-input" placeholder="Tulis pesan..." autocomplete="off" aria-label="Pesan">\n                            <button type="submit" id="bablast-lc-send" title="Kirim" aria-label="Kirim pesan">\n                                <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>\n                            </button>\n                        </div>\n                    </form>\n                </div>\n\n                \x3c!-- Conversation History View --\x3e\n                <div id="bablast-lc-history-container" style="display:none;">\n                    <div class="bablast-lc-history-header">\n                        <span class="bablast-lc-history-title">Percakapan Sebelumnya</span>\n                        <button type="button" id="bablast-lc-btn-history-back" class="bablast-lc-history-back-btn">← Ke Chat</button>\n                    </div>\n                    <div id="bablast-lc-history-list" class="bablast-lc-history-list"></div>\n                    <div class="bablast-lc-history-footer">\n                        <button type="button" id="bablast-lc-btn-new-chat" class="bablast-lc-btn-start">\n                            + Mulai Percakapan Baru\n                        </button>\n                    </div>\n                </div>\n\n                \x3c!-- End Chat Confirmation Modal --\x3e\n                <div id="bablast-lc-modal-end-chat" class="bablast-lc-modal-overlay" style="display:none;">\n                    <div class="bablast-lc-modal-card">\n                        <h4 class="bablast-lc-modal-title">Akhiri Percakapan?</h4>\n                        <p class="bablast-lc-modal-desc">Sesi percakapan ini akan diselesaikan. Anda dapat memberikan penilaian dan memulai chat baru setelahnya.</p>\n                        <div class="bablast-lc-modal-actions">\n                            <button type="button" id="bablast-lc-modal-cancel" class="bablast-lc-modal-btn cancel">Batal</button>\n                            <button type="button" id="bablast-lc-modal-confirm" class="bablast-lc-modal-btn confirm">Ya, Akhiri</button>\n                        </div>\n                    </div>\n                </div>\n\n                <div class="bablast-lc-brand">\n                    Powered by <a href="https://bablast.id" target="_blank" rel="noopener noreferrer" style="color:inherit;text-decoration:none;font-weight:600">Bablast.id</a>\n                </div>\n            </div>\n        `),
                    document.body.appendChild(t),
                    document
                        .getElementById("bablast-lc-bubble")
                        .addEventListener("click", jn),
                    document
                        .getElementById("bablast-lc-close")
                        .addEventListener("click", jn),
                    document
                        .getElementById("bablast-lc-btn-history")
                        ?.addEventListener("click", X),
                    document
                        .getElementById("bablast-lc-btn-history-back")
                        ?.addEventListener("click", () => {
                            ((Y = !1), nn());
                        }),
                    document
                        .getElementById("bablast-lc-btn-new-chat")
                        ?.addEventListener("click", Z));
                const e = document.getElementById("bablast-lc-btn-menu"),
                    o = document.getElementById("bablast-lc-header-dropdown"),
                    i = document.getElementById("bablast-lc-opt-end-chat"),
                    s = document.getElementById("bablast-lc-modal-end-chat"),
                    r = document.getElementById("bablast-lc-modal-cancel"),
                    c = document.getElementById("bablast-lc-modal-confirm");
                (e?.addEventListener("click", (n) => {
                    (n.stopPropagation(),
                        o &&
                            (o.style.display =
                                "none" === o.style.display ? "block" : "none"));
                }),
                    document.addEventListener("click", () => {
                        o && (o.style.display = "none");
                    }),
                    i?.addEventListener("click", (n) => {
                        (n.stopPropagation(),
                            o && (o.style.display = "none"),
                            s && (s.style.display = "flex"));
                    }),
                    r?.addEventListener("click", () => {
                        s && (s.style.display = "none");
                    }),
                    c?.addEventListener("click", () => {
                        (async () => {
                            const n = document.getElementById(
                                "bablast-lc-modal-end-chat",
                            );
                            n && (n.style.display = "none");
                            try {
                                await fetch(`${l}/close/${a}/${f}`, {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                    },
                                    body: JSON.stringify({
                                        closed_by: "visitor",
                                    }),
                                });
                            } catch (n) {
                                console.warn(
                                    "[Bablast LiveChat] Close session error:",
                                    n,
                                );
                            }
                            (V(!0), h(f, void 0, "resolved"));
                            const t =
                                document.getElementById("bablast-lc-body");
                            if (t) {
                                const n = document.createElement("div");
                                ((n.className = "bablast-lc-divider"),
                                    (n.style.marginTop = "12px"),
                                    (n.innerHTML =
                                        "<span>Anda telah mengakhiri sesi percakapan ini</span>"),
                                    t.appendChild(n),
                                    (t.scrollTop = t.scrollHeight));
                            }
                        })();
                    }),
                    document
                        .getElementById("bablast-lc-proactive-reply")
                        .addEventListener("click", () => {
                            (O(), _ || jn());
                        }),
                    document
                        .getElementById("bablast-lc-proactive-dismiss")
                        .addEventListener("click", O),
                    document
                        .getElementById("bablast-lc-footer")
                        .addEventListener("submit", Mn),
                    document
                        .getElementById("bablast-lc-prechat-form")
                        .addEventListener("submit", tn),
                    document
                        .getElementById("bablast-lc-feedback-form")
                        .addEventListener("submit", K),
                    document
                        .querySelectorAll(".bablast-lc-rating-btn")
                        .forEach((n) => {
                            n.addEventListener("click", () =>
                                U(Number(n.dataset.rating)),
                            );
                        }),
                    document
                        .getElementById("bablast-lc-btn-new-session")
                        ?.addEventListener("click", Z),
                    document
                        .getElementById("bablast-lc-attach")
                        .addEventListener("click", () =>
                            document
                                .getElementById("bablast-lc-file-input")
                                .click(),
                        ),
                    document
                        .getElementById("bablast-lc-file-input")
                        .addEventListener("change", (n) =>
                            gn(n.target.files && n.target.files[0]),
                        ),
                    document
                        .getElementById("bablast-lc-attachment-remove")
                        .addEventListener("click", mn),
                    (() => {
                        const n = document.getElementById("bablast-lc-window");
                        if (!n) return;
                        let t = 0;
                        (n.addEventListener("dragenter", (e) => {
                            e.dataTransfer &&
                                Array.from(e.dataTransfer.types).includes(
                                    "Files",
                                ) &&
                                (e.preventDefault(),
                                (t += 1),
                                n.classList.add("dragging"));
                        }),
                            n.addEventListener("dragover", (n) =>
                                n.preventDefault(),
                            ),
                            n.addEventListener("dragleave", (e) => {
                                (e.preventDefault(),
                                    (t = Math.max(0, t - 1)),
                                    t || n.classList.remove("dragging"));
                            }),
                            n.addEventListener("drop", (e) => {
                                (e.preventDefault(),
                                    (t = 0),
                                    n.classList.remove("dragging"),
                                    gn(
                                        e.dataTransfer &&
                                            e.dataTransfer.files &&
                                            e.dataTransfer.files[0],
                                    ));
                            }));
                    })(),
                    (() => {
                        const n = document.getElementById("bablast-lc-input"),
                            t = document.getElementById("bablast-lc-window"),
                            e = (n) => {
                                if (!_ || B) return;
                                const t =
                                    n.clipboardData || window.clipboardData;
                                if (!t) return;
                                const e = t.items;
                                if (e)
                                    for (let t = 0; t < e.length; t++) {
                                        const a = e[t];
                                        if (
                                            "file" === a.kind &&
                                            (a.type.startsWith("image/") ||
                                                D.includes(a.type))
                                        ) {
                                            const t = a.getAsFile();
                                            if (t) {
                                                n.preventDefault();
                                                let e = t.name;
                                                if (
                                                    !e ||
                                                    "image.png" === e ||
                                                    "blob" === e
                                                ) {
                                                    const n =
                                                        t.type.split("/")[1] ||
                                                        "png";
                                                    e = `screenshot_${new Date().toISOString().replace(/[:.]/g, "-").slice(0, 19)}.${n}`;
                                                }
                                                const a = new File([t], e, {
                                                    type: t.type,
                                                });
                                                return void gn(a);
                                            }
                                        }
                                    }
                            };
                        (n && n.addEventListener("paste", e),
                            t && t.addEventListener("paste", e));
                    })());
            })(),
            Nn("active"),
            document.addEventListener("visibilitychange", () => {
                "visible" === document.visibilityState
                    ? Nn("active")
                    : Nn("away");
            }),
            window.addEventListener("focus", () => Nn("active")),
            window.addEventListener("blur", () => {
                "visible" !== document.visibilityState && Nn("away");
            }),
            window.addEventListener("pagehide", () => Nn("offline")),
            window.addEventListener("beforeunload", () => Nn("offline")),
            Pn && clearInterval(Pn),
            (Pn = setInterval(() => {
                "visible" === document.visibilityState
                    ? Nn("active")
                    : Nn("away");
            }, 45e3)),
            Tn(),
            await (async () => {
                try {
                    const n = await fetch(`${l}/config/${a}`);
                    if (n.ok) {
                        const t = await n.json();
                        t.data &&
                            ((F = { ...F, ...t.data }),
                            (document.getElementById(
                                "bablast-lc-title",
                            ).textContent = F.header_title || F.widget_name),
                            cn(),
                            F.avatar_url
                                ? (document.getElementById(
                                      "bablast-lc-avatar-box",
                                  ).innerHTML =
                                      `<img src="${F.avatar_url}" alt="Avatar">`)
                                : F.header_title &&
                                  (document.getElementById(
                                      "bablast-lc-avatar-text",
                                  ).textContent = F.header_title
                                      .charAt(0)
                                      .toUpperCase()),
                            N(F.primary_color, F.widget_position),
                            nn(),
                            (() => {
                                if (!F.is_proactive_enabled || _) return;
                                A && clearTimeout(A);
                                const n =
                                    1e3 *
                                    Math.max(
                                        3,
                                        Number(F.proactive_delay_seconds) || 15,
                                    );
                                A = setTimeout(() => {
                                    _ ||
                                        0 !== dn().length ||
                                        H(
                                            F.header_title,
                                            F.proactive_message,
                                            F.avatar_url,
                                        );
                                }, n);
                            })());
                    }
                } catch (n) {
                    console.error("[Bablast LiveChat] Config fetch error:", n);
                }
            })(),
            (Hn = !0),
            e.forEach((n) => {
                const t = n?.method
                    ? [n.method, ...Array.from(n.args || [])]
                    : Array.from(n?.args || n || []);
                t.length && Rn(t[0], ...t.slice(1));
            }),
            (e.length = 0),
            Array.isArray(window.BablastLiveChatQueue) &&
                (window.BablastLiveChatQueue.length = 0),
            nn(),
            await Sn());
    };
    "loading" === document.readyState
        ? document.addEventListener("DOMContentLoaded", Kn)
        : Kn();
})();
