const API_BASE = (() => {
    const path = window.location.pathname;
    const dir = path.substring(0, path.lastIndexOf('/') + 1);
    return `${dir}api/index.php`;
})();

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
    viewLogin.classList.remove('hidden');
    appLayout.classList.add('hidden');
}

function showApp() {
    viewLogin.classList.add('hidden');
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
        defaultView = 'erm';
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
    document.querySelectorAll('.nav-item').forEach(nav => {
        nav.classList.remove('bg-primary-container', 'text-on-primary-container', 'font-bold', 'border-r-4', 'border-primary');
        nav.classList.add('text-on-surface-variant');
    });
    
    if(menus[viewKey]) {
        menus[viewKey].classList.remove('text-on-surface-variant');
        menus[viewKey].classList.add('bg-primary-container', 'text-on-primary-container', 'font-bold', 'border-r-4', 'border-primary');
    }

    // Update Views
    Object.values(views).forEach(v => v.classList.add('hidden'));
    if(views[viewKey]) views[viewKey].classList.remove('hidden');
}

// --- FETCH HELPER WITH AUTH ---
function apiFetch(url, options = {}) {
    if(!options.headers) options.headers = {};
    const token = currentToken || localStorage.getItem('prm_token');
    if (token) {
        options.headers['Authorization'] = `Bearer ${token}`;
    } else {
        console.warn('apiFetch: no auth token available for', url);
    }
    
    return fetch(url, options).then(res => {
        if (res.status === 401) {
            // Token expired or invalid
            btnLogout.click();
            throw new Error('Session Expired');
        }
        return res;
    });
}

