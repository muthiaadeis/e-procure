<script>
    function purchaseRequestForm(initial) {
        return {
            items: (initial.items && initial.items.length) ? initial.items : [
                { description: '', line_table: '', line_sub_table: '', qty: '1', unit: '', price: '', remarks: '' }
            ],
            use_ppn: !!initial.use_ppn,
            ppn_percent: initial.ppn_percent || 11,

            addItem() {
                this.items.push({ description: '', line_table: '', line_sub_table: '', qty: '1', unit: '', price: '', remarks: '' });
                this.$nextTick(() => {
                    const el = document.getElementById('pr-item-row-' + (this.items.length - 1));
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            },

            removeItem(index) {
                // Tetap bisa hapus meski tinggal 1 baris terakhir.
                // Validasi "minimal 1 item" dicek ulang di handleSubmit saat form disubmit.
                this.items.splice(index, 1);
            },

            formatThousands(value) {
                const digits = String(value === undefined || value === null ? '' : value).replace(/\D/g, '');
                if (!digits) return '';
                return Number(digits).toLocaleString('id-ID');
            },

            parseThousands(value) {
                return String(value || '').replace(/\D/g, '');
            },

            formatRupiah(value) {
                return 'Rp ' + (Number(value) || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
            },

            itemTotal(item) {
                return (Number(item.qty) || 0) * (Number(item.price) || 0);
            },

            get subtotal() {
                return this.items.reduce((sum, i) => sum + this.itemTotal(i), 0);
            },

            get ppnAmount() {
                if (!this.use_ppn) return 0;
                return this.subtotal * ((Number(this.ppn_percent) || 0) / 100);
            },

            get grandTotal() {
                return this.subtotal + this.ppnAmount;
            },

            handleSubmit(event) {
                if (this.items.length === 0) {
                    alert('Tambahkan minimal 1 item.');
                    event.preventDefault();
                    return;
                }
                const incomplete = this.items.some(i =>
                    !String(i.description || '').trim() ||
                    !String(i.unit || '').trim() ||
                    !i.qty || Number(i.qty) <= 0 ||
                    i.price === '' || i.price === null || Number(i.price) < 0
                );
                if (incomplete) {
                    alert('Lengkapi Description, Qty, Unit, dan Price untuk setiap item.');
                    event.preventDefault();
                }
            },
        };
    }
</script>
