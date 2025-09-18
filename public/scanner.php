<?php
declare(strict_types=1);

// Vérifier si on est appelé directement ou depuis index.php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../bootstrap.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner QR - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .scanner-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            opacity: 0.8;
            font-size: 14px;
        }

        .scanner-area {
            padding: 30px;
            text-align: center;
        }

        #reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto 20px;
            border-radius: 10px;
            overflow: hidden;
            border: 3px solid #ecf0f1;
        }

        .controls {
            margin: 20px 0;
        }

        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
        }

        .btn.success {
            background: #27ae60;
        }

        .btn.danger {
            background: #e74c3c;
        }

        .status {
            margin: 20px 0;
            padding: 15px;
            border-radius: 10px;
            font-weight: 500;
        }

        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .result {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: left;
        }

        .result h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .result p {
            margin: 5px 0;
            color: #666;
        }

        .hidden {
            display: none;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .footer {
            background: #ecf0f1;
            padding: 15px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .footer a {
            color: #3498db;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .scanner-container {
                margin: 10px;
            }
            
            .scanner-area {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="scanner-container">
        <div class="header">
            <h1>Scanner QR Code</h1>
            <p>Scannez le QR code pour marquer la présence</p>
        </div>

        <div class="scanner-area">
            <div id="reader" class="hidden"></div>
            
            <div class="controls">
                <button id="startBtn" class="btn">
                    <span class="loading hidden"></span>
                    Démarrer le scanner
                </button>
                <button id="stopBtn" class="btn danger hidden">Arrêter</button>
            </div>

            <div id="status" class="status hidden"></div>
            <div id="result" class="result hidden"></div>
        </div>

        <div class="footer">
            <a href="/dashboard">← Retour au tableau de bord</a>
        </div>
    </div>

    <!-- QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    
    <script>
        class QRScanner {
            constructor() {
                this.html5QrCode = null;
                this.isScanning = false;
                this.init();
            }

            init() {
                this.startBtn = document.getElementById('startBtn');
                this.stopBtn = document.getElementById('stopBtn');
                this.reader = document.getElementById('reader');
                this.status = document.getElementById('status');
                this.result = document.getElementById('result');
                this.loading = this.startBtn.querySelector('.loading');

                this.startBtn.addEventListener('click', () => this.startScanning());
                this.stopBtn.addEventListener('click', () => this.stopScanning());
            }

            showStatus(message, type = 'info') {
                this.status.textContent = message;
                this.status.className = `status ${type}`;
                this.status.classList.remove('hidden');
            }

            hideStatus() {
                this.status.classList.add('hidden');
            }

            showResult(data) {
                this.result.innerHTML = `
                    <h3>Résultat du scan</h3>
                    <p><strong>Code scanné:</strong> ${data.code}</p>
                    <p><strong>Statut:</strong> ${data.status}</p>
                    <p><strong>Message:</strong> ${data.message}</p>
                    ${data.student ? `<p><strong>Étudiant:</strong> ${data.student}</p>` : ''}
                `;
                this.result.classList.remove('hidden');
            }

            async startScanning() {
                try {
                    this.loading.classList.remove('hidden');
                    this.startBtn.disabled = true;
                    this.showStatus('Initialisation de la caméra...', 'info');

                    this.html5QrCode = new Html5Qrcode("reader");
                    
                    // Obtenir les caméras disponibles
                    const cameras = await Html5Qrcode.getCameras();
                    
                    if (cameras && cameras.length > 0) {
                        // Préférer la caméra arrière si disponible
                        const cameraId = cameras.length > 1 ? cameras[1].id : cameras[0].id;
                        
                        await this.html5QrCode.start(
                            cameraId,
                            {
                                fps: 10,
                                qrbox: { width: 250, height: 250 }
                            },
                            (decodedText, decodedResult) => {
                                this.onScanSuccess(decodedText);
                            },
                            (errorMessage) => {
                                // Ignorer les erreurs de scan (normales quand aucun QR n'est détecté)
                            }
                        );

                        this.isScanning = true;
                        this.reader.classList.remove('hidden');
                        this.startBtn.classList.add('hidden');
                        this.stopBtn.classList.remove('hidden');
                        this.showStatus('Scanner actif - Pointez vers un QR code', 'success');
                        
                    } else {
                        throw new Error('Aucune caméra trouvée');
                    }
                } catch (error) {
                    console.error('Erreur lors du démarrage du scanner:', error);
                    this.showStatus(`Erreur: ${error.message}`, 'error');
                    this.resetUI();
                } finally {
                    this.loading.classList.add('hidden');
                    this.startBtn.disabled = false;
                }
            }

            async stopScanning() {
                if (this.html5QrCode && this.isScanning) {
                    try {
                        await this.html5QrCode.stop();
                        this.html5QrCode.clear();
                    } catch (error) {
                        console.error('Erreur lors de l\'arrêt du scanner:', error);
                    }
                }
                this.resetUI();
                this.showStatus('Scanner arrêté', 'info');
            }

            resetUI() {
                this.isScanning = false;
                this.reader.classList.add('hidden');
                this.startBtn.classList.remove('hidden');
                this.stopBtn.classList.add('hidden');
                this.loading.classList.add('hidden');
                this.startBtn.disabled = false;
            }

            async onScanSuccess(qrCodeMessage) {
                console.log('QR Code scanné:', qrCodeMessage);
                
                // Arrêter temporairement le scanner pour traiter le résultat
                if (this.html5QrCode && this.isScanning) {
                    await this.html5QrCode.pause();
                }

                this.showStatus('Traitement du QR code...', 'info');

                try {
                    // Envoyer le QR code au serveur
                    const response = await fetch('/api/attendance', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            qr_code: qrCodeMessage,
                            timestamp: new Date().toISOString()
                        })
                    });

                    const data = await response.json();

                    if (data.ok) {
                        this.showStatus('Présence enregistrée avec succès!', 'success');
                        this.showResult({
                            code: qrCodeMessage,
                            status: 'Succès',
                            message: data.message || 'Présence marquée',
                            student: data.student_name || null
                        });
                    } else {
                        this.showStatus(`Erreur: ${data.error}`, 'error');
                        this.showResult({
                            code: qrCodeMessage,
                            status: 'Erreur',
                            message: data.error
                        });
                    }
                } catch (error) {
                    console.error('Erreur lors de l\'envoi:', error);
                    this.showStatus('Erreur de communication avec le serveur', 'error');
                    this.showResult({
                        code: qrCodeMessage,
                        status: 'Erreur',
                        message: 'Impossible de communiquer avec le serveur'
                    });
                }

                // Reprendre le scanner après 3 secondes
                setTimeout(async () => {
                    if (this.html5QrCode && this.isScanning) {
                        try {
                            await this.html5QrCode.resume();
                        } catch (error) {
                            console.error('Erreur lors de la reprise du scanner:', error);
                        }
                    }
                }, 3000);
            }
        }

        // Initialiser le scanner au chargement de la page
        document.addEventListener('DOMContentLoaded', () => {
            new QRScanner();
        });
    </script>
</body>
</html>