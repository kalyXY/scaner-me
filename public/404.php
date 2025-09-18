<?php
declare(strict_types=1);

// Vérifier si on est appelé directement ou depuis index.php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../bootstrap.php';
}

http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page non trouvée - <?php echo APP_NAME; ?></title>
    
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

        .error-container {
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-2xl);
            padding: var(--space-12);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .error-code {
            font-size: 8rem;
            font-weight: var(--font-weight-bold);
            background: linear-gradient(135deg, var(--primary-600), var(--primary-800));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: var(--space-4);
        }

        .error-title {
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            color: var(--secondary-900);
            margin-bottom: var(--space-4);
        }

        .error-description {
            font-size: var(--font-size-lg);
            color: var(--secondary-600);
            margin-bottom: var(--space-8);
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: var(--space-4);
            justify-content: center;
            flex-wrap: wrap;
        }

        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 20%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .floating-element:nth-child(4) {
            bottom: 10%;
            right: 20%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }

        @media (max-width: 768px) {
            .error-container {
                padding: var(--space-8);
                margin: var(--space-4);
            }

            .error-code {
                font-size: 6rem;
            }

            .error-title {
                font-size: var(--font-size-2xl);
            }

            .error-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <svg class="floating-element" width="60" height="60" viewBox="0 0 24 24" fill="currentColor">
            <path d="M9.5 6.5v3h-3v-3h3M11 5H5v6h6V5zm-1.5 9.5v3h-3v-3h3M11 13H5v6h6v-6zm6.5-6.5v3h-3v-3h3M19 5h-6v6h6V5zm-6.5 9.5v3h-3v-3h3M13 13h6v6h-6v-6zM21 21H3V3h18v18z"/>
        </svg>
        <svg class="floating-element" width="80" height="80" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
        <svg class="floating-element" width="50" height="50" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
        </svg>
        <svg class="floating-element" width="70" height="70" viewBox="0 0 24 24" fill="currentColor">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>

    <div class="error-container animate-on-load">
        <div class="error-code">404</div>
        <h1 class="error-title">Page non trouvée</h1>
        <p class="error-description">
            Désolé, la page que vous cherchez n'existe pas ou a été déplacée. 
            Vérifiez l'URL ou utilisez les liens ci-dessous pour naviguer.
        </p>
        
        <div class="error-actions">
            <a href="/" class="btn btn-primary btn-lg">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                Accueil
            </a>
            
            <a href="scanner" class="btn btn-outline btn-lg">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9.5 6.5v3h-3v-3h3M11 5H5v6h6V5zm-1.5 9.5v3h-3v-3h3M11 13H5v6h6v-6zm6.5-6.5v3h-3v-3h3M19 5h-6v6h6V5zm-6.5 9.5v3h-3v-3h3M13 13h6v6h-6v-6zM21 21H3V3h18v18z"/>
                </svg>
                Scanner QR
            </a>
            
            <button onclick="history.back()" class="btn btn-ghost btn-lg">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.42-1.41L7.83 13H20v-2z"/>
                </svg>
                Retour
            </button>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/app.js"></script>
</body>
</html>