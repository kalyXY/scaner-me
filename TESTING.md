# 🧪 Guide de Test - Système de Présence QR

Ce guide vous explique comment tester complètement le système de présence et de contrôle des examens par QR code.

## 🚀 Installation et Configuration

### Option 1: Docker (Recommandée)

```bash
# Cloner le projet
git clone <repository-url>
cd qr-attendance-system

# Démarrer les services
make up
# ou
docker compose up -d

# Installer les dépendances PHP
docker compose exec app composer install

# Configurer la base de données
docker compose exec db mysql -u root -p < schema.sql

# Générer des données de test
docker compose exec app php scripts/seed_sample_data.php --clear
```

### Option 2: Installation Manuelle

```bash
# Prérequis: PHP 8.1+, MySQL 8.0+, Composer

# Installer les dépendances
composer install

# Configurer l'environnement
cp .env.example .env
# Éditer .env avec vos paramètres de base de données

# Créer la base de données
mysql -u root -p < schema.sql

# Générer des données de test
php scripts/seed_sample_data.php --clear
```

## 📋 Données de Test

Le script `seed_sample_data.php` crée les données suivantes :

### 👥 Étudiants (8 au total)
- **Alice Dupont** (3A) - UUID: `00000000-0000-0000-0000-000000000001`
- **Bob Martin** (3A) - UUID: `00000000-0000-0000-0000-000000000002`
- **Claire Bernard** (3B) - UUID: `00000000-0000-0000-0000-000000000003`
- **David Moreau** (3B) - UUID: `00000000-0000-0000-0000-000000000004`
- **Emma Leroy** (3A) - UUID: `00000000-0000-0000-0000-000000000005`
- **François Roux** (3B) - UUID: `00000000-0000-0000-0000-000000000006`
- **Gabrielle Blanc** (3A) - UUID: `00000000-0000-0000-0000-000000000007`
- **Hugo Garnier** (3B) - UUID: `00000000-0000-0000-0000-000000000008`

### 📚 Cours
- **MATH301** - Mathématiques Avancées (3A) - Lundi 08:00-10:00
- **PHYS301** - Physique Quantique (3A) - Mardi 10:15-12:15
- **INFO301** - Algorithmique (3A) - Mercredi 14:00-16:00
- **MATH302** - Analyse Numérique (3B) - Lundi 14:00-16:00
- **PHYS302** - Thermodynamique (3B) - Mardi 08:00-10:00
- **INFO302** - Base de Données (3B) - Jeudi 10:15-12:15

### 💳 Paiements
- Les 4 premiers étudiants ont payé leurs frais d'examen
- Les 4 derniers n'ont pas encore payé

### 🗓️ Sessions Programmées
- **Aujourd'hui** : 3 sessions de cours normales
- **Demain** : 2 sessions de cours
- **Semaine prochaine** : 3 examens

## 🔍 Tests à Effectuer

### 1. Test du Tableau de Bord

**URL :** `http://localhost:8080/dashboard`

**Vérifications :**
- [ ] Affichage des sessions du jour
- [ ] Compteurs de présences/retards corrects
- [ ] Liste des paiements récents
- [ ] Autorisations d'examens
- [ ] Navigation vers le scanner
- [ ] Export CSV fonctionnel

### 2. Test du Scanner QR

**URL :** `http://localhost:8080/scanner`

#### 2.1 Scan avec Caméra
**Étapes :**
1. Cliquer sur "Démarrer Scanner"
2. Autoriser l'accès à la caméra
3. Générer un QR code de test
4. Scanner le code

#### 2.2 Saisie Manuelle
**Étapes :**
1. Cliquer sur "Saisie Manuelle"
2. Entrer un UUID de test : `00000000-0000-0000-0000-000000000001`
3. Valider

**Résultats attendus :**
- [ ] Affichage des informations de l'étudiant
- [ ] Statut de présence correct (présent/en retard)
- [ ] Messages d'erreur appropriés pour UUID invalide

