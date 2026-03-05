<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1b5e20">
    <title>Offline Login Admin - Sidoagung Farm</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: linear-gradient(180deg, #f8fafc, #ecfdf5);
            color: #0f172a;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .card {
            width: min(560px, 100%);
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 24px;
            text-align: center;
        }

        .logo {
            width: 84px;
            height: 84px;
            object-fit: contain;
            margin: 0 auto 16px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 1.35rem;
        }

        p {
            margin: 0;
            line-height: 1.6;
            color: #475569;
        }

        .actions {
            margin-top: 18px;
            display: grid;
            gap: 10px;
        }

        a,
        button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border: 0;
            cursor: pointer;
            font-size: 0.95rem;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-primary {
            background: #1b5e20;
            color: #ffffff;
        }

        .btn-outline {
            background: #ffffff;
            color: #1b5e20;
            border: 1px solid #86efac;
        }
    </style>
</head>
<body>
<div class="card">
    <img class="logo" src="/images/logo/saf-logo.png" alt="Logo Sidoagung Farm">
    <h1>Login Admin Membutuhkan Koneksi Internet</h1>
    <p>Form login admin tidak bisa memvalidasi akun saat offline. Sambungkan internet, lalu lanjutkan login ke dashboard admin.</p>
    <div class="actions">
        <a class="btn-primary" href="/login?next=%2Fadmin">Buka Form Login</a>
        <a class="btn-outline" href="/">Kembali ke Beranda</a>
        <button id="retry-online" class="btn-outline" type="button">Cek Koneksi Lagi</button>
    </div>
</div>
<script>
    (() => {
        const button = document.getElementById('retry-online');
        if (!button) return;

        button.addEventListener('click', () => {
            if (navigator.onLine) {
                window.location.assign('/login?next=%2Fadmin');
                return;
            }

            window.location.reload();
        });
    })();
</script>
</body>
</html>
