<script>
    function rlpForm(initial) {
        return {
            vendorOptions: initial.vendorOptions || [],
            jobCodeOptions: initial.jobCodeOptions || [],
            items: initial.items && initial.items.length ? initial.items : [
                { description: '', pn: '', qty: 1, uom: '', job_code_id: '', part_catalog_u_price: '' }
            ],
            vendors: initial.vendors && initial.vendors.length ? initial.vendors : [],
            costs: initial.costs && initial.costs.length ? initial.costs : [
                { job_description: '', quantity: 1, u_price: 0 }
            ],
            selectedVendorIndex: initial.selectedVendorIndex,

            showVendorModal: false,
            vendorModalError: '',
            newVendor: {
                vendor_id: '', vendor_name: '', brand: '',
                delivery_estimate: '', prices: [],
            },

            emptyVendor() {
                return {
                    vendor_id: '',
                    vendor_name: '',
                    brand: '',
                    delivery_estimate: '',
                    // Satu harga per baris RRP Item (mengikuti urutan this.items).
                    prices: this.items.map(() => ''),
                };
            },

            // Bulatkan input supaya quantity selalu bilangan bulat, gak ada desimal.
            // Dipanggil saat blur (bukan tiap ketikan) supaya user bebas ngetik/hapus dulu.
            roundInt(value) {
                const num = Math.floor(Number(value) || 0);
                return num < 1 ? 1 : num;
            },

            // Format angka jadi "50.000" (pemisah ribuan titik, gaya Indonesia).
            formatThousands(value) {
                if (value === '' || value === null || value === undefined) return '';
                const num = Math.floor(Number(value) || 0);
                return num.toLocaleString('id-ID');
            },

            // Kebalikan formatThousands: buang semua titik/karakter non-digit, balikin angka murni.
            parseThousands(str) {
                const digits = String(str || '').replace(/[^0-9]/g, '');
                return digits === '' ? '' : parseInt(digits, 10);
            },

            // Job code dipilih -> PN, Description, & Part Catalog U/Price item ikut keisi otomatis.
            selectJobCode(item, opt) {
                item.job_code_id = opt.id;
                if (opt.description) item.description = opt.description;
                if (opt.part_number) item.pn = opt.part_number;
                if (opt.price !== null && opt.price !== undefined && opt.price !== '') {
                    item.part_catalog_u_price = Number(opt.price);
                }
            },

            clearJobCode(item) {
                item.job_code_id = '';
            },

            vendorNameById(id) {
                const found = this.vendorOptions.find(v => String(v.id) === String(id));
                return found ? found.vendor_name : '';
            },

            jobCodeLabel(id) {
                const found = this.jobCodeOptions.find(j => String(j.id) === String(id));
                if (!found) return '';
                return found.description ? (found.job_code + ' — ' + found.description) : found.job_code;
            },

            // Baris item dianggap lengkap kalau semua field wajib (kecuali PN & Job Code) sudah diisi.
            isItemRowComplete(item) {
                return !!String(item.description || '').trim()
                    && !!String(item.uom || '').trim()
                    && item.qty !== '' && item.qty !== null && Number(item.qty) >= 1
                    && item.part_catalog_u_price !== '' && item.part_catalog_u_price !== null && !isNaN(Number(item.part_catalog_u_price));
            },

            // Baris penawaran dianggap lengkap kalau semua field wajib (kecuali PN) sudah diisi.
            isVendorRowComplete(vendor) {
                return !!vendor.vendor_id
                    && !!String(vendor.item_name || '').trim()
                    && !!String(vendor.brand || '').trim()
                    && !!String(vendor.delivery_estimate || '').trim()
                    && vendor.qty !== '' && vendor.qty !== null && Number(vendor.qty) >= 1
                    && vendor.u_price !== '' && vendor.u_price !== null && !isNaN(Number(vendor.u_price));
            },

            get quotationIncomplete() {
                return this.vendors.length === 0 || this.vendors.some(v => !this.isVendorRowComplete(v));
            },

            get itemsIncomplete() {
                return this.items.length === 0 || this.items.some(i => !this.isItemRowComplete(i));
            },

            handleSubmit(event) {
                if (this.itemsIncomplete) {
                    alert('Please complete all RRP item fields (PN and Job Code are optional) before submitting.');
                    event.preventDefault();
                    return;
                }
                if (this.vendors.length === 0) {
                    alert('Add at least one vendor quotation before submitting.');
                    event.preventDefault();
                    return;
                }
                if (this.quotationIncomplete) {
                    alert('Please complete all quotation fields (Part Number is optional) before submitting.');
                    event.preventDefault();
                }
            },

            formatMoney(value) {
                const num = Number(value) || 0;
                return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            vendorExt(vendor) {
                return (Number(vendor.u_price) || 0) * (Number(vendor.qty) || 0);
            },

            get partCatalogTotalExt() {
                return this.items.reduce((sum, i) => sum + ((Number(i.part_catalog_u_price) || 0) * (Number(i.qty) || 0)), 0);
            },

            get selectedVendor() {
                return (this.selectedVendorIndex !== null && this.vendors[this.selectedVendorIndex])
                    ? this.vendors[this.selectedVendorIndex]
                    : null;
            },

            // Revenue = Ext/Price vendor terpilih dikurangi total Ext/Price part catalog.
            get revenueExtPrice() {
                if (!this.selectedVendor) return 0;
                return this.vendorExt(this.selectedVendor) - this.partCatalogTotalExt;
            },

            get wurGrandTotal() {
                return this.costs.reduce((sum, c) => sum + ((Number(c.quantity) || 0) * (Number(c.u_price) || 0)), 0);
            },

            addItem() {
                this.items.push({ description: '', pn: '', qty: 1, uom: '', job_code_id: '', part_catalog_u_price: '' });
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },

            openVendorModal() {
                this.newVendor = this.emptyVendor();
                this.vendorModalError = '';
                this.showVendorModal = true;
            },

            addVendor() {
                if (!this.newVendor.vendor_id || !String(this.newVendor.brand || '').trim() || !String(this.newVendor.delivery_estimate || '').trim()) {
                    this.vendorModalError = 'Vendor, Brand, and Delivery Estimate are required.';
                    return;
                }

                // Ambil hanya baris item yang diisi harganya oleh vendor ini.
                const pricedRows = this.items
                    .map((item, idx) => ({ item, price: this.newVendor.prices[idx] }))
                    .filter(row => row.price !== '' && row.price !== null && row.price !== undefined && !isNaN(Number(row.price)));

                if (pricedRows.length === 0) {
                    this.vendorModalError = 'Fill in a price for at least one item.';
                    return;
                }

                const vendorName = this.vendorNameById(this.newVendor.vendor_id);
                const firstNewIndex = this.vendors.length;

                pricedRows.forEach(({ item, price }) => {
                    this.vendors.push({
                        vendor_id: this.newVendor.vendor_id,
                        vendor_name: vendorName,
                        item_name: item.description,
                        brand: this.newVendor.brand,
                        part_number: item.pn,
                        qty: this.roundInt(item.qty),
                        u_price: Number(price) || 0,
                        delivery_estimate: this.newVendor.delivery_estimate,
                    });
                });

                if (this.selectedVendorIndex === null || this.selectedVendorIndex === undefined) {
                    this.selectedVendorIndex = firstNewIndex;
                }

                this.vendorModalError = '';
                this.showVendorModal = false;
            },

            removeVendor(index) {
                this.vendors.splice(index, 1);

                if (this.selectedVendorIndex === index) {
                    this.selectedVendorIndex = this.vendors.length ? 0 : null;
                } else if (this.selectedVendorIndex !== null && this.selectedVendorIndex > index) {
                    this.selectedVendorIndex--;
                }
            },

            addCostRow() {
                this.costs.push({ job_description: '', quantity: 1, u_price: 0 });
            },

            removeCostRow(index) {
                if (this.costs.length > 1) {
                    this.costs.splice(index, 1);
                }
            },
        };
    }
</script>
