=== Shortcode Arcade Crypto Idle Game ===
Contributors: jackofall1232
Donate link: https://shortcodearcade.com
Tags: game, idle game, crypto, clicker game, mining game
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 0.4.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A crypto-themed idle clicker game with balanced progression, prestige mechanics, and optional leaderboards.

== Description ==

**Shortcode Arcade Crypto Idle Game** is an idle clicker game where players grow a virtual crypto mining operation over time. Players click to generate currency, purchase upgrades, and unlock passive income systems.

The game is designed for **fair, long-term progression**, using a carefully tuned scaling curve combined with a prestige (“Hard Fork”) system to prevent runaway inflation and repetitive upgrade stacking.

This plugin is self-contained and runs entirely inside WordPress, making it ideal for gaming sites, communities, or experimental content.

**Core Features:**

* **Click-to-Mine Gameplay** – Generate in-game currency through active clicking
* **Balanced Progression Curve** – Upgrade costs scale dynamically for long-term play
* **Multiple Upgrade Paths** – Unlock and stack production upgrades
* **Prestige System (“Hard Fork”)** – Reset progress for permanent production bonuses
* **Auto-Save** – Progress saves automatically at regular intervals
* **Offline Progress** – Earn limited passive income while away
* **Modern UI** – Clean, game-focused interface
* **Mobile Responsive** – Fully playable on desktop, tablet, and mobile

**Optional Advanced Features:**

* **Cloud Saves** – Store player progress in the WordPress database (login required)
* **Leaderboards** – Rank players using prestige-weighted scores
* **Ad Integration** – Optional ad placement via shortcode attribute
* **REST API** – Public endpoints for leaderboard data

**Shortcodes:**

Display the game:  
`[crypto_idle_game]`

Display the game with custom ad code:  
`[crypto_idle_game ad_code="<your ad network code>"]`

Display the leaderboard (requires cloud saves):  
`[crypto_idle_leaderboard]`

**Cloud Saves & Leaderboards:**

When enabled in **Settings → Crypto Idle Game**, cloud saves allow you to:

- Store player progress in your WordPress database
- Require user login for saving/loading games
- Enable competitive leaderboards
- Keep all player data on your own server

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins → Add New
3. Search for “Shortcode Arcade Crypto Idle Game”
4. Click “Install Now” and then “Activate”

= Manual Installation =

1. Download the plugin ZIP file
2. Upload the `shortcodearcade-crypto-idle-game` folder to `/wp-content/plugins/`
3. Activate the plugin through the Plugins menu

= After Installation =

1. Add `[crypto_idle_game]` to any page or post
2. (Optional) Enable cloud saves and leaderboards in plugin settings
3. (Optional) Add ad code using the shortcode attribute

== Frequently Asked Questions ==

= Does the game save progress? =

Yes. Progress is saved locally in the browser at regular intervals. Optional cloud saves can be enabled by the site administrator.

= What’s the difference between local saves and cloud saves? =

**Local Saves:** Stored in the browser. No login required.  
**Cloud Saves:** Stored in the WordPress database. Login required. Enables leaderboards.

= How do I enable the leaderboard? =

1. Go to Settings → Crypto Idle Game  
2. Enable Cloud Saves  
3. Enable Leaderboards  
4. Add `[crypto_idle_leaderboard]` to a page  

= Is the game mobile-friendly? =

Yes. The interface is fully responsive and touch-friendly.

= Where is player data stored? =

Local saves are stored in browser localStorage. Cloud saves are stored in WordPress custom database tables when enabled.

== Screenshots ==

1. Main game interface  
2. Upgrade progression panel  
3. Prestige (“Hard Fork”) system  
4. Mobile responsive layout  
5. Leaderboard view  
6. Admin settings panel  

== Changelog ==

= 0.4.2 - 2026-01-13 =
* Renamed plugin to Shortcode Arcade Crypto Idle Game
* Updated all admin and frontend UI labels
* Updated readme, plugin headers, and branding
* No gameplay changes
* No breaking changes or data migrations

= 0.4.0 - 2025-01-06 =
* Stable release
* Improved progression balancing
* Added prestige scaling refinements
* Improved admin UI clarity
* No breaking changes

= 0.3.4 - 2025-01-06 =
* Gameplay balance refinements

= 0.3.3 - 2025-01-04 =
* Fixed Plugin Checker and PHPCS warnings
* Improved database handling

== Upgrade Notice ==

= 0.4.2 =
Branding and naming update for WordPress.org compliance. No functional or data changes.

== Credits ==

Developed by: jackofall1232  
Website: https://shortcodearcade.com

== Privacy Policy ==

Shortcode Arcade Crypto Idle Game respects user privacy:

* Local saves are stored in browser localStorage
* Cloud saves (optional) are stored in WordPress custom tables
* No external analytics, tracking, or telemetry
* Site administrators retain full control over all stored data

Site owners are responsible for updating their privacy policy if cloud saves are enabled.
