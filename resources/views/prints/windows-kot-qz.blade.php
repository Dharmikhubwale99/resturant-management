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
  <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.3/qz-tray.js"></script>

  <script>
    (async () => {
      // ✅ સાચું promise shape (QZ 2.2.x)
      qz.security.setCertificatePromise(function (resolve, reject) {
        resolve("unsigned");                 // dev only; QZ configમાં unsigned allow ON
      });
      qz.security.setSignaturePromise(function (toSign) {
        return function (resolve, reject) {  // resolver function જ 반환 થવો જોઈએ
          resolve();                         // unsigned ⇒ કોઈ signature નહિ
        };
      });

      const usingSecure = location.protocol === 'https:';
      if (!qz.websocket.isActive()) {
        await qz.websocket.connect({ usingSecure });
      }

      // Printer select
      let printer = await qz.printers.getDefault();
      if (!printer) {
        // fallback: તમારું thermal queue નામ નાખો
        printer = "POS-80"; // <-- બદલો તમારી સિસ્ટમ મુજબ
      }

      const cfg = qz.configs.create(printer, {
        encoding: 'UTF-8',
        altPrinting: true,      // ઘણા ડ્રાઇવર્સમાં જરૂરી પડે છે
        // jobName: 'KOT #{{ $kotId }}'
      });

      const resp = await fetch('/windows/kot/escpos/{{ $kotId }}', { credentials: 'same-origin' });
      if (!resp.ok) { alert('ESC/POS fetch failed: ' + resp.status); return; }
      const data = await resp.text();

      await qz.print(cfg, [{ type: 'raw', format: 'plain', data }]);
      window.close();
    })();
    </script>


</body>
</html>
