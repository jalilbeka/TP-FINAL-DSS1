/* ============================================================
   DSS University — Internationalization (i18n)
   ============================================================ */

const translations = {
  en: {
    /* Auth */
    'auth.title': 'DSS University',
    'auth.subtitle': 'Decision Support System',
    'auth.login': 'Login',
    'auth.register': 'Register',
    'auth.username': 'Username',
    'auth.password': 'Password',
    'auth.email': 'Email Address',
    'auth.fullname': 'Full Name',
    'auth.login_btn': 'Sign In',
    'auth.register_btn': 'Create Account',
    'auth.no_account': "Don't have an account?",
    'auth.has_account': 'Already have an account?',
    'auth.switch_register': 'Register here',
    'auth.switch_login': 'Login here',
    'auth.error_empty': 'Please fill in all fields',
    'auth.error_credentials': 'Invalid username or password',
    'auth.error_exists': 'Username already exists',
    'auth.register_success': 'Account created successfully',

    /* Navigation */
    'nav.dashboard': 'Dashboard',
    'nav.students': 'Students',
    'nav.specializations': 'Specializations',
    'nav.criteria': 'Criteria',
    'nav.evaluations': 'Evaluations',
    'nav.results': 'Results',

    /* Header */
    'header.title': 'DSS University',
    'header.logout': 'Logout',
    'header.role_admin': 'Admin',
    'header.role_student': 'Student',

    /* Dashboard */
    'dashboard.title': 'Dashboard Overview',
    'dashboard.total_students': 'Total Students',
    'dashboard.total_specs': 'Specializations',
    'dashboard.total_criteria': 'Criteria',
    'dashboard.quick_stats': 'Quick Statistics',
    'dashboard.recent_activity': 'Recent Activity',
    'dashboard.welcome': 'Welcome back',
    'dashboard.evaluations_done': 'Evaluations Completed',
    'dashboard.avg_score': 'Average Score',

    /* Students */
    'students.title': 'Students Management',
    'students.add': 'Add Student',
    'students.id': 'ID',
    'students.username': 'Username',
    'students.fullname': 'Full Name',
    'students.email': 'Email',
    'students.role': 'Role',
    'students.actions': 'Actions',
    'students.edit': 'Edit Student',
    'students.delete_confirm': 'Are you sure you want to delete this student?',
    'students.my_profile': 'My Profile',
    'students.search': 'Search students...',

    /* Specializations */
    'specs.title': 'Specializations Management',
    'specs.add': 'Add Specialization',
    'specs.id': 'ID',
    'specs.name': 'Name',
    'specs.code': 'Code',
    'specs.description': 'Description',
    'specs.actions': 'Actions',
    'specs.edit': 'Edit Specialization',
    'specs.delete_confirm': 'Are you sure you want to delete this specialization?',
    'specs.ai': 'Artificial Intelligence',
    'specs.cybersecurity': 'Cybersecurity',
    'specs.data_science': 'Data Science',
    'specs.isse': 'ISSE',
    'specs.ai_desc': 'Machine learning, neural networks, and intelligent systems',
    'specs.cybersecurity_desc': 'Network security, cryptography, and ethical hacking',
    'specs.data_science_desc': 'Data analysis, statistics, and big data technologies',
    'specs.isse_desc': 'Information Systems and Software Engineering',

    /* Criteria */
    'criteria.title': 'Criteria Management',
    'criteria.add': 'Add Criterion',
    'criteria.id': 'ID',
    'criteria.name': 'Name',
    'criteria.type': 'Type',
    'criteria.weight': 'Weight',
    'criteria.actions': 'Actions',
    'criteria.edit': 'Edit Criterion',
    'criteria.delete_confirm': 'Are you sure you want to delete this criterion?',
    'criteria.maximize': 'Maximize',
    'criteria.minimize': 'Minimize',
    'criteria.programming': 'Programming',
    'criteria.mathematics': 'Mathematics',
    'criteria.competency': 'Competency Alignment',
    'criteria.career': 'Career Opportunities',
    'criteria.difficulty': 'Difficulty',
    'criteria.interest': 'Personal Interest',

    /* Evaluations */
    'eval.title': 'Evaluation Matrix',
    'eval.select_student': 'Select Student',
    'eval.save': 'Save Evaluations',
    'eval.saving': 'Saving...',
    'eval.saved': 'Evaluations saved successfully',
    'eval.error_range': 'Scores must be between 1 and 10',
    'eval.error_incomplete': 'Please fill all scores',
    'eval.specialization': 'Specialization',
    'eval.score_hint': 'Enter scores from 1 (lowest) to 10 (highest)',
    'eval.no_student': 'Please select a student to evaluate',
    'eval.preview': 'Preview Results',

    /* Results */
    'results.title': 'Results & Ranking',
    'results.select_student': 'Select Student',
    'results.rank': 'Rank',
    'results.specialization': 'Specialization',
    'results.score': 'Overall Score',
    'results.normalized': 'Normalized Score',
    'results.recommendation': 'Recommendation',
    'results.recommended_spec': 'Recommended Specialization',
    'results.recommended_label': 'Recommended',
    'results.bar_chart_title': 'Overall Scores by Specialization',
    'results.radar_chart_title': 'Criteria Comparison',
    'results.no_data': 'No evaluation data available. Complete evaluations first.',
    'results.calculate': 'Calculate Results',
    'results.score_breakdown': 'Score Breakdown',
    'results.weighted_score': 'Weighted Score',

    /* Common */
    'common.save': 'Save',
    'common.cancel': 'Cancel',
    'common.delete': 'Delete',
    'common.edit': 'Edit',
    'common.add': 'Add',
    'common.confirm': 'Confirm',
    'common.loading': 'Loading...',
    'common.error': 'An error occurred',
    'common.success': 'Operation successful',
    'common.no_data': 'No data available',
    'common.actions': 'Actions',
    'common.close': 'Close',
    'common.search': 'Search...',
    'common.yes': 'Yes',
    'common.no': 'No',
    'common.of': 'of',
    'common.total': 'Total',
    'common.back': 'Back',
    'common.next': 'Next',
    'common.required': 'Required',
  },

  fr: {
    /* Auth */
    'auth.title': 'DSS Université',
    'auth.subtitle': "Système d'Aide à la Décision",
    'auth.login': 'Connexion',
    'auth.register': "S'inscrire",
    'auth.username': "Nom d'utilisateur",
    'auth.password': 'Mot de passe',
    'auth.email': 'Adresse e-mail',
    'auth.fullname': 'Nom complet',
    'auth.login_btn': 'Se connecter',
    'auth.register_btn': 'Créer un compte',
    'auth.no_account': "Vous n'avez pas de compte ?",
    'auth.has_account': 'Vous avez déjà un compte ?',
    'auth.switch_register': "S'inscrire ici",
    'auth.switch_login': 'Se connecter ici',
    'auth.error_empty': 'Veuillez remplir tous les champs',
    'auth.error_credentials': 'Nom d\'utilisateur ou mot de passe invalide',
    'auth.error_exists': "Le nom d'utilisateur existe déjà",
    'auth.register_success': 'Compte créé avec succès',

    /* Navigation */
    'nav.dashboard': 'Tableau de bord',
    'nav.students': 'Étudiants',
    'nav.specializations': 'Spécialisations',
    'nav.criteria': 'Critères',
    'nav.evaluations': 'Évaluations',
    'nav.results': 'Résultats',

    /* Header */
    'header.title': 'DSS Université',
    'header.logout': 'Déconnexion',
    'header.role_admin': 'Admin',
    'header.role_student': 'Étudiant',

    /* Dashboard */
    'dashboard.title': "Vue d'ensemble",
    'dashboard.total_students': 'Total Étudiants',
    'dashboard.total_specs': 'Spécialisations',
    'dashboard.total_criteria': 'Critères',
    'dashboard.quick_stats': 'Statistiques rapides',
    'dashboard.recent_activity': 'Activité récente',
    'dashboard.welcome': 'Bienvenue',
    'dashboard.evaluations_done': 'Évaluations complétées',
    'dashboard.avg_score': 'Score moyen',

    /* Students */
    'students.title': 'Gestion des Étudiants',
    'students.add': 'Ajouter un Étudiant',
    'students.id': 'ID',
    'students.username': "Nom d'utilisateur",
    'students.fullname': 'Nom complet',
    'students.email': 'E-mail',
    'students.role': 'Rôle',
    'students.actions': 'Actions',
    'students.edit': "Modifier l'Étudiant",
    'students.delete_confirm': 'Êtes-vous sûr de vouloir supprimer cet étudiant ?',
    'students.my_profile': 'Mon Profil',
    'students.search': 'Rechercher des étudiants...',

    /* Specializations */
    'specs.title': 'Gestion des Spécialisations',
    'specs.add': 'Ajouter une Spécialisation',
    'specs.id': 'ID',
    'specs.name': 'Nom',
    'specs.code': 'Code',
    'specs.description': 'Description',
    'specs.actions': 'Actions',
    'specs.edit': 'Modifier la Spécialisation',
    'specs.delete_confirm': 'Êtes-vous sûr de vouloir supprimer cette spécialisation ?',
    'specs.ai': 'Intelligence Artificielle',
    'specs.cybersecurity': 'Cybersécurité',
    'specs.data_science': 'Science des Données',
    'specs.isse': 'ISSE',
    'specs.ai_desc': "Apprentissage automatique, réseaux de neurones et systèmes intelligents",
    'specs.cybersecurity_desc': "Sécurité des réseaux, cryptographie et piratage éthique",
    'specs.data_science_desc': "Analyse de données, statistiques et technologies big data",
    'specs.isse_desc': "Systèmes d'Information et Génie Logiciel",

    /* Criteria */
    'criteria.title': 'Gestion des Critères',
    'criteria.add': 'Ajouter un Critère',
    'criteria.id': 'ID',
    'criteria.name': 'Nom',
    'criteria.type': 'Type',
    'criteria.weight': 'Poids',
    'criteria.actions': 'Actions',
    'criteria.edit': 'Modifier le Critère',
    'criteria.delete_confirm': 'Êtes-vous sûr de vouloir supprimer ce critère ?',
    'criteria.maximize': 'Maximiser',
    'criteria.minimize': 'Minimiser',
    'criteria.programming': 'Programmation',
    'criteria.mathematics': 'Mathématiques',
    'criteria.competency': 'Alignement des Compétences',
    'criteria.career': 'Opportunités de Carrière',
    'criteria.difficulty': 'Difficulté',
    'criteria.interest': 'Intérêt Personnel',

    /* Evaluations */
    'eval.title': "Matrice d'Évaluation",
    'eval.select_student': 'Sélectionner un Étudiant',
    'eval.save': 'Enregistrer les Évaluations',
    'eval.saving': 'Enregistrement...',
    'eval.saved': 'Évaluations enregistrées avec succès',
    'eval.error_range': 'Les scores doivent être entre 1 et 10',
    'eval.error_incomplete': 'Veuillez remplir tous les scores',
    'eval.specialization': 'Spécialisation',
    'eval.score_hint': 'Entrez des scores de 1 (plus bas) à 10 (plus haut)',
    'eval.no_student': 'Veuillez sélectionner un étudiant à évaluer',
    'eval.preview': 'Aperçu des Résultats',

    /* Results */
    'results.title': 'Résultats & Classement',
    'results.select_student': 'Sélectionner un Étudiant',
    'results.rank': 'Rang',
    'results.specialization': 'Spécialisation',
    'results.score': 'Score Global',
    'results.normalized': 'Score Normalisé',
    'results.recommendation': 'Recommandation',
    'results.recommended_spec': 'Spécialisation Recommandée',
    'results.recommended_label': 'Recommandé',
    'results.bar_chart_title': 'Scores globaux par Spécialisation',
    'results.radar_chart_title': 'Comparaison des Critères',
    'results.no_data': "Aucune donnée d'évaluation disponible. Complétez d'abord les évaluations.",
    'results.calculate': 'Calculer les Résultats',
    'results.score_breakdown': 'Détail des Scores',
    'results.weighted_score': 'Score Pondéré',

    /* Common */
    'common.save': 'Enregistrer',
    'common.cancel': 'Annuler',
    'common.delete': 'Supprimer',
    'common.edit': 'Modifier',
    'common.add': 'Ajouter',
    'common.confirm': 'Confirmer',
    'common.loading': 'Chargement...',
    'common.error': 'Une erreur est survenue',
    'common.success': 'Opération réussie',
    'common.no_data': 'Aucune donnée disponible',
    'common.actions': 'Actions',
    'common.close': 'Fermer',
    'common.search': 'Rechercher...',
    'common.yes': 'Oui',
    'common.no': 'Non',
    'common.of': 'de',
    'common.total': 'Total',
    'common.back': 'Retour',
    'common.next': 'Suivant',
    'common.required': 'Obligatoire',
  },
};

