<script>
    function purchaseOrderForm(initial) {
        return {
            items: (initial.items && initial.items.length) ? initial.items : [
                { description: '', qty: '1', uom: '', brand: '', price: '' }
            ],
            use_ppn: initial.use_ppn !== undefined ? !!initial.use_ppn : true,
            ppn_percent: initial.ppn_percent || 11,

            init() {
                this.$nextTick(() => {
                    if (this.$el) {
                        this.$el.querySelectorAll('textarea').forEach(t => this.autoResize(t));
                    }
                });
            },

            autoResize(el) {
                if (!el) return;
                el.style.height = 'auto';
                const h = Math.max(38, el.scrollHeight);
                el.style.height = h + 'px';
            },

            addItem() {
                this.items.push({ description: '', qty: '1', uom: '', brand: '', price: '' });
                this.$nextTick(() => {
                    const el = document.getElementById('po-item-row-' + (this.items.length - 1));
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.querySelectorAll('textarea').forEach(t => this.autoResize(t));
                    }
                });
            },

            removeItem(index) {
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

            async handleSubmit(event) {
                event.preventDefault();

                if (this.items.length === 0) {
                    alert('Tambahkan minimal 1 item.');
                    return;
                }
                const incomplete = this.items.some(i =>
                    !String(i.description || '').trim() ||
                    !String(i.uom || '').trim() ||
                    !i.qty || Number(i.qty) <= 0 ||
                    i.price === '' || i.price === null || Number(i.price) < 0
                );
                if (incomplete) {
                    alert('Lengkapi Description, Qty, UOM, dan Unit Price untuk setiap item.');
                    return;
                }

                const sigInput = document.getElementById('po-create-signature-input');
                if (sigInput && !sigInput.value) {
                    alert('Silakan tanda tangani dokumen sebagai pembuat (Prepared By) terlebih dahulu.');
                    return;
                }

                const form = event.target;
                const isEdit = Boolean(form.querySelector('input[name="_method"][value="PUT"]'));
                const confirmMsg = isEdit
                    ? 'Apakah data Purchase Order sudah benar? Dokumen akan diajukan ulang untuk persetujuan dari awal.'
                    : 'Apakah Anda yakin ingin menyimpan dan mengajukan Purchase Order ini?';

                const confirmed = await window.showConfirm(confirmMsg, {
                    title: isEdit ? 'Ajukan Ulang PO' : 'Simpan PO',
                    confirmText: isEdit ? 'Ya, Ajukan Ulang' : 'Ya, Simpan',
                    cancelText: 'Periksa Lagi'
                });

                if (!confirmed) return;

                form.submit();
            },
        };
    }
</script>
