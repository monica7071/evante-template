<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Witness 2 Signature for Service | {{ $projectName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .signature-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        .signature-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .signature-header h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .signature-header p {
            margin: 5px 0 0;
            font-size: 16px;
            color: #666;
        }
        #signatureCanvas {
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            height: 200px;
            background-color: #fff;
            touch-action: none;
        }
        .signature-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .signature-status {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="signature-container">
            <div class="signature-header">
                <h2>Witness 2 Signature for Service/ ลงชื่อพยาน 2</h2>
                <p>Witness 2 Sign Agreement / พยาน 2 ลงนามข้อตกลง</p>
                <p class="mt-3">Project: {{ $projectName }}</p>
            </div>
            
            <div class="form-group mb-4">
                <label for="witnessName" class="form-label">Witness Name / ชื่อพยาน <span class="text-danger">*</span></label>
                <input type="text" id="witnessName" class="form-control" placeholder="Enter witness name / กรุณากรอกชื่อพยาน" value="{{ old('witness_name', $witnessName ?? '') }}" required>
                <div id="nameError" class="text-danger mt-1" style="display: none;">Please enter witness name / กรุณากรอกชื่อพยาน</div>
            </div>
            
            <div class="signature-pad-container">
                <h5 class="mb-3">Your Signature / ลายเซ็นของคุณ <span class="text-danger">*</span></h5>
                <canvas id="signatureCanvas"></canvas>
                
                <div class="signature-actions">
                    <button id="previewPdfButton" class="btn btn-outline-secondary">Download PDF</button>
                    <button id="clearButton" class="btn btn-secondary">Clear / ล้าง</button>
                    <button id="saveButton" class="btn btn-primary">Save Signature / บันทึกลายเซ็น</button>
                </div>
                
                <div id="signatureStatus" class="signature-status"></div>
                
                <div id="successContent" style="display: none; margin-top: 20px;">
                    <div class="alert alert-success">
                        <h4>Thank you for your signature!</h4>
                        <p>Your signature has been successfully saved.</p>
                        <p>ขอบคุณสำหรับลายเซ็นของคุณ! ลายเซ็นของคุณได้รับการบันทึกเรียบร้อยแล้ว</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(isset($signatureExists) && $signatureExists)
                Swal.fire({
                    title: 'Signature Already Exists',
                    html: 'This form has already been signed by <strong>{{ $signerName }}</strong> on {{ \Carbon\Carbon::parse($signatureTime)->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}.<br><br>You can still sign again if needed, which will replace the previous signature.',
                    icon: 'info',
                    confirmButtonText: 'Continue'
                });
            @endif
            
            const canvas = document.getElementById('signatureCanvas');
            const witnessNameInput = document.getElementById('witnessName');
            const nameError = document.getElementById('nameError');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });

            function validateWitnessName() {
                const value = witnessNameInput.value.trim();
                if (!value) {
                    nameError.style.display = 'block';
                    witnessNameInput.focus({ preventScroll: true });
                    return false;
                }

                nameError.style.display = 'none';
                return true;
            }

            witnessNameInput.addEventListener('input', () => {
                if (witnessNameInput.value.trim()) {
                    nameError.style.display = 'none';
                }
            });
            
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear();
            }
            
            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            // Download PDF button
            const previewBtn = document.getElementById('previewPdfButton');
            if (previewBtn) {
                previewBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const requestId = '{{ $requestId }}';
                    if (!requestId) {
                        Swal.fire({
                            title: 'Download failed',
                            text: 'Request ID not found',
                            icon: 'error'
                        });
                        return;
                    }
                    const token = '{{ $token }}';
                    if (!token) {
                        Swal.fire({
                            title: 'Download failed',
                            text: 'Missing access token',
                            icon: 'error'
                        });
                        return;
                    }

                    const downloadUrl = `/public/generate-print-service-agreement/${token}`;
                    const originalText = previewBtn.textContent;
                    previewBtn.disabled = true;
                    previewBtn.textContent = 'Downloading...';

                    fetch(downloadUrl, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Failed to generate PDF');
                            }
                            const contentType = response.headers.get('Content-Type') || '';
                            if (!contentType.includes('pdf')) {
                                throw new Error('Unexpected response type');
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const tempLink = document.createElement('a');
                            tempLink.href = url;
                            tempLink.setAttribute('download', `witness2-service-${requestId}.pdf`);
                            document.body.appendChild(tempLink);
                            tempLink.click();
                            document.body.removeChild(tempLink);
                            window.URL.revokeObjectURL(url);
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Download failed',
                                text: error.message,
                                icon: 'error'
                            });
                        })
                        .finally(() => {
                            previewBtn.disabled = false;
                            previewBtn.textContent = originalText;
                        });
                });
            }
            
            document.getElementById('clearButton').addEventListener('click', function() {
                signaturePad.clear();
                document.getElementById('signatureStatus').style.display = 'none';
                document.getElementById('successContent').style.display = 'none';
            });
            
            document.getElementById('saveButton').addEventListener('click', function() {
                if (!validateWitnessName()) {
                    return;
                }

                const witnessName = witnessNameInput.value.trim();
                
                if (signaturePad.isEmpty()) {
                    Swal.fire({
                        title: 'Signature Required',
                        text: 'Please provide a signature / กรุณาเซ็นชื่อ',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                const signatureData = signaturePad.toDataURL('image/png');
                
                Swal.fire({
                    title: 'Saving Signature',
                    text: 'Please wait... / กรุณารอสักครู่...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                fetch('{{ route("witness2.signature.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        signature: signatureData,
                        witness_name: witnessName,
                        request_id: '{{ $requestId }}',
                        type: '{{ $type }}',
                        token: '{{ $token }}'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Thank You!',
                            html: 'Your signature has been successfully saved.<br>ขอบคุณสำหรับลายเซ็นของคุณ! ลายเซ็นของคุณได้รับการบันทึกเรียบร้อยแล้ว',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                        document.getElementById('saveButton').disabled = true;
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error saving signature: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        });
    </script>
</body>
</html>