# 🎓 Système de Présence QR - PHP Natif

Un système professionnel de gestion des présences et examens utilisant des codes QR, développé en **PHP 8.0.30 natif** avec **MySQL via XAMPP** et **JavaScript**.

## ✨ Fonctionnalités

- 📱 **Scanner QR Moderne** - Interface caméra avec scan en temps réel (JavaScript natif)
- 📊 **Tableau de bord professionnel** - Interface responsive pure CSS/HTML
- 🎯 **Contrôle d'examens** - Vérification automatique des paiements
- 📈 **Rapports détaillés** - Statistiques de présence par session et étudiant
- 💳 **Gestion des paiements** - Suivi des paiements d'examens
- 🔐 **Autorisations d'examen** - Contrôle d'accès basé sur les paiements
- 📄 **Export CSV** - Export des données de présence
- 🏗️ **Architecture MVC** - Code structuré et maintenable
- 🔍 **Logging simple** - Traçabilité des actions
- 🚀 **API RESTful** - Endpoints modernes avec réponses JSON
- 📱 **Mobile First** - Optimisé pour smartphones et tablettes
- ⚡ **Zéro dépendance** - PHP natif uniquement, pas de Composer

## 🛠️ Prérequis

- **XAMPP** avec PHP 8.0.30 et MySQL
- Navigateur web moderne (Chrome, Firefox, Safari, Edge)

## ⚡ Installation Rapide

1. **Installer XAMPP**
   - Téléchargez depuis [apachefriends.org](https://www.apachefriends.org/)
   - Installez avec PHP 8.0.30
   - Démarrez Apache et MySQL

2. **Copier les fichiers**
   ```bash
   # Copiez tous les fichiers dans htdocs/qr-attendance/
   # Exemple: C:\xampp\htdocs\qr-attendance\
   ```

3. **Configurer la base de données**
   - Ouvrez phpMyAdmin: http://localhost/phpmyadmin
   - Créez la base `school_mvp`
   - Importez `schema.sql`

4. **Tester l'installation**
   ```bash
   # Ouvrez dans votre navigateur:
   http://localhost/qr-attendance/test_installation.php
   ```

5. **Accéder à l'application**
   - Dashboard: http://localhost/qr-attendance/public/
   - Scanner QR: http://localhost/qr-attendance/public/scanner
   - API Health: http://localhost/qr-attendance/public/api/health

## 🔧 Configuration

### Fichier de configuration : `config_native.php`

```php
// Configuration XAMPP par défaut
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'school_mvp');
define('DB_USER', 'root');
define('DB_PASS', ''); // Vide pour XAMPP par défaut
```

### Variables d'environnement
- `APP_ENV` - development/production
- `APP_NAME` - Nom de l'application
- Configuration timezone (Europe/Paris par défaut)

## 📡 API Endpoints

### Présences
- `POST /api/attendance` - Enregistrer une présence
- `GET /api/attendance` - Obtenir les présences
- `GET /api/students` - Liste des étudiants
- `POST /api/students` - Ajouter un étudiant

### Système
- `GET /api/health` - État de santé du système
- `GET /api/export/csv` - Export CSV des présences

### Exemples d'utilisation

**Enregistrer une présence :**
```bash
curl -X POST http://localhost/qr-attendance/public/api/attendance \
  -H "Content-Type: application/json" \
  -d '{"qr_code": "00000000-0000-0000-0000-000000000001"}'
```

**Health Check :**
```bash
curl http://localhost/qr-attendance/public/api/health
```

## 🧪 Test Rapide

1. **Tester l'installation**
   ```
   http://localhost/qr-attendance/test_installation.php
   ```

2. **Test du scanner**
   - Aller sur http://localhost/qr-attendance/public/scanner
   - Cliquer "Démarrer le scanner"
   - Autoriser l'accès caméra
   - Scanner un QR code ou saisir manuellement un UUID

3. **Données de test**
   - Exécutez `scripts/seed_sample_data.php` pour des données d'exemple
   - UUIDs de test : `00000000-0000-0000-0000-000000000001` à `000000008`

## 🏗️ Architecture PHP Natif

```
/
├── autoload.php           # Autoloader PHP natif simple
├── bootstrap.php          # Bootstrap de l'application
├── config_native.php      # Configuration XAMPP
├── test_installation.php  # Script de test
├── public/               # Point d'entrée web
│   ├── index.php         # Routeur principal
│   ├── dashboard.php     # Tableau de bord
│   └── scanner.php       # Interface de scan QR
└── src/                  # Code source
    ├── Config/           # Classes de configuration
    ├── Controllers/      # Contrôleurs MVC
    ├── Models/          # Modèles de données
    ├── Services/        # Services métier
    └── Utils/           # Fonctions utilitaires
```

## 🎯 Scanner QR Code

Le scanner utilise la bibliothèque JavaScript `html5-qrcode` :
- Interface moderne et responsive
- Support caméra temps réel
- Gestion d'erreurs complète
- Feedback visuel immédiat
- Compatible mobile/desktop

## 📊 Base de Données MySQL

### Structure principale
- `students` - Étudiants et leurs UUIDs
- `courses` - Cours et matières  
- `course_sessions` - Sessions de cours planifiées
- `attendance` - Enregistrements de présence
- `payments` - Paiements d'examens
- `exam_authorizations` - Autorisations d'examen

### Import du schéma
```sql
-- Via phpMyAdmin ou ligne de commande
mysql -u root school_mvp < schema.sql
```

## 🔒 Sécurité

- **Validation d'entrée** stricte avec fonctions natives PHP
- **Échappement des sorties** avec `htmlspecialchars()`
- **Requêtes préparées** PDO pour éviter les injections SQL
- **Headers de sécurité** configurés
- **Logging des erreurs** en mode développement

## 🚀 Avantages PHP Natif

- ✅ **Zéro dépendance** - Pas de Composer, vendor/ ou autoload complexe
- ✅ **Performance** - Pas de surcharge de frameworks
- ✅ **Simplicité** - Code facile à comprendre et maintenir
- ✅ **Compatibilité** - Fonctionne sur tout serveur PHP 8.0.30+
- ✅ **Débogage** - Erreurs claires et traçables
- ✅ **Portable** - Copiez/collez et ça fonctionne

## 📚 Documentation

- `INSTALLATION.md` - Guide d'installation détaillé
- `API.md` - Documentation complète de l'API
- `TESTING.md` - Guide de test complet

## 🆘 Dépannage

### Erreurs courantes

1. **Erreur de connexion DB**
   - Vérifiez que XAMPP MySQL est démarré
   - Vérifiez les paramètres dans `config_native.php`

2. **Erreur de caméra**
   - Utilisez HTTPS ou localhost
   - Autorisez l'accès caméra dans le navigateur

3. **Erreur 500**
   - Vérifiez les logs d'erreur XAMPP
   - Activez `display_errors` en développement

### Support

1. Exécutez `test_installation.php` pour diagnostiquer
2. Vérifiez les logs d'erreur XAMPP
3. Consultez la documentation dans `/docs`

---

**Version 2.0 PHP Natif** - Système professionnel sans dépendances externes

Compatible PHP 8.0.30 | MySQL via XAMPP | JavaScript Vanilla