function parseJsonOrText(response) {
    return response.text().then(text => {
        try {
            return { status: response.status, body: JSON.parse(text) };
        } catch (e) {
            return { status: response.status, body: text };
        }
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
    btnSearch.disabled = true;
    messageContainer.classList.add('hidden');
    resultSection.classList.add('hidden');

    apiFetch(`${API_BASE}/pasien?action=kapasitas_aktif&no_erm=${noErm}`)
        .then(parseJsonOrText)
        .then(res => {
            btnSearch.textContent = 'Cari';
            btnSearch.disabled = false;

            if(res.status === 200) {
                displayKapasitas(noErm, res.body.records);
                fetchRiwayatSesi(noErm); // Fetch history after loading kapasitas
            } else {
                const msg = res.body && res.body.message ? res.body.message : `Error ${res.status}: ${typeof res.body === 'string' ? res.body : 'Unknown'}`;
                showMessage(msg, 'error');
                cardsContainer.innerHTML = '';
            }
        })
        .catch(err => {
            btnSearch.textContent = 'Cari';
            btnSearch.disabled = false;
            showMessage(`Network Error: ${err.message}`, 'error');
        });
}

function fetchRiwayatSesi(noErm) {
    const historyContainer = document.getElementById('historyContainer');
    historyContainer.innerHTML = '<div class="p-2 text-center text-on-surface-variant font-body-sm">Memuat riwayat...</div>';

    apiFetch(`${API_BASE}/pasien?action=riwayat_sesi&no_erm=${noErm}`)
        .then(parseJsonOrText)
        .then(res => {
            historyContainer.innerHTML = '';
            if(res.status === 200 && res.body.records.length > 0) {
                res.body.records.forEach(hist => {
                    const dateObj = new Date(hist.tanggal_paket);
                    const formattedDate = dateObj.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
                    
                    historyContainer.innerHTML += `
                        <div class="flex items-center px-2 py-3 border-b border-outline-variant hover:bg-surface transition-colors cursor-default">
                            <div class="w-1/3 font-mono-data text-mono-data text-on-surface">${formattedDate}</div>
                            <div class="w-1/3 font-body-sm text-body-sm text-on-surface truncate" title="${hist.nama_tindakan}">${hist.nama_tindakan}</div>
                            <div class="w-1/3 text-right">
                                <span class="inline-flex items-center gap-1 bg-surface-variant text-on-surface px-2 py-0.5 rounded font-label-sm text-label-sm">
                                    ${hist.no_register_kunjungan}
                                </span>
                            </div>
                        </div>
                    `;
                });
            } else {
                historyContainer.innerHTML = '<div class="p-2 text-center text-on-surface-variant font-body-sm">Belum ada riwayat pemotongan sesi.</div>';
            }
        })
        .catch(err => {
            historyContainer.innerHTML = '<div class="p-2 text-center text-error font-body-sm">Gagal memuat riwayat.</div>';
        });
}

function displayKapasitas(noErm, records) {
    displayErm.textContent = noErm;
    cardsContainer.innerHTML = '';
    
    if(!records || records.length === 0) {
        cardsContainer.innerHTML = '<p class="text-on-surface-variant col-span-full">Tidak ada paket ditemukan untuk nomor ERM ini.</p>';
        return;
    }

    records.forEach(kap => {
        const card = document.createElement('div');
        // Tailwind classes for the card
        let borderClass = kap.status === 'AKTIF' ? 'border-primary' : 'border-outline-variant opacity-75';
        card.className = `flex flex-col gap-4 bg-surface-container-lowest border ${borderClass} rounded-lg p-6 shadow-sm h-full relative overflow-hidden group hover:shadow-md transition-shadow`;

        let statusColor = kap.status === 'AKTIF' ? 'text-primary' : (kap.status === 'HABIS' ? 'text-error' : 'text-on-surface-variant');

        card.innerHTML = `
            <div class="flex items-center justify-between border-b border-outline-variant pb-3 mb-1">
                <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined ${statusColor}">${kap.status === 'AKTIF' ? 'assignment' : 'assignment_late'}</span>
                    Paket: ${kap.nama_paket}
                </h3>
            </div>
            <div class="flex flex-col gap-stack-compact">
                <!-- Data List -->
                <div class="flex flex-col gap-3 bg-surface-container-low rounded p-3 border border-outline-variant mt-2">
                    <div class="flex justify-between items-center">
                        <span class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">event_busy</span> Tgl Expired
                        </span>
                        <span class="font-body-sm text-body-sm text-on-surface font-medium">${kap.tanggal_expired}</span>
                    </div>
                    <div class="h-[1px] w-full bg-outline-variant opacity-50"></div>
                    <div class="flex justify-between items-center">
                        <span class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">confirmation_number</span> Nomor Register
                        </span>
                        <span class="font-body-sm text-body-sm text-on-surface font-medium">${kap.nomor_register}</span>
                    </div>
                    <div class="h-[1px] w-full bg-outline-variant opacity-50"></div>
                    <div class="flex justify-between items-center">
                        <span class="font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">hourglass_bottom</span> Sisa Sesi
                        </span>
                        <span class="font-body-sm text-body-sm font-bold ${statusColor} text-lg">${kap.sisa} <span class="font-normal text-sm">/ ${kap.total_sesi}</span></span>
                    </div>
                </div>
            </div>
            <div class="mt-auto pt-4 flex gap-2">
                <button class="btn-use-session flex-1 bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-on-primary-fixed-variant transition-colors disabled:opacity-50 disabled:cursor-not-allowed" onclick='openModal(${JSON.stringify(kap)})' ${kap.status !== 'AKTIF' || parseInt(kap.sisa) <= 0 ? 'disabled' : ''}>
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

document.querySelectorAll('.modal-close').forEach(btn => {
    btn.onclick = () => useSessionModal.classList.add('hidden');
});

// Since Tailwind uses class 'hidden' to hide, we must override the 'active' usage in openModal
window.openModal = function(record) {
    activeKapasitasData = record;
    modalPaketName.textContent = record.nama_paket;
    modalSisaSesiBaru.textContent = record.sisa - 1; // It's record.sisa
    useSessionModal.classList.remove('hidden');
}

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
        useSessionModal.classList.add('hidden');

        if(res.status === 201) {
            showMessage(res.body.message, 'success');
            // Refresh data
            document.getElementById('btnSearch').click();
        } else {
            alert(res.body.message || 'Gagal memotong sesi.');
        }
    });
});

// --- KASIR MODULE LOGIC ---
const tableKasirHistory = document.getElementById('tableKasirHistory');

function loadKasirHistory() {
    if(!tableKasirHistory) return;
    
    tableKasirHistory.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-on-surface-variant"><span class="material-symbols-outlined spin">progress_activity</span> Memuat data...</td></tr>';
    
    const fKategori = document.getElementById('filterKategori') ? document.getElementById('filterKategori').value : 'PAKET';
    const fNama = document.getElementById('filterNama') ? document.getElementById('filterNama').value : 'PAKET';
    const fAsal = document.getElementById('filterAsalTabel') ? document.getElementById('filterAsalTabel').value : '';
    const fTambah = document.getElementById('filterFcrTambah') ? document.getElementById('filterFcrTambah').value : '';

    apiFetch(`${API_BASE}/kasir?action=history&kategori=${fKategori}&nama=${fNama}&asaltabel=${fAsal}&fcrtambah=${fTambah}`)
        .then(res => res.json())
        .then(data => {
            tableKasirHistory.innerHTML = '';
            if(data.records && data.records.length > 0) {
                data.records.forEach(r => {
                    const tgl = new Date(r.tanggal_transaksi).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
                    const biaya = parseInt(r.total_biaya).toLocaleString('id-ID');
                    
                    tableKasirHistory.innerHTML += `
                        <tr class="hover:bg-primary/5 transition-all group">
                            <td class="py-5 px-6 text-[14px] whitespace-nowrap text-on-surface-variant group-hover:text-primary transition-colors">${tgl}</td>
                            <td class="py-5 px-6 text-[14px] whitespace-nowrap font-bold text-primary">${r.no_register}</td>
                            <td class="py-5 px-6 text-[14px] whitespace-nowrap text-on-surface-variant">${r.no_erm}</td>
                            <td class="py-5 px-6 text-[14px] font-semibold text-on-surface">${r.nama_pasien}</td>
                            <td class="py-5 px-6 text-[14px] text-on-surface-variant group-hover:text-on-surface transition-colors">${r.nama_paket}</td>
                            <td class="py-5 px-6 text-[14px] text-right whitespace-nowrap font-bold text-green-600">Rp ${biaya}</td>
                        </tr>
                    `;
                });
            } else if (data.message) {
                tableKasirHistory.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-error"><strong>Pesan Server:</strong> ${data.message}</td></tr>`;
            } else {
                tableKasirHistory.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-on-surface-variant">Belum ada riwayat transaksi penjualan paket.</td></tr>';
            }
        })
        .catch(err => {
            console.error(err);
            tableKasirHistory.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-error">Gagal memuat data riwayat transaksi.</td></tr>';
        });
}

// We override showApp to add loadKasirHistory instead of loadMasterPaket
const originalShowApp = showApp;
window.showApp = function() {
    originalShowApp();
    if(currentUser && (currentUser.role === 'admin' || currentUser.role === 'kasir')) {
        loadKasirHistory();
    }
}

// --- MASTER DATA MODULE LOGIC ---
const btnTambahPaket = document.getElementById('btnTambahPaket');
const modalMasterPaket = document.getElementById('modalMasterPaket');
const formMasterPaket = document.getElementById('formMasterPaket');
const tableMasterPaket = document.getElementById('tableMasterPaket');

if(btnTambahPaket) {
    btnTambahPaket.addEventListener('click', () => {
        document.getElementById('modalMasterPaketTitle').innerHTML = '<span class="material-symbols-outlined text-primary">add_box</span> Tambah Paket Baru';
        formMasterPaket.reset();
        document.getElementById('paketId').value = '';
        modalMasterPaket.classList.remove('hidden');
    });
}

document.querySelectorAll('.modal-close-paket').forEach(btn => {
    btn.onclick = () => modalMasterPaket.classList.add('hidden');
});

if(formMasterPaket) {
    formMasterPaket.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const id = document.getElementById('paketId').value;
        const payload = {
            id: id,
            nama: document.getElementById('paketNama').value,
            tipe_paket: document.getElementById('paketTipe').value,
            total_sesi: document.getElementById('paketTotalSesi').value,
            masa_berlaku_hari: document.getElementById('paketMasaBerlaku').value
        };

        const action = id ? 'update_paket' : 'add_paket';

        apiFetch(`${API_BASE}/master?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json().then(data => ({status: res.status, body: data})))
        .then(res => {
            if(res.status === 201 || res.status === 200) {
                modalMasterPaket.classList.add('hidden');
                loadMasterData();
            } else {
                alert(res.body.message || 'Gagal menyimpan paket');
            }
        });
    });
}

function loadMasterData() {
    apiFetch(`${API_BASE}/paket`)
        .then(res => res.json())
        .then(data => {
            tableMasterPaket.innerHTML = '';
            if(data.records && data.records.length > 0) {
                data.records.forEach(p => {
                    tableMasterPaket.innerHTML += `
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="px-6 py-4 font-medium">${p.nama}</td>
                            <td class="px-6 py-4">${p.tipe_paket}</td>
                            <td class="px-6 py-4 text-center">${p.total_sesi}</td>
                            <td class="px-6 py-4 text-center">${p.masa_berlaku_hari} Hari</td>
                            <td class="px-6 py-4 text-right">
                                <button onclick='editPaket(${JSON.stringify(p)})' class="text-primary hover:text-primary-container p-1"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                                <button onclick='hapusPaket(${p.id})' class="text-error hover:text-error-container p-1"><span class="material-symbols-outlined text-[20px]">delete</span></button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tableMasterPaket.innerHTML = '<tr><td colspan="5" class="text-center py-4">Belum ada data paket.</td></tr>';
            }
        });
}

