const API_BASE = (() => {
    if (window.location.protocol === 'file:') {
        return 'http://localhost:8000/local/api/index.php';
    }
    const path = window.location.pathname;
    const dir = path.substring(0, path.lastIndexOf('/') + 1);
    return `${window.location.origin}${dir}api/index.php`;
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
    'audit': document.getElementById('view-audit'),
    'pasien': document.getElementById('view-pasien')
};

// Menus
const menus = {
    'erm': document.getElementById('menu-erm'),
    'kasir': document.getElementById('menu-kasir'),
    'master': document.getElementById('menu-master'),
    'audit': document.getElementById('menu-audit'),
    'pasien': document.getElementById('menu-pasien')
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
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(res => {
            btnLogin.disabled = false;
            document.getElementById('loginText').classList.remove('hidden');
            document.getElementById('loginLoader').classList.add('hidden');

            if (res.status === 200) {
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
    Object.values(menus).forEach(m => { if (m) m.style.display = 'none'; });

    let defaultView = 'kasir';

    if (role === 'admin') {
        Object.values(menus).forEach(m => { if (m) m.style.display = 'flex'; });
        defaultView = 'kasir';
    } else if (role === 'kasir') {
        menus['kasir'].style.display = 'flex';
        menus['pasien'].style.display = 'flex';
        defaultView = 'kasir';
    } else if (role === 'manajemen') {
        menus['audit'].style.display = 'flex';
        menus['pasien'].style.display = 'flex';
        defaultView = 'audit';
    } else if (role === 'erm') {
        menus['erm'].style.display = 'flex';
        menus['pasien'].style.display = 'flex';
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

    if (menus[viewKey]) {
        menus[viewKey].classList.remove('text-on-surface-variant');
        menus[viewKey].classList.add('bg-primary-container', 'text-on-primary-container', 'font-bold', 'border-r-4', 'border-primary');
    }

    // Update Views
    Object.values(views).forEach(v => v.classList.add('hidden'));
    if (views[viewKey]) views[viewKey].classList.remove('hidden');
}

// --- FETCH HELPER WITH AUTH ---
function apiFetch(url, options = {}) {
    if (!options.headers) options.headers = {};
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
    if (noErm) fetchKapasitas(noErm);
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

            if (res.status === 200) {
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
            if (res.status === 200 && res.body.records.length > 0) {
                res.body.records.forEach(hist => {
                    const dateObj = new Date(hist.tanggal_paket);
                    const formattedDate = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

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

    if (!records || records.length === 0) {
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
window.openModal = function (record) {
    activeKapasitasData = record;
    modalPaketName.textContent = record.nama_paket;
    modalSisaSesiBaru.textContent = record.sisa - 1; // It's record.sisa
    useSessionModal.classList.remove('hidden');
}

confirmUseBtn.addEventListener('click', () => {
    if (!activeKapasitasData) return;

    confirmUseBtn.disabled = true;
    confirmUseBtn.textContent = 'Memproses...';

    const now = new Date();
    const tzOffset = now.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(Date.now() - tzOffset)).toISOString().slice(0, 19).replace('T', ' ');

    const payload = {
        id_kapasitas: activeKapasitasData.id,
        sisa_saat_ini: activeKapasitasData.sisa,
        no_erm: activeKapasitasData.no_erm,
        tanggal_paket: localISOTime,
        sesi_ke: (activeKapasitasData.total_sesi - activeKapasitasData.sisa) + 1
    };

    apiFetch(`${API_BASE}/pasien?action=gunakan_sesi`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(res => {
            confirmUseBtn.disabled = false;
            confirmUseBtn.textContent = 'Gunakan Sesi';
            useSessionModal.classList.add('hidden');

            if (res.status === 201) {
                showMessage(res.body.message, 'success');
                // Refresh data
                if (window.loadPasienMaster) window.loadPasienMaster();
                // Close the detail modal after success
                if (window.closeDetailModal) {
                    window.closeDetailModal();
                }
            } else {
                alert(res.body.message || 'Gagal memotong sesi.');
            }
        });
});

// --- KASIR MODULE LOGIC ---
const tableKasirHistory = document.getElementById('tableKasirHistory');

function loadKasirHistory(page = 1) {
    if (!tableKasirHistory) return;

    // Set default tanggal to current month if empty
    const filterTanggalKasir = document.getElementById('filterTanggalKasir');

    if (filterTanggalKasir && !filterTanggalKasir.value) {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        filterTanggalKasir.value = `${yyyy}-${mm}`;
    }

    tableKasirHistory.innerHTML = '<tr><td colspan="8" class="p-6 text-center text-on-surface-variant"><span class="material-symbols-outlined spin">progress_activity</span> Memuat data...</td></tr>';

    const fNamaDrop = document.getElementById('filterNamaDropdown') ? document.getElementById('filterNamaDropdown').value : '';
    const fNamaText = document.getElementById('filterNamaText') ? document.getElementById('filterNamaText').value : '';
    const fStatus = document.getElementById('filterStatusDropdown') ? document.getElementById('filterStatusDropdown').value : '';
    const fTanggal = filterTanggalKasir ? filterTanggalKasir.value : '';

    apiFetch(`${API_BASE}/kasir?action=history&page=${page}&nama_drop=${encodeURIComponent(fNamaDrop)}&nama_text=${encodeURIComponent(fNamaText)}&status=${encodeURIComponent(fStatus)}&tanggal=${encodeURIComponent(fTanggal)}`)
        .then(res => res.json())
        .then(data => {
            tableKasirHistory.innerHTML = '';
            const paginationContainer = document.getElementById('kasirPagination');
            if (paginationContainer) paginationContainer.innerHTML = '';

            if (data.records && data.records.length > 0) {
                data.records.forEach(r => {
                    const tgl = new Date(r.tanggal_transaksi).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                    const biaya = parseInt(r.total_biaya).toLocaleString('id-ID');

                    const isProcessed = r.is_processed == 1;
                    const checkboxAttrs = isProcessed ? 'checked disabled class="w-4 h-4 text-surface-variant bg-surface-container-lowest border-outline-variant/50 rounded cursor-not-allowed"' : 'class="w-4 h-4 text-primary bg-surface-container-lowest border-outline-variant rounded focus:ring-primary focus:ring-2 cursor-pointer kasir-checkbox" onchange="handleKasirCheckboxChange()"';
                    const rowClass = isProcessed ? 'bg-surface-container-lowest/50 opacity-60' : 'hover:bg-primary/5 transition-all group';

                    tableKasirHistory.innerHTML += `
                        <tr class="${rowClass}">
                            <td class="py-5 px-6">
                                <input type="checkbox" value="${r.id_transaksi}" ${checkboxAttrs}>
                            </td>
                            <td class="py-5 px-6 text-[14px] whitespace-nowrap text-on-surface-variant group-hover:text-primary transition-colors">${tgl}</td>
                            <td class="py-5 px-6 text-[14px] whitespace-nowrap font-bold text-primary">${r.no_register}</td>
                            <td class="py-5 px-6 text-[14px] font-semibold text-on-surface">${r.nama_pasien}</td>
                            <td class="py-5 px-6 text-[14px] text-on-surface-variant group-hover:text-on-surface transition-colors">
                                <span class="font-mono-data text-primary mr-1">${r.kode_paket || '-'}</span> 
                                ${r.nama_paket}
                            </td>
                            <td class="py-5 px-6 text-right whitespace-nowrap">
                                <div class="text-[14px] font-bold text-green-600">Rp ${biaya}</div>
                                <!-- <div class="text-[10px] text-on-surface-variant mt-0.5">
                                    RMV: ${r.rmv || '-'} | Unit: ${r.rmunit || '-'} | Cust: ${r.cust || '-'}
                                </div> -->
                            </td>
                        </tr>
                    `;
                });

                // Render Pagination
                if (data.total_pages > 1 && paginationContainer) {
                    let startPage = Math.max(1, data.current_page - 2);
                    let endPage = Math.min(data.total_pages, startPage + 4);

                    if (endPage - startPage < 4) {
                        startPage = Math.max(1, endPage - 4);
                    }

                    let pagHtml = `<div class="flex items-center gap-1">`;
                    pagHtml += `<span class="text-sm text-on-surface-variant mr-4">Total: ${data.total_records} data (Hal ${data.current_page} / ${data.total_pages})</span>`;

                    // Prev Button
                    if (data.current_page > 1) {
                        pagHtml += `<button onclick="loadKasirHistory(${data.current_page - 1})" class="px-3 py-1 rounded border border-outline-variant hover:bg-surface-container-high transition text-sm">Prev</button>`;
                    } else {
                        pagHtml += `<button disabled class="px-3 py-1 rounded border border-outline-variant/50 text-outline-variant cursor-not-allowed text-sm">Prev</button>`;
                    }

                    // Page Numbers
                    for (let i = startPage; i <= endPage; i++) {
                        if (i === data.current_page) {
                            pagHtml += `<button class="px-3 py-1 rounded border border-primary bg-primary text-on-primary font-bold text-sm">${i}</button>`;
                        } else {
                            pagHtml += `<button onclick="loadKasirHistory(${i})" class="px-3 py-1 rounded border border-outline-variant hover:bg-surface-container-high transition text-sm">${i}</button>`;
                        }
                    }

                    // Next Button
                    if (data.current_page < data.total_pages) {
                        pagHtml += `<button onclick="loadKasirHistory(${data.current_page + 1})" class="px-3 py-1 rounded border border-outline-variant hover:bg-surface-container-high transition text-sm">Next</button>`;
                    } else {
                        pagHtml += `<button disabled class="px-3 py-1 rounded border border-outline-variant/50 text-outline-variant cursor-not-allowed text-sm">Next</button>`;
                    }

                    pagHtml += `</div>`;

                    paginationContainer.innerHTML = pagHtml;
                }

            } else if (data.message) {
                tableKasirHistory.innerHTML = `<tr><td colspan="8" class="p-6 text-center text-error"><strong>Pesan Server:</strong> ${data.message}</td></tr>`;
            } else {
                tableKasirHistory.innerHTML = '<tr><td colspan="8" class="p-6 text-center text-on-surface-variant italic">Belum ada transaksi kasir.</td></tr>';
            }
        })
        .catch(err => {
            console.error("Kasir error:", err);
            tableKasirHistory.innerHTML = `<tr><td colspan="8" class="p-6 text-center text-error">Gagal terhubung ke server Kasir.</td></tr>`;
        });
}

function handleKasirCheckboxChange() {
    const checkboxes = document.querySelectorAll('.kasir-checkbox:checked');
    const btnSimpan = document.getElementById('btnSimpanKasir');
    if (checkboxes.length > 0) {
        btnSimpan.removeAttribute('disabled');
    } else {
        btnSimpan.setAttribute('disabled', 'true');
    }
}

function simpanProsesKasir() {
    const checkboxes = document.querySelectorAll('.kasir-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) return;

    const btnSimpan = document.getElementById('btnSimpanKasir');
    const originalText = btnSimpan.innerHTML;
    btnSimpan.innerHTML = '<span class="material-symbols-outlined spin text-[18px]">progress_activity</span><span>Menyimpan...</span>';
    btnSimpan.setAttribute('disabled', 'true');

    apiFetch(`${API_BASE}/kasir?action=mark_processed`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: ids })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadKasirHistory(); // reload history
                alert(data.message);
                // Kembalikan teks asli secara eksplisit
                btnSimpan.innerHTML = '<span class="material-symbols-outlined text-[18px]">save</span><span>Simpan & Kunci</span>';
            } else {
                alert(data.message || 'Gagal menyimpan.');
                btnSimpan.removeAttribute('disabled');
                btnSimpan.innerHTML = '<span class="material-symbols-outlined text-[18px]">save</span><span>Simpan & Kunci</span>';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
            btnSimpan.removeAttribute('disabled');
            btnSimpan.innerHTML = '<span class="material-symbols-outlined text-[18px]">save</span><span>Simpan & Kunci</span>';
        });
}

// We override showApp to add loadKasirHistory instead of loadMasterPaket
const originalShowApp = showApp;
window.showApp = function () {
    originalShowApp();
    if (currentUser && (currentUser.role === 'admin' || currentUser.role === 'kasir')) {
        loadKasirHistory();
    }
}

// --- MASTER DATA MODULE LOGIC ---
const btnTambahPaket = document.getElementById('btnTambahPaket');
const modalMasterPaket = document.getElementById('modalMasterPaket');
const formMasterPaket = document.getElementById('formMasterPaket');
const tableMasterPaket = document.getElementById('tableMasterPaket');

if (btnTambahPaket) {
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

if (formMasterPaket) {
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
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(res => {
                if (res.status === 201 || res.status === 200) {
                    modalMasterPaket.classList.add('hidden');
                    loadMasterData();
                } else {
                    alert(res.body.message || 'Gagal menyimpan paket');
                }
            });
    });
}

function loadMasterData(page = 1) {
    apiFetch(`${API_BASE}/paket?page=${page}`)
        .then(res => res.json())
        .then(data => {
            tableMasterPaket.innerHTML = '';
            if (data.records && data.records.length > 0) {
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

                // Render Pagination
                const paginationContainer = document.getElementById('masterPagination');
                if (data.total_pages > 1 && paginationContainer) {
                    let startPage = Math.max(1, data.current_page - 2);
                    let endPage = Math.min(data.total_pages, startPage + 4);

                    if (endPage - startPage < 4) {
                        startPage = Math.max(1, endPage - 4);
                    }

                    let pagHtml = `<div class="flex items-center gap-1">`;
                    pagHtml += `<span class="text-sm text-on-surface-variant mr-4">Total: ${data.total_records} data (Hal ${data.current_page} / ${data.total_pages})</span>`;

                    // Prev Button
                    if (data.current_page > 1) {
                        pagHtml += `<button onclick="loadMasterData(${data.current_page - 1})" class="px-3 py-1 rounded border border-outline-variant hover:bg-surface-container-high transition text-sm">Prev</button>`;
                    } else {
                        pagHtml += `<button disabled class="px-3 py-1 rounded border border-outline-variant/50 text-outline-variant cursor-not-allowed text-sm">Prev</button>`;
                    }

                    // Page Numbers
                    for (let i = startPage; i <= endPage; i++) {
                        if (i === data.current_page) {
                            pagHtml += `<button class="px-3 py-1 rounded border border-primary bg-primary text-on-primary font-bold text-sm">${i}</button>`;
                        } else {
                            pagHtml += `<button onclick="loadMasterData(${i})" class="px-3 py-1 rounded border border-outline-variant hover:bg-surface-container-high transition text-sm">${i}</button>`;
                        }
                    }

                    // Next Button
                    if (data.current_page < data.total_pages) {
                        pagHtml += `<button onclick="loadMasterData(${data.current_page + 1})" class="px-3 py-1 rounded border border-outline-variant hover:bg-surface-container-high transition text-sm">Next</button>`;
                    } else {
                        pagHtml += `<button disabled class="px-3 py-1 rounded border border-outline-variant/50 text-outline-variant cursor-not-allowed text-sm">Next</button>`;
                    }

                    pagHtml += `</div>`;

                    paginationContainer.innerHTML = pagHtml;
                } else if (paginationContainer) {
                    paginationContainer.innerHTML = '';
                }

            } else {
                tableMasterPaket.innerHTML = '<tr><td colspan="5" class="text-center py-4">Belum ada data paket.</td></tr>';
            }
        });
}

window.editPaket = function (p) {
    document.getElementById('modalMasterPaketTitle').innerHTML = '<span class="material-symbols-outlined text-primary">edit</span> Edit Paket';
    document.getElementById('paketId').value = p.id;
    document.getElementById('paketNama').value = p.nama;
    document.getElementById('paketTipe').value = p.tipe_paket;
    document.getElementById('paketTotalSesi').value = p.total_sesi;
    document.getElementById('paketMasaBerlaku').value = p.masa_berlaku_hari;
    modalMasterPaket.classList.remove('hidden');
}

window.hapusPaket = function (id) {
    if (confirm('Apakah Anda yakin ingin menghapus paket ini?')) {
        apiFetch(`${API_BASE}/master?action=delete_paket`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(res => {
                if (res.status === 200) {
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

function loadAuditData(page = 1) {
    // Load Summary
    apiFetch(`${API_BASE}/audit?action=summary`)
        .then(res => res.json())
        .then(data => {
            if (auditTotalPasien) auditTotalPasien.textContent = data.total_pasien_aktif || 0;
            if (auditSesiHariIni) auditSesiHariIni.textContent = data.sesi_hari_ini || 0;
            if (auditSisaSesiGlobal) auditSisaSesiGlobal.textContent = data.sisa_sesi_total || 0;
        });

    // Load Logs
    apiFetch(`${API_BASE}/audit?action=logs&page=${page}`)
        .then(res => res.json())
        .then(data => {
            if (tableAuditLogs) {
                tableAuditLogs.innerHTML = '';
                if (data.records && data.records.length > 0) {
                    data.records.forEach(log => {
                        const dateObj = new Date(log.tanggal_paket);
                        const formattedDate = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                        tableAuditLogs.innerHTML += `
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="px-6 py-4 font-mono-data text-xs">${formattedDate}</td>
                                <td class="px-6 py-4"><span class="bg-surface-variant px-2 py-1 rounded text-xs">${log.no_register_kunjungan}</span></td>
                                <td class="px-6 py-4">${log.nama_paket} <span class="font-bold">(${log.sesi_ke})</span></td>
                                <td class="px-6 py-4">${log.nama_tindakan}</td>
                            </tr>
                        `;
                    });

                    // Render Pagination
                    const paginationContainer = document.getElementById('auditPagination');
                    if (data.total_pages > 1 && paginationContainer) {
                        let startPage = Math.max(1, data.current_page - 2);
                        let endPage = Math.min(data.total_pages, startPage + 4);

                        if (endPage - startPage < 4) {
                            startPage = Math.max(1, endPage - 4);
                        }

                        let pagHtml = `<div class="flex items-center gap-1">`;
                        pagHtml += `<span class="text-sm text-on-surface-variant mr-4">Total: ${data.total_records} data (Hal ${data.current_page} / ${data.total_pages})</span>`;

                        // Prev Button
                        if (data.current_page > 1) {
                            pagHtml += `<button onclick="loadAuditData(${data.current_page - 1})" class="px-3 py-1 rounded border border-outline-variant hover:bg-surface-container-high transition text-sm">Prev</button>`;
                        } else {
                            pagHtml += `<button disabled class="px-3 py-1 rounded border border-outline-variant/50 text-outline-variant cursor-not-allowed text-sm">Prev</button>`;
                        }

                        // Page Numbers
                        for (let i = startPage; i <= endPage; i++) {
                            if (i === data.current_page) {
                                pagHtml += `<button class="px-3 py-1 rounded border border-primary bg-primary text-on-primary font-bold text-sm">${i}</button>`;
                            } else {
                                pagHtml += `<button onclick="loadAuditData(${i})" class="px-3 py-1 rounded border border-outline-variant hover:bg-surface-container-high transition text-sm">${i}</button>`;
                            }
                        }

                        // Next Button
                        if (data.current_page < data.total_pages) {
                            pagHtml += `<button onclick="loadAuditData(${data.current_page + 1})" class="px-3 py-1 rounded border border-outline-variant hover:bg-surface-container-high transition text-sm">Next</button>`;
                        } else {
                            pagHtml += `<button disabled class="px-3 py-1 rounded border border-outline-variant/50 text-outline-variant cursor-not-allowed text-sm">Next</button>`;
                        }

                        pagHtml += `</div>`;

                        paginationContainer.innerHTML = pagHtml;
                    } else if (paginationContainer) {
                        paginationContainer.innerHTML = '';
                    }
                } else {
                    tableAuditLogs.innerHTML = '<tr><td colspan="4" class="text-center py-4">Belum ada log aktifitas.</td></tr>';
                }
            }
        });
}

// Override switchView to fetch data when entering certain views
const originalSwitchView = switchView;
window.switchView = function (viewKey) {
    originalSwitchView(viewKey);
    if (viewKey === 'master' && currentUser && currentUser.role === 'admin') {
        loadMasterData();
    }
    if (viewKey === 'audit' && currentUser && (currentUser.role === 'admin' || currentUser.role === 'manajemen')) {
        loadAuditData();
    }
    if (viewKey === 'pasien' && currentUser) {
        loadPasienMaster();
        loadTindakanDropdown();
    }
}

window.loadTindakanDropdown = function () {
    const dropdown = document.getElementById('modalTindakanDropdown');
    if (!dropdown) return;

    apiFetch(`${API_BASE}/tindakan`)
        .then(res => res.json())
        .then(data => {
            dropdown.innerHTML = '<option value="" disabled selected>Pilih Tindakan...</option>';
            if (data.records && data.records.length > 0) {
                data.records.forEach(t => {
                    dropdown.innerHTML += `<option value="${t.id}">${t.kode_tindakan} - ${t.nama_tindakan}</option>`;
                });
            }
        })
        .catch(err => {
            console.error(err);
            dropdown.innerHTML = '<option value="" disabled selected>Gagal memuat tindakan...</option>';
        });
}

// --- PASIEN MODULE LOGIC ---
const tablePasienMaster = document.getElementById('tablePasienMaster');
const tablePasienDetail = document.getElementById('tablePasienDetail');

window.loadPasienMaster = function () {
    const tableBody = document.getElementById('tablePasienList');
    const dateFilter = document.getElementById('filterTanggalKunjungan').value;

    if (!dateFilter) {
        tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">Silakan pilih Tanggal Kunjungan terlebih dahulu</td></tr>`;
        return;
    }

    tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-on-surface-variant flex items-center justify-center gap-2"><span class="material-symbols-outlined animate-spin">sync</span> Memuat data...</td></tr>`;

    apiFetch(`${API_BASE}/pasien?action=all_kapasitas&date=${dateFilter}`)
        .then(res => res.json())
        .then(data => {
            tableBody.innerHTML = '';
            // Reset detail
            if (tablePasienDetail) {
                tablePasienDetail.innerHTML = `<tr><td colspan="4" class="text-center py-8 text-on-surface-variant italic">Pilih salah satu pasien di tabel sebelah kiri untuk melihat detail.</td></tr>`;
            }

            if (data && data.records && data.records.length > 0) {
                window.allKapasitasData = data.records; // Save for modal lookup
                let rowToClick = null;
                data.records.forEach(kap => {
                    const isHabis = kap.status.toUpperCase() === 'HABIS';
                    const statusClass = isHabis ? 'bg-surface-variant text-on-surface-variant font-bold' : 'bg-green-100 text-green-800 font-bold';
                    const row = document.createElement('tr');
                    row.className = isHabis ? 'bg-surface-container-lowest opacity-60 transition-colors pasien-row' : 'hover:bg-surface-container-lowest transition-colors pasien-row';
                    row.setAttribute('data-status', kap.status.toUpperCase());

                    row.innerHTML = `
                        <td class="px-6 py-4 text-xs font-mono-data">${new Date(kap.tanggal_beli).toLocaleDateString('id-ID')}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-on-surface">${kap.nama_pasien}</div>
                            <div class="text-xs text-on-surface-variant">${kap.no_erm}</div>
                        </td>
                        <td class="px-6 py-4">${kap.nama_paket}</td>
                        <td class="px-6 py-4 font-bold ${kap.sisa > 0 ? 'text-primary' : 'text-error'}">${kap.sisa} / ${kap.total_sesi}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs ${statusClass}">${kap.status}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="openDetailModal('${kap.id}')" class="bg-surface hover:bg-surface-container-high text-primary border border-outline-variant rounded px-3 py-1 text-sm transition-colors shadow-sm font-medium">Detail</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
                // Re-apply filters to the newly loaded rows
                if (window.filterPasienTable) window.filterPasienTable();
            } else {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-on-surface-variant italic">Tidak ada data paket untuk tanggal kunjungan ini.</td></tr>';
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-error">Terjadi kesalahan saat memuat data.</td></tr>';
        });
};

window.filterPasienTable = function () {
    const searchInput = document.getElementById('pasienSearchInput');
    const statusFilter = document.getElementById('pasienStatusFilter');
    if (!searchInput || !statusFilter) return;

    const searchTerm = searchInput.value.toLowerCase();
    const statusTerm = statusFilter.value.toUpperCase();

    const rows = document.querySelectorAll('#tablePasienList .pasien-row');

    rows.forEach(row => {
        const textContent = row.innerText.toLowerCase();
        const rowStatus = row.getAttribute('data-status') || '';

        const matchesSearch = textContent.includes(searchTerm);
        const matchesStatus = statusTerm === '' || rowStatus === statusTerm;

        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const btnGunakanSesi = document.getElementById('btnGunakanSesi');
    if (btnGunakanSesi) {
        btnGunakanSesi.addEventListener('click', () => {
            if (window.activeKapasitasData && window.openModal) {
                window.openModal(window.activeKapasitasData);
            }
        });
    }
});
window.openDetailModal = function (id_kapasitas) {
    if (!window.allKapasitasData) return;
    const kap = window.allKapasitasData.find(k => k.id == id_kapasitas);
    if (!kap) return;

    const tablePasienDetail = document.getElementById('tablePasienDetail');
    if (!tablePasienDetail) return;

    tablePasienDetail.innerHTML = '<tr><td colspan="3" class="text-center py-8">Memuat data...</td></tr>';

    // Show modal
    const modal = document.getElementById('detailPasienModal');
    if (modal) {
        modal.classList.remove('hidden');

        // Populate Top Cards
        document.getElementById('dm_nama').innerText = kap.nama_pasien;
        document.getElementById('dm_rm').innerText = `No. Register ${kap.no_erm || '-'}`;
        document.getElementById('dm_paket').innerText = kap.nama_paket;
        document.getElementById('dm_id_paket').innerText = kap.id;
        document.getElementById('dm_beli').innerText = new Date(kap.tanggal_beli).toLocaleDateString('id-ID');
        document.getElementById('dm_exp').innerText = kap.tanggal_expired ? new Date(kap.tanggal_expired).toLocaleDateString('id-ID') : '-';

        const isHabis = kap.status.toUpperCase() === 'HABIS';
        const badge = document.getElementById('dm_badge');
        badge.innerText = kap.status;
        badge.className = isHabis ? 'px-2 py-1 rounded text-xs bg-surface-variant text-on-surface-variant font-bold' : 'px-2 py-1 rounded text-xs bg-green-100 text-green-800 font-bold';

        const used = kap.total_sesi - kap.sisa;
        document.getElementById('dm_used_total').innerText = `${used} / ${kap.total_sesi}`;
        document.getElementById('dm_sisa').innerText = `${kap.sisa} sesi`;
        const pct = kap.total_sesi > 0 ? (used / kap.total_sesi * 100) : 0;
        document.getElementById('dm_progress_bar').style.width = `${pct}%`;
    }

    // Set "Gunakan Sesi" button in modal
    const btnModal = document.getElementById('btnGunakanSesiModal');
    if (btnModal) {
        if (kap.sisa > 0) {
            btnModal.disabled = false;
            btnModal.onclick = () => {
                window.activeKapasitasData = {
                    id: kap.id,
                    nama_pasien: kap.nama_pasien,
                    no_erm: kap.no_erm,
                    nomor_register: kap.nomor_register,
                    nama_paket: kap.nama_paket,
                    sisa: kap.sisa,
                    total_sesi: kap.total_sesi
                };
                window.openModal(window.activeKapasitasData);
            };
        } else {
            btnModal.disabled = true;
            btnModal.onclick = null;
        }
    }

    apiFetch(`${API_BASE}/pasien?action=riwayat_by_kapasitas&id_kapasitas=${id_kapasitas}&all=true`)
        .then(res => res.json())
        .then(data => {
            if (data && data.records) {
                window.currentDetailRiwayat = data.records;
                renderDetailRiwayatPage(1);
            } else {
                tablePasienDetail.innerHTML = '<tr><td colspan="3" class="text-center py-6 text-on-surface-variant italic">Belum ada penggunaan</td></tr>';
                document.getElementById('detailPagination').classList.add('hidden');
            }
        })
        .catch(err => {
            console.error(err);
            tablePasienDetail.innerHTML = '<tr><td colspan="3" class="text-center py-6 text-error">Gagal memuat riwayat pemakaian.</td></tr>';
        });
};

window.renderDetailRiwayatPage = function (page) {
    const tablePasienDetail = document.getElementById('tablePasienDetail');
    const paginationContainer = document.getElementById('detailPagination');
    if (!tablePasienDetail || !window.currentDetailRiwayat) return;

    const limit = 3;
    const totalRecords = window.currentDetailRiwayat.length;
    const totalPages = Math.ceil(totalRecords / limit) || 1;

    if (page < 1) page = 1;
    if (page > totalPages) page = totalPages;

    const start = (page - 1) * limit;
    const end = start + limit;
    const pageData = window.currentDetailRiwayat.slice(start, end);

    tablePasienDetail.innerHTML = '';

    if (pageData.length > 0) {
        pageData.forEach(row => {
            const tgl = new Date(row.tanggal_paket).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

            const d = new Date();
            const todayStr = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            const rowDateStr = row.tanggal_paket ? row.tanggal_paket.substring(0, 10) : '';
            let deleteBtnHTML = '';
            if (rowDateStr === todayStr) {
                deleteBtnHTML = `<button onclick="batalSesi('${row.id}')" class="text-error hover:bg-error-container p-1 rounded transition-colors ml-2" title="Batal Sesi"><span class="material-symbols-outlined text-[18px] align-middle">delete</span></button>`;
            }

            tablePasienDetail.innerHTML += `
                <tr class="hover:bg-surface-container-lowest transition-colors">
                    <td class="py-2 pr-2">${tgl}</td>
                    <td class="py-2 px-2">Sesi ${row.sesi_ke || '-'}</td>
                    <td class="py-2 pl-2 text-on-surface-variant font-mono-data text-[13px] flex justify-between items-center">
                        <span>No. Register ${row.no_erm || '-'}</span>
                        <div>
                            ${deleteBtnHTML}
                        </div>
                    </td>
                </tr>
            `;
        });

        // Render Pagination
        if (totalRecords > limit) {
            if (paginationContainer) paginationContainer.classList.remove('hidden');
            let paginationHTML = '';

            paginationHTML += `<button onclick="renderDetailRiwayatPage(${page - 1})" ${page === 1 ? 'disabled' : ''} class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition disabled:opacity-50 disabled:cursor-not-allowed bg-surface"><span class="material-symbols-outlined text-[18px]">chevron_left</span></button>`;

            for (let i = 1; i <= totalPages; i++) {
                if (i === page) {
                    paginationHTML += `<button class="w-8 h-8 flex items-center justify-center rounded bg-primary text-on-primary font-bold text-sm shadow-sm">${i}</button>`;
                } else {
                    paginationHTML += `<button onclick="renderDetailRiwayatPage(${i})" class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant text-on-surface hover:bg-surface-container-high transition bg-surface text-sm">${i}</button>`;
                }
            }

            paginationHTML += `<button onclick="renderDetailRiwayatPage(${page + 1})" ${page === totalPages ? 'disabled' : ''} class="w-8 h-8 flex items-center justify-center rounded border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition disabled:opacity-50 disabled:cursor-not-allowed bg-surface"><span class="material-symbols-outlined text-[18px]">chevron_right</span></button>`;

            if (paginationContainer) paginationContainer.innerHTML = paginationHTML;
        } else {
            if (paginationContainer) paginationContainer.classList.add('hidden');
        }
    } else {
        tablePasienDetail.innerHTML = '<tr><td colspan="3" class="text-center py-6 text-on-surface-variant italic">Belum ada penggunaan</td></tr>';
        if (paginationContainer) paginationContainer.classList.add('hidden');
    }
};

window.closeDetailModal = function () {
    const modal = document.getElementById('detailPasienModal');
    if (modal) modal.classList.add('hidden');
};

window.batalSesi = function (id) {
    if (!confirm('Apakah Anda yakin ingin membatalkan penggunaan sesi ini? Sisa sesi akan dikembalikan.')) return;

    apiFetch(`${API_BASE}/pasien?action=batal_sesi`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(res => {
            if (res.status === 200) {
                showMessage(res.body.message, 'success');
                if (window.loadPasienMaster) window.loadPasienMaster();
                if (window.closeDetailModal) window.closeDetailModal();
            } else {
                alert(res.body.message || 'Gagal membatalkan sesi.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat membatalkan sesi.');
        });
};

function exportAuditCSV() {
    apiFetch(`${API_BASE}/audit?action=logs&all=true`)
        .then(res => res.json())
        .then(data => {
            if (!data || data.length === 0) {
                alert('Tidak ada data untuk diexport.');
                return;
            }

            // Create CSV headers
            let csvContent = "Tanggal,No ERM,No Register,Nama Paket,Sesi Ke,Nama Tindakan\n";

            data.forEach(log => {
                // Escape fields with quotes if they contain commas
                const escapeCsv = (str) => `"${(str || '').toString().replace(/"/g, '""')}"`;

                const row = [
                    escapeCsv(log.tanggal_paket),
                    escapeCsv(log.no_erm),
                    escapeCsv(log.no_register_kunjungan),
                    escapeCsv(log.nama_paket),
                    escapeCsv(log.sesi_ke),
                    escapeCsv(log.nama_tindakan)
                ];
                csvContent += row.join(",") + "\n";
            });

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.setAttribute("href", url);
            link.setAttribute("download", `audit_log_${new Date().toISOString().slice(0, 10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        })
        .catch(err => {
            console.error(err);
            alert('Gagal mengekspor data.');
        });
}

// --- NOTIFICATIONS LOGIC ---
function loadNotifications() {
    const notifContainer = document.getElementById('notifContainer');
    const notifBadge = document.getElementById('notifBadge');
    const btnReadNotif = document.getElementById('btnReadNotif');

    if (!notifContainer) return;

    Promise.all([
        apiFetch(`${API_BASE}/kasir?action=history`).then(res => res.json()).catch(() => ({ records: [] })),
        apiFetch(`${API_BASE}/audit?action=logs&all=true`).then(res => res.json()).catch(() => [])
    ]).then(([kasirData, auditData]) => {
        let notifications = [];

        if (kasirData && kasirData.records && Array.isArray(kasirData.records)) {
            kasirData.records.slice(0, 10).forEach(r => {
                notifications.push({
                    type: 'kasir',
                    date: new Date(r.tanggal_transaksi),
                    title: 'Transaksi Baru (Kasir)',
                    desc: `Transaksi untuk pasien ${r.nama_pasien} telah berhasil ditambahkan.`,
                    icon: 'receipt_long'
                });
            });
        }

        if (auditData && Array.isArray(auditData)) {
            auditData.slice(0, 10).forEach(r => {
                notifications.push({
                    type: 'audit',
                    date: new Date(r.tanggal_paket),
                    title: 'Penggunaan Sesi Pelayanan',
                    desc: `Sesi ${r.sesi_ke} pasien ${r.no_erm} untuk tindakan ${r.nama_tindakan} telah dicatat.`,
                    icon: 'assignment_turned_in'
                });
            });
        }

        notifications.sort((a, b) => b.date - a.date);
        notifications = notifications.slice(0, 5);

        notifContainer.innerHTML = '';
        if (notifications.length > 0) {
            if (notifBadge) notifBadge.classList.remove('hidden');

            notifications.forEach(n => {
                const diffMs = new Date() - n.date;
                const diffMins = Math.floor(diffMs / 60000);
                let timeText = diffMins < 60 ? `${diffMins} menit yang lalu` :
                    (diffMins < 1440 ? `${Math.floor(diffMins / 60)} jam yang lalu` :
                        `${Math.floor(diffMins / 1440)} hari yang lalu`);
                if (diffMins < 1 || isNaN(diffMins)) timeText = 'Baru saja';

                const bgIcon = n.type === 'kasir' ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant';

                notifContainer.innerHTML += `
                    <div class="p-3 hover:bg-surface-container-lowest rounded-xl transition-colors cursor-pointer flex gap-3 notif-item" onclick="handleNotifClick('${n.type}')">
                        <div class="w-8 h-8 rounded-full ${bgIcon} flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[18px]">${n.icon}</span>
                        </div>
                        <div class="flex flex-col">
                            <p class="text-[13px] text-on-surface font-semibold leading-tight">${n.title}</p>
                            <p class="text-[12px] text-on-surface-variant mt-1 line-clamp-2">${n.desc}</p>
                            <p class="text-[10px] text-primary mt-1">${timeText}</p>
                        </div>
                    </div>
                `;
            });
        } else {
            notifContainer.innerHTML = '<div class="p-4 text-center text-[12px] text-on-surface-variant">Belum ada notifikasi baru.</div>';
            if (notifBadge) notifBadge.classList.add('hidden');
        }
    });

    if (btnReadNotif) {
        btnReadNotif.onclick = () => {
            if (notifBadge) notifBadge.classList.add('hidden');
            notifContainer.querySelectorAll('.notif-item').forEach(el => el.classList.add('opacity-60'));
        };
    }
}

window.handleNotifClick = function(type) {
    const notifDropdown = document.getElementById('notifDropdown');
    if (notifDropdown) notifDropdown.classList.add('hidden'); // Close dropdown

    if (type === 'kasir') {
        if (window.switchView) window.switchView('kasir');
    } else if (type === 'audit') {
        if (window.switchView) window.switchView('pasien');
    }
};


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
    loadNotifications();

    // Set default tanggal Kasir to current month
    const filterTanggalKasir = document.getElementById('filterTanggalKasir');
    if (filterTanggalKasir) {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        filterTanggalKasir.value = `${yyyy}-${mm}`;
    }

    document.getElementById('filterTanggalKunjungan').addEventListener('change', loadPasienMaster);
});
