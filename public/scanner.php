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
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/design-system.css">
    <link rel="stylesheet" href="assets/css/components.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-4);
        }

        .scanner-container {
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-2xl);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
            position: relative;
        }

        .scanner-header {
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
            color: white;
            padding: var(--space-8);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .scanner-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="qr" patternUnits="userSpaceOnUse" width="10" height="10"><rect width="5" height="5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23qr)"/></svg>');
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .scanner-header h1 {
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--space-2);
            position: relative;
            z-index: 1;
        }

        .scanner-header p {
            opacity: 0.9;
            font-size: var(--font-size-base);
            position: relative;
            z-index: 1;
        }

        .scanner-body {
            padding: var(--space-8);
        }

        .camera-container {
            position: relative;
            margin-bottom: var(--space-6);
        }

        #reader {
            width: 100%;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 4px solid var(--primary-100);
            background: var(--secondary-50);
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .scanner-placeholder {
            text-align: center;
            color: var(--secondary-500);
            padding: var(--space-12);
        }

        .scanner-placeholder svg {
            margin-bottom: var(--space-4);
            opacity: 0.5;
        }

        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 3px solid var(--primary-500);
            border-radius: var(--radius-lg);
            pointer-events: none;
            z-index: 10;
        }

        .scanner-overlay::before,
        .scanner-overlay::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid var(--primary-500);
        }

        .scanner-overlay::before {
            top: -3px;
            left: -3px;
            border-right: none;
            border-bottom: none;
        }

        .scanner-overlay::after {
            bottom: -3px;
            right: -3px;
            border-left: none;
            border-top: none;
        }

        .scanner-controls {
            display: flex;
            justify-content: center;
            gap: var(--space-4);
            margin-bottom: var(--space-6);
        }

        .scan-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-4);
            margin-bottom: var(--space-6);
        }

        .scan-stat {
            text-align: center;
            padding: var(--space-4);
            background: var(--secondary-50);
            border-radius: var(--radius-lg);
            border: 1px solid var(--secondary-200);
        }

        .scan-stat-value {
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            color: var(--primary-600);
            margin-bottom: var(--space-1);
        }

        .scan-stat-label {
            font-size: var(--font-size-sm);
            color: var(--secondary-600);
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .scan-result {
            background: var(--success-50);
            border: 1px solid var(--success-200);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            margin-bottom: var(--space-6);
            animation: fadeIn 0.5s ease-out;
        }

        .scan-result.error {
            background: var(--error-50);
            border-color: var(--error-200);
        }

        .scan-result-header {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin-bottom: var(--space-4);
        }

        .scan-result-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .scan-result-icon.success {
            background: var(--success-100);
            color: var(--success-600);
        }

        .scan-result-icon.error {
            background: var(--error-100);
            color: var(--error-600);
        }

        .scan-result-title {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--secondary-900);
        }

        .scan-result-details {
            display: grid;
            gap: var(--space-2);
        }

        .scan-result-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-2) 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .scan-result-detail:last-child {
            border-bottom: none;
        }

        .scan-result-label {
            font-size: var(--font-size-sm);
            color: var(--secondary-600);
            font-weight: var(--font-weight-medium);
        }

        .scan-result-value {
            font-size: var(--font-size-sm);
            color: var(--secondary-900);
            font-weight: var(--font-weight-medium);
            text-align: right;
        }

        .manual-input {
            background: var(--secondary-50);
            border: 1px solid var(--secondary-200);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            margin-bottom: var(--space-6);
        }

        .manual-input h3 {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--space-4);
            color: var(--secondary-800);
        }

        .manual-form {
            display: flex;
            gap: var(--space-3);
        }

        .manual-form input {
            flex: 1;
            padding: var(--space-3);
            border: 1px solid var(--secondary-300);
            border-radius: var(--radius-lg);
            font-size: var(--font-size-base);
        }

        .scanner-footer {
            background: var(--secondary-50);
            padding: var(--space-6);
            text-align: center;
            border-top: 1px solid var(--secondary-200);
        }

        .scanner-footer a {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            color: var(--primary-600);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
            transition: color var(--transition-fast);
        }

        .scanner-footer a:hover {
            color: var(--primary-700);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .scanning-indicator {
            position: absolute;
            top: var(--space-4);
            right: var(--space-4);
            background: var(--success-600);
            color: white;
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            display: flex;
            align-items: center;
            gap: var(--space-2);
            z-index: 20;
        }

        .scanning-indicator::before {
            content: '';
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: pulse 1s infinite;
        }

        @media (max-width: 768px) {
            .scanner-container {
                margin: var(--space-4);
                max-width: calc(100vw - 2rem);
            }

            .scanner-header,
            .scanner-body {
                padding: var(--space-6);
            }

            .scan-stats {
                grid-template-columns: 1fr;
                gap: var(--space-3);
            }

            .scanner-controls {
                flex-direction: column;
                align-items: center;
            }

            .manual-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="scanner-container animate-on-load">
        <!-- Header -->
        <div class="scanner-header">
            <h1>Scanner QR Code</h1>
            <p>Scannez ou saisissez manuellement le code étudiant</p>
        </div>

        <!-- Body -->
        <div class="scanner-body">
            <!-- Statistics -->
            <div class="scan-stats">
                <div class="scan-stat">
                    <div class="scan-stat-value" id="totalScans">0</div>
                    <div class="scan-stat-label">Total scans</div>
                </div>
                <div class="scan-stat">
                    <div class="scan-stat-value" id="successScans">0</div>
                    <div class="scan-stat-label">Succès</div>
                </div>
                <div class="scan-stat">
                    <div class="scan-stat-value" id="errorScans">0</div>
                    <div class="scan-stat-label">Erreurs</div>
                </div>
            </div>

            <!-- Camera Container -->
            <div class="camera-container">
                <div id="reader">
                    <div class="scanner-placeholder">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.5 6.5v3h-3v-3h3M11 5H5v6h6V5zm-1.5 9.5v3h-3v-3h3M11 13H5v6h6v-6zm6.5-6.5v3h-3v-3h3M19 5h-6v6h6V5zm-6.5 9.5v3h-3v-3h3M13 13h6v6h-6v-6zM21 21H3V3h18v18z"/>
                        </svg>
                        <p>Caméra non activée</p>
                        <p class="text-sm text-gray-400 mt-2">Cliquez sur "Démarrer" pour activer le scanner</p>
                    </div>
                </div>
                
                <div class="scanner-overlay hidden" id="scannerOverlay"></div>
                
                <div class="scanning-indicator hidden" id="scanningIndicator">
                    Scan en cours...
                </div>
            </div>

            <!-- Controls -->
            <div class="scanner-controls">
                <button id="startBtn" class="btn btn-primary btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    Démarrer le scanner
                </button>
                
                <button id="stopBtn" class="btn btn-danger btn-lg hidden">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 6h12v12H6z"/>
                    </svg>
                    Arrêter
                </button>
                
                <button id="toggleManual" class="btn btn-outline btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                    </svg>
                    Saisie manuelle
                </button>
            </div>

            <!-- Manual Input -->
            <div class="manual-input hidden" id="manualInput">
                <h3>Saisie manuelle du code étudiant</h3>
                <form class="manual-form" id="manualForm">
                    <input type="text" 
                           id="manualCode" 
                           placeholder="UUID ou code étudiant (ex: 00000000-0000-0000-0000-000000000001)"
                           class="form-input"
                           pattern="[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}"
                           required>
                    <button type="submit" class="btn btn-success">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                        Valider
                    </button>
                </form>
            </div>

            <!-- Status Messages -->
            <div id="statusContainer"></div>

            <!-- Results -->
            <div id="resultContainer"></div>
        </div>

        <!-- Footer -->
        <div class="scanner-footer">
            <a href="/">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.42-1.41L7.83 13H20v-2z"/>
                </svg>
                Retour au tableau de bord
            </a>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="assets/js/app.js"></script>
    
    <script>
        class ProfessionalQRScanner {
            constructor() {
                this.html5QrCode = null;
                this.isScanning = false;
                this.stats = { total: 0, success: 0, error: 0 };
                this.init();
            }

            init() {
                this.bindElements();
                this.bindEvents();
                this.updateStats();
            }

            bindElements() {
                this.elements = {
                    startBtn: document.getElementById('startBtn'),
                    stopBtn: document.getElementById('stopBtn'),
                    toggleManual: document.getElementById('toggleManual'),
                    reader: document.getElementById('reader'),
                    scannerOverlay: document.getElementById('scannerOverlay'),
                    scanningIndicator: document.getElementById('scanningIndicator'),
                    manualInput: document.getElementById('manualInput'),
                    manualForm: document.getElementById('manualForm'),
                    manualCode: document.getElementById('manualCode'),
                    statusContainer: document.getElementById('statusContainer'),
                    resultContainer: document.getElementById('resultContainer'),
                    totalScans: document.getElementById('totalScans'),
                    successScans: document.getElementById('successScans'),
                    errorScans: document.getElementById('errorScans')
                };
            }

            bindEvents() {
                this.elements.startBtn.addEventListener('click', () => this.startScanning());
                this.elements.stopBtn.addEventListener('click', () => this.stopScanning());
                this.elements.toggleManual.addEventListener('click', () => this.toggleManualInput());
                this.elements.manualForm.addEventListener('submit', (e) => this.handleManualSubmit(e));
            }

            async startScanning() {
                try {
                    this.showStatus('Initialisation de la caméra...', 'info');
                    this.elements.startBtn.disabled = true;

                    this.html5QrCode = new Html5Qrcode("reader");
                    const cameras = await Html5Qrcode.getCameras();
                    
                    if (cameras && cameras.length > 0) {
                        const cameraId = cameras.length > 1 ? cameras[1].id : cameras[0].id;
                        
                        await this.html5QrCode.start(
                            cameraId,
                            {
                                fps: 10,
                                qrbox: { width: 250, height: 250 },
                                aspectRatio: 1.0
                            },
                            (decodedText) => this.onScanSuccess(decodedText),
                            (errorMessage) => {} // Ignorer les erreurs de scan normales
                        );

                        this.isScanning = true;
                        this.updateUI(true);
                        this.showStatus('Scanner actif - Pointez vers un QR code', 'success');
                        
                    } else {
                        throw new Error('Aucune caméra trouvée sur cet appareil');
                    }
                } catch (error) {
                    console.error('Erreur scanner:', error);
                    this.showStatus(`Erreur: ${error.message}`, 'error');
                    this.updateUI(false);
                } finally {
                    this.elements.startBtn.disabled = false;
                }
            }

            async stopScanning() {
                if (this.html5QrCode && this.isScanning) {
                    try {
                        await this.html5QrCode.stop();
                        this.html5QrCode.clear();
                    } catch (error) {
                        console.error('Erreur arrêt scanner:', error);
                    }
                }
                this.isScanning = false;
                this.updateUI(false);
                this.showStatus('Scanner arrêté', 'info');
            }

            updateUI(scanning) {
                if (scanning) {
                    this.elements.startBtn.classList.add('hidden');
                    this.elements.stopBtn.classList.remove('hidden');
                    this.elements.scannerOverlay.classList.remove('hidden');
                    this.elements.scanningIndicator.classList.remove('hidden');
                } else {
                    this.elements.startBtn.classList.remove('hidden');
                    this.elements.stopBtn.classList.add('hidden');
                    this.elements.scannerOverlay.classList.add('hidden');
                    this.elements.scanningIndicator.classList.add('hidden');
                }
            }

            toggleManualInput() {
                const isHidden = this.elements.manualInput.classList.contains('hidden');
                this.elements.manualInput.classList.toggle('hidden');
                
                if (!isHidden) {
                    this.elements.toggleManual.innerHTML = `
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                        Saisie manuelle
                    `;
                } else {
                    this.elements.toggleManual.innerHTML = `
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                        Fermer saisie
                    `;
                    this.elements.manualCode.focus();
                }
            }

            handleManualSubmit(event) {
                event.preventDefault();
                const code = this.elements.manualCode.value.trim();
                if (code) {
                    this.onScanSuccess(code);
                    this.elements.manualCode.value = '';
                }
            }

            async onScanSuccess(qrCodeMessage) {
                // Pause temporaire du scanner
                if (this.html5QrCode && this.isScanning) {
                    await this.html5QrCode.pause();
                }

                this.showStatus('Traitement du code...', 'info');
                this.stats.total++;

                try {
                    const response = await fetch('api/attendance', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            qr_code: qrCodeMessage,
                            timestamp: new Date().toISOString()
                        })
                    });

                    const data = await response.json();
                    
                    if (data.ok) {
                        this.stats.success++;
                        this.showResult(data, 'success');
                        this.showStatus('Présence enregistrée avec succès!', 'success');
                    } else {
                        this.stats.error++;
                        this.showResult({ error: data.error, qr_code: qrCodeMessage }, 'error');
                        this.showStatus(`Erreur: ${data.error}`, 'error');
                    }
                } catch (error) {
                    this.stats.error++;
                    console.error('Erreur réseau:', error);
                    this.showResult({ error: 'Erreur de communication', qr_code: qrCodeMessage }, 'error');
                    this.showStatus('Erreur de communication avec le serveur', 'error');
                }

                this.updateStats();

                // Reprendre le scanner après 3 secondes
                setTimeout(async () => {
                    if (this.html5QrCode && this.isScanning) {
                        try {
                            await this.html5QrCode.resume();
                        } catch (error) {
                            console.error('Erreur reprise scanner:', error);
                        }
                    }
                }, 3000);
            }

            showStatus(message, type) {
                const alertClass = {
                    'success': 'alert-success',
                    'error': 'alert-error',
                    'info': 'alert-info'
                }[type] || 'alert-info';

                this.elements.statusContainer.innerHTML = `
                    <div class="alert ${alertClass} animate-fadeIn">
                        ${message}
                    </div>
                `;

                setTimeout(() => {
                    this.elements.statusContainer.innerHTML = '';
                }, 5000);
            }

            showResult(data, type) {
                const isSuccess = type === 'success';
                const icon = isSuccess ? 
                    `<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>` :
                    `<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>`;

                const resultHtml = `
                    <div class="scan-result ${isSuccess ? '' : 'error'} animate-fadeIn">
                        <div class="scan-result-header">
                            <div class="scan-result-icon ${type}">
                                ${icon}
                            </div>
                            <div class="scan-result-title">
                                ${isSuccess ? 'Présence enregistrée' : 'Erreur de scan'}
                            </div>
                        </div>
                        <div class="scan-result-details">
                            ${isSuccess ? `
                                <div class="scan-result-detail">
                                    <span class="scan-result-label">Étudiant</span>
                                    <span class="scan-result-value">${data.student_name || 'N/A'}</span>
                                </div>
                                <div class="scan-result-detail">
                                    <span class="scan-result-label">Statut</span>
                                    <span class="scan-result-value">${data.status || 'Présent'}</span>
                                </div>
                                <div class="scan-result-detail">
                                    <span class="scan-result-label">Session</span>
                                    <span class="scan-result-value">${data.session_name || 'N/A'}</span>
                                </div>
                            ` : `
                                <div class="scan-result-detail">
                                    <span class="scan-result-label">Code scanné</span>
                                    <span class="scan-result-value font-mono">${data.qr_code}</span>
                                </div>
                                <div class="scan-result-detail">
                                    <span class="scan-result-label">Erreur</span>
                                    <span class="scan-result-value">${data.error}</span>
                                </div>
                            `}
                            <div class="scan-result-detail">
                                <span class="scan-result-label">Heure</span>
                                <span class="scan-result-value">${new Date().toLocaleTimeString('fr-FR')}</span>
                            </div>
                        </div>
                    </div>
                `;

                this.elements.resultContainer.innerHTML = resultHtml;

                // Auto-hide après 10 secondes
                setTimeout(() => {
                    const result = this.elements.resultContainer.querySelector('.scan-result');
                    if (result) {
                        result.style.opacity = '0';
                        result.style.transform = 'translateY(-20px)';
                        setTimeout(() => {
                            this.elements.resultContainer.innerHTML = '';
                        }, 300);
                    }
                }, 10000);
            }

            updateStats() {
                this.elements.totalScans.textContent = this.stats.total;
                this.elements.successScans.textContent = this.stats.success;
                this.elements.errorScans.textContent = this.stats.error;
            }
        }

        // Initialiser le scanner
        document.addEventListener('DOMContentLoaded', () => {
            new ProfessionalQRScanner();
        });
    </script>
</body>
</html>