### 3. Test des Examens

Pour tester le contrôle d'accès aux examens :

1. Créer une session d'examen pour aujourd'hui :
```sql
INSERT INTO course_sessions (course_id, session_date, start_time, end_time, is_exam, exam_id, late_after_minutes) 
VALUES (1, CURDATE(), '14:00:00', '16:00:00', 1, 1, 5);
```

2. Tester avec un étudiant qui a payé (UUID 1-4) :
   - Résultat attendu : "Examen Autorisé" ✅

3. Tester avec un étudiant qui n'a pas payé (UUID 5-8) :
   - Résultat attendu : "Accès Refusé" ❌

### 4. Test des APIs

#### 4.1 Health Check
```bash
curl http://localhost:8080/api/health
```
**Résultat attendu :** `{"ok": true, "status": "healthy", ...}`

#### 4.2 Enregistrement de Présence
```bash
curl -X POST http://localhost:8080/api/scan \
  -H "Content-Type: application/json" \
  -d '{"uuid": "00000000-0000-0000-0000-000000000001"}'
```

#### 4.3 Sessions du Jour
```bash
curl "http://localhost:8080/api/sessions?date=2024-01-15"
```

#### 4.4 Présences d'une Session
```bash
curl "http://localhost:8080/api/attendance/session?session_id=1"
```

### 5. Test de Génération QR

```bash
# Générer tous les QR codes
php scripts/generate_qr.php

# Vérifier la création des fichiers
ls -la assets/qr/
```

**Résultat attendu :** 8 fichiers PNG avec les QR codes des étudiants

## 📱 Test avec Smartphone

### Générer des QR Codes de Test

1. Utiliser un générateur QR en ligne
2. Créer des QR codes avec les URLs :
   - `http://localhost:8080/api/scan?uuid=00000000-0000-0000-0000-000000000001`
   - `http://localhost:8080/api/scan?uuid=00000000-0000-0000-0000-000000000002`
   - etc.

3. Tester le scan avec l'interface web sur mobile

## 🐛 Résolution des Problèmes

### Erreur "No active session"
**Cause :** Aucune session programmée pour l'heure actuelle
**Solution :** 
```sql
-- Créer une session pour maintenant
INSERT INTO course_sessions (course_id, session_date, start_time, end_time, late_after_minutes) 
VALUES (1, CURDATE(), TIME(NOW()), ADDTIME(TIME(NOW()), '02:00:00'), 10);
```

### Erreur "Student not found"
**Cause :** UUID invalide ou étudiant inactif
**Solution :** Vérifier l'UUID dans la table `students`

### Problème de Caméra
**Cause :** Permissions non accordées ou HTTPS requis
**Solution :** 
- Autoriser l'accès caméra
- Utiliser HTTPS en production
- Utiliser la saisie manuelle en test

### Erreur de Base de Données
**Cause :** Configuration incorrecte
**Solution :** Vérifier `.env` et la connexion MySQL

## 📊 Métriques de Test

Lors des tests, vérifiez :

- [ ] **Performance** : Temps de réponse < 1s
- [ ] **Fiabilité** : Aucune erreur 500
- [ ] **UX** : Interface intuitive
- [ ] **Sécurité** : Validation des entrées
- [ ] **Compatibilité** : Fonctionne sur mobile

## 🔄 Tests Automatisés

```bash
# Tests unitaires
composer test

# Analyse statique
composer analyse

# Vérification du style
composer cs-check
```

## 📝 Rapport de Test

Créez un rapport avec :
- ✅ Fonctionnalités testées
- 🐛 Bugs identifiés
- 🚀 Améliorations suggérées
- 📊 Métriques de performance

---

**Note :** Ce système est un MVP. En production, ajoutez :
- Authentification administrative
- Sauvegarde automatique
- Monitoring avancé
- Tests de charge