<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | {{ $projectName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        .sig-card {
            max-width: 620px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .sig-card-header {
            background: linear-gradient(135deg, #667777 0%, #4a5858 100%);
            color: #fff;
            padding: 24px 28px 20px;
        }
        .sig-card-header h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0 0 4px;
        }
        .sig-card-header p {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.85;
        }
        .sig-meta {
            display: flex;
            gap: 20px;
            margin-top: 14px;
            flex-wrap: wrap;
        }
        .sig-meta-item {
            font-size: 0.78rem;
            opacity: 0.9;
        }
        .sig-meta-item strong {
            display: block;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.7;
            margin-bottom: 2px;
        }
        .sig-card-body {
            padding: 24px 28px 28px;
        }
        .sig-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        #signatureCanvas {
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            width: 100%;
            height: 200px;
            background: #fafbfc;
            touch-action: none;
            cursor: crosshair;
        }
        #signatureCanvas.drawing {
            border-color: #667777;
            border-style: solid;
        }
        .sig-hint {
            text-align: center;
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 6px;
        }
        .sig-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .sig-actions .btn {
            flex: 1;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 0.9rem;
        }
        .btn-sig-save {
            background: #667777;
            color: #fff;
            border: none;
        }
        .btn-sig-save:hover {
            background: #4a5858;
            color: #fff;
        }
        .btn-sig-clear {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #e5e7eb;
        }
        .btn-sig-clear:hover {
            background: #e5e7eb;
            color: #374151;
        }

        /* Already signed banner */
        .signed-banner {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .signed-banner i {
            font-size: 1.5rem;
            color: #16a34a;
        }
        .signed-banner .signed-info {
            font-size: 0.85rem;
            color: #166534;
        }
        .signed-banner .signed-info strong {
            display: block;
            font-size: 0.95rem;
            margin-bottom: 2px;
        }

        @media (max-width: 640px) {
            .sig-card { margin: 16px; border-radius: 12px; }
            .sig-card-header { padding: 18px 20px 16px; }
            .sig-card-body { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="sig-card">
        <div class="sig-card-header">
            <h2>{{ $title }}</h2>
            <p>{{ $subtitle }}</p>
            <div class="sig-meta">
                <div class="sig-meta-item">
                    <strong>Project</strong>
                    {{ $projectName }}
                </div>
                <div class="sig-meta-item">
                    <strong>Unit</strong>
                    {{ $unitCode }}
                </div>
                <div class="sig-meta-item">
                    <strong>Sale No.</strong>
                    {{ $sale->sale_number }}
                </div>
            </div>
        </div>

        <div class="sig-card-body">
            @if($signedAt)
                <div class="signed-banner">
                    <i class="bi bi-check-circle-fill"></i>
                    <div class="signed-info">
                        <strong>Already Signed / เซ็นลายเซ็นแล้ว</strong>
                        Signed by <strong>{{ $signerName }}</strong> on {{ \Carbon\Carbon::parse($signedAt)->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}
                    </div>
                </div>
            @endif

            <div class="mb-3">
                <label for="signerName" class="sig-label">Your Name / ชื่อของคุณ <span class="text-danger">*</span></label>
                <input type="text" id="signerName" class="form-control" placeholder="Enter your full name / กรุณากรอกชื่อเต็ม" value="{{ $prefillName }}">
                <div id="nameError" class="text-danger mt-1" style="display:none; font-size:0.82rem;">Please enter your name / กรุณากรอกชื่อ</div>
            </div>

            <div>
                <label class="sig-label">Your Signature / ลายเซ็นของคุณ <span class="text-danger">*</span></label>
                <canvas id="signatureCanvas"></canvas>
                <div class="sig-hint">Draw your signature above / วาดลายเซ็นด้านบน</div>
            </div>

            <div class="sig-actions">
                <button id="clearBtn" class="btn btn-sig-clear">
                    <i class="bi bi-arrow-counterclockwise"></i> Clear / ล้าง
                </button>
                <button id="saveBtn" class="btn btn-sig-save">
                    <i class="bi bi-check2-circle"></i> Save Signature / บันทึก
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('signatureCanvas');
            const nameInput = document.getElementById('signerName');
            const nameError = document.getElementById('nameError');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: '#1e293b',
            });

            // --- Canvas sizing ---
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                signaturePad.clear();
            }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            // Visual feedback while drawing
            canvas.addEventListener('pointerdown', () => canvas.classList.add('drawing'));
            canvas.addEventListener('pointerup', () => canvas.classList.remove('drawing'));

            // Already signed alert
            @if($signedAt)
                Swal.fire({
                    title: 'Signature Already Exists',
                    html: 'This form has already been signed by <strong>{{ $signerName }}</strong> on {{ \Carbon\Carbon::parse($signedAt)->timezone("Asia/Bangkok")->format("d/m/Y H:i") }}.<br><br>You can sign again if needed.',
                    icon: 'info',
                    confirmButtonText: 'Continue',
                    confirmButtonColor: '#667777',
                });
            @endif

            // Clear
            document.getElementById('clearBtn').addEventListener('click', function () {
                signaturePad.clear();
            });

            // Save
            document.getElementById('saveBtn').addEventListener('click', function () {
                // Validate name
                if (!nameInput.value.trim()) {
                    nameError.style.display = 'block';
                    nameInput.focus();
                    return;
                }
                nameError.style.display = 'none';

                // Validate signature
                if (signaturePad.isEmpty()) {
                    Swal.fire({
                        title: 'Signature Required',
                        text: 'Please provide a signature / กรุณาเซ็นชื่อ',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#667777',
                    });
                    return;
                }

                const signatureData = signaturePad.toDataURL('image/png');

                Swal.fire({
                    title: 'Saving Signature',
                    text: 'Please wait... / กรุณารอสักครู่...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                fetch('{{ route("contracts.reservation-agreement.signature.save", ["sale" => $sale->id, "type" => $type]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        signer_name: nameInput.value.trim(),
                        signature: signatureData,
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Thank You!',
                            html: 'Your signature has been successfully saved.<br>ลายเซ็นของคุณได้รับการบันทึกเรียบร้อยแล้ว',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#667777',
                        });
                        document.getElementById('saveBtn').disabled = true;
                    } else {
                        Swal.fire({ title: 'Error', text: data.message, icon: 'error' });
                    }
                })
                .catch(err => {
                    Swal.fire({ title: 'Error', text: 'Error saving signature: ' + err.message, icon: 'error' });
                });
            });

            // Hide name error on input
            nameInput.addEventListener('input', () => {
                if (nameInput.value.trim()) nameError.style.display = 'none';
            });
        });
    </script>
</body>
</html>
