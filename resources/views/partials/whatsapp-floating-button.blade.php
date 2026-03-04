@php
    $waNumber = '628156710231';
    $waMessage = rawurlencode('Halo, saya ingin mengetahui lebih lanjut tentang produk PT. Sidoagung Farm.');
    $waUrl = "https://wa.me/{$waNumber}?text={$waMessage}";
@endphp

<div id="saf-wa-widget" class="saf-wa-widget" data-wa-url="{{ $waUrl }}">
    <div class="saf-wa-tooltip" aria-hidden="true">
        <span>Ada pertanyaan? <strong>Chat kami!</strong></span>
    </div>

    <div class="saf-wa-card" hidden>
        <div class="saf-wa-card-header">
            <div class="saf-wa-agent">
                <span class="saf-wa-avatar">
                    <img src="{{ asset('images/logo/saf-logo.png') }}" alt="SAF">
                </span>
                <span class="saf-wa-agent-meta">
                    <strong>PT. Sidoagung Farm</strong>
                    <small>Tim Marketing</small>
                </span>
            </div>
            <button type="button" class="saf-wa-close" aria-label="Tutup chat WhatsApp">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="saf-wa-card-body">
            <div class="saf-wa-bubble-row">
                <span class="saf-wa-bubble-avatar">
                    <img src="{{ asset('images/logo/saf-logo.png') }}" alt="SAF">
                </span>
                <div class="saf-wa-bubble">
                    <p>
                        Halo! Selamat Datang di <br> <strong>PT. Sidoagung Farm</strong>.<br>
                        Ada yang bisa kami bantu?
                    </p>
                    <p class="saf-wa-time">
                        <span class="saf-wa-time-value">--:--</span>
                        <span class="saf-wa-check">&#10003;&#10003;</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="saf-wa-card-footer">
            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="saf-wa-chat-cta">
                <svg viewBox="0 0 32 32" aria-hidden="true">
                    <path d="M16 1C7.716 1 1 7.716 1 16c0 2.628.672 5.1 1.848 7.256L1 31l7.98-1.82A14.94 14.94 0 0 0 16 31c8.284 0 15-6.716 15-15S24.284 1 16 1Z" fill="white"></path>
                    <path d="M16 3.2C8.928 3.2 3.2 8.928 3.2 16c0 2.42.648 4.688 1.776 6.64l.26.44-1.104 4.032 4.148-1.084.424.248A12.736 12.736 0 0 0 16 28.8c7.072 0 12.8-5.728 12.8-12.8S23.072 3.2 16 3.2Z" fill="#25D366"></path>
                    <path d="M11.832 9.6c-.328-.736-.672-.752-1-.764C10.568 8.828 10.3 8.8 10 8.8c-.3 0-.8.112-1.22.548-.42.436-1.58 1.544-1.58 3.764s1.616 4.368 1.84 4.668c.224.3 3.148 4.98 7.74 6.776 3.828 1.512 4.604 1.212 5.436 1.136.832-.076 2.688-1.1 3.068-2.16.38-1.06.38-1.968.268-2.16-.112-.192-.412-.3-.86-.524-.448-.224-2.664-1.312-3.076-1.46-.412-.148-.712-.224-1.012.224-.3.448-1.16 1.46-1.42 1.76-.26.3-.52.336-.968.112-.448-.224-1.892-.7-3.604-2.228-1.332-1.188-2.232-2.656-2.492-3.104-.26-.448-.028-.692.196-.916.2-.2.448-.52.672-.78.224-.26.3-.448.448-.748.148-.3.076-.564-.036-.788-.112-.224-1.012-2.46-1.408-3.36Z" fill="white"></path>
                </svg>
                Mulai Chat di WhatsApp
            </a>
            <p class="saf-wa-reply-note">Biasanya membalas dalam beberapa menit</p>
        </div>
    </div>

    <button type="button" class="saf-wa-toggle" aria-label="Hubungi kami via WhatsApp" aria-expanded="false">
        <span class="saf-wa-pulse saf-wa-pulse-a"></span>
        <span class="saf-wa-pulse saf-wa-pulse-b"></span>
        <span class="saf-wa-icon saf-wa-open-icon">
            <svg viewBox="0 0 32 32" aria-hidden="true">
                <path d="M16 1C7.716 1 1 7.716 1 16c0 2.628.672 5.1 1.848 7.256L1 31l7.98-1.82A14.94 14.94 0 0 0 16 31c8.284 0 15-6.716 15-15S24.284 1 16 1Z" fill="white"></path>
                <path d="M16 3.2C8.928 3.2 3.2 8.928 3.2 16c0 2.42.648 4.688 1.776 6.64l.26.44-1.104 4.032 4.148-1.084.424.248A12.736 12.736 0 0 0 16 28.8c7.072 0 12.8-5.728 12.8-12.8S23.072 3.2 16 3.2Z" fill="#25D366"></path>
                <path d="M11.832 9.6c-.328-.736-.672-.752-1-.764C10.568 8.828 10.3 8.8 10 8.8c-.3 0-.8.112-1.22.548-.42.436-1.58 1.544-1.58 3.764s1.616 4.368 1.84 4.668c.224.3 3.148 4.98 7.74 6.776 3.828 1.512 4.604 1.212 5.436 1.136.832-.076 2.688-1.1 3.068-2.16.38-1.06.38-1.968.268-2.16-.112-.192-.412-.3-.86-.524-.448-.224-2.664-1.312-3.076-1.46-.412-.148-.712-.224-1.012.224-.3.448-1.16 1.46-1.42 1.76-.26.3-.52.336-.968.112-.448-.224-1.892-.7-3.604-2.228-1.332-1.188-2.232-2.656-2.492-3.104-.26-.448-.028-.692.196-.916.2-.2.448-.52.672-.78.224-.26.3-.448.448-.748.148-.3.076-.564-.036-.788-.112-.224-1.012-2.46-1.408-3.36Z" fill="white"></path>
            </svg>
        </span>
        <span class="saf-wa-icon saf-wa-close-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </span>
    </button>
