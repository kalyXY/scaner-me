# Installation - QR Attendance System (PHP Natif)

## Prérequis

- **XAMPP** avec PHP 8.0.30 et MySQL
- Navigateur web moderne (Chrome, Firefox, Safari, Edge)

## Installation

### 1. Installation de XAMPP

1. Téléchargez XAMPP depuis [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Installez XAMPP avec PHP 8.0.30
3. Démarrez Apache et MySQL depuis le panneau de contrôle XAMPP

### 2. Configuration de la base de données

1. Ouvrez phpMyAdmin : [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Créez une nouvelle base de données nommée `school_mvp`
3. Importez le fichier `schema.sql` dans cette base de données

### 3. Installation de l'application

1. Copiez tous les fichiers de l'application dans le dossier `htdocs` de XAMPP
   - Exemple : `C:\xampp\htdocs\qr-attendance\`

2. Vérifiez la configuration dans `config_native.php` :
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_PORT', '3306');
   define('DB_NAME', 'school_mvp');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Vide par défaut pour XAMPP
   ```

### 4. Test de l'installation

1. Ouvrez votre navigateur
2. Accédez à : [http://localhost/qr-attendance/public/](http://localhost/qr-attendance/public/)
3. Vous devriez voir le tableau de bord

## Utilisation

### Accès aux différentes pages

- **Tableau de bord** : [http://localhost/qr-attendance/public/](http://localhost/qr-attendance/public/)
- **Scanner QR** : [http://localhost/qr-attendance/public/scanner](http://localhost/qr-attendance/public/scanner)

### API Endpoints

- `GET /api/health` - Vérification de l'état du système
- `GET /api/students` - Liste des étudiants
- `POST /api/students` - Ajouter un étudiant
- `POST /api/attendance` - Marquer la présence via QR code
- `GET /api/attendance` - Obtenir les données de présence
- `GET /api/export/csv` - Exporter en CSV

### Scanner QR Code

1. Accédez au scanner : [http://localhost/qr-attendance/public/scanner](http://localhost/qr-attendance/public/scanner)
2. Cliquez sur "Démarrer le scanner"
3. Autorisez l'accès à la caméra
4. Pointez vers un QR code d'étudiant
5. La présence sera automatiquement enregistrée

## Structure de l'application

```
/
├── autoload.php           # Autoloader PHP natif
├── bootstrap.php          # Bootstrap de l'application
├── config_native.php      # Configuration pour XAMPP
├── schema.sql            # Structure de la base de données
├── public/               # Dossier public (point d'entrée web)
│   ├── index.php         # Routeur principal
│   ├── dashboard.php     # Tableau de bord
│   └── scanner.php       # Interface de scan QR
└── src/                  # Code source de l'application
    ├── Config/           # Classes de configuration
    ├── Controllers/      # Contrôleurs
    ├── Models/          # Modèles de données
    ├── Services/        # Services métier
    └── Utils/           # Fonctions utilitaires
```

## Dépannage

### Erreur de connexion à la base de données

1. Vérifiez que MySQL est démarré dans XAMPP
2. Vérifiez les paramètres de connexion dans `config_native.php`
3. Vérifiez que la base de données `school_mvp` existe

### Erreur de caméra dans le scanner

1. Vérifiez que vous utilisez HTTPS ou localhost
2. Autorisez l'accès à la caméra dans votre navigateur
3. Vérifiez qu'aucune autre application n'utilise la caméra

### Erreurs PHP

1. Vérifiez que vous utilisez PHP 8.0.30 ou supérieur
2. Activez l'affichage des erreurs en mode développement
3. Vérifiez les logs d'erreur de XAMPP

## Fonctionnalités

- ✅ Scan de QR codes via caméra web
- ✅ Gestion des présences
- ✅ Tableau de bord avec statistiques
- ✅ Export CSV
- ✅ API REST
- ✅ Interface responsive
- ✅ Compatible PHP 8.0.30 natif
- ✅ Base de données MySQL via XAMPP

## Support

Cette version utilise uniquement PHP natif 8.0.30, MySQL via XAMPP et JavaScript vanilla. 
Aucune dépendance externe n'est requise via Composer.