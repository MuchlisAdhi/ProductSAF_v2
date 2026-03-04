<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1b5e20">
    <title>Splash - Sidoagung Farm</title>
    <style>
        @font-face {
            font-family: 'Poppins Offline';
            src: url('{{ asset('fonts/pwa/Poppins-Light.ttf') }}') format('truetype');
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Poppins Offline';
            src: url('{{ asset('fonts/pwa/Poppins-Regular.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Poppins Offline';
            src: url('{{ asset('fonts/pwa/Poppins-Bold.ttf') }}') format('truetype');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Oswald Offline';
            src: url('{{ asset('fonts/pwa/Oswald-Var.ttf') }}') format('truetype');
            font-weight: 200 700;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --brand-1: #1b5e20;
            --brand-2: #2e7d32;
            --brand-3: #145214;
            --brand-4: #0d3b0d;
            --white: #ffffff;
            --duration: 3.2s;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Poppins Offline', 'Poppins', 'Segoe UI', Tahoma, Arial, sans-serif;
            background: linear-gradient(160deg, var(--brand-1) 0%, var(--brand-2) 40%, var(--brand-3) 70%, var(--brand-4) 100%);
            color: var(--white);
        }

        .splash {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .grid {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .glow {
            position: absolute;
            top: 50%;
            left: 50%;
            width: min(68vw, 400px);
            aspect-ratio: 1 / 1;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(76, 175, 80, 0.18) 0%, transparent 70%);
            pointer-events: none;
        }

        .leaf {
            position: absolute;
            width: 120px;
            opacity: 0.08;
            animation: leafFade 1.5s ease-out both;
        }

        .leaf svg {
            width: 100%;
            height: auto;
            display: block;
        }

        .leaf path,
        .leaf line {
            stroke: #fff;
            fill: #fff;
        }

        .leaf.lt { top: -30px; left: -20px; transform: rotate(-25deg); }
        .leaf.rt { top: -20px; right: -10px; width: 100px; transform: rotate(30deg); animation-delay: 0.15s; }
        .leaf.lb { bottom: -40px; left: -30px; width: 140px; transform: rotate(20deg) scaleX(-1); animation-delay: 0.3s; }
        .leaf.rb { bottom: -30px; right: -20px; width: 120px; transform: rotate(-15deg); animation-delay: 0.2s; }

        .float-circle {
            position: absolute;
            border-radius: 50%;
            border: 1.5px solid rgba(255, 255, 255, 0.15);
        }

        .float-circle.top {
            top: 12%;
            right: 8%;
            width: 60px;
            height: 60px;
            animation: floatY 5s ease-in-out infinite;
        }

        .float-circle.bottom {
            bottom: 18%;
            left: 6%;
            width: 40px;
            height: 40px;
            border-color: rgba(255, 255, 255, 0.12);
            animation: floatYRev 6s ease-in-out infinite;
            animation-delay: 1s;
        }

        .content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .logo-wrap {
            margin-bottom: 28px;
            padding: 18px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1.5px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 0 0 8px rgba(255, 255, 255, 0.04), 0 20px 60px rgba(0, 0, 0, 0.35);
            animation: logoPop .85s cubic-bezier(.34, 1.56, .64, 1) both;
        }

        .logo-wrap img {
            width: clamp(88px, 22vw, 110px);
            height: clamp(88px, 22vw, 110px);
            display: block;
            object-fit: contain;
        }

        .katalog-label {
            margin: 0 0 8px;
            font-size: .95rem;
            font-weight: 300;
            letter-spacing: .32em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.65);
            animation: fadeUp .6s ease-out both;
            animation-delay: .55s;
        }

        h1 {
            margin: 0;
            font-family: 'Oswald Offline', 'Oswald', 'Arial Narrow Bold', Arial, sans-serif;
            font-weight: 700;
            font-size: clamp(1.9rem, 6vw, 2.8rem);
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #fff;
            line-height: 1.15;
            text-shadow: 0 2px 24px rgba(0, 0, 0, 0.4);
            animation: fadeUp .65s ease-out both;
            animation-delay: .75s;
        }

        .divider {
            margin-top: 20px;
            width: 180px;
            height: 1.5px;
            border-radius: 2px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            transform-origin: center;
            animation: lineGrow .7s ease-out both;
            animation-delay: 1s;
        }

        .tagline {
            margin: 14px 0 0;
            font-size: .78rem;
            font-weight: 400;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.45);
            animation: fadeUp .6s ease-out both;
            animation-delay: 1.2s;
        }

        .loader {
            position: absolute;
            bottom: 52px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            animation: fadeIn .5s ease-out both;
            animation-delay: 1.5s;
        }

        .loader-dots {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .loader-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.7);
            animation: dotPulse 1.2s ease-in-out infinite;
        }

        .loader-dots span:nth-child(2) { animation-delay: .22s; }
        .loader-dots span:nth-child(3) { animation-delay: .44s; }
        .loader-dots span:nth-child(4) { animation-delay: .66s; }

        .loader p {
            margin: 2px 0 0;
            font-size: .7rem;
            font-weight: 300;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.35);
        }

        .bottom-bar {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 3px;
            background: linear-gradient(90deg, #4caf50, #81c784, #a5d6a7, #81c784, #4caf50);
            transform-origin: left center;
            animation: progressBar 3s ease-in-out both;
            animation-delay: .3s;
        }

        .splash.fade-out {
            animation: fadeOut .7s ease-in-out forwards;
        }

        @keyframes logoPop {
            from { opacity: 0; transform: translateY(20px) scale(.55); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes lineGrow {
            from { opacity: 0; transform: scaleX(0); }
            to { opacity: 1; transform: scaleX(1); }
        }

        @keyframes dotPulse {
            0%, 100% { opacity: .3; transform: scale(.8); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        @keyframes progressBar {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        @keyframes floatY {
            0%, 100% { transform: translateY(-8px); }
            50% { transform: translateY(8px); }
        }

        @keyframes floatYRev {
            0%, 100% { transform: translateY(8px); }
            50% { transform: translateY(-8px); }
        }

        @keyframes leafFade {
            from { opacity: 0; transform: translate(-20px, -20px); }
            to { opacity: .08; transform: translate(0, 0); }
        }

        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(1.04); }
        }
    </style>
</head>
<body>
<div id="saf-splash" class="splash" role="status" aria-live="polite" aria-label="Memuat aplikasi Sidoagung Farm">
    <div class="grid"></div>
    <div class="glow"></div>

    <div class="leaf lt">@include('pwa.partials.leaf')</div>
    <div class="leaf rt">@include('pwa.partials.leaf')</div>
    <div class="leaf lb">@include('pwa.partials.leaf')</div>
    <div class="leaf rb">@include('pwa.partials.leaf')</div>

    <div class="float-circle top"></div>
    <div class="float-circle bottom"></div>

    <div class="content">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo/saf-logo.png') }}" alt="SAF Logo">
        </div>
        <p class="katalog-label">Produk Katalog</p>
        <h1>PT. SidoAgung Farm</h1>
        <div class="divider"></div>
        <p class="tagline">Kualitas Terbaik, Pilihan Utama</p>
    </div>

    <div class="loader">
        <div class="loader-dots"><span></span><span></span><span></span><span></span></div>
        <p>Memuat...</p>
    </div>

    <div class="bottom-bar"></div>
</div>

<script>
    (() => {
        const splash = document.getElementById('saf-splash');
        const fallbackTarget = @json(route('home', absolute: false));
        const target = @json(request()->query('next', route('home', absolute: false)));

        const resolveTarget = (value) => {
            if (typeof value !== 'string') return fallbackTarget;
            if (!value.startsWith('/') || value.startsWith('//')) return fallbackTarget;
            return value;
        };

        const goTo = resolveTarget(target);

        window.setTimeout(() => {
            splash?.classList.add('fade-out');
        }, 3200);

        window.setTimeout(() => {
            window.location.replace(goTo);
        }, 3900);
    })();
</script>
</body>
</html>
