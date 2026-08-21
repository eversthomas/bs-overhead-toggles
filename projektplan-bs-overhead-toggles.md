# Projektplan: BS Overhead Toggles

**WordPress-Plugin zur zentralen Verwaltung abschaltbarer WordPress-Standardfunktionen**

Status: Planungsbasis für Cursor-Entwicklung
Version: 0.1 (Planungsdokument)

---

## 1. Auftrag an Cursor — vor Beginn der Umsetzung

Bevor mit dem Code begonnen wird, soll Cursor:

1. **Aktuellen WordPress-Core-Stand recherchieren.** Prüfe die aktuell unterstützte WordPress-Core-Version (Stand: WordPress 7.x, Stand August 2026) auf Änderungen an den unten genannten Hooks/Filtern — insbesondere ob Funktionsnamen, Hook-Signaturen oder Standardverhalten sich seit WP 6.x verändert haben (z. B. durch Block-Editor/FSE-Weiterentwicklung).
2. **WordPress Coding Standards (WPCS) und Plugin-Best-Practices recherchieren** und während der gesamten Entwicklung einhalten (siehe Abschnitt 6).
3. **Bestehende vergleichbare Plugins kurz sichten** (z. B. Perfmatters, Disable Bloat, Clearfy) — nicht zum Kopieren, sondern um Fallstricke bei Hook-Reihenfolge und Kompatibilität zu verstehen, die andere Entwickler bereits gelöst haben.
4. Rückfragen stellen, wenn Hook-Verhalten sich in aktuellen WP-Versionen geändert hat und die unten beschriebene Umsetzung dadurch nicht mehr korrekt wäre.

---

## 2. Ziel und Scope

Ein einziges Plugin ersetzt die wiederkehrende Praxis, WordPress-Overhead (Emojis, Teile von Gutenberg, REST-API-Bestandteile, Kopfbereich-Meta, automatisch generierte Frontend-Klassen, Revisions-/Autosave-Verhalten) in jeder `functions.php` neu zu deaktivieren. Zielgruppe: eigene Theme-Projekte (u. a. Bezugs Framework, ortsverein-nv) sowie künftige Kundenprojekte.

