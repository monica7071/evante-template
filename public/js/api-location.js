let provinces = [];
let amphures = [];
let tambons = [];
let allLocations = [];
let sortOrder = 'desc';
let sortBy = 'created_at';
let searchTerm = '';

function filterLocationsBySearchTerm(term = '') {
    const normalized = (term || '').toLowerCase().trim();
    const zoneSections = document.querySelectorAll('[data-zone-section]');
    let hasVisibleCard = false;

    zoneSections.forEach(section => {
        const cards = section.querySelectorAll('[data-project-card]');
        let sectionVisible = false;

        cards.forEach(card => {
            const keywords = card.dataset.projectKeywords || '';
            const matched = !normalized || keywords.includes(normalized);
            card.classList.toggle('hidden', !matched);
            if (matched) {
                sectionVisible = true;
            }
        });

        section.classList.toggle('hidden', !sectionVisible);
        if (sectionVisible) {
            hasVisibleCard = true;
        }
    });

    const emptyState = document.getElementById('location-search-empty');
    if (emptyState) {
        emptyState.classList.toggle('hidden', hasVisibleCard);
    }
}

function filterZonesBySearchTerm(term = '') {
    const normalized = (term || '').toLowerCase().trim();
    const zoneSections = document.querySelectorAll('[data-zone-section]');
    let hasVisibleSection = false;

    zoneSections.forEach(section => {
        const zoneName = (section.dataset.zoneName || '').toLowerCase();
        const matched = !normalized || zoneName.includes(normalized);
        section.classList.toggle('hidden', !matched);
        if (matched) {
            hasVisibleSection = true;
        }
    });

    const emptyState = document.getElementById('location-search-empty');
    if (emptyState) {
        emptyState.classList.toggle('hidden', hasVisibleSection);
    }
}
// ฟังก์ชันในการโหลดข้อมูล API ทั้งหมดตั้งแต่เริ่มต้น
// Small helper to safely fetch JSON and throw informative errors when response
// is not OK or not JSON. This prevents "Unexpected token" on HTML error pages.
async function fetchJson(url, options = {}) {
    const res = await fetch(url, options);
    const text = await res.text().catch(() => '');

    if (!res.ok) {
        throw new Error(`HTTP ${res.status} ${res.statusText} from ${url}: ${text.slice(0, 200)}`);
    }

    // แปลง text เป็น JSON โดยตรง เพื่อรองรับ GitHub Raw ที่ header ไม่ใช่ application/json
    try {
        return JSON.parse(text);
    } catch (err) {
        throw new Error(`Failed to parse JSON from ${url}: ${text.slice(0, 200)}`);
    }
}

async function fetchWithFallback(fetchers = []) {
    let lastError;
    for (const fetcher of fetchers) {
        try {
            const data = await fetcher();
            if (data) {
                return data;
            }
        } catch (err) {
            lastError = err;
        }
    }
    throw lastError || new Error('No data sources succeeded');
}

// โหลดข้อมูลจังหวัด
fetchWithFallback([
    () => fetchJson('/data/thai/provinces.json'),
    () => fetchJson('/api/thai/provinces'),
    () => fetchJson('https://raw.githubusercontent.com/Dhanabhon/thailand-geodata/refs/heads/main/json/provinces.json')
])
    .then(data => {
        const raw = Array.isArray(data) ? data : (Array.isArray(data?.provinces) ? data.provinces : []);
        provinces = raw.map(item => ({
            id: item.PROVINCE_ID ?? item.id ?? item.CODE ?? null,
            name_en: item.PROVINCE_ENGLISH ?? item.name_en ?? item.name ?? '',
            name_th: item.PROVINCE_THAI ?? item.name_th ?? '',
            code: item.CODE ?? null,
        })).filter(p => p.id && (p.name_en || p.name_th));
        renderProvinces();
    })
    .catch(err => {
        console.error('Failed to load provinces from any source:', err);
    });

