/**
 * @typedef {Object} SearchOptions
 * @property {string[]|string} keys - Kunci (properti) dalam objek data yang akan dicari. Bisa berupa string tunggal atau array string. Mendukung notasi titik untuk properti bersarang (misal: 'bank.mapel.nama_mapel').
 * @property {boolean} [caseSensitive=false] - Apakah pencarian peka huruf besar/kecil. Defaultnya tidak peka (false).
 * @property {boolean} [exactMatch=false] - Apakah harus mencocokkan persis (true) atau sebagian (false). Defaultnya sebagian (false).
 * @property {function(any): string} [prepareValue=(value) => String(value)] - Fungsi untuk mempersiapkan nilai data sebelum dicari (misal, mengubah angka jadi string).
 * @property {function(string): string[]} [prepareQuery=(query) => query.toLowerCase().split(' ').filter(s => s)] - Fungsi untuk mempersiapkan kueri pencarian (misal, lowercase dan split by space).
 * @property {function(string[], string): boolean} [testQuery=(queryParts, dataValue) => { ... }] - Fungsi untuk menguji kueri dengan nilai data.
 */

/**
 * Plugin pencarian data sederhana untuk array objek, mendukung properti bersarang dan multiple arrays dalam objek induk,
 * mengembalikan hasil terpisah untuk setiap array sumber.
 *
 * @param {Object[]|Object} sourceData - Array objek yang akan dicari, atau objek yang berisi array yang akan dicari.
 * @param {SearchOptions & { arrayKeys?: string[] }} options - Opsi konfigurasi pencarian.
 * Jika sourceData adalah objek, `arrayKeys` (misal: ['schedules_1', 'schedules_2']) harus disediakan.
 * @returns {Object} Objek dengan metode `search`.
 */
function simpleDataSearch(sourceData, options) {
    const defaultOptions = {
        caseSensitive: false,
        exactMatch: false,
        prepareValue: (value) => String(value),
        prepareQuery: (query) => {
            return query.toLowerCase().split(' ').filter(s => s);
        },
        testQuery: (queryParts, dataValue) => {
            const preparedDataValue = options.caseSensitive ? dataValue : dataValue.toLowerCase();
            if (options.exactMatch) {
                return preparedDataValue === (options.caseSensitive ? queryParts.join(' ') : queryParts.join(' '));
            } else {
                for (let i = 0; i < queryParts.length; i++) {
                    if (preparedDataValue.indexOf(queryParts[i]) === -1) {
                        return false;
                    }
                }
                return true;
            }
        }
    };

    const config = { ...defaultOptions, ...options };

    // Pastikan `keys` selalu berupa array
    if (typeof config.keys === 'string') {
        config.keys = [config.keys];
    } else if (!Array.isArray(config.keys)) {
        throw new Error("Opsi 'keys' harus berupa string atau array of strings.");
    }

    // Helper untuk mengambil nilai properti bersarang
    function getNestedValue(obj, path) {
        return path.split('.').reduce((current, key) => {
            return (current && typeof current === 'object' && current.hasOwnProperty(key)) ? current[key] : undefined;
        }, obj);
    }

    // --- LOGIKA BARU UNTUK PENYIMPANAN ARRAY SUMBER ---
    // Simpan referensi ke array asli, bukan menggabungkannya
    let sourceArrays = {};
    if (Array.isArray(sourceData)) {
        // Jika sourceData sudah array, beri kunci default
        sourceArrays['default'] = sourceData;
    } else if (typeof sourceData === 'object' && sourceData !== null && Array.isArray(config.arrayKeys)) {
        // Jika sourceData adalah objek DAN arrayKeys ditentukan
        for (const arrayKey of config.arrayKeys) {
            const potentialArray = sourceData[arrayKey];
            if (Array.isArray(potentialArray)) {
                sourceArrays[arrayKey] = potentialArray; // Simpan array dengan kuncinya
            } else {
                console.warn(`Properti '${arrayKey}' dalam objek sumber bukan array atau tidak ada. Ini akan dilewati.`);
            }
        }
        if (Object.keys(sourceArrays).length === 0 && config.arrayKeys.length > 0) {
            throw new Error("Tidak ada array yang valid ditemukan berdasarkan 'arrayKeys' yang diberikan.");
        }
    } else {
        throw new Error("Parameter 'sourceData' harus array atau objek dengan 'arrayKeys' yang valid.");
    }
    // --- AKHIR LOGIKA PENYIMPANAN ARRAY SUMBER ---

    /**
     * Melakukan pencarian data.
     * @param {string} searchTerm - String pencarian dari pengguna.
     * @returns {Object} Objek yang berisi hasil pencarian terpisah per array sumber.
     * Contoh: { 'schedules_1': [...], 'schedules_2': [...] }
     * Jika input awal adalah array tunggal, kuncinya akan 'default'.
     */
    function search(searchTerm) {
        const resultsBySource = {};

        // Jika kueri kosong, kembalikan semua data asli dari setiap sumber
        if (!searchTerm || searchTerm.trim() === '') {
            for (const key in sourceArrays) {
                if (sourceArrays.hasOwnProperty(key)) {
                    resultsBySource[key] = sourceArrays[key];
                }
            }
            return resultsBySource;
        }

        const preparedQueryParts = config.prepareQuery(searchTerm);

        // Iterasi melalui setiap array sumber yang telah disimpan
        for (const sourceKey in sourceArrays) {
            if (sourceArrays.hasOwnProperty(sourceKey)) {
                const currentArray = sourceArrays[sourceKey];
                const filteredResults = currentArray.filter(item => {
                    for (const keyPath of config.keys) {
                        const value = getNestedValue(item, keyPath);
                        if (value !== undefined && value !== null) {
                            const preparedDataValue = config.prepareValue(value);
                            if (config.testQuery(preparedQueryParts, preparedDataValue)) {
                                return true; // Item ini cocok dari sumber ini
                            }
                        }
                    }
                    return false; // Item ini tidak cocok dari sumber ini
                });
                resultsBySource[sourceKey] = filteredResults; // Simpan hasil filter untuk sumber ini
            }
        }

        return resultsBySource;
    }

    return {
        search: search
    };
}