window.editPaket = function(p) {
    document.getElementById('modalMasterPaketTitle').innerHTML = '<span class="material-symbols-outlined text-primary">edit</span> Edit Paket';
    document.getElementById('paketId').value = p.id;
    document.getElementById('paketNama').value = p.nama;
    document.getElementById('paketTipe').value = p.tipe_paket;
    document.getElementById('paketTotalSesi').value = p.total_sesi;
    document.getElementById('paketMasaBerlaku').value = p.masa_berlaku_hari;
    modalMasterPaket.classList.remove('hidden');
}

window.hapusPaket = function(id) {
    if(confirm('Apakah Anda yakin ingin menghapus paket ini?')) {
        apiFetch(`${API_BASE}/master?action=delete_paket`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json().then(data => ({status: res.status, body: data})))
        .then(res => {
            if(res.status === 200) {
                loadMasterData();
            } else {
                alert(res.body.message || 'Gagal menghapus paket');
            }
        });
    }
}

// --- AUDIT MODULE LOGIC ---
const auditTotalPasien = document.getElementById('auditTotalPasien');
const auditSesiHariIni = document.getElementById('auditSesiHariIni');
const auditSisaSesiGlobal = document.getElementById('auditSisaSesiGlobal');
const tableAuditLogs = document.getElementById('tableAuditLogs');

