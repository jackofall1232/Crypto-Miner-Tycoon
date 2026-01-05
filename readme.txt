=== Crypto Miner Tycoon ===
Contributors: jackofall1232
Donate link: https://ShortcodeArcade.com
Tags: game, idle game, crypto, clicker game, mining game
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 0.3.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An engaging crypto-themed idle clicker game with Elo-balanced progression, cloud saves, and competitive leaderboards.

== Description ==

Crypto Miner Tycoon is an addictive idle clicker game where players build their cryptocurrency mining empire from scratch. Click to mine satoshis, purchase upgrades, and watch your passive income grow exponentially!

**Core Features:**

* **Click-to-Mine Mechanics** - Earn satoshis by clicking the glowing Bitcoin symbol
* **Elo-Balanced Progression** - Sophisticated rating system ensures balanced gameplay
* **10 Unique Upgrades** - From Basic Pickaxes to Black Hole Extractors
* **Prestige System** - "Hard Fork" to reset with permanent +10% production bonuses (stackable!)
* **Auto-Save** - Game progress saves automatically every 10 seconds
* **Offline Progress** - Earn passive income while away (up to 24 hours)
* **Beautiful Design** - Cyberpunk-inspired glassmorphic UI with neon effects
* **Mobile Responsive** - Play seamlessly on any device

**Advanced Features (Optional):**

* **Cloud Saves** - Save player progress to WordPress database (requires user login)
* **Leaderboards** - Display top miners with prestige-weighted scoring
* **Ad Integration** - Built-in ad space for monetization
* **REST API** - Full API for cloud saves and leaderboard data

**How the Elo System Works:**

The game uses an innovative Elo rating system (similar to chess ratings) to balance upgrade costs. As your miner rating increases with each purchase, more powerful upgrades become available - but they also cost more based on the difficulty curve. This creates perfectly balanced progression that remains challenging and engaging throughout.

**Shortcodes:**

Display the game:
`[crypto_miner_tycoon]`

Display the game with custom ad code:
`[crypto_miner_tycoon ad_code="<your ad network code>"]`

Display the leaderboard (requires cloud saves enabled):
`[crypto_miner_leaderboard]`

**Perfect For:**

* Cryptocurrency blogs and news sites
* Gaming communities and portals
* Educational sites teaching about blockchain
* Crypto exchanges and wallet providers
* Anyone wanting engaging, interactive content

**Cloud Saves & Leaderboards:**

Enable cloud saves in Settings > Crypto Miner Tycoon to:
- Store player progress in your WordPress database
- Require user login for save functionality
- Enable competitive leaderboards
- Track top players with medals and rankings
- All data stays on YOUR server (no third-party dependencies)

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins > Add New
3. Search for "Crypto Miner Tycoon"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Upload the `crypto-miner-tycoon` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

= After Installation =

1. Add the shortcode `[crypto_miner_tycoon]` to any page or post
2. (Optional) Go to Settings > Crypto Miner Tycoon to enable cloud saves and leaderboards
3. (Optional) Add your ad code via shortcode attribute

== Frequently Asked Questions ==

= How do I display the game on my site? =

Add the shortcode `[crypto_miner_tycoon]` to any page or post where you want the game to appear.

= Does the game save progress? =

Yes! By default, the game saves to the player's browser (localStorage) every 10 seconds. You can optionally enable cloud saves in the admin settings to save progress to your WordPress database.

= What's the difference between local saves and cloud saves? =

**Local Saves (Default):** Saves to the player's browser. Works immediately with no setup. Players can't access saves on different devices.

**Cloud Saves (Optional):** Saves to your WordPress database. Requires players to be logged in. Players can access saves from any device. Enables leaderboards.

= How do I enable the leaderboard? =

1. Go to Settings > Crypto Miner Tycoon
2. Check "Enable Cloud Saves"
3. Check "Enable Leaderboard"
4. Add `[crypto_miner_leaderboard]` shortcode to any page

= Can I add advertisements? =

Yes! You can pass ad code via the shortcode attribute: `[crypto_miner_tycoon ad_code="<your ad code>"]`

