# CLAUDE.md -- ELOQIO AI Content Reservation

Plugin WordPress (PHP 7.4+, WP 6.5+) qui implémente le W3C TDM Reservation
Protocol : il sert `/.well-known/tdmrep.json`, ajoute le header HTTP
`tdm-reservation` et la balise `<meta name="tdm-reservation">` sur le front.
Pas de CLI Python ici, le `~/Code/CLAUDE.md` ne s'applique pas.

## Architecture

- `eloqio-ai-content-reservation.php` : header WP, constantes
  (`ELOQIO_ACR_VERSION`, `ELOQIO_ACR_FILE`, `ELOQIO_ACR_DIR`), require
  manuel des classes (pas de Composer), boot via `plugins_loaded`.
- `src/Plugin.php` : singleton, source de vérité des settings, cache
  mémoire d'une requête. Option WP `eloqio_acr_settings` (clé
  `Plugin::OPTION_KEY`), defaults dans `Plugin::DEFAULTS`. Les settings
  passent par le filtre `eloqio_acr_settings` : un thème ou un plugin peut
  ainsi piloter la réservation depuis une source unique (ex. choreo-engine
  en SSOT de la politique IA). Le plugin reste autonome sans hook.
- `src/Endpoint.php` : sert le JSON sur hook `init` priorité 0 en
  matchant `REQUEST_URI`. Pas de rewrite rule, pas de fichier physique
  (compatible Let's Encrypt et hosts read-only).
- `src/Headers.php` : `send_headers` pour le HTTP header,
  `wp_head` priorité 1 pour la meta. Skip si admin ou plugin désactivé.
- `src/Settings.php` : page `Settings → AI Content Reservation`, slug
  `eloqio-ai-content-reservation`, group `eloqio_acr`.
- `src/SiteHealth.php` : test async via REST namespace `eloqio-acr/v1`,
  endpoint `/site-health-endpoint`. Retry sans `sslverify` si erreur SSL
  (dev local, certs auto-signés).
- `uninstall.php` : drop la seule option `eloqio_acr_settings`.

## Conventions

- Namespace `ELOQIO\AiContentReservation`, classes `final`, une classe
  par fichier, nom de fichier = nom de classe.
- Tabs pour l'indentation (WordPress Coding Standards).
- Text domain `eloqio-ai-content-reservation`, traductions dans
  `languages/` (`.pot` + `fr_FR`).
- Tout le user-facing reste en anglais (plugin distribué sur WP.org).

## Build / déploiement

- Pas de build local. Distribution gérée par GitHub Actions
  (`.github/workflows/deploy.yml`) : un tag `v*` pousse vers le SVN
  WordPress.org via `10up/action-wordpress-plugin-deploy`.
- `.distignore` contrôle ce qui part dans le zip WP.org (exclut
  `.claude`, `README.md`, dev tooling).
- Bumps de version à synchroniser dans 3 endroits : header du fichier
  principal, constante `ELOQIO_ACR_VERSION`, `Stable tag` du
  `readme.txt`.

## Tests

- `tests/run-unit.php` : tests unitaires standalone (PHP CLI, zéro
  dépendance, dans l'esprit du plugin — pas de PHPUnit, pas de wp-env).
  Stubs WordPress minimaux en tête de fichier. Lancer : `php tests/run-unit.php`.

## Gotchas

- L'endpoint `/.well-known/tdmrep.json` est intercepté avant le routing
  WP. Si un autre plugin réserve la même URL, l'ordre des hooks `init`
  décide.
- `Headers::register()` no-op si `is_enabled()` est false : pour tester
  une activation/désactivation, vider le cache de `Plugin::settings()`
  (durée de vie = la requête, donc en pratique relancer la requête).
- `tdm_reservation` est forcé à `0` ou `1` côté lecture, jamais
  d'autres valeurs même si l'option est corrompue.