function loadAuditData() {
    // Load Summary
    apiFetch(`${API_BASE}/audit?action=summary`)
        .then(res => res.json())
        .then(data => {
            if(auditTotalPasien) auditTotalPasien.textContent = data.total_pasien_aktif || 0;
            if(auditSesiHariIni) auditSesiHariIni.textContent = data.sesi_hari_ini || 0;
            if(auditSisaSesiGlobal) auditSisaSesiGlobal.textContent = data.sisa_sesi_total || 0;
        });

    // Load Logs
    apiFetch(`${API_BASE}/audit?action=logs`)
        .then(res => res.json())
        .then(data => {
            if(tableAuditLogs) {
                tableAuditLogs.innerHTML = '';
                if(data && data.length > 0) {
                    data.forEach(log => {
                        const dateObj = new Date(log.tanggal_paket);
                        const formattedDate = dateObj.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'});
                        tableAuditLogs.innerHTML += `
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="px-6 py-4 font-mono-data text-xs">${formattedDate}</td>
                                <td class="px-6 py-4 font-bold text-primary">${log.no_erm}</td>
                                <td class="px-6 py-4"><span class="bg-surface-variant px-2 py-1 rounded text-xs">${log.no_register_kunjungan}</span></td>
                                <td class="px-6 py-4">${log.nama_paket} <span class="font-bold">(${log.sesi_ke})</span></td>
                                <td class="px-6 py-4">${log.nama_tindakan}</td>
                            </tr>
                        `;
                    });
                } else {
                    tableAuditLogs.innerHTML = '<tr><td colspan="5" class="text-center py-4">Belum ada log aktifitas.</td></tr>';
                }
            }
        });
}

