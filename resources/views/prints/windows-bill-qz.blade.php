<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:24px}
    .btn{display:inline-block;padding:8px 12px;border:1px solid #222;border-radius:8px;text-decoration:none}
  </style>
</head>
<body>
  <h3>Printing Bill…</h3>

  <p>
    <a class="btn" id="retry" href="#">Retry</a>
    <a class="btn" id="show" target="_blank" href="/windows/bill/escpos/{{ $orderId }}">View Data</a>
  </p>

  <!-- QZ Tray (2.2.x). CDN OK; અથવા public/js/qz-tray.js માં copy કરી શકો -->
  <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.3/qz-tray.js"></script>

  <script>
    (function () {
      const ESC_URL = '/windows/bill/escpos/{{ $orderId }}';

      async function connectQZ() {
        try {
          const usingSecure = location.protocol === 'https:';

          qz.security.setCertificatePromise(function (resolve, reject) {
            // DEV: unsigned certificate
            resolve("unsigned");
          });
          qz.security.setSignaturePromise(function (toSign) {
            return function (resolve, reject) {
              // unsigned ⇒ no signature
              resolve();
            };
          });

          if (!qz.websocket.isActive()) {
            await qz.websocket.connect({ usingSecure });
          }
        } catch (e) {
          alert('QZ connect failed:\n' + e);
          throw e;
        }
      }

      async function doPrint() {
        await connectQZ();

        let printer = await qz.printers.getDefault();
        if (!printer) {
          // fallback: તમારું thermal queue name
          printer = "POS-80";
        }

        const cfg = qz.configs.create(printer, {
          encoding: 'UTF-8',
          altPrinting: true,
          // jobName: 'Bill #{{ $orderId }}',
        });

        const resp = await fetch(ESC_URL, { credentials: 'same-origin' });
        if (!resp.ok) { alert('Fetch ESC/POS failed: ' + resp.status); return; }
        const data = await resp.text();

        await qz.print(cfg, [{ type: 'raw', format: 'plain', data }]);

        // Close this helper tab/window
        try { window.close(); } catch(e) {}
      }

      document.getElementById('retry').addEventListener('click', function (e) {
        e.preventDefault();
        doPrint();
      });

      // auto-run
      doPrint();
    })();
  </script>
</body>
</html>
