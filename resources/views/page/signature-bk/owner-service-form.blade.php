<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Signature | {{ $projectName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <!-- Add SweetAlert2 CDN -->
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
                <h2>Owner Signature / ลงชื่อเจ้าของ</h2>
                <p>Owner Sign Agreement / เจ้าของลงนามข้อตกลง</p>
                <p class="mt-3">Project: {{ $projectName }}</p>
            </div>
            
            <div class="form-group mb-4">
                <label for="receiverName" class="form-label">Owner Name / ชื่อเจ้าของ <span class="text-danger">*</span></label>
                <input type="text" id="receiverName" class="form-control" placeholder="Owner name / ชื่อเจ้าของ" value="{{ $receiverName }}" required>
                <div id="nameError" class="text-danger mt-1" style="display: none;">Please enter owner name / กรุณากรอกชื่อเจ้าของ</div>
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
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if signature already exists and show SweetAlert2 notification
            @if(isset($signatureExists) && $signatureExists)
                Swal.fire({
                    title: 'Signature Already Exists',
                    html: 'This form has already been signed by <strong>{{ $signerName }}</strong> on {{ \Carbon\Carbon::parse($signatureTime)->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}.<br><br>You can sign again if needed, which will replace the previous signature.',
                    icon: 'info',
                    confirmButtonText: 'Continue'
                });
            @endif
            
            // Initialize signature pad
            const canvas = document.getElementById('signatureCanvas');
            const receiverNameInput = document.getElementById('receiverName');
            const nameError = document.getElementById('nameError');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });

            function validateReceiverName() {
                const value = receiverNameInput.value.trim();
                if (!value) {
                    nameError.style.display = 'block';
                    receiverNameInput.focus({ preventScroll: true });
                    return false;
                }

                nameError.style.display = 'none';
                return true;
            }

            receiverNameInput.addEventListener('input', () => {
                if (receiverNameInput.value.trim()) {
                    nameError.style.display = 'none';
                }
            });
            
            // Handle window resize to maintain proper signature pad scaling
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear(); // Clear the canvas
            }
            
            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();
            
            // Download PDF button (was preview)
            const previewBtn = document.getElementById('previewPdfButton');
            if (previewBtn) {
                previewBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const requestId = '{{ $requestId }}';
                    if (!requestId) {
                        alert('Request ID not found');
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
                            tempLink.setAttribute('download', `owner-service-${requestId}.pdf`);
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
                            previewBtn.textContent = 'Download PDF';
                        });
                });
            }

            // Clear button
            document.getElementById('clearButton').addEventListener('click', function() {
                signaturePad.clear();
                document.getElementById('signatureStatus').style.display = 'none';
            });
            
            // Save button
            document.getElementById('saveButton').addEventListener('click', function() {
                // Validate receiver name
                if (!validateReceiverName()) {
                    return;
                }

                const receiverName = receiverNameInput.value.trim();
                
                // Validate signature
                if (signaturePad.isEmpty()) {
                    showStatus('Please provide a signature / กรุณาเซ็นชื่อ', 'error');
                    return;
                }
                
                const signatureData = signaturePad.toDataURL('image/png');
                
                // Send signature to server
                fetch('{{ route("owner.service.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        signature: signatureData,
                        receiver_name: receiverName,
                        request_id: '{{ $requestId }}',
                        type: '{{ $type }}',
                        token: '{{ $token }}'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('signatureStatus').style.display = 'none';
                        
                        // Show success message with SweetAlert2
                        Swal.fire({
                            title: 'Thank You!',
                            html: 'Your signature has been successfully saved.<br>ขอบคุณสำหรับลายเซ็นของคุณ! ลายเซ็นของคุณได้รับการบันทึกเรียบร้อยแล้ว',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                        
                        // Disable the save button after successful save
                        document.getElementById('saveButton').disabled = true;
                    } else {
                        showStatus('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    showStatus('Error saving signature: ' + error.message, 'error');
                });
            });
            
            function showStatus(message, type) {
                const statusElement = document.getElementById('signatureStatus');
                statusElement.textContent = message;
                statusElement.style.display = 'block';
                
                // Reset classes
                statusElement.classList.remove('success', 'error');
                
                if (type === 'success') {
                    statusElement.classList.add('success');
                } else if (type === 'error') {
                    statusElement.classList.add('error');
                }
            }
        });
    </script>
</body>
</html>
