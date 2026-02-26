<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Maintenance | PT. Sidoagung Farm</title>
    @php
        $backgroundImage = file_exists(public_path('images/404.png'))
            ? asset('images/404.png')
            : asset('images/bg-office.jpeg');
    @endphp
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #f8fafc;
            background: #0b1220;
        }

        .maintenance-shell {
            position: relative;
            min-height: 100vh;
            display: grid;
            place-items: end end;
            padding: 2rem;
            background: #0b1220;
            overflow: hidden;
        }

        .maintenance-shell::before,
        .maintenance-shell::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .maintenance-shell::before {
            background:
                linear-gradient(to bottom, rgba(10, 19, 38, 0.68), rgba(10, 19, 38, 0.56)),
                url('{{ $backgroundImage }}') center center / cover no-repeat;
            filter: blur(6px) saturate(0.95);
            transform: scale(1.05);
        }

        .maintenance-shell::after {
            background: url('{{ $backgroundImage }}') center center / contain no-repeat;
            opacity: 0.96;
        }

        .maintenance-card {
            position: relative;
            z-index: 2;
            width: min(560px, 42vw);
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.52), rgba(15, 23, 42, 0.28));
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 1.25rem;
            box-shadow: 0 28px 50px rgba(15, 23, 42, 0.26);
            backdrop-filter: blur(4px);
            padding: 1.25rem 1.25rem 1.3rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: .9rem;
        }

        .brand img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .brand strong {
            font-family: Impact, Haettenschweiler, "Arial Narrow Bold", sans-serif;
            font-size: 1.95rem;
            line-height: 1;
            letter-spacing: 0.02em;
            color: #ffffff;
            text-shadow: 0 2px 14px rgba(0, 0, 0, 0.35);
        }

        h1 {
            margin: 0 0 .5rem;
            font-size: clamp(1.55rem, 2.45vw, 2.3rem);
            line-height: 1.18;
            color: #ffffff;
            text-shadow: 0 2px 16px rgba(0, 0, 0, 0.34);
        }

        p {
            margin: 0;
            font-size: clamp(0.98rem, 1.45vw, 1.1rem);
            line-height: 1.5;
            color: rgba(248, 250, 252, 0.95);
            max-width: 48ch;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.28);
        }

        @media (max-width: 991.98px) {
            .maintenance-shell {
                place-items: end center;
                padding: 1rem;
            }

            .maintenance-card {
                width: min(620px, 100%);
            }
        }

        @media (max-width: 640px) {
            .maintenance-shell {
                place-items: end center;
                padding: 0.75rem;
            }

            .maintenance-card {
                width: 100%;
                border-radius: 1rem;
                padding: 1rem 0.95rem;
                margin-bottom: 0.35rem;
            }

            .brand {
                gap: .55rem;
                margin-bottom: .7rem;
            }

            .brand img {
                width: 34px;
                height: 34px;
            }

            .brand strong {
                font-size: 1.45rem;
            }

            h1 {
                font-size: 1.55rem;
            }

            p {
                font-size: 0.96rem;
            }
        }
    </style>
</head>
<body>
<main class="maintenance-shell" role="main">
    <section class="maintenance-card" aria-labelledby="maintenance-title">
        <div class="brand">
            <img src="{{ asset('images/logo/saf-logo.png') }}" alt="Logo Sidoagung Farm">
            <strong>PT. Sidoagung Farm</strong>
        </div>
        <h1 id="maintenance-title">Website Sedang Dalam Perbaikan</h1>
        <p>Kami sedang melakukan pemeliharaan sistem agar layanan lebih stabil. Silakan kembali beberapa saat lagi.</p>
    </section>
</main>
</body>
</html>
