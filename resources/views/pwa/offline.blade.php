<!DOCTYPE html>
{{-- Shown by the service worker when there's no internet and the page wasn't saved. Self-contained: no database, no build files. --}}
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#141414">
    <title>Offline | {{ config('shop.name') }}</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
            background:#f7f3ee;color:#141414;font:16px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif;text-align:center}
        .card{max-width:380px}
        .logo{font:600 32px/1 Georgia,serif;letter-spacing:.3em}
        .logo small{display:block;margin-top:6px;font:400 10px/1 system-ui,sans-serif;letter-spacing:.55em;color:#777}
        .icon{width:64px;height:64px;margin:36px auto 20px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center}
        h1{margin:0;font:600 28px/1.2 Georgia,serif}
        p{margin:10px 0 0;color:#555}
        .actions{margin-top:28px;display:flex;flex-direction:column;gap:10px}
        button,a{display:block;min-height:48px;padding:14px;border:1px solid #141414;font:600 13px/1.4 system-ui,sans-serif;
            letter-spacing:.06em;text-transform:uppercase;text-decoration:none;cursor:pointer}
        button{background:#141414;color:#fff}
        a{background:transparent;color:#141414}
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">PACH<small>WOMEN</small></div>
        <div class="icon" aria-hidden="true">
            <svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#141414"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 8.735 8.735m0 0a.374.374 0 1 1 .53.53m-.53-.53.53.53m0 0L21 21M14.652 9.348a3.75 3.75 0 0 1 0 5.304m2.121-7.425a6.75 6.75 0 0 1 0 9.546m2.121-11.667c3.808 3.807 3.808 9.98 0 13.788m-9.546-4.242a3.733 3.733 0 0 1-1.06-2.122m-1.061 4.243a6.75 6.75 0 0 1-1.625-6.929m-.496 9.05c-3.068-3.067-3.664-7.67-1.79-11.334M12 12h.008v.008H12V12Z"/></svg>
        </div>
        <h1>You're offline</h1>
        <p>This page hasn't been saved on your phone yet. Pages you've opened before still work. Check your connection and try again.</p>
        <div class="actions">
            <button type="button" onclick="location.reload()">Try again</button>
            <a href="/">Go to home</a>
        </div>
    </div>
    <script>window.addEventListener('online', () => location.reload());</script>
</body>
</html>
