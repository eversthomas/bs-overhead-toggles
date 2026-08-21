# BS Overhead Toggles

WordPress-Overhead (Emojis, Gutenberg-Teile, REST, Head-Meta, Frontend-Klassen, Heartbeat, Revisionen …) zentral abschalten, statt dasselbe in jeder `functions.php` zu wiederholen.

Menü: **Overhead** (unter dem Dashboard). PHP 8.1+, WordPress 7.0+.

---

## Theme-API

Themes und andere Plugins sollen den Zustand **lesen**, nicht raten. Die Funktionen existieren nur, wenn dieses Plugin aktiv ist — immer mit `function_exists()` absichern.

```php
if ( function_exists( 'bsot_is_disabled' ) && ! bsot_is_disabled( 'gutenberg', get_post_type() ) ) {
	add_theme_support( 'align-wide' );
}
```

**Nie hart von einem Toggle ausgehen.** Ohne Plugin, bei gesperrter `wp-config.php`-Konstante oder mit Gutenberg-Ausnahme für einen Inhaltstyp ist das Verhalten ein anderes.

### `bsot_is_disabled( $feature, $context = null )`

`true`, wenn das Modul aktiv ist und die Funktion damit wirklich abgeschaltet bzw. verändert wird (nicht gesperrt durch `wp-config.php`).

| Aufruf | Bedeutung |
|---|---|
| `bsot_is_disabled( 'emojis' )` | Emoji-Skripte sind aus |
| `bsot_is_disabled( 'gutenberg' )` | Classic Editor ist global an (Ausnahmen möglich) |
| `bsot_is_disabled( 'gutenberg', 'post' )` | Block-Editor für diesen Inhaltstyp ist aus |
| `bsot_is_disabled( 'heartbeat', 'frontend' )` | Heartbeat auf der Website ist ganz aus (nicht nur langsamer) |

Zweiter Parameter je nach Modul: Gutenberg = Post-Type-Slug; Heartbeat = `frontend`, `backend` oder `editor` (`editor` ist nie ganz aus). Ohne Kontext: „Modul ist an“.

### `bsot_get_option( $key, $default = null )`

Rohwert aus `bsot_options`. Punkt-Notation für Nested Keys.

```php
bsot_get_option( 'gutenberg' );              // array{ enabled, post_types } oder false
bsot_get_option( 'gutenberg.post_types' );   // string[]
bsot_get_option( 'heartbeat.frontend' );     // 'off'|'15'|'30'|'60'|'120'
bsot_get_option( 'autosave.interval' );      // int (Sekunden), wenn Modul an
bsot_get_option( 'revisions.keep' );         // 0|3|5|10
bsot_get_option( 'trash.days' );             // 0|7|14|30
bsot_get_option( 'rest_guests.whitelist' );  // string[]
```

Unbekannter Schlüssel → `$default`.

---

## Wann aufrufen

| Zeitpunkt | Was das Plugin schon getan hat |
|---|---|
| Datei geladen | Funktionen sind definiert |
| `plugins_loaded` (Prio 10) | Optionen gelesen, `AUTOSAVE_INTERVAL` / `EMPTY_TRASH_DAYS` gesetzt falls gewählt |
| `after_setup_theme` | API vollständig nutzbar — **empfohlen für Themes** |
| `init` Prio 5 | Module hängen ihre Hooks ein |

In `functions.php` (läuft bei `after_setup_theme`) ist die API bereit. Nicht vor `plugins_loaded` Prio 10 von einem anderen Plugin aus abfragen.

---

## Modul-IDs

`emojis`, `block_library_css`, `global_styles`, `img_auto_sizes`, `dashicons`, `oembed`, `feed_links`, `canonical`, `shortlink`, `generator`, `rsd`, `gutenberg`, `classic_widgets`, `rest_discovery`, `rest_users`, `rest_guests`, `body_classes`, `post_classes`, `nav_classes`, `image_classes`, `block_classes`, `xmlrpc`, `self_pingbacks`, `heartbeat`, `autosave`, `revisions`, `trash`.

Eigene Module: Hook `bsot_register_modules` (übergibt `Registry`).

---

## Filter / Prioritäten (für Entwickler)

- Gutenberg: `use_block_editor_for_post_type` Prio **100**
- REST-Gäste: `rest_authentication_errors` Prio **110** (nach Cookie- und Application-Password-Checks)
- Klassen-Filter: meist Prio **99** bzw. `render_block` Prio **20**
- Heartbeat: `heartbeat_settings`, Dequeue Prio **100**

Auth-Kern (Application Passwords, Cookies, Nonces) ist nicht abschaltbar.

---

## Quick-Setup

Oben auf der Optionsseite: **Standard-Konfiguration anwenden**. Schaltet den 90 %-Fall (Emojis, Block-CSS, Dashicons, oEmbed, Feeds, Generator, RSD, Kurzlink, REST-Hinweis, XML-RPC, Eigen-Pingbacks, Body-IDs). Gutenberg, Canonical, REST-Sperren, Speicherung und experimentelle Klassen bleiben aus. `wp-config.php`-Sperren bleiben.

**Alles zurücksetzen** (mit Nachfrage) setzt alle ungesperrten Schalter auf Aus.
