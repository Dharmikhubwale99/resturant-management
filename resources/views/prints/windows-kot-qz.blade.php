<!-- resources/views/prints/windows-kot-qz.blade.php -->
<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>body{font-family:system-ui;margin:24px}.btn{padding:8px12px;border:1px solid #222;border-radius:8px;text-decoration:none}</style>
</head>
<body>
  <h3>Printing KOT…</h3>
  <p><a class="btn" id="retry" href="#">Retry</a>
     <a class="btn" id="show" target="_blank" href="/windows/kot/escpos/{{ $kotId }}">View Data</a></p>

  <!-- QZ Tray JS (ડાઉનલોડ કરી public/js/qz-tray.js મૂકો અથવા CDN વાપરો) -->
  <script src="/js/qz-tray.js"></script>
  <script>
    async function doPrint() {
      try {
        if (!qz.websocket.isActive()) { await qz.websocket.connect(); }

        qz.security.setCertificatePromise(function(resolve, reject) { resolve("unsigned"); });
        qz.security.setSignaturePromise(function(toSign) { return Promise.resolve(null); });

        const printer = await qz.printers.getDefault();
        const cfg = qz.configs.create(printer, { encoding: 'UTF-8' });

        const data = await fetch('/windows/kot/escpos/{{ $kotId }}').then(r => r.text());
        await qz.print(cfg, [{ type: 'raw', format: 'plain', data }]);

        window.close();
      } catch (e) {
        console.error(e);
        alert('QZ Tray connection/print failed. Is QZ running?');
      }
    }
    document.addEventListener('DOMContentLoaded', doPrint);
    document.getElementById('retry').addEventListener('click', function(e){ e.preventDefault(); doPrint(); });
  </script>
</body>
</html>
