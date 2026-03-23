<script>
document.addEventListener('DOMContentLoaded', function () {
    const provinceSelect = document.getElementById('location_province');
    const districtSelect = document.getElementById('location_district');
    const subdistrictSelect = document.getElementById('location_subdistrict');
    const postalInput = document.getElementById('location_postal_code');

    if (!provinceSelect || !districtSelect || !subdistrictSelect) {
        return;
    }

    const provinces = @json($provinceOptions ?? []);
    const districts = @json($districtOptions ?? []);
    const subDistricts = @json($subDistrictOptions ?? []);

    const renderOptions = (select, items, labelKey, idKey, selectedValue, extra = null) => {
        select.innerHTML = '<option value="">-- เลือก --</option>';
        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item[labelKey];
            option.dataset.id = item[idKey];
            option.textContent = item[labelKey];
            if (extra && typeof extra === 'function') {
                extra(option, item);
            }
            if (selectedValue && selectedValue === item[labelKey]) {
                option.selected = true;
            }
            select.appendChild(option);
        });
    };

    const getSelectedId = (select) => parseInt(select.selectedOptions[0]?.dataset.id || '0', 10) || null;

    const selectedProvinceName = provinceSelect.dataset.selected || provinceSelect.value;
    renderOptions(provinceSelect, provinces, 'PROVINCE_THAI', 'PROVINCE_ID', selectedProvinceName);

    const populateDistricts = () => {
        const provinceId = getSelectedId(provinceSelect);
        const filtered = districts.filter((d) => d.PROVINCE_ID === provinceId);
        const selectedDistrictName = districtSelect.dataset.selected || districtSelect.value;
        renderOptions(districtSelect, filtered, 'DISTRICT_THAI', 'DISTRICT_ID', selectedDistrictName);
        populateSubdistricts();
    };

    const syncPostalCode = () => {
        if (!postalInput) return;
        const selected = subdistrictSelect.selectedOptions[0];
        postalInput.value = selected?.dataset.postal || '';
    };

    const populateSubdistricts = () => {
        const districtId = getSelectedId(districtSelect);
        const filtered = subDistricts.filter((s) => s.DISTRICT_ID === districtId);
        const selectedSubdistrictName = subdistrictSelect.dataset.selected || subdistrictSelect.value;
        renderOptions(
            subdistrictSelect,
            filtered,
            'SUB_DISTRICT_THAI',
            'SUB_DISTRICT_ID',
            selectedSubdistrictName,
            (option, item) => {
                option.dataset.postal = item.POSTAL_CODE || '';
            }
        );
        syncPostalCode();
    };

    provinceSelect.addEventListener('change', populateDistricts);
    districtSelect.addEventListener('change', populateSubdistricts);
    subdistrictSelect.addEventListener('change', syncPostalCode);

    populateDistricts();
    syncPostalCode();
});
</script>
