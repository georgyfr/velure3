---
Task ID: 1
Agent: main
Task: Créer le plugin Velure Core pour personnalisation 100% de la page d'accueil

Work Log:
- Analysé les 9 sections du front-page.php (Hero, Features, Categories, Products, Split Banner, Marquee, Testimonials, Blog, Instagram)
- Analysé inc/dynamic-fields.php (619 lignes) et functions.php (422 lignes)
- Créé la structure du plugin velure-core/ avec 5 fichiers
- Implémenté 11 sous-pages ACF (Sections & Order, Hero, Features, Categories, Products, Banner, Marquee, Testimonials, Blog, Instagram, Global Styles)
- Ajouté le drag-and-drop d'ordre des sections via ACF select multiple
- Ajouté les options avancées par section (padding, colonnes, layout, tri, autoplay, overlay, direction marquee, etc.)
- Ajouté les styles globaux (CSS personnalisé, padding sections, toggle animations)
- Déplacé le CPT velure_testimonial dans le plugin
- Créé les aliases de compatibilité velure3_* -> velure_core_* pour zéro modification du thème
- Mis à jour front-page.php pour rendre les sections dans l'ordre dynamique (switch/case dans foreach)
- Mis à jour functions.php pour déléguer au plugin via action hook velure_core_loaded
- Renommé dynamic-fields.php en dynamic-fields-shim.php (fallback si plugin inactif)
- Push sur GitHub commit 5da224a
- Généré velure3-v1.0.0.zip et velure-core-v1.0.0.zip

Stage Summary:
- Plugin Velure Core v1.0.0 créé avec 100% de la page d'accueil configurable
- Architecture propre : plugin = logique métier, thème = affichage
- Synchronisation via aliases de fonctions + action hook
- 2 zips disponibles : thème + plugin (séparés)