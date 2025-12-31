const CMT = (() => {

    let gameState = {
        satoshis: 0,
        clickPower: 1,
        passiveIncome: 0,
        rating: 1000,
        upgrades: {}
    };

    const upgrades = [
        { id: 'click', name: 'Better Pickaxe', cost: 10, click: 1 },
        { id: 'cpu', name: 'CPU Miner', cost: 50, passive: 0.1 }
    ];

    function format(n) {
        return n >= 1000 ? (n / 1000).toFixed(1) + 'K' : n.toFixed(1);
    }

    function updateUI() {
        document.getElementById('satoshis').textContent = format(gameState.satoshis);
        document.getElementById('clickPower').textContent = gameState.clickPower;
        document.getElementById('passiveIncome').textContent = gameState.passiveIncome.toFixed(1);
        document.getElementById('rating').textContent = gameState.rating;

        const list = document.getElementById('upgradesList');
        list.innerHTML = '';

        upgrades.forEach(u => {
            const canBuy = gameState.satoshis >= u.cost;
            const div = document.createElement('div');
            div.className = 'upgrade-item' + (canBuy ? '' : ' disabled');
            div.innerHTML = `<strong>${u.name}</strong><br>Cost: ${u.cost}`;
            if (canBuy) div.onclick = () => buyUpgrade(u);
            list.appendChild(div);
        });

        document.getElementById('prestigeButton').disabled = gameState.satoshis < 1_000_000;
    }

    function mine() {
        gameState.satoshis += gameState.clickPower;
        updateUI();
        save();
    }

    function buyUpgrade(u) {
        gameState.satoshis -= u.cost;
        if (u.click) gameState.clickPower += u.click;
        if (u.passive) gameState.passiveIncome += u.passive;
        gameState.rating += 10;
        updateUI();
        save();
    }

    function prestige() {
        if (!confirm('Hard Fork?')) return;
        gameState = {
            satoshis: 0,
            clickPower: 1,
            passiveIncome: 0,
            rating: 1000,
            upgrades: {}
        };
        save();
        updateUI();
    }

    function save() {
        localStorage.setItem('cmt_save', JSON.stringify(gameState));
    }

    function load() {
        const s = localStorage.getItem('cmt_save');
        if (s) gameState = JSON.parse(s);
    }

    function showModal() {
        document.getElementById('infoModal').classList.add('show');
    }

    function hideModal() {
        document.getElementById('infoModal').classList.remove('show');
    }

    load();
    updateUI();
    setInterval(() => {
        gameState.satoshis += gameState.passiveIncome;
        updateUI();
    }, 1000);

    return { mine, prestige, showModal, hideModal };

})();