= Is the game mobile-friendly? =

Absolutely! The game is fully responsive and optimized for mobile devices, tablets, and desktops.

= What is the Elo rating system? =

The Elo rating system (borrowed from chess) dynamically adjusts upgrade costs based on your progress. Higher-rated upgrades cost more when you're just starting, but become affordable as your miner rating increases. This ensures the game remains balanced regardless of how long you play.

= How does the prestige system work? =

Once you reach 1,000,000 satoshis, you can perform a "Hard Fork" (prestige). This resets your progress but gives you a permanent +10% production bonus. Each prestige level stacks, so prestige level 5 gives you +50% production on ALL income!

= Does offline progress work? =

Yes! When you return to the game after being away, you'll earn passive income based on your production rate (up to 24 hours maximum).

= Can I customize the game? =

The plugin is GPL licensed, so you're free to modify it as needed. The code is well-organized, commented, and follows WordPress coding standards.

= Where is player data stored? =

**Local Saves:** Stored in the player's browser (localStorage)
**Cloud Saves:** Stored in your WordPress database in custom tables
**Privacy:** All data stays on YOUR server - no external services or tracking

= Will this work with caching plugins? =

Yes! The game is designed to work with caching plugins. JavaScript handles all game logic client-side.

== Screenshots ==

1. Main game interface with cyberpunk design and glowing Bitcoin mining button
2. Upgrades panel showing Elo-balanced progression system
3. Prestige system with permanent production bonuses
4. Mobile responsive design - play on any device
5. Leaderboard showing top miners with medals and rankings
6. Admin settings panel for cloud saves and leaderboards

== Changelog ==

= 0.3.0 - 2025-01-01 =
* Added: Cloud save system with WordPress integration
* Added: Competitive leaderboard with prestige-weighted scoring
* Added: Admin settings panel
* Added: REST API for cloud saves and leaderboard
* Added: Offline progress (earn while away, up to 24 hours)
* Added: Leaderboard shortcode `[crypto_miner_leaderboard]`
* Fixed: Prestige multiplier math (now applies at earn-time, not purchase-time)
* Fixed: Floating point drift prevention
* Improved: Code organization and WordPress standards compliance

= 0.2.0 - 2024-12-XX =
* Added: Prestige/Hard Fork system
* Added: Auto-save functionality
* Improved: UI animations and visual effects
* Fixed: Mobile responsiveness issues

= 0.1.0 - 2024-12-XX =
* Initial beta release
* Click-to-mine mechanics
* 10 unique upgrades
* Elo-balanced progression system
* Cyberpunk UI design

== Upgrade Notice ==

= 0.3.0 =
Major update! Added cloud saves, leaderboards, and offline progress. Cloud saves are optional and disabled by default. If upgrading from 0.2.0, player saves will continue working via localStorage.

== Credits ==

**Developed by:** jackofall1232  
**Website:** [ShortcodeArcade](https://ShortcodeArcade.com)
**Other Projects:** Ask Adam Pro - Multi-AI Orchestration for WordPress

**Special Thanks:**
* Fonts: Orbitron and Rajdhani from Google Fonts
* WordPress Community for continued support

**Support the Developer:**
This plugin is provided 100% free to the WordPress community. If you find it useful, please:
* Leave a 5-star review
* Share with other crypto enthusiasts
* Check out my other plugins at [AskAdamIT](https://askadamit.com)|[ShortcodeArcade](https://ShortcodeArcade.com)

**Feature Requests & Bug Reports:**
Visit the plugin support forum or contact me directly through admin@askadamit.com

== Privacy Policy ==

Crypto Miner Tycoon respects user privacy:

* **Local Saves (Default):** All data stored in browser localStorage. No data sent to external servers.
* **Cloud Saves (Optional):** Data stored in YOUR WordPress database only when enabled by site admin.
* **No External Services:** The plugin does not connect to any external APIs or services.
* **No Tracking:** We don't track player activity or collect analytics.
* **GDPR Compliant:** Players can delete their data by clearing browser cache (local) or site admin can delete from database (cloud).

Site administrators are responsible for their own privacy policies when enabling cloud saves.
