<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code</title>
    {{-- <script src="https://unpkg.com/html5-qrcode@2.3.7/minified/html5-qrcode.min.js"></script> --}}
</head>
<body>
    <h1>Scan QR Code</h1>
    <div id="reader" style="width: 500px; margin: auto;"></div>
    
    <form id="qrForm" method="POST" action="{{ route('qr.process') }}">
        @csrf
        <input type="hidden" name="qr_data" id="qrData">
    </form>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.7/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof Html5Qrcode === "undefined") {
                console.error("Html5Qrcode is not defined!");
                return;
            }

            const qrReader = new Html5Qrcode("reader");
            const qrForm = document.getElementById("qrForm");
            const qrDataInput = document.getElementById("qrData");

            qrReader.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 300, height: 300 },
                },
                (decodedText) => {
                    console.log(`QR Code detected: ${decodedText}`);
                    qrDataInput.value = decodedText;
                    qrForm.submit();
                },
                (errorMessage) => {
                    console.warn(`QR Code no match: ${errorMessage}`);
                }
            ).catch((err) => {
                console.error(`Unable to start scanning: ${err}`);
            });
        });
    </script>

</body>
</html>
