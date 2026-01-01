/**
 * Crypto Miner Tycoon - Game Logic
 * Version: 1.0.0
 */

(function() {
    'use strict';
    
    // Game State
    let gameState = {
        satoshis: 0,
        clickPower: 1,
        passiveIncome: 0,
        rating: 1000,
        prestigeLevel: 0,
        prestigeMultiplier: 1,
        upgrades: {}
    };

    // Upgrade Definitions with Elo-based balancing
    const upgradeDefinitions = [
        {
            id: 'betterClicker',
            name: 'Better Pickaxe',
            baseDescription: 'Increases click power by 1',
            baseCost: 10,
            rating: 1000,
            effect: (state) => state.clickPower += 1 * state.prestigeMultiplier,
            costMultiplier: 1.15
        },
        {
            id: 'cpuMiner',
            name: 'CPU Miner',
            baseDescription: 'Generates 0.1 satoshis/sec',
            baseCost: 50,
            rating: 1050,
            effect: (state) => state.passiveIncome += 0.1 * state.prestigeMultiplier,
            costMultiplier: 1.2
        },
        {
            id: 'powerfulClicker',
            name: 'Diamond Pickaxe',
            baseDescription: 'Increases click power by 5',
            baseCost: 100,
            rating: 1100,
            effect: (state) => state.clickPower += 5 * state.prestigeMultiplier,
            costMultiplier: 1.15
        },
        {
            id: 'gpuRig',
            name: 'GPU Mining Rig',
            baseDescription: 'Generates 1 satoshi/sec',
            baseCost: 500,
            rating: 1200,
            effect: (state) => state.passiveIncome += 1 * state.prestigeMultiplier,
            costMultiplier: 1.25
        },
        {
            id: 'megaClicker',
            name: 'Quantum Pickaxe',
            baseDescription: 'Increases click power by 25',
            baseCost: 1000,
            rating: 1300,
            effect: (state) => state.clickPower += 25 * state.prestigeMultiplier,
            costMultiplier: 1.15
        },
        {
            id: 'asicMiner',
            name: 'ASIC Miner',
            baseDescription: 'Generates 10 satoshis/sec',
            baseCost: 5000,
            rating: 1400,
            effect: (state) => state.passiveIncome += 10 * state.prestigeMultiplier,
            costMultiplier: 1.3
        },
        {
            id: 'ultraClicker',
            name: 'Neutron Star Drill',
            baseDescription: 'Increases click power by 100',
            baseCost: 10000,
            rating: 1500,
            effect: (state) => state.clickPower += 100 * state.prestigeMultiplier,
            costMultiplier: 1.15
        },
        {
            id: 'miningFarm',
            name: 'Mining Farm',
            baseDescription: 'Generates 50 satoshis/sec',
            baseCost: 50000,
            rating: 1600,
            effect: (state) => state.passiveIncome += 50 * state.prestigeMultiplier,
            costMultiplier: 1.35
        },
        {
            id: 'godClicker',
            name: 'Black Hole Extractor',
            baseDescription: 'Increases click power by 500',
            baseCost: 100000,
            rating: 1700,
            effect: (state) => state.clickPower += 500 * state.prestigeMultiplier,
            costMultiplier: 1.15
        },
        {
            id: 'datacenter',
            name: 'Data Center',
            baseDescription: 'Generates 250 satoshis/sec',
            baseCost: 500000,
            rating: 1800,
            effect: (state) => state.passiveIncome += 250 * state.prestigeMultiplier,
            costMultiplier: 1.4
        }
    ];

    /**
     * Calculate upgrade cost using Elo-based formula
     */
    function getUpgradeCost(upgrade) {
        const owned = gameState.upgrades[upgrade.id] || 0;
        const ratingDiff = upgrade.rating - gameState.rating;
        const eloMultiplier = Math.max(0.5, 1 + (ratingDiff / 400));
        const ownedMultiplier = Math.pow(upgrade.costMultiplier, owned);
        return Math.ceil(upgrade.baseCost * eloMultiplier * ownedMultiplier);
    }

    /**
     * Mining click function
     */
    window.cmtMine = function() {
        const earnedAmount = gameState.clickPower;
        gameState.satoshis += earnedAmount;
        
        // Create floating particle effect
        const button = document.getElementById('cmt-mineButton');
        if (button) {
            const rect = button.getBoundingClientRect();
            const particle = document.createElement('div');
            particle.className = 'cmt-click-particle';
            particle.textContent = '+' + formatNumber(earnedAmount);
            particle.style.left = (rect.left + rect.width / 2 - 30) + 'px';
            particle.style.top = (rect.top + rect.height / 2) + 'px';
            document.body.appendChild(particle);
            
            setTimeout(() => particle.remove(), 1000);
        }
        
        updateUI();
    };

    /**
     * Buy upgrade function
     */
    window.cmtBuyUpgrade = function(upgradeId) {
        const upgrade = upgradeDefinitions.find(u => u.id === upgradeId);
        if (!upgrade) return;
        
        const cost = getUpgradeCost(upgrade);
        
        if (gameState.satoshis >= cost) {
            gameState.satoshis -= cost;
            
            // Apply upgrade effect
            upgrade.effect(gameState);
            
            // Track owned count
            gameState.upgrades[upgradeId] = (gameState.upgrades[upgradeId] || 0) + 1;
            
            // Increase rating based on upgrade tier
            gameState.rating += 10;
            
            updateUI();
            saveGame();
        }
    };

    /**
     * Prestige function
     */
    window.cmtPrestige = function() {
        if (gameState.satoshis >= 1000000) {
            if (confirm('Are you sure you want to Hard Fork? This will reset your progress but give you a permanent +10% production bonus!')) {
                gameState.prestigeLevel++;
                gameState.prestigeMultiplier = 1 + (gameState.prestigeLevel * 0.1);
                gameState.satoshis = 0;
                gameState.clickPower = 1;
                gameState.passiveIncome = 0;
                gameState.rating = 1000;
                gameState.upgrades = {};
                
                updateUI();
                saveGame();
            }
        }
    };

    /**
     * Format large numbers
     */
    function formatNumber(num) {
        if (num >= 1000000000) return (num / 1000000000).toFixed(2) + 'B';
        if (num >= 1000000) return (num / 1000000).toFixed(2) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(2) + 'K';
        return num.toFixed(2);
    }

    /**
     * Update UI
     */
    function updateUI() {
        // Update stats
        const satoshisEl = document.getElementById('cmt-satoshis');
        const clickPowerEl = document.getElementById('cmt-clickPower');
        const passiveIncomeEl = document.getElementById('cmt-passiveIncome');
        const ratingEl = document.getElementById('cmt-rating');
        
        if (satoshisEl) satoshisEl.textContent = formatNumber(gameState.satoshis);
        if (clickPowerEl) clickPowerEl.textContent = formatNumber(gameState.clickPower);
        if (passiveIncomeEl) passiveIncomeEl.textContent = formatNumber(gameState.passiveIncome);
        if (ratingEl) ratingEl.textContent = Math.floor(gameState.rating);
        
        // Update upgrades list
        const upgradesList = document.getElementById('cmt-upgradesList');
        if (upgradesList) {
            upgradesList.innerHTML = '';
            
            upgradeDefinitions.forEach(upgrade => {
                const cost = getUpgradeCost(upgrade);
                const owned = gameState.upgrades[upgrade.id] || 0;
                const canAfford = gameState.satoshis >= cost;
                
                const upgradeDiv = document.createElement('div');
                upgradeDiv.className = 'cmt-upgrade-item' + (canAfford ? '' : ' cmt-disabled');
                upgradeDiv.onclick = () => window.cmtBuyUpgrade(upgrade.id);
                
                upgradeDiv.innerHTML = `
                    <div class="cmt-upgrade-header">
                        <div class="cmt-upgrade-name">${upgrade.name}</div>
                        <div class="cmt-upgrade-cost">${formatNumber(cost)}</div>
                    </div>
                    <div class="cmt-upgrade-description">${upgrade.baseDescription}</div>
                    <div class="cmt-upgrade-owned">Owned: ${owned}</div>
                `;
                
                upgradesList.appendChild(upgradeDiv);
            });
        }
        
        // Update prestige button
        const prestigeButton = document.getElementById('cmt-prestigeButton');
        if (prestigeButton) {
            prestigeButton.disabled = gameState.satoshis < 1000000;
        }
        
        // Update prestige info if prestige level > 0
        if (gameState.prestigeLevel > 0) {
            const prestigeInfo = document.querySelector('.cmt-prestige-info');
            if (prestigeInfo) {
                prestigeInfo.innerHTML = `
                    Prestige Level: ${gameState.prestigeLevel} (+${(gameState.prestigeLevel * 10)}% production)<br>
                    <span style="font-size: 0.9rem; opacity: 0.7;">Next Hard Fork available at 1,000,000 satoshis</span>
                `;
            }
        }
    }

    /**
     * Passive income loop
     */
    function passiveIncomeLoop() {
        gameState.satoshis += gameState.passiveIncome / 10; // Update 10 times per second
        updateUI();
    }

    /**
     * Save game
     */
    function saveGame() {
        localStorage.setItem('cmtCryptoMinerSave', JSON.stringify(gameState));
        
        const indicator = document.getElementById('cmt-saveIndicator');
        if (indicator) {
            indicator.classList.add('cmt-show');
            setTimeout(() => indicator.classList.remove('cmt-show'), 2000);
        }
    }

    /**
     * Load game
     */
    function loadGame() {
        const saved = localStorage.getItem('cmtCryptoMinerSave');
        if (saved) {
            try {
                gameState = JSON.parse(saved);
                updateUI();
            } catch (e) {
                console.error('Failed to load saved game:', e);
            }
        }
    }

    /**
     * Modal functions
     */
    window.cmtShowModal = function() {
        const modal = document.getElementById('cmt-infoModal');
        if (modal) {
            modal.classList.add('cmt-show');
        }
    };

    window.cmtHideModal = function() {
        const modal = document.getElementById('cmt-infoModal');
        if (modal) {
            modal.classList.remove('cmt-show');
        }
    };

    /**
     * Initialize game when DOM is ready
     */
    function initGame() {
        loadGame();
        updateUI();
        
        // Start passive income loop
        setInterval(passiveIncomeLoop, 100); // 10 times per second
        
        // Auto-save every 10 seconds
        setInterval(saveGame, 10000);
        
        // Show info modal on first visit
        if (!localStorage.getItem('cmtCryptoMinerVisited')) {
            setTimeout(() => window.cmtShowModal(), 500);
            localStorage.setItem('cmtCryptoMinerVisited', 'true');
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGame);
    } else {
        initGame();
    }
})();