let currentLanguage = localStorage.getItem('dss_lang') || 'en';

/**
 * Get a translated string by key.
 * @param {string} key  Dot-notation key (e.g. 'auth.login')
 * @returns {string}
 */
function t(key) {
  return translations[currentLanguage]?.[key] ?? translations['en']?.[key] ?? key;
}

/**
 * Set the active language and persist to localStorage.
 * @param {'en'|'fr'} lang
 */
function setLanguage(lang) {
  if (!translations[lang]) return;
  currentLanguage = lang;
  localStorage.setItem('dss_lang', lang);
  applyTranslations();
}

/**
 * @returns {'en'|'fr'}
 */
function getCurrentLanguage() {
  return currentLanguage;
}

/**
 * Walk the DOM and update every element carrying a `data-i18n` attribute.
 * Supports `data-i18n-placeholder` for input placeholders and
 * `data-i18n-title` for title attributes.
 */
function applyTranslations() {
  document.querySelectorAll('[data-i18n]').forEach((el) => {
    const key = el.getAttribute('data-i18n');
    const value = t(key);
    if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
      el.placeholder = value;
    } else {
      el.textContent = value;
    }
  });

  document.querySelectorAll('[data-i18n-placeholder]').forEach((el) => {
    el.placeholder = t(el.getAttribute('data-i18n-placeholder'));
  });

  document.querySelectorAll('[data-i18n-title]').forEach((el) => {
    el.title = t(el.getAttribute('data-i18n-title'));
  });
}

// Make available globally
window.i18n = { t, setLanguage, getCurrentLanguage, applyTranslations };