// โหลดข้อมูลอำเภอทั้งหมดไว้ล่วงหน้า
fetchWithFallback([
    () => fetchJson('/data/thai/districts.json'),
    () => fetchJson('/api/thai/amphures'),
    () => fetchJson('https://raw.githubusercontent.com/Dhanabhon/thailand-geodata/refs/heads/main/json/districts.json')
])
    .then(data => {
        const raw = Array.isArray(data) ? data : (Array.isArray(data?.districts) ? data.districts : []);
        amphures = raw.map(item => ({
            id: item.DISTRICT_ID ?? item.id ?? item.DISTRICT_CODE ?? item.CODE ?? null,
            province_id: item.PROVINCE_ID ?? item.province_id ?? null,
            name_en: item.DISTRICT_ENGLISH ?? item.name_en ?? item.DISTRICT_THAI ?? '',
            name_th: item.DISTRICT_THAI ?? item.name_th ?? '',
            code: item.DISTRICT_CODE ?? item.CODE ?? null,
        })).filter(a => a.id && a.province_id);
        const selectedProvince = document.getElementById('province');
        if (selectedProvince && selectedProvince.value) {
            loadAmphure();
        }
    })
    .catch(err => {
        console.error('Failed to load amphures from any source:', err);
    });

// โหลดข้อมูลตำบลทั้งหมดไว้ล่วงหน้า
fetchWithFallback([
    () => fetchJson('/data/thai/sub_districts.json'),
    () => fetchJson('/api/thai/tambons'),
    () => fetchJson('https://raw.githubusercontent.com/Dhanabhon/thailand-geodata/refs/heads/main/json/sub_districts.json')
])
    .then(data => {
        const raw = Array.isArray(data) ? data : (Array.isArray(data?.sub_districts) ? data.sub_districts : []);
        tambons = raw.map(item => ({
            id: item.SUB_DISTRICT_ID ?? item.id ?? item.SUB_DISTRICT_CODE ?? item.CODE ?? null,
            district_id: item.DISTRICT_ID ?? item.district_id ?? null,
            name_en: item.SUB_DISTRICT_ENGLISH ?? item.name_en ?? item.SUB_DISTRICT_THAI ?? '',
            name_th: item.SUB_DISTRICT_THAI ?? item.name_th ?? '',
            zip_code: item.POSTAL_CODE ?? item.zip_code ?? item.zipcode ?? '',
        })).filter(t => t.id && t.district_id);
        const selectedAmphure = document.getElementById('amphure');
        if (selectedAmphure && selectedAmphure.value) {
            loadTambon();
        }
    })
    .catch(err => {
        console.error('Failed to load tambons from any source:', err);
    });