</div>

@once
    @push('head')
        <style>
            .saf-wa-widget {
                --saf-wa-left: max(1rem, calc(env(safe-area-inset-left, 0px) + 0.85rem));
                --saf-wa-right: max(1rem, calc(env(safe-area-inset-right, 0px) + 0.85rem));
                --saf-wa-bottom: max(1rem, calc(env(safe-area-inset-bottom, 0px) + 0.9rem));
                position: fixed;
                right: var(--saf-wa-right);
                bottom: var(--saf-wa-bottom);
                z-index: 70;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 0.7rem;
            }

            .saf-wa-card {
                width: min(320px, calc(100vw - var(--saf-wa-left) - var(--saf-wa-right)));
                max-height: min(520px, calc(100dvh - var(--saf-wa-bottom) - 5.5rem));
                border-radius: 1rem;
                overflow: hidden;
                box-shadow: 0 22px 48px rgba(15, 23, 42, 0.28);
                transform-origin: bottom right;
                transform: translateY(12px) scale(0.9);
                opacity: 0;
                pointer-events: none;
                transition: transform 220ms ease, opacity 220ms ease;
            }

            .saf-wa-widget.is-open .saf-wa-card {
                transform: translateY(0) scale(1);
                opacity: 1;
                pointer-events: auto;
            }

            .saf-wa-card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.78rem 0.88rem;
                background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            }

            .saf-wa-agent {
                display: flex;
                align-items: center;
                gap: 0.62rem;
            }

            .saf-wa-avatar {
                position: relative;
                width: 44px;
                height: 44px;
                border-radius: 999px;
                border: 2px solid #fff;
                background: #1b5e20;
                display: grid;
                place-items: center;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.22);
            }

            .saf-wa-avatar::after {
                content: "";
                position: absolute;
                right: -1px;
                bottom: -1px;
                width: 11px;
                height: 11px;
                border-radius: 50%;
                border: 2px solid #fff;
                background: #25d366;
            }

            .saf-wa-avatar img {
                width: 30px;
                height: 30px;
                object-fit: contain;
            }

            .saf-wa-agent-meta {
                display: flex;
                flex-direction: column;
                line-height: 1.25;
                color: #fff;
                font-family: "Poppins", "Segoe UI", Tahoma, Arial, sans-serif;
            }

            .saf-wa-agent-meta strong {
                font-size: 0.86rem;
                font-weight: 600;
            }

            .saf-wa-agent-meta small {
                margin-top: 2px;
                font-size: 0.72rem;
                color: #d1fae5;
            }

            .saf-wa-close {
                border: 0;
                background: transparent;
                color: rgba(255, 255, 255, 0.75);
                width: 30px;
                height: 30px;
                border-radius: 999px;
                display: grid;
                place-items: center;
                cursor: pointer;
                transition: color 160ms ease, background-color 160ms ease;
            }

            .saf-wa-close svg {
                width: 14px;
                height: 14px;
            }

            .saf-wa-close:hover {
                color: #fff;
                background: rgba(255, 255, 255, 0.12);
            }

            .saf-wa-card-body {
                padding: 0.95rem 0.88rem;
                background: #e5ddd5;
                background-image: radial-gradient(rgba(148, 163, 184, 0.24) 0.9px, transparent 0.9px);
                background-size: 13px 13px;
                overflow-y: auto;
            }

            .saf-wa-bubble-row {
                display: flex;
                align-items: flex-end;
                gap: 0.44rem;
            }

            .saf-wa-bubble-avatar {
                width: 28px;
                height: 28px;
                flex-shrink: 0;
                border-radius: 999px;
                background: #1b5e20;
                display: grid;
                place-items: center;
                overflow: hidden;
                margin-bottom: 2px;
            }

            .saf-wa-bubble-avatar img {
                width: 18px;
                height: 18px;
                object-fit: contain;
            }

            .saf-wa-bubble {
                background: #fff;
                border-radius: 1rem 1rem 1rem 0.3rem;
                padding: 0.56rem 0.68rem;
                max-width: 232px;
                position: relative;
                box-shadow: 0 2px 5px rgba(15, 23, 42, 0.08);
                font-family: "Poppins", "Segoe UI", Tahoma, Arial, sans-serif;
            }

            .saf-wa-bubble::before {
                content: "";
                position: absolute;
                left: -6px;
                bottom: 0;
                width: 0;
                height: 0;
                border-top: 9px solid transparent;
                border-right: 6px solid #fff;
                border-bottom: 0 solid transparent;
            }

            .saf-wa-bubble p {
                margin: 0;
                color: #1f2937;
                font-size: 0.82rem;
                line-height: 1.5;
            }

            .saf-wa-bubble strong {
                color: #1b5e20;
            }

            .saf-wa-time {
                margin-top: 0.24rem !important;
                text-align: right;
                font-size: 0.64rem !important;
                color: #94a3b8 !important;
            }

            .saf-wa-check {
                margin-left: 0.2rem;
                color: #25d366;
                font-size: 0.64rem;
            }

            .saf-wa-card-footer {
                background: #fff;
                padding: 0.75rem 0.88rem 0.82rem;
                border-top: 1px solid #f1f5f9;
            }

            .saf-wa-chat-cta {
                width: 100%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.55rem;
                border: 0;
                border-radius: 0.75rem;
                text-decoration: none;
                padding: 0.68rem 0.85rem;
                font-family: "Poppins", "Segoe UI", Tahoma, Arial, sans-serif;
                font-size: 0.84rem;
                font-weight: 600;
                color: #fff;
                background: linear-gradient(135deg, #25d366 0%, #1ebe5d 100%);
                box-shadow: 0 8px 20px rgba(37, 211, 102, 0.2);
                transition: transform 160ms ease, box-shadow 160ms ease;
            }

            .saf-wa-chat-cta svg {
                width: 21px;
                height: 21px;
                flex-shrink: 0;
            }

            .saf-wa-chat-cta:hover {
                transform: translateY(-1px);
                box-shadow: 0 10px 22px rgba(37, 211, 102, 0.3);
            }

            .saf-wa-reply-note {
                margin: 0.42rem 0 0;
                text-align: center;
                font-size: 0.66rem;
                color: #9ca3af;
                font-family: "Poppins", "Segoe UI", Tahoma, Arial, sans-serif;
            }

            .saf-wa-toggle {
                width: 58px;
                height: 58px;
                border-radius: 999px;
                border: 0;
                position: relative;
                display: grid;
                place-items: center;
                background: linear-gradient(135deg, #25d366 0%, #1a9e4e 100%);
                box-shadow: 0 6px 24px rgba(37, 211, 102, 0.45), 0 2px 8px rgba(0, 0, 0, 0.15);
                color: #fff;
                cursor: pointer;
            }

            .saf-wa-toggle:active {
                transform: scale(0.94);
            }

            .saf-wa-icon {
                position: relative;
                z-index: 2;
                display: inline-grid;
                place-items: center;
                transition: transform 200ms ease, opacity 200ms ease;
            }

            .saf-wa-open-icon svg {
                width: 31px;
                height: 31px;
            }

            .saf-wa-close-icon {
                position: absolute;
                opacity: 0;
                transform: scale(0.85) rotate(-20deg);
            }

            .saf-wa-close-icon svg {
                width: 18px;
                height: 18px;
            }

            .saf-wa-widget.is-open .saf-wa-open-icon {
                opacity: 0;
                transform: scale(0.8) rotate(14deg);
            }

            .saf-wa-widget.is-open .saf-wa-close-icon {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }

            .saf-wa-pulse {
                position: absolute;
                inset: 0;
                border-radius: 999px;
                z-index: 1;
                pointer-events: none;
            }

            .saf-wa-pulse-a {
                background: rgba(37, 211, 102, 0.35);
                animation: saf-wa-pulse 2s infinite;
            }

            .saf-wa-pulse-b {
                background: rgba(37, 211, 102, 0.2);
                animation: saf-wa-pulse 2s infinite 0.4s;
            }

            .saf-wa-widget.is-open .saf-wa-pulse {
                display: none;
            }

            .saf-wa-tooltip {
                position: absolute;
                right: 70px;
                bottom: 12px;
                pointer-events: none;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 999px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);
                padding: 0.42rem 0.72rem;
                white-space: nowrap;
                opacity: 0;
                transform: translateX(10px);
                animation: saf-wa-tooltip-in 340ms ease 1.35s forwards;
                font-family: "Poppins", "Segoe UI", Tahoma, Arial, sans-serif;
            }

            .saf-wa-tooltip span {
                font-size: 0.74rem;
                color: #374151;
            }

            .saf-wa-tooltip strong {
                color: #25d366;
            }

            .saf-wa-tooltip::after {
                content: "";
                position: absolute;
                right: -7px;
                top: 50%;
                transform: translateY(-50%);
                width: 0;
                height: 0;
                border-top: 7px solid transparent;
                border-bottom: 7px solid transparent;
                border-left: 7px solid #fff;
            }

            .saf-wa-widget.is-open .saf-wa-tooltip {
                display: none;
            }

            @media (max-width: 640px) {
                .saf-wa-widget {
                    --saf-wa-left: max(0.82rem, calc(env(safe-area-inset-left, 0px) + 0.65rem));
                    --saf-wa-right: max(0.82rem, calc(env(safe-area-inset-right, 0px) + 0.65rem));
                    --saf-wa-bottom: max(0.82rem, calc(env(safe-area-inset-bottom, 0px) + 0.65rem));
                }

                .saf-wa-tooltip {
                    display: none;
                }

                .saf-wa-card {
                    width: min(340px, calc(100vw - var(--saf-wa-left) - var(--saf-wa-right)));
                    max-height: min(500px, calc(100dvh - var(--saf-wa-bottom) - 5.2rem));
                    border-radius: 0.95rem;
                }

                .saf-wa-card-header {
                    padding: 0.72rem 0.8rem;
                }

                .saf-wa-card-body {
                    padding: 0.82rem 0.78rem;
                }

                .saf-wa-card-footer {
                    padding: 0.7rem 0.78rem 0.76rem;
                }

                .saf-wa-avatar {
                    width: 40px;
                    height: 40px;
                }

                .saf-wa-avatar img {
                    width: 27px;
                    height: 27px;
                }

                .saf-wa-agent-meta strong {
                    font-size: 0.8rem;
                }

                .saf-wa-agent-meta small {
                    font-size: 0.68rem;
                }

                .saf-wa-toggle {
                    width: 54px;
                    height: 54px;
                }

                .saf-wa-open-icon svg {
                    width: 28px;
                    height: 28px;
                }
            }

            @media (max-width: 390px) {
                .saf-wa-widget {
                    --saf-wa-left: max(0.7rem, calc(env(safe-area-inset-left, 0px) + 0.52rem));
                    --saf-wa-right: max(0.7rem, calc(env(safe-area-inset-right, 0px) + 0.52rem));
                    --saf-wa-bottom: max(0.7rem, calc(env(safe-area-inset-bottom, 0px) + 0.52rem));
                }

                .saf-wa-card {
                    position: fixed;
                    left: var(--saf-wa-left);
                    right: var(--saf-wa-right);
                    bottom: calc(var(--saf-wa-bottom) + 4rem);
                    width: auto;
                    max-height: min(460px, calc(100dvh - var(--saf-wa-bottom) - 4.8rem));
                    transform-origin: bottom center;
                }

                .saf-wa-bubble {
                    max-width: 206px;
                }

                .saf-wa-chat-cta {
                    padding: 0.64rem 0.72rem;
                    font-size: 0.8rem;
                }
            }

            @media (max-height: 560px) and (orientation: landscape) {
                .saf-wa-card {
                    max-height: calc(100dvh - var(--saf-wa-bottom) - 4.7rem);
                }
            }

            @keyframes saf-wa-pulse {
                0% {
                    transform: scale(1);
                    opacity: 0.6;
                }
                100% {
                    transform: scale(2.05);
                    opacity: 0;
                }
            }

            @keyframes saf-wa-tooltip-in {
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                const widget = document.getElementById('saf-wa-widget');
                if (!widget || widget.dataset.bound === '1') return;
                widget.dataset.bound = '1';

                const card = widget.querySelector('.saf-wa-card');
                const toggle = widget.querySelector('.saf-wa-toggle');
                const closeButton = widget.querySelector('.saf-wa-close');
                const timeValue = widget.querySelector('.saf-wa-time-value');

                if (!card || !toggle || !closeButton) return;

                if (timeValue) {
                    try {
                        timeValue.textContent = new Intl.DateTimeFormat('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit',
                        }).format(new Date());
                    } catch (error) {
                        const now = new Date();
                        const hh = String(now.getHours()).padStart(2, '0');
                        const mm = String(now.getMinutes()).padStart(2, '0');
                        timeValue.textContent = `${hh}:${mm}`;
                    }
                }

                const setOpen = (open) => {
                    widget.classList.toggle('is-open', open);
                    card.hidden = !open;
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                };

                toggle.addEventListener('click', (event) => {
                    event.preventDefault();
                    setOpen(!widget.classList.contains('is-open'));
                });

                closeButton.addEventListener('click', () => setOpen(false));

                document.addEventListener('click', (event) => {
                    if (!widget.classList.contains('is-open')) return;
                    if (widget.contains(event.target)) return;
                    setOpen(false);
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') setOpen(false);
                });
            })();
        </script>
    @endpush
@endonce

