<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:24px}
    .btn{display:inline-block;padding:10px 14px;border:1px solid #222;border-radius:8px;text-decoration:none}
  </style>
</head>
<body>
  <h3>Sending to Bluetooth Printer…</h3>
  <p>If it doesn’t open, tap “Open Printer App”.</p>
  <p>
    <a id="open" class="btn"
       href="my.bluetoothprint.scheme://{{ $responseUrl }}">Open Printer App</a>
    <a id="fallback" class="btn" style="margin-left:8px" href="{{ $responseUrl }}" target="_blank">
      See JSON
    </a>
  </p>

  <script>
    // Auto open on load
    document.addEventListener('DOMContentLoaded', function () {
      // App expects plain href without encoding (as per vendor sample)
      window.location.href = 'my.bluetoothprint.scheme://' + @json($responseUrl);
      // close this tab a little later (optional)
      setTimeout(function(){ try { window.close(); } catch(e) {} }, 1500);
    });
  </script>
</body>
</html>
