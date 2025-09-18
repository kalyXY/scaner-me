# 🎓 Système de Présence QR Professionnel

Un système professionnel de gestion des présences et examens utilisant des codes QR, construit avec PHP 8.2, MySQL et une architecture MVC moderne.

## ✨ Fonctionnalités

- 📱 **Scanner QR Moderne** - Interface caméra avec scan en temps réel
- 📊 **Tableau de bord professionnel** - Interface responsive avec Bootstrap 5
- 🎯 **Contrôle d'examens** - Vérification automatique des paiements
- 📈 **Rapports détaillés** - Statistiques de présence par session et étudiant
- 💳 **Gestion des paiements** - Suivi des paiements d'examens
- 🔐 **Autorisations d'examen** - Contrôle d'accès basé sur les paiements
- 📄 **Export CSV** - Export des données de présence
- 🏗️ **Architecture MVC** - Code structuré et maintenable
- 🔍 **Logging avancé** - Traçabilité complète des actions
- 🚀 **API RESTful** - Endpoints modernes avec réponses JSON
- 🐳 **Docker Ready** - Déploiement simplifié avec Docker
- 📱 **Mobile First** - Optimisé pour smartphones et tablettes

## 🛠️ Prérequis

- **Docker & Docker Compose** (recommandé)
- Ou **PHP 8.1+**, **MySQL 8.0+**, **Composer** (installation manuelle)

## ⚡ Installation Rapide (Docker)

1. **Cloner et démarrer**
   ```bash
   git clone <repository-url>
   cd qr-attendance-system
   make up
   ```

2. **Installer les dépendances**
   ```bash
   docker compose exec app composer install
   ```

3. **Configurer la base de données**
   ```bash
   docker compose exec db mysql -u root -p < schema.sql
   ```

4. **Initialiser avec des données de test**
   ```bash
   docker compose exec app php scripts/seed_sample_data.php --clear
   ```

5. **Accéder à l'application**
   - Dashboard: http://localhost:8080/dashboard
   - Scanner QR: http://localhost:8080/scanner
   - API Health: http://localhost:8080/api/health

## 🔧 Configuration

### Variables d'environnement

Copiez `.env.example` vers `.env` et ajustez les valeurs :

```bash
cp .env.example .env
```

Variables principales :
- `APP_ENV` - Environnement (development/production)
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` - Configuration base de données
- `LOG_LEVEL` - Niveau de logging (DEBUG/INFO/WARNING/ERROR)

### Configuration avancée

Le fichier `config/app.php` contient toutes les configurations système :
- Paramètres base de données
- Configuration logging
- Paramètres sécurité
- Options QR codes

## 📡 API Endpoints

### Présences
- `POST /api/scan` - Enregistrer une présence
- `GET /api/sessions?date=YYYY-MM-DD` - Sessions du jour
- `GET /api/attendance/session?session_id=ID` - Présences d'une session
- `GET /api/attendance/date?date=YYYY-MM-DD` - Présences du jour

### Système
- `GET /api/health` - État de santé du système
- `GET /api/dashboard` - Données du tableau de bord
- `GET /api/export/csv` - Export CSV des présences

### Exemples d'utilisation

**Enregistrer une présence :**
```bash
curl -X POST http://localhost:8080/api/scan \
  -H "Content-Type: application/json" \
  -d '{"uuid": "00000000-0000-0000-0000-000000000001"}'
```

**Obtenir les sessions du jour :**
```bash
curl http://localhost:8080/api/sessions?date=2024-01-15
```

## 🧪 Test Rapide

**Données de test disponibles :**
- 8 étudiants avec UUIDs de `00000000-0000-0000-0000-000000000001` à `000000008`
- Sessions programmées pour aujourd'hui
- 4 premiers étudiants ont payé leurs frais d'examen

**Test du scanner :**
1. Aller sur http://localhost:8080/scanner
2. Cliquer "Saisie Manuelle"
3. Entrer : `00000000-0000-0000-0000-000000000001`
4. Valider → Doit afficher les infos de Alice Dupont

**Voir le guide complet :** [TESTING.md](TESTING.md)

## 🏗️ Architecture

```
src/
├── Controllers/     # Contrôleurs MVC
├── Models/         # Modèles de données
├── Services/       # Logique métier
├── Config/         # Configuration système
├── Exceptions/     # Exceptions personnalisées
└── Utils/          # Fonctions utilitaires

templates/          # Templates Twig
public/            # Point d'entrée web
tests/             # Tests unitaires et d'intégration
```

## 🎯 Génération des codes QR

```bash
# Générer tous les codes QR des étudiants actifs
php scripts/generate_qr.php

# Ou via Docker
docker compose exec app php scripts/generate_qr.php
```

Les codes QR sont générés dans `assets/qr/` et contiennent l'URL de scan avec l'UUID de l'étudiant.

## 🧪 Tests et Qualité

```bash
# Tests unitaires
composer test

# Analyse statique
composer analyse

# Vérification du style de code
composer cs-check

# Correction automatique du style
composer cs-fix
```

## 📊 Monitoring et Logs

### Logs
- **Emplacement** : `logs/app.log`
- **Rotation** : 30 jours
- **Niveaux** : DEBUG, INFO, WARNING, ERROR, CRITICAL

### Health Check
```bash
curl http://localhost:8080/api/health
```

Vérifie :
- Connexion base de données
- Système de fichiers
- Extensions PHP requises

## 🚀 Déploiement Production

### Docker Production
```bash
# Build image de production
docker build -t qr-attendance:latest .

# Démarrer en production
docker run -d \
  --name qr-attendance \
  -p 80:80 \
  -e APP_ENV=production \
  -e DB_HOST=your-db-host \
  -e DB_NAME=school_mvp \
  -e DB_USER=your-user \
  -e DB_PASS=your-password \
  qr-attendance:latest
```

### Optimisations Production
- **OPcache** activé pour de meilleures performances
- **Logs** configurés pour la production
- **Headers de sécurité** configurés
- **Compression gzip** activée

## 🔒 Sécurité

- **Validation d'entrée** stricte
- **Échappement des sorties** automatique
- **Headers de sécurité** configurés
- **Logging des tentatives** d'accès
- **Protection CSRF** (à venir)

## 📚 Base de Données

### Structure principale
- `students` - Étudiants et leurs UUIDs
- `courses` - Cours et matières
- `course_sessions` - Sessions de cours planifiées
- `attendance` - Enregistrements de présence
- `payments` - Paiements d'examens
- `exam_authorizations` - Autorisations d'examen

### Migrations
```bash
# Importer le schéma initial
mysql -u root -p school_mvp < schema.sql
```

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/amazing-feature`)
3. Commit les changements (`git commit -m 'Add amazing feature'`)
4. Push vers la branche (`git push origin feature/amazing-feature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 🆘 Support

Pour toute question ou problème :
1. Vérifiez les logs : `tail -f logs/app.log`
2. Testez le health check : `curl http://localhost:8080/api/health`
3. Consultez la documentation API intégrée

---

**Version 2.0** - Système professionnel de gestion des présences QR