// เรนเดอร์รายชื่อจังหวัดเมื่อข้อมูลพร้อมและ DOM มี element
function renderProvinces() {
    const provinceSelect = document.getElementById('province');
    if (!provinceSelect) return;
    // เคลียร์เพื่อกันซ้ำ
    provinceSelect.innerHTML = '<option value="">Select Province</option>';
    if (provinces.length > 0) {
        provinces
            .slice()
            .sort((a, b) => (a.name_en || '').localeCompare(b.name_en || ''))
            .forEach(province => {
                const option = document.createElement('option');
                option.value = province.id;
                option.textContent = province.name_en;
                provinceSelect.appendChild(option);
            });
    }
    // Ensure it's interactable
    provinceSelect.disabled = false;
    provinceSelect.style.pointerEvents = 'auto';
    provinceSelect.tabIndex = 0;
}
// ฟังก์ชันในการโหลดข้อมูลอำเภอตามจังหวัดที่เลือก
function loadAmphure() {
    const provinceId = document.getElementById('province').value;
    const amphureSelect = document.getElementById('amphure');
    const tambonSelect = document.getElementById('tambon');
    // ล้างข้อมูลอำเภอและตำบล
    amphureSelect.innerHTML = '<option value="">Select District</option>';
    tambonSelect.innerHTML = '<option value="">Select Subdistrict</option>';
    // Disable selects until we have data
    amphureSelect.disabled = true;
    tambonSelect.disabled = true;
    if (provinceId && amphures.length > 0) {
        // กรองอำเภอตามรหัสจังหวัด
        const filteredAmphures = amphures
            .filter(amphure => amphure.province_id == provinceId)
            .sort((a, b) => (a.name_en || '').localeCompare(b.name_en || ''));
        // เพิ่มตัวเลือกอำเภอ
        filteredAmphures.forEach(amphure => {
            const option = document.createElement('option');
            option.value = amphure.id;
            option.textContent = amphure.name_en;
            amphureSelect.appendChild(option);
        });
        amphureSelect.disabled = filteredAmphures.length === 0;
    }
}
// ฟังก์ชันในการโหลดข้อมูลตำบลตามอำเภอที่เลือก
function loadTambon() {
    const amphureId = document.getElementById('amphure').value;
    const tambonSelect = document.getElementById('tambon');
    const postalCodeInput = document.getElementById('postal_code');
    // ล้างข้อมูลตำบลและรหัสไปรษณีย์
    tambonSelect.innerHTML = '<option value="">Select Subdistrict</option>';
    postalCodeInput.value = '';
    // Disable until we have data
    tambonSelect.disabled = true;
    if (amphureId && tambons.length > 0) {
        // กรองตำบลตามรหัสอำเภอ
        const filteredTambons = tambons
            .filter(tambon => tambon.district_id == amphureId)
            .sort((a, b) => (a.name_en || '').localeCompare(b.name_en || ''));
        // เพิ่มตัวเลือกตำบล
        filteredTambons.forEach(tambon => {
            const option = document.createElement('option');
            option.value = tambon.id;
            option.textContent = tambon.name_en;
            option.dataset.postal_code = (tambon.zip_code || tambon.zipcode || ''); // เก็บรหัสไปรษณีย์ไว้ใน dataset
            tambonSelect.appendChild(option);
        });
        tambonSelect.disabled = filteredTambons.length === 0;
    }
}
// ฟังก์ชันอัพเดทรหัสไปรษณีย์เมื่อเลือกตำบล
function updatePostalCode() {
    const tambonSelect = document.getElementById('tambon');
    const postalCodeInput = document.getElementById('postal_code');
    const selectedOption = tambonSelect.options[tambonSelect.selectedIndex];
    if (selectedOption && selectedOption.value) {
        postalCodeInput.value = selectedOption.dataset.postal_code || '';
    } else {
        postalCodeInput.value = '';
    }
}
// ฟังก์ชันโหลด location ทั้งหมดจาก backend
function fetchZones() {
    fetch(`/api/zone?sort=${sortOrder}&sortBy=${sortBy}`)
        .then(response => response.json())
        .then(zones => renderZones(zones));
}
function renderZones(zones) {
    const container = document.querySelector('[data-location-list]');
    if (!container) return;
    container.innerHTML = '';
    zones.forEach(zone => {
        // ดึง projects ของ zone
        fetch(`/api/location?project_id=${zone.zone_id}`)
            .then(res => res.json())
            .then(projects => {
                // render โซนและ projects
                const sectionDiv = document.createElement('div');
                sectionDiv.className = 'mb-8';
                sectionDiv.innerHTML = `
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-base font-semibold text-gray-700">${zone.zone_name}</div>
                        <div class="text-sm text-gray-500">Project Total : ${projects.length}</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        ${projects.map(project => `
                            <div class="bg-gray-100 rounded-2xl px-6 py-4 flex flex-col justify-between min-h-[80px]">
                                <div class="font-semibold text-gray-800 mb-2">${project.project_name}</div>
                                <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                                    <i class="fi fi-sr-marker" style="color: #C19165;"></i>
                                    ${zone.tumbon} ${zone.district} ${zone.province}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
                container.appendChild(sectionDiv);
            });
    });
}
function setupSortAndSearch() {
    const sortDropdown = document.getElementById('location-sort-dropdown');
    if (sortDropdown) {
        sortDropdown.value = sortOrder;
        sortDropdown.addEventListener('change', function() {
            sortOrder = this.value;
            fetchZones();
        });
    }
    const searchInput = document.getElementById('search-location');
    if (searchInput) {
        searchInput.value = '';
        searchInput.addEventListener('input', function() {
            filterZonesBySearchTerm(this.value);
        });
    }
}
// ฟังก์ชันแสดงผล location ในหน้า
function renderLocations(locations) {
    const container = document.querySelector('[data-location-list]');
    if (!container) return;
    container.innerHTML = '';

    // จัดกลุ่มตาม zone_name
    const groupedLocations = {};
    locations.forEach(loc => {
        if (!groupedLocations[loc.zone_name]) {
            groupedLocations[loc.zone_name] = [];
        }
        groupedLocations[loc.zone_name].push(loc);
    });

    // แสดงผลแต่ละกลุ่ม
    for (const zoneName in groupedLocations) {
        const zoneLocations = groupedLocations[zoneName];

        // สร้างส่วนหัวของกลุ่ม
        const sectionDiv = document.createElement('div');
        sectionDiv.className = 'mb-8';
        sectionDiv.innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <div class="text-base font-semibold text-gray-700">${zoneName}</div>
                <div class="text-sm text-gray-400">Project Total : <span class="font-bold text-gray-700">${zoneLocations.length}</span></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" data-zone-locations>
            </div>
        `;

        const locationsGrid = sectionDiv.querySelector('[data-zone-locations]');

        // เพิ่มแต่ละ location ในกลุ่มนี้
        zoneLocations.forEach(loc => {
            const div = document.createElement('div');
            div.className = 'bg-gray-100 rounded-2xl px-6 py-4 flex flex-col justify-between min-h-[80px]';
            div.innerHTML = `
                <div class="font-semibold text-gray-800 mb-2">${loc.address || loc.zone_name}</div>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <i class="fi fi-sr-marker" style="color: #C19165;"></i>
                    ${loc.tumbon} ${loc.district} ${loc.province}
                </div>
            `;
            locationsGrid.appendChild(div);
        });

        container.appendChild(sectionDiv);
    }
}
// ฟังก์ชัน submit ฟอร์มสร้าง location
function setupLocationForm() {
    const form = document.getElementById('locationForm');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        // ตรวจสอบการกรอกข้อมูล
        const formErrors = document.getElementById('form-errors');
        const zone_name = form.querySelector('input[name="zone_name"]').value.trim();
        const provinceId = form.querySelector('#province').value;
        const amphureId = form.querySelector('#amphure').value;
        const tambonId = form.querySelector('#tambon').value;
        if (!zone_name || !provinceId || !amphureId || !tambonId) {
            formErrors.textContent = 'Please fill in all the data';
            formErrors.style.display = 'block';
            return;
        }
        // Ensure datasets loaded
        if (!(Array.isArray(provinces) && provinces.length) || !(Array.isArray(amphures) && amphures.length) || !(Array.isArray(tambons) && tambons.length)) {
            formErrors.textContent = 'Location data is still loading. Please wait a moment and try again.';
            formErrors.style.display = 'block';
            return;
        }
        // province
        const provinceObj = provinces.find(p => p.id == provinceId);
        const province = provinceObj ? (provinceObj.name_en || provinceObj.name || provinceObj.name_th || '') : '';
        // district
        const amphureObj = amphures.find(a => a.id == amphureId);
        const district = amphureObj ? (amphureObj.name_en || amphureObj.name || amphureObj.name_th || '') : '';
        // tumbon
        const tambonObj = tambons.find(t => t.id == tambonId);
        const tumbon = tambonObj ? (tambonObj.name_en || tambonObj.name || tambonObj.name_th || '') : '';
        const postal_code = form.querySelector('#postal_code').value;
        if (!province || !district || !tumbon) {
            formErrors.textContent = 'Invalid location selection. Please reselect Province/District/Subdistrict.';
            formErrors.style.display = 'block';
            return;
        }
        const address = '';
        const buildings = '';
        // แสดง loading
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.textContent = 'Saving...';
        submitBtn.disabled = true;
        fetch('/api/zone', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                zone_name,
                province,
                district,
                tumbon,
                postal_code,
                address,
                buildings
            })
        })
        .then(async res => {
            if (!res.ok) {
                let errorMsg = 'Server responded with status: ' + res.status;
                try {
                    const data = await res.json();
                    if (data && data.message) {
                        errorMsg = data.message;
                    }
                    // Laravel validation errors
                    if (data && data.errors) {
                        const first = Object.values(data.errors)[0];
                        if (first && first[0]) errorMsg = first[0];
                    }
                } catch (_) {
                    // ignore JSON parse error
                }
                throw new Error(errorMsg);
            }
            return res.json();
        })
        .then(data => {
            fetchZones(); // โหลดข้อมูล Zone ใหม่
            document.dispatchEvent(new CustomEvent('close-modal'));
            if (window.Alpine) {
                try {
                    const modalElement = document.querySelector('[x-data]');
                    if (modalElement) {
                        window.Alpine.evaluate(modalElement, 'showModal = false');
                    }
                } catch (e) {
                    console.warn('Could not access Alpine directly:', e);
                }
            }
            const closeButton = document.querySelector('[x-cloak] button');
            if (closeButton) {
                closeButton.click();
            }
            form.reset();
            // Refresh the whole page so newly created data appears immediately
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            formErrors.textContent = 'Error: ' + error.message;
            formErrors.style.display = 'block';
        })
        .finally(() => {
            submitBtn.textContent = originalBtnText;
            submitBtn.disabled = false;
        });
    });
}
  // เคลียร์ฟอร์มเมื่อปิด Modal
function setupModalCloseHandler() {
    // หาปุ่มปิดใน modal
    const closeButtons = document.querySelectorAll('[x-cloak] button');
    if (closeButtons.length > 0) {
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const form = document.getElementById('locationForm');
                if (form) {
                    form.reset();
                    const formErrors = document.getElementById('form-errors');
                    if (formErrors) {
                        formErrors.style.display = 'none';
                    }
                }
            });
        });
    }

    // ฟังก์ชันทำความสะอาด form เมื่อปิด modal
    function resetFormOnClose() {
        const form = document.getElementById('locationForm');
        if (form) {
            form.reset();
            const formErrors = document.getElementById('form-errors');
            if (formErrors) {
                formErrors.style.display = 'none';
            }
        }
    }

    // ลงทะเบียน event listener สำหรับ Alpine.js
    document.addEventListener('close-modal', resetFormOnClose);

    // ตรวจสอบการคลิกภายนอก modal (backdrop)
    const backdrop = document.querySelector('.fixed.inset-0.bg-gray-900');
    if (backdrop) {
        backdrop.addEventListener('click', resetFormOnClose);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    fetchZones();
    setupLocationForm();
    setupModalCloseHandler();
    setupSortAndSearch();
    // เผื่อกรณีโหลดข้อมูลจังหวัดเสร็จก่อน DOM พร้อม
    renderProvinces();
    // Initial disable until user selects and data is ready
    const amphureSelect = document.getElementById('amphure');
    const tambonSelect = document.getElementById('tambon');
    if (amphureSelect) amphureSelect.disabled = true;
    if (tambonSelect) tambonSelect.disabled = true;
    // Ensure inline handlers can call these functions
    window.loadAmphure = loadAmphure;
    window.loadTambon = loadTambon;
    window.updatePostalCode = updatePostalCode;
});

