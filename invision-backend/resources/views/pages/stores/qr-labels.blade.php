<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store QR Labels</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; padding: 24px; color: #111827; }
        .toolbar { display: flex; gap: 12px; align-items: center; margin-bottom: 24px; }
        .toolbar button { background: #4f46e5; color: #fff; border: 0; border-radius: 6px; padding: 8px 18px; font-size: 14px; cursor: pointer; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
        .label { border: 1px dashed #d1d5db; border-radius: 8px; padding: 16px; text-align: center; break-inside: avoid; page-break-inside: avoid; }
        .label .qr svg { width: 180px; height: 180px; }
        .label .name { font-weight: 700; font-size: 15px; margin-top: 6px; }
        .label .meta { font-size: 12px; color: #6b7280; }
        .label .code { font-family: monospace; font-size: 11px; color: #374151; margin-top: 4px; }
        @media print { .toolbar { display: none; } body { padding: 0; } .label { border-color: #9ca3af; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print</button>
        <span>{{ $stores->count() }} {{ Str::plural('label', $stores->count()) }} · scan in the field force app to start the visit</span>
    </div>

    @if($stores->isEmpty())
        <p>No stores selected.</p>
    @endif

    <div class="grid">
        @foreach($stores as $store)
        <div class="label">
            <div class="qr" data-code="{{ $store->qr_code }}"></div>
            <div class="name">{{ $store->name }}</div>
            <div class="meta">{{ $store->code }}{{ $store->address ? ' · '.$store->address : '' }}</div>
            <div class="code">{{ $store->qr_code }}</div>
        </div>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js" integrity="sha384-lQXOAyZwHXE55JFyrOMB7nY2Wv+m5ZWNtJcHrd1rceRQXAYNLak8ukN5TjBTcIwz" crossorigin="anonymous"></script>
    <script>
        document.querySelectorAll('.qr').forEach(el => {
            const qr = qrcode(0, 'M');
            qr.addData(el.dataset.code);
            qr.make();
            el.innerHTML = qr.createSvgTag({ scalable: true, margin: 2 });
        });
    </script>
</body>
</html>