**Explizit außerhalb des Scopes:** Sicherheits-Härtung (Login-Schutz, 2FA, Datei-Zugriffsschutz etc.). Das ist konzeptionell ein *hinzufügendes* Sicherheitsplugin, kein *Overhead-reduzierendes* Plugin, und soll bei Bedarf als eigenständiges Geschwisterprojekt (Arbeitstitel „BS Hardening") entstehen — nicht Teil dieses Plugins.

---

## 3. Branding & Konventionen

- Plugin-Name: **BS Overhead Toggles**
- PHP-Prefix: `bsot_` (Funktionen, Optionen, Hooks) — folgt der etablierten Namenskonvention der übrigen BS-Plugins (vgl. `bsf_` bei Bezugs Framework)
- PHP-Namespace (falls objektorientiert aufgebaut): `BS\OverheadToggles`
- JS-Namespace (falls nötig): `BSOT`
- Backend-UI folgt **BS-PluginDesignSystem.md** (Design-Tokens in OKLCH, Typografie, Komponenten-Blueprints) — diese Datei soll Cursor vor dem UI-Aufbau einlesen und konsequent anwenden, damit das Plugin optisch zu den übrigen BS-Plugins passt.
- Kein Build-Step, Vanilla PHP/JS/CSS — keine Frameworks, kein npm-Toolchain-Zwang (Linie aus den übrigen Projekten).
- Textdomain: `bs-overhead-toggles`, i18n-ready (auch wenn zunächst nur Deutsch benötigt wird).

---

## 4. Architektur & Modularität

**Grundprinzip:** Jeder Toggle ist ein eigenständiges, in sich gekapseltes Modul — kein monolithisches „alles in einer Datei"-Plugin.

```
bs-overhead-toggles/
├── bs-overhead-toggles.php        # Plugin-Header, Bootstrap, Autoloading
├── includes/
│   ├── class-bsot-settings.php    # Settings API, Optionsseite, Sanitizing
│   ├── class-bsot-toggle.php      # Basis-Klasse/Interface für ein Toggle-Modul
│   ├── class-bsot-api.php         # Öffentliche Abfrage-API für Themes (siehe 5.4)
│   └── modules/
│       ├── class-emojis.php
│       ├── class-gutenberg.php
│       ├── class-classic-widgets.php
│       ├── class-rest-api.php
│       ├── class-head-cleanup.php
│       ├── class-frontend-classes.php
│       ├── class-xmlrpc.php
│       ├── class-heartbeat.php
│       └── class-revisions-storage.php
├── admin/
│   ├── views/                     # Templates für Optionsseite
│   └── assets/                    # CSS/JS nach BS-PluginDesignSystem
└── languages/
```

**Modul-Interface (Konzept):** Jedes Modul implementiert dieselbe Basisstruktur — `get_id()`, `get_label()`, `get_description()`, `get_impact_text()`, `is_enabled()`, `register()` (hookt sich selbst ein, wenn aktiv). Damit ist ein neues Toggle immer nur eine neue Datei plus Eintrag in der Modul-Registry — keine Änderung an Settings-Seite oder Kernlogik nötig.

---

## 5. Funktionsumfang (Toggle-Katalog)

Jeder Punkt braucht im Backend: **Was macht's / Nutzen / Was kann brechen** (siehe Abschnitt 7).

### 5.1 Frontend-Output
- Emoji-Scripts + DNS-Prefetch entfernen
- Block-Library-CSS entfernen (unabhängig vom Gutenberg-Editor-Toggle, s. u.)
- Dashicons im Frontend entfernen (nur relevant, wenn Adminbar im Frontend sichtbar ist)
- oEmbed-Discovery-Links + JS entfernen
- Feed-Links im Head entfernen

### 5.2 Gutenberg / Editor — granulare Datenstruktur, kein einfaches Bool

Wichtig: **nicht** `gutenberg_disabled: true/false`, sondern zukunftsoffen:

```php
'gutenberg' => [
    'enabled_globally' => false,       // Classic Editor global aktiv
    'post_types'        => [],          // Ausnahmen: hier Gutenberg gezielt wieder erlauben
    'strip_default_css' => true,        // Block-Library-CSS unabhängig regelbar
],
'classic_widgets' => true,             // EIGENER, unabhängiger Toggle — nicht an Gutenberg gekoppelt
```

Begründung für diese Struktur: Aktuell wird Gutenberg komplett deaktiviert, aber Classic Widgets wird gelegentlich weiterhin gebraucht — daher als eigener Schalter. Außerdem ist perspektivisch geplant, eigene PHP-Gutenberg-Blocks zu bauen — dann muss Gutenberg pro Post-Type gezielt wieder aktivierbar sein, ohne die gespeicherte Optionsstruktur später brechen zu müssen.

### 5.3 REST API — granular, kein Total-Schalter

- REST-Discovery-Links aus dem Head entfernen (kosmetisch, kein Sicherheitsgewinn)
- `/wp/v2/users`-Endpoint für nicht authentifizierte Zugriffe sperren (Schutz vor Username-Enumeration)
- REST API für nicht eingeloggte Besucher komplett sperren, mit Whitelist-Feld für Ausnahme-Routen (z. B. eigene bs-fabric-Endpoints, Formular-Endpoints)
- **Kein** Toggle für: Application Passwords, Cookie-Auth, Nonce-Verifizierung — diese Kernmechanismen dürfen nicht abschaltbar angeboten werden (Risiko von Selbstschüssen).

### 5.4 Head-Cleanup (SEO-relevante Core-Ausgaben)

Ziel: eigenständige SEO-Umsetzung ohne SEO-Plugin ermöglichen, ohne `wp_head` als Hook selbst zu entfernen (würde CSS/JS-Enqueues mitreißen). Stattdessen gezielt einzelne Callbacks entfernen:
- `rel_canonical`
- `wp_shortlink_wp_head`
- `wp_generator` (Versionsnummer)
- RSD-Link, WLW-Manifest

### 5.5 Frontend-Klassen-Cleanup (mehrere unabhängige Filter, eigene Toggle-Gruppe)

- `body_class`-Cleanup (unerwünschte Standardklassen wie `postid-*`, `logged-in`, `admin-bar` etc., konfigurierbar welche)
- `post_class`-Cleanup
- Nav-Menu-Klassen-Cleanup (`menu-item-*`, `menu-item-type-*`)
- Bild-/Caption-/Gallery-Klassen-Cleanup
- Gutenberg-Block-Klassen-Cleanup (`wp-block-*`, `has-text-align-*`) — **als „experimentell" markieren**, da fragiler gegenüber WP-Versionsänderungen

Warnhinweis, der in die UI muss: Diese Klassen werden teils von anderen Plugins/Skripten als JS-Hooks genutzt (z. B. Lightbox-Skripte über `.wp-image-*`) — Cleanup kann fremde Funktionalität brechen.

### 5.6 Sonstige Overhead-Reduzierung
- XML-RPC komplett deaktivieren (Warnhinweis: bricht Jetpack und mobile Blogging-Apps, falls genutzt)
- Self-Pingbacks verhindern
- Heartbeat API deaktivieren oder Intervall verlängern (getrennte Optionen für Frontend/Backend/Post-Editor, da unterschiedlich sinnvoll)
- Autosave-Intervall verlängern (`AUTOSAVE_INTERVAL`)

### 5.7 Speicherung/Verlauf
- Post-Revisionen: aus / auf Zahl begrenzt (Standard: unbegrenzt)
- Papierkorb-Aufbewahrungsdauer (`EMPTY_TRASH_DAYS`)

---

## 6. Theme-Integrations-API (wichtig für spätere Theme-Entwicklung)

Damit künftige Themes (inkl. Bezugs Framework, ortsverein-nv-Nachfolger) nicht doppelt gegen dieselben Hooks arbeiten oder falsche Annahmen treffen:

```php
bsot_is_disabled( 'gutenberg' );              // bool
bsot_is_disabled( 'gutenberg', 'post' );       // bool, post-type-spezifisch
bsot_get_option( 'heartbeat_interval' );       // Rohwert für komplexere Theme-Logik
```

Regel für Theme-Code: **Nie hart von einem Toggle-Zustand ausgehen.** Beispiel: `add_theme_support('align-wide')` nur setzen, wenn `!bsot_is_disabled('gutenberg', get_post_type())`. Diese API + Regel gehört als Dokumentationsabschnitt ins Plugin-Readme, damit sie bei jeder neuen Theme-Entwicklung nachschlagbar ist.

Hook-Reihenfolge: Plugin hookt auf `init` bzw. `after_setup_theme` mit definierter, dokumentierter Priorität, damit Ladereihenfolge zwischen Plugin und Theme nicht zufällig ist.

---

## 7. Backend-UI-Anforderungen

- Optionsseite über WordPress Settings API, gruppiert nach den Kategorien aus Abschnitt 5 (Tabs oder Akkordeon — Entscheidung nach BS-PluginDesignSystem-Komponenten)
- **Pro Toggle ein einheitliches Dreier-Schema** als wiederkehrende UI-Komponente:
  - *Was macht's* (technische Kurzbeschreibung)
  - *Nutzen* (warum abschalten)
  - *Bricht es was* (Warnhinweis, falls zutreffend — visuell hervorgehoben, z. B. bei XML-RPC, Gutenberg-Block-Klassen, Revisionen)
- Granulare Toggles (Gutenberg-Post-Types, REST-Whitelist) brauchen erweiterbare Unter-Formulare, nicht nur Checkboxen
- Visuelle Kennzeichnung „experimentell" für fragile Toggles (Block-Klassen-Cleanup)
- Design konsequent nach BS-PluginDesignSystem.md (Tokens, Typografie, Komponenten) — Cursor soll diese Datei vor UI-Umsetzung einlesen

---

## 7a. UX-Richtlinien für die Optionsseite

Grundprinzip: Auch wenn du selbst der primäre Nutzer bist, sollte die Oberfläche so gestaltet sein, dass sie **ohne Erklärung verständlich ist und wenig Klicks für die häufigste Aufgabe braucht** — keine internen Systemkonzepte (Hook-Namen, Filter, interne Modul-IDs) an Stellen sichtbar machen, die für die Entscheidung „an/aus" irrelevant sind.

Konkret für dieses Plugin:

- **Sprache der Erläuterungstexte:** Alltagssprache statt WordPress-Interna. „Entfernt die kleine Versionsnummer, die WordPress standardmäßig im Quellcode hinterlässt" statt „Entfernt `wp_generator` vom `wp_head`-Hook". Technische Hook-Namen können optional in einem eingeklappten „Für Entwickler"-Detail stehen, nicht in der Standardansicht.
- **Gruppierung nach Nutzen, nicht nach WordPress-Subsystem.** Die Kategorien aus Abschnitt 5 sind technisch sauber gegliedert (Frontend-Output, REST API, Head-Cleanup ...), aber für die UI lohnt sich zusätzlich eine Gruppierung nach Motivation — z. B. „Performance", „Sicherheit/Privacy", „Aufräumen im Code" — damit man beim Durchklicken nicht WordPress-Architektur verstehen muss, um zu wissen, wo ein Toggle sitzt.
- **Wenig Klicks für den Hauptfall:** Da 90 % der Projekte dieselbe Grundkonfiguration brauchen (Gutenberg aus, Emojis aus, Head-Cleanup an), sollte es ein **Preset/Quick-Setup** geben — ein Klick für „Standard-Konfiguration anwenden", danach optional Feinjustierung. Das vermeidet, bei jedem neuen Projekt alle Checkboxen einzeln durchzugehen.
- **Warnhinweise visuell konsistent, nicht als Fließtext versteckt.** Das „Bricht es was"-Feld aus Abschnitt 7 sollte immer an derselben Stelle im selben Format erscheinen (z. B. Icon + farblich abgesetzter Kasten), damit man es beim schnellen Scrollen nicht überliest — besonders wichtig bei XML-RPC, Block-Klassen-Cleanup und Revisionen, wo Abschalten etwas kaputtmachen kann.
- **Zustand jederzeit erkennbar ohne Nachdenken:** klare An/Aus-Optik pro Toggle (nicht nur eine nackte Checkbox ohne visuellen Status), damit bei einem späteren Blick auf ein altes Projekt sofort klar ist, was aktiv ist, ohne jede Beschreibung neu zu lesen.

## 8. Sicherheit (des Plugins selbst)

- Capability-Check `current_user_can('manage_options')` auf Optionsseite und beim Speichern
- Nonce-Verifizierung bei jedem Formular-Submit
- Settings API mit `sanitize_callback` je Option — keine ungeprüfte Verarbeitung von `$_POST`
- Keine der oben ausgeschlossenen Kernmechanismen (Application Passwords, Cookie-Auth, Nonces) als deaktivierbare Optionen anbieten

---

## 9. Entwicklungsphasen (Vorschlag für Cursor)

0. Recherche-Phase (siehe Abschnitt 1) + Grundgerüst/Bootstrap/Autoloading
1. Modul-Basisklasse/Interface + Settings-API-Grundgerüst (leere Optionsseite, Speichern funktioniert)
2. Frontend-Output-Module (5.1) — einfachste Kategorie, guter Einstieg
3. Head-Cleanup (5.4)
4. Gutenberg/Classic Widgets inkl. granularer Datenstruktur (5.2)
5. REST API-Module (5.3)
6. Frontend-Klassen-Cleanup (5.5) — inkl. „experimentell"-Kennzeichnung
7. Sonstige Overhead-Module (5.6) + Speicherung/Verlauf (5.7)
8. Theme-Integrations-API (Abschnitt 6) + Dokumentation
9. UI-Politur nach BS-PluginDesignSystem, Erläuterungstexte final, Warnhinweise final
10. Test auf mind. einem realen Theme-Projekt (z. B. ortsverein-nv) vor Rollout

---

## 10. Offene Entscheidungen (vor Cursor-Start final zu klären)

- Objektorientiert (Klassen/Autoloading) oder funktional — Empfehlung: objektorientiert wegen Modul-Interface, aber abhängig von Präferenz für dieses Projekt
- Tabs vs. Akkordeon im Backend
- Ob Netzwerk-/Multisite-Unterstützung schon in v1 mitgedacht wird oder spätere Ausbaustufe bleibt