// Override switchView to fetch data when entering certain views
const originalSwitchView = switchView;
window.switchView = function(viewKey) {
    originalSwitchView(viewKey);
    if(viewKey === 'master' && currentUser && currentUser.role === 'admin') {
        loadMasterData();
    }
    if(viewKey === 'audit' && currentUser && (currentUser.role === 'admin' || currentUser.role === 'manajemen')) {
        loadAuditData();
    }
}

// --- HEADER INTERACTIONS (DROPDOWNS) ---
function setupHeaderDropdowns() {
    const btnNotif = document.getElementById('btnHeaderNotif');
    const ddNotif = document.getElementById('dropdownNotif');
    
    const btnSettings = document.getElementById('btnHeaderSettings');
    const ddSettings = document.getElementById('dropdownSettings');
    
    const btnProfile = document.getElementById('btnHeaderProfile');
    const ddProfile = document.getElementById('dropdownProfile');

    // Close all dropdowns
    function closeAllDropdowns() {
        [ddNotif, ddSettings, ddProfile].forEach(dd => {
            if (dd) {
                dd.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
            }
        });
    }

    function toggleDropdown(dd) {
        if (!dd) return;
        const isClosed = dd.classList.contains('opacity-0');
        closeAllDropdowns();
        if (isClosed) {
            dd.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
        }
    }

    if (btnNotif) btnNotif.addEventListener('click', (e) => { e.stopPropagation(); toggleDropdown(ddNotif); });
    if (btnSettings) btnSettings.addEventListener('click', (e) => { e.stopPropagation(); toggleDropdown(ddSettings); });
    if (btnProfile) btnProfile.addEventListener('click', (e) => { e.stopPropagation(); toggleDropdown(ddProfile); });

    // Close on click outside
    document.addEventListener('click', () => closeAllDropdowns());
    
    // Prevent closing when clicking inside dropdowns
    [ddNotif, ddSettings, ddProfile].forEach(dd => {
        if (dd) dd.addEventListener('click', (e) => e.stopPropagation());
    });

    // Logout from header
    const btnHeaderLogout = document.getElementById('btnHeaderLogout');
    if (btnHeaderLogout) {
        btnHeaderLogout.addEventListener('click', () => {
            btnLogout.click();
        });
    }
}

// --- DARK MODE LOGIC ---
function setupDarkMode() {
    const btnToggleTheme = document.getElementById('btnToggleTheme');
    const themeIcon = document.getElementById('themeIcon');
    const themeToggleThumb = document.getElementById('themeToggleThumb');
    const themeToggleTrack = document.getElementById('themeToggleTrack');
    const htmlElement = document.documentElement;
    
    // Load preference
    const isDarkMode = localStorage.getItem('prm_theme') === 'dark';
    
    function applyTheme(isDark) {
        if (isDark) {
            htmlElement.classList.add('dark');
            if (themeIcon) {
                themeIcon.textContent = 'light_mode';
                themeIcon.classList.add('text-primary');
            }
            if (themeToggleThumb) themeToggleThumb.classList.add('translate-x-4');
            if (themeToggleTrack) themeToggleTrack.classList.add('bg-primary');
        } else {
            htmlElement.classList.remove('dark');
            if (themeIcon) {
                themeIcon.textContent = 'dark_mode';
                themeIcon.classList.remove('text-primary');
            }
            if (themeToggleThumb) themeToggleThumb.classList.remove('translate-x-4');
            if (themeToggleTrack) themeToggleTrack.classList.remove('bg-primary');
        }
    }

    applyTheme(isDarkMode);

    if (btnToggleTheme) {
        btnToggleTheme.addEventListener('click', () => {
            const willBeDark = !htmlElement.classList.contains('dark');
            localStorage.setItem('prm_theme', willBeDark ? 'dark' : 'light');
            applyTheme(willBeDark);
        });
    }
}

// Initialize header functions
document.addEventListener('DOMContentLoaded', () => {
    setupHeaderDropdowns();
    setupDarkMode();
});
