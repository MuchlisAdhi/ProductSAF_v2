<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Gangguan Layanan | PT. Sidoagung Farm</title>
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
            color: #0f2a45;
            background: #0b1220;
        }

        .maintenance-shell {
            position: relative;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            overflow: hidden;
        }

        .maintenance-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(to bottom, rgba(6, 18, 38, 0.58), rgba(6, 18, 38, 0.5)),
                url('{{ $backgroundImage }}') center center / cover no-repeat;
        }

        .maintenance-card {
            position: relative;
            width: min(860px, 100%);
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 1.25rem;
            box-shadow: 0 28px 50px rgba(15, 23, 42, 0.26);
            backdrop-filter: blur(3px);
            padding: 1.5rem 1.5rem 1.75rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .brand img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .brand strong {
            font-size: 1.1rem;
            letter-spacing: 0.02em;
            color: #0a7a35;
        }

        h1 {
            margin: 0 0 .5rem;
            font-size: clamp(1.7rem, 2.8vw, 2.7rem);
            line-height: 1.2;
            color: #17375f;
        }

        p {
            margin: 0 0 1rem;
            font-size: clamp(1rem, 1.8vw, 1.2rem);
            color: #304f73;
        }

        .maintenance-note {
            display: inline-block;
            margin-top: .75rem;
            padding: .5rem .85rem;
            border-radius: 999px;
            background: #fff3e6;
            border: 1px solid #ffd9a8;
            color: #9c4b00;
            font-weight: 600;
            font-size: .92rem;
        }
    </style>
</head>
<body>
<main class="maintenance-shell" role="main">
    <section class="maintenance-card" aria-labelledby="maintenance-title">
        <div class="brand">
            <img src="{{ asset('images/logo/logo-sidoagung-merah.png') }}" alt="Logo Sidoagung Farm">
            <strong>PT. Sidoagung Farm</strong>
        </div>
        <h1 id="maintenance-title">Layanan Sedang Mengalami Gangguan</h1>
        <p>Terjadi kendala pada sistem. Tim kami sedang melakukan penanganan untuk memulihkan layanan secepatnya.</p>
        <span class="maintenance-note">Status: Penanganan Insiden</span>
    </section>
</main>
</body>
</html>
