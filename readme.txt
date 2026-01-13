=== Shortcode Arcade Crypto Idle Game ===
Contributors: jackofall1232
Donate link: https://ShortcodeArcade.com
Tags: game, idle game, crypto, clicker game, mining game
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 0.4.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A crypto-themed idle clicker game with Elo-balanced progression, prestige mechanics, and optional leaderboards.

== Description ==

Crypto Miner Tycoon is an idle clicker game where players build their cryptocurrency mining empire from scratch. Click to mine satoshis, purchase upgrades, and grow passive income over time.

The game focuses on fair, long-term progression using an Elo-inspired difficulty curve combined with prestige mechanics to prevent runaway scaling and repetitive upgrade stacking.

**Core Features:**

* **Click-to-Mine Mechanics** – Earn satoshis by clicking the mining button
* **Elo-Balanced Progression** – Upgrade costs scale dynamically for fair gameplay
* **10 Unique Upgrades** – From Basic Pickaxes to Black Hole Extractors
* **Prestige System** – "Hard Fork" resets progress for permanent production bonuses
* **Auto-Save** – Progress saves automatically every 10 seconds
* **Offline Progress** – Earn passive income while away (up to 24 hours)
* **Modern UI** – Cyberpunk-inspired glassmorphic design
* **Mobile Responsive** – Works on desktop, tablet, and mobile

**Advanced Features (Optional):**

* **Cloud Saves** – Store player progress in the WordPress database (requires login)
* **Leaderboards** – Display top players with prestige-weighted scoring
* **Ad Integration** – Optional ad placement via shortcode
* **REST API** – Access cloud save and leaderboard data programmatically

**Shortcodes:**

Display the game:  
`[crypto_miner_tycoon]`

Display the game with custom ad code:  
`[crypto_miner_tycoon ad_code="<your ad network code>"]`

Display the leaderboard (requires cloud saves):  
`[crypto_miner_leaderboard]`

**Cloud Saves & Leaderboards:**

Enable cloud saves in **Settings → Crypto Miner Tycoon** to:
- Store player progress in your WordPress database
- Require user login for saving and loading games
- Enable competitive leaderboards
- Keep all player data on your own server

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins → Add New
3. Search for "Crypto Miner Tycoon"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Upload the `crypto-miner-tycoon` folder to `/wp-content/plugins/`
3. Activate the plugin through the Plugins menu

= After Installation =

1. Add `[crypto_miner_tycoon]` to any page or post
2. (Optional) Enable cloud saves and leaderboards in plugin settings
3. (Optional) Add ad code using the shortcode attribute

== Frequently Asked Questions ==

= Does the game save progress? =

Yes. Progress is saved locally in the browser every 10 seconds. Optional cloud saves can be enabled by the site administrator.

= What's the difference between local saves and cloud saves? =

**Local Saves:** Stored in the browser. No login required.  
**Cloud Saves:** Stored in the WordPress database. Login required. Enables leaderboards.

= How do I enable the leaderboard? =

1. Go to Settings → Crypto Miner Tycoon  
2. Enable Cloud Saves  
3. Enable Leaderboard  
4. Add `[crypto_miner_leaderboard]` to a page  

= Is the game mobile-friendly? =

Yes. The interface is fully responsive.

= Where is player data stored? =

Local saves are stored in browser localStorage. Cloud saves are stored in WordPress custom database tables when enabled.

== Screenshots ==

1. Main game interface  
2. Upgrade progression panel  
3. Prestige / Hard Fork system  
4. Mobile responsive layout  
5. Leaderboard view  
6. Admin settings panel  

== Changelog ==

= 0.4.0 - 2025-01-06 =
* Stable release
* Improved: Elo progression algorithm for fair long-term gameplay
* Added: Exponential prestige cost scaling (5x multiplier per prestige)
* Added: Diminishing returns on duplicate upgrades (80%, 60%, 40%, 20%)
* Improved: Overall progression pacing and balance
* Improved: Admin UI clarity and settings behavior
* No breaking changes or data migrations

= 0.3.4 - 2025-01-06 =
* Gameplay balance refinements and progression tuning

= 0.3.3 - 2025-01-04 =
* Fixed: Plugin Checker and PHPCS warnings
* Improved: Database handling and admin stability
* No gameplay changes

== Upgrade Notice ==

= 0.4.0 =
Stable release with improved gameplay balance, refined progression mechanics, and admin UX improvements. No breaking changes.

== Credits ==

Developed by: jackofall1232  
Website: https://ShortcodeArcade.com

== Privacy Policy ==

Crypto Miner Tycoon respects user privacy:

* Local saves are stored in browser localStorage
* Cloud saves (optional) are stored in WordPress custom tables
* No external APIs, analytics, or telemetry
* Site administrators retain full control over all stored data

Site owners are responsible for updating their privacy policy if cloud saves are enabled.
