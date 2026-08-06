const API_BASE = '/api/index.php'; // Atau http://localhost:8003/api/index.php

// State
let currentUser = null;
let currentToken = null;
let activeKapasitasData = null;

// DOM Elements
const viewLogin = document.getElementById('view-login');
const appLayout = document.getElementById('app-layout');
const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');
const btnLogin = document.getElementById('btnLogin');
const btnLogout = document.getElementById('btnLogout');
const userName = document.getElementById('userName');
const userRole = document.getElementById('userRole');
const userInitial = document.getElementById('userInitial');

// Views
const views = {
    'erm': document.getElementById('view-erm'),
    'kasir': document.getElementById('view-kasir'),
    'master': document.getElementById('view-master'),
    'audit': document.getElementById('view-audit')
};

// Menus
const menus = {
    'erm': document.getElementById('menu-erm'),
    'kasir': document.getElementById('menu-kasir'),
    'master': document.getElementById('menu-master'),
    'audit': document.getElementById('menu-audit')
};

// Initialization
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    setupNavigation();
});

// --- AUTHENTICATION ---
function checkAuth() {
    const token = localStorage.getItem('prm_token');
    const userStr = localStorage.getItem('prm_user');

    if (token && userStr) {
        currentToken = token;
        currentUser = JSON.parse(userStr);
        showApp();
    } else {
        showLogin();
    }
}

loginForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const username = document.getElementById('loginUsername').value.trim();
    const password = document.getElementById('loginPassword').value.trim(); // TRIM ADDED

    btnLogin.disabled = true;
    document.getElementById('loginText').classList.add('hidden');
    document.getElementById('loginLoader').classList.remove('hidden');
    loginMessage.classList.add('hidden');

    fetch(`${API_BASE}/auth?action=login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    })
    .then(res => res.json().then(data => ({status: res.status, body: data})))
    .then(res => {
        btnLogin.disabled = false;
        document.getElementById('loginText').classList.remove('hidden');
        document.getElementById('loginLoader').classList.add('hidden');

        if(res.status === 200) {
            localStorage.setItem('prm_token', res.body.token);
            localStorage.setItem('prm_user', JSON.stringify(res.body.user));
            checkAuth();
        } else {
            loginMessage.textContent = res.body.message;
            loginMessage.classList.remove('hidden');
        }
    })
    .catch(err => {
        btnLogin.disabled = false;
        document.getElementById('loginText').classList.remove('hidden');
        document.getElementById('loginLoader').classList.add('hidden');
        loginMessage.textContent = 'Network Error';
        loginMessage.classList.remove('hidden');
    });
});

btnLogout.addEventListener('click', () => {
    localStorage.removeItem('prm_token');
    localStorage.removeItem('prm_user');
    currentToken = null;
    currentUser = null;
    showLogin();
});

// --- ROUTING & UI STATE ---
function showLogin() {
    viewLogin.classList.add('active');
    appLayout.classList.add('hidden');
}

function showApp() {
    viewLogin.classList.remove('active');
    appLayout.classList.remove('hidden');
    
    // Set User Info
    userName.textContent = currentUser.nama_lengkap;
    userRole.textContent = currentUser.role.toUpperCase();
    userInitial.textContent = currentUser.nama_lengkap.charAt(0).toUpperCase();

    // Setup Role Based Access
    setupRoleAccess();
}

function setupRoleAccess() {
    const role = currentUser.role;
    
    // Reset all menus to hidden first
    Object.values(menus).forEach(m => m.style.display = 'none');

    let defaultView = 'erm';

    if (role === 'admin') {
        Object.values(menus).forEach(m => m.style.display = 'flex');
        defaultView = 'master';
    } else if (role === 'kasir') {
        menus['kasir'].style.display = 'flex';
        menus['erm'].style.display = 'flex'; // Kasir butuh lihat sisa sesi
        defaultView = 'kasir';
    } else if (role === 'manajemen') {
        menus['audit'].style.display = 'flex';
        defaultView = 'audit';
    } else if (role === 'erm') {
        menus['erm'].style.display = 'flex';
        defaultView = 'erm';
    }

    switchView(defaultView);
}

function setupNavigation() {
    document.querySelectorAll('.nav-item').forEach(nav => {
        nav.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = nav.getAttribute('data-target');
            const targetKey = targetId.replace('view-', '');
            switchView(targetKey);
        });
    });
}

function switchView(viewKey) {
    // Update Nav
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    if(menus[viewKey]) menus[viewKey].classList.add('active');

    // Update Views
    Object.values(views).forEach(v => v.classList.add('hidden'));
    if(views[viewKey]) views[viewKey].classList.remove('hidden');
}

// --- FETCH HELPER WITH AUTH ---
function apiFetch(url, options = {}) {
    if(!options.headers) options.headers = {};
    options.headers['Authorization'] = `Bearer ${currentToken}`;
    
    return fetch(url, options).then(res => {
        if(res.status === 401) {
            // Token expired or invalid
            btnLogout.click();
            throw new Error('Session Expired');
        }
        return res;
    });
}


// --- ERM MODULE LOGIC ---
const searchForm = document.getElementById('searchForm');
const noErmInput = document.getElementById('noErmInput');
const btnSearch = document.getElementById('btnSearch');
const messageContainer = document.getElementById('messageContainer');
const resultSection = document.getElementById('resultSection');
const displayErm = document.getElementById('displayErm');
const cardsContainer = document.getElementById('cardsContainer');

searchForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const noErm = noErmInput.value.trim();
    if(noErm) fetchKapasitas(noErm);
});

function fetchKapasitas(noErm) {
    btnSearch.textContent = 'Mencari...';
    messageContainer.classList.add('hidden');
    resultSection.classList.add('hidden');
    
    apiFetch(`${API_BASE}/pasien?action=kapasitas_aktif&no_erm=${noErm}`)
        .then(res => res.json().then(data => ({status: res.status, body: data})))
        .then(res => {
            btnSearch.textContent = 'Cari';
            if(res.status === 200 && res.body.records) {
                displayKapasitas(noErm, res.body.records);
            } else {
                showMessage(res.body.message || 'Tidak ada paket aktif.', 'error');
            }
        })
        .catch(err => {
            btnSearch.textContent = 'Cari';
        });
}

function displayKapasitas(noErm, records) {
    displayErm.textContent = noErm;
    cardsContainer.innerHTML = ''; 

    records.forEach(record => {
        const card = document.createElement('div');
        card.className = 'card';
        card.innerHTML = `
            <div class="card-header">
                <div class="card-title">${record.nama_paket}</div>
                <div class="card-badge">${record.status}</div>
            </div>
            <div class="card-body">
                <div class="sisa-highlight">${record.sisa} <span style="font-size: 1rem; color: #6b7280;">/ ${record.total_sesi}</span></div>
                <span class="sisa-label">Sisa Sesi</span>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary btn-full" onclick='openModal(${JSON.stringify(record)})'>
                    Gunakan 1 Sesi
                </button>
            </div>
        `;
        cardsContainer.appendChild(card);
    });

    resultSection.classList.remove('hidden');
}

function showMessage(msg, type = 'info') {
    messageContainer.className = `message-box msg-${type}`;
    messageContainer.innerHTML = msg;
    messageContainer.classList.remove('hidden');
}

// Modal Logic
const useSessionModal = document.getElementById('useSessionModal');
const modalPaketName = document.getElementById('modalPaketName');
const modalSisaSesiBaru = document.getElementById('modalSisaSesiBaru');
const modalNoKunjungan = document.getElementById('modalNoKunjungan');
const confirmUseBtn = document.getElementById('confirmUseBtn');

window.openModal = function(record) {
    activeKapasitasData = record;
    modalPaketName.textContent = record.nama_paket;
    modalSisaSesiBaru.textContent = record.sisa - 1;
    useSessionModal.classList.add('active');
}

document.getElementById('closeModalBtn').onclick = () => useSessionModal.classList.remove('active');
document.getElementById('cancelModalBtn').onclick = () => useSessionModal.classList.remove('active');

confirmUseBtn.addEventListener('click', () => {
    if(!activeKapasitasData) return;
    
    confirmUseBtn.disabled = true;
    confirmUseBtn.textContent = 'Memproses...';

    const now = new Date();
    const tzOffset = now.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(Date.now() - tzOffset)).toISOString().slice(0, 19).replace('T', ' ');

    const payload = {
        id_kapasitas: activeKapasitasData.id,
        sisa_saat_ini: activeKapasitasData.sisa,
        id_tindakan: 1, 
        no_erm: activeKapasitasData.no_erm,
        no_register_kunjungan: modalNoKunjungan.value.trim() || 'REG-AUTO',
        tanggal_paket: localISOTime,
        sesi_ke: (activeKapasitasData.total_sesi - activeKapasitasData.sisa) + 1
    };

    apiFetch(`${API_BASE}/pasien?action=gunakan_sesi`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json().then(data => ({status: res.status, body: data})))
    .then(res => {
        confirmUseBtn.disabled = false;
        confirmUseBtn.textContent = 'Gunakan Sesi';
        useSessionModal.classList.remove('active');

        if(res.status === 201) {
            alert(res.body.message);
            fetchKapasitas(activeKapasitasData.no_erm);
        } else {
            alert(res.body.message || 'Gagal memotong sesi.');
        }
    });
});
