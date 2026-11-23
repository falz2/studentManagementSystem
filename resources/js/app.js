import './bootstrap';

if (document.querySelector('[data-farm-map], [data-order-delivery-map], [data-seller-location-form], [data-rider-share-map], [data-copy-value]')) {
    import('./maps');
}

const searchInput = document.querySelector('[data-table-search]');

if (searchInput) {
    const rows = [...document.querySelectorAll('[data-searchable-row]')];
    const emptyState = document.querySelector('[data-empty-state]');

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLocaleLowerCase();
        let visibleRows = 0;

        rows.forEach((row) => {
            const matches = row.textContent.toLocaleLowerCase().includes(query);
            row.classList.toggle('hidden', !matches);
            visibleRows += Number(matches);
        });

        emptyState?.classList.toggle('hidden', visibleRows > 0);
    });
}
