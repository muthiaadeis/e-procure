<script>
    function rlpForm(initial) {
        let itemSeq = 0;
        let vendorSeq = 0;

        // ---------- Rebuild items with a stable client-side uid ----------
        // The uid is what lets a vendor's price rows stay linked to the right
        // item even after items are added/removed later (the "edit" requirement).
        const rawItems = (initial.items && initial.items.length) ? initial.items : [
            { job_code_id: '', description: '', pn: '', qty: '1', uom: '', part_catalog_u_price: '' }
        ];

        const items = rawItems.map((raw) => ({
            uid: 'item-' + (itemSeq++),
            job_code_id: raw.job_code_id || '',
            description: raw.description || '',
            pn: raw.pn || '',
            qty: raw.qty || '1',
            uom: raw.uom || '',
            part_catalog_u_price: raw.part_catalog_u_price || '',
            wur_u_price: '', // filled in below from initial.costs
        }));

        // ---------- Rebuild vendors: quotes keyed by item uid instead of index ----------
        const rawVendors = (initial.vendors && initial.vendors.length) ? initial.vendors : [];

        const vendors = rawVendors.map((rv, vIndex) => {
            const quotes = {};
            items.forEach((item, iIndex) => {
                const legacyItem = rawItems[iIndex];
                const legacyQuote = legacyItem && legacyItem.quotes ? legacyItem.quotes[vIndex] : null;
                quotes[item.uid] = (legacyQuote && legacyQuote.u_price !== undefined) ? legacyQuote.u_price : '';
            });
            return {
                uid: 'vendor-' + (vendorSeq++),
                vendor_id: rv.vendor_id || '',
                delivery_estimate: rv.delivery_estimate || '',
                discount_percent: rv.discount_percent || '',
                use_ppn: !!rv.use_ppn,
                quotes,
            };
        });

        // ---------- Winning vendor: was an index before, now tracked by uid ----------
        let selectedVendorUid = null;
        const legacySelectedIndex = initial.selectedVendorIndex !== undefined && initial.selectedVendorIndex !== null
            ? initial.selectedVendorIndex
            : (initial.items || []).reduce((found, i) => found !== null ? found : (i.selected_vendor_index ?? null), null);
        if (legacySelectedIndex !== null && legacySelectedIndex !== undefined && vendors[legacySelectedIndex]) {
            selectedVendorUid = vendors[legacySelectedIndex].uid;
        }

        // ---------- WUR cost rows line up 1-to-1 with items (same order) ----------
        // Quantity always follows the item automatically. Job Description starts
        // out copied from the item's description, but the user can retype it —
        // it's a separate field (wur_job_description), not locked to item.description.
        const rawCosts = initial.costs || [];
        items.forEach((item, index) => {
            const existingCost = rawCosts[index] || null;
            item.wur_u_price = existingCost ? (existingCost.u_price || '') : '';
            item.wur_job_description = existingCost && existingCost.job_description
                ? existingCost.job_description
                : item.description;
        });

        return {
            vendorOptions: initial.vendorOptions || [],
            jobCodeOptions: initial.jobCodeOptions || [],

            _itemSeq: itemSeq,
            _vendorSeq: vendorSeq,

            items,
            vendors,
            selectedVendorUid,

            // ---------- Helper angka ----------
            roundInt(value) {
                const num = Math.floor(Number(value) || 0);
                return num < 1 ? 1 : num;
            },

            formatThousands(value) {
                const digits = String(value === undefined || value === null ? '' : value).replace(/\D/g, '');
                if (!digits) return '0';
                return Number(digits).toLocaleString('id-ID');
            },

            parseThousands(value) {
                return String(value || '').replace(/\D/g, '');
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

            filteredJobCodeOptions(search) {
                const s = String(search || '').toLowerCase().trim();
                if (!s) return this.jobCodeOptions;
                return this.jobCodeOptions.filter(o =>
                    String(o.job_code || '').toLowerCase().includes(s) ||
                    String(o.description || '').toLowerCase().includes(s)
                );
            },

            filteredVendorOptions(search) {
                const s = String(search || '').toLowerCase().trim();
                if (!s) return this.vendorOptions;
                return this.vendorOptions.filter(o => String(o.vendor_name || '').toLowerCase().includes(s));
            },

            // Waktu Job Code dipilih: Description, PN, dan Part Catalog U/Price ikut otomatis.
            selectJobCode(item, id) {
                // Only auto-fill the WUR job description if the user hasn't retyped
                // it themselves yet (still empty, or still matching the old description).
                const wasUntouched = !item.wur_job_description || item.wur_job_description === item.description;

                item.job_code_id = id;
                const jc = this.jobCodeOptions.find(j => String(j.id) === String(id));
                item.description = jc?.description || '';
                item.pn = jc?.part_number || '';
                item.part_catalog_u_price = (jc && jc.price !== null && jc.price !== undefined)
                    ? String(Math.round(Number(jc.price)))
                    : '';

                if (wasUntouched) {
                    item.wur_job_description = item.description;
                }
            },

            // ---------- Step 1: RRP Items ----------
            addItem() {
                const uid = 'item-' + (this._itemSeq++);
                this.items.push({
                    uid, job_code_id: '', description: '', pn: '', qty: '1', uom: '',
                    part_catalog_u_price: '', wur_u_price: '', wur_job_description: '',
                });
                // Every existing vendor form gets a new (empty) price row for this item.
                this.vendors.forEach(v => { v.quotes[uid] = ''; });
            },

            removeItem(index) {
                if (this.items.length <= 1) return;
                const [removed] = this.items.splice(index, 1);
                this.vendors.forEach(v => { delete v.quotes[removed.uid]; });
            },

            isItemComplete(item) {
                return !!item.job_code_id
                    && !!String(item.uom || '').trim()
                    && item.qty !== '' && item.qty !== null && Number(item.qty) >= 1;
            },

            get itemsReady() {
                return this.items.length > 0 && this.items.every(i => this.isItemComplete(i));
            },

            itemPartCatalogExt(item) {
                return (Number(item.part_catalog_u_price) || 0) * (Number(item.qty) || 0);
            },

            get partCatalogTotalExt() {
                return this.items.reduce((sum, i) => sum + this.itemPartCatalogExt(i), 0);
            },

            // ---------- Step 2: Vendors (one full form per vendor) ----------
            addVendor() {
                if (!this.itemsReady) return;
                const uid = 'vendor-' + (this._vendorSeq++);
                const quotes = {};
                this.items.forEach(i => { quotes[i.uid] = ''; });
                this.vendors.push({ uid, vendor_id: '', delivery_estimate: '', discount_percent: '', use_ppn: false, quotes });
            },

            removeVendor(vIndex) {
                const removed = this.vendors[vIndex];
                if (removed && this.selectedVendorUid === removed.uid) {
                    this.selectedVendorUid = null;
                }
                this.vendors.splice(vIndex, 1);
            },

            isVendorMasterComplete(vendor) {
                return !!vendor.vendor_id && !!String(vendor.delivery_estimate || '').trim();
            },

            get vendorsIncomplete() {
                return this.vendors.length === 0 || this.vendors.some(v => !this.isVendorMasterComplete(v));
            },

            isVendorQuotesComplete(vendor) {
                return this.items.every(i => {
                    const q = vendor.quotes[i.uid];
                    return q !== '' && q !== null && q !== undefined && !isNaN(Number(q));
                });
            },

            get vendorQuotesIncomplete() {
                return this.vendors.some(v => !this.isVendorQuotesComplete(v));
            },

                        vendorItemExt(vendor, item) {
                const price = vendor.quotes[item.uid];
                return (Number(price) || 0) * (Number(item.qty) || 0);
            },

            vendorGrandTotal(vendor) {
                return this.items.reduce((sum, i) => sum + this.vendorItemExt(vendor, i), 0);
            },

            vendorDiscountAmount(vendor) {
                const pct = Number(vendor.discount_percent) || 0;
                if (pct <= 0) return 0;
                return this.vendorGrandTotal(vendor) * (pct / 100);
            },

            vendorAfterDiscount(vendor) {
                return this.vendorGrandTotal(vendor) - this.vendorDiscountAmount(vendor);
            },

            vendorPpnAmount(vendor) {
                if (!vendor.use_ppn) return 0;
                return this.vendorAfterDiscount(vendor) * 0.11;
            },

            vendorFinalTotal(vendor) {
                return this.vendorAfterDiscount(vendor) + this.vendorPpnAmount(vendor);
            },

            // Versi per-item dari harga final (dipakai buat preview Revenue per item di Step 1,
            // supaya konsisten sama cara backend hitung revenue_ext_price).
            vendorItemFinalExt(vendor, item) {
                const raw = this.vendorItemExt(vendor, item);
                const pct = Number(vendor.discount_percent) || 0;
                const afterDiscount = raw * (1 - pct / 100);
                return vendor.use_ppn ? afterDiscount * 1.11 : afterDiscount;
            },

            get selectedVendorObj() {
                return this.vendors.find(v => v.uid === this.selectedVendorUid) || null;
            },

            get selectedVendorIndex() {
                if (this.selectedVendorUid === null || this.selectedVendorUid === undefined) return null;
                const idx = this.vendors.findIndex(v => v.uid === this.selectedVendorUid);
                return idx === -1 ? null : idx;
            },

            get selectedVendorGrandTotal() {
                return this.selectedVendorObj ? this.vendorFinalTotal(this.selectedVendorObj) : 0;
            },

            // Revenue for a specific vendor (calculated directly from its price quotes vs Part Catalog)
            vendorRevenue(vendor) {
                return this.partCatalogTotalExt - this.vendorFinalTotal(vendor);
            },

            // Per-item revenue for a specific vendor
            vendorItemRevenue(vendor, item) {
                const q = vendor.quotes[item.uid];
                if (q === '' || q === null || q === undefined) return null;
                return this.itemPartCatalogExt(item) - this.vendorItemFinalExt(vendor, item);
            },

            // Percentage margin for a vendor
            vendorRevenueMargin(vendor) {
                if (!this.partCatalogTotalExt || this.partCatalogTotalExt <= 0) return 0;
                return Math.round((this.vendorRevenue(vendor) / this.partCatalogTotalExt) * 100);
            },

            // Check if vendor has at least one quote filled
            vendorHasQuotes(vendor) {
                return this.items.some(i => {
                    const q = vendor.quotes[i.uid];
                    return q !== '' && q !== null && q !== undefined && Number(q) > 0;
                });
            },

            // Vendor with the highest revenue (best profit comparison before picking)
            get bestRevenueVendor() {
                let best = null;
                let maxRev = -Infinity;
                this.vendors.forEach(v => {
                    if (this.vendorHasQuotes(v)) {
                        const rev = this.vendorRevenue(v);
                        if (rev > maxRev) {
                            maxRev = rev;
                            best = v;
                        }
                    }
                });
                return best;
            },

            // Overall Revenue for selected vendor (or best vendor if none selected yet)
            get revenueExtPrice() {
                if (this.selectedVendorObj) {
                    return this.partCatalogTotalExt - this.selectedVendorGrandTotal;
                }
                if (this.bestRevenueVendor) {
                    return this.vendorRevenue(this.bestRevenueVendor);
                }
                return 0;
            },

            // Returns revenue for an item. If a winner is picked, returns from that winner.
            // If no winner is picked yet, returns from the vendor giving the best overall revenue.
            itemRevenueExt(item) {
                const targetVendor = this.selectedVendorObj || this.bestRevenueVendor;
                if (!targetVendor || !this.vendorHasQuotes(targetVendor)) return null;
                return this.itemPartCatalogExt(item) - this.vendorItemFinalExt(targetVendor, item);
            },

            // List of vendors with quotes for this item (for comparison breakdown)
            itemVendorsWithQuotes(item) {
                return this.vendors.filter(v => {
                    const q = v.quotes[item.uid];
                    return q !== '' && q !== null && q !== undefined && Number(q) > 0;
                }).map(v => ({
                    uid: v.uid,
                    name: this.vendorNameById(v.vendor_id) || 'Vendor',
                    revenue: this.itemPartCatalogExt(item) - this.vendorItemFinalExt(v, item),
                    isWinner: this.selectedVendorUid === v.uid,
                }));
            },

            // ---------- Step 3: WUR Cost Estimasi (auto, one row per item) ----------
            itemWurTotal(item) {
                return (Number(item.qty) || 0) * (Number(item.wur_u_price) || 0);
            },

            get wurGrandTotal() {
                return this.items.reduce((sum, i) => sum + this.itemWurTotal(i), 0);
            },

            // ---------- Prepared By signature (create only) ----------
            signPad: null,
            initSignPad() {
                this.$nextTick(() => {
                    const setup = () => {
                        const canvas = document.getElementById('rlp-prepared-signature-canvas');
                        if (!canvas) return; // gak ada canvas di halaman edit, itu normal
                        if (!canvas.offsetWidth || !canvas.offsetHeight) {
                            requestAnimationFrame(setup);
                            return;
                        }
                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext('2d').scale(ratio, ratio);
                        if (window.SignaturePad) {
                            this.signPad = new SignaturePad(canvas, {
                                backgroundColor: 'rgb(255,255,255)',
                                minWidth: 1.8,
                                maxWidth: 3.8,
                                penColor: 'rgb(15, 23, 42)'
                            });
                        }
                    };
                    setup();
                });
            },
            clearSignPad() {
                if (this.signPad) this.signPad.clear();
            },

            // ---------- Submit ----------
            handleSubmit(event) {
                if (!this.itemsReady) {
                    alert('Please complete every RRP item first (Job Code, Quantity, UOM) before filling in vendors.');
                    event.preventDefault();
                    return;
                }
                if (this.vendorsIncomplete) {
                    alert('Please complete the Vendors list: pick a vendor and fill in its delivery estimate for every vendor form.');
                    event.preventDefault();
                    return;
                }
                if (this.vendorQuotesIncomplete) {
                    alert('Please fill in the price for every item in every vendor form.');
                    event.preventDefault();
                    return;
                }
                if (this.selectedVendorUid === null || this.selectedVendorUid === undefined) {
                    alert('Please pick the winning vendor (click "Pick as Winner" on one vendor form).');
                    event.preventDefault();
                    return;
                }
                // Ttd Prepared By cuma wajib pas bikin RRP baru (di halaman edit gak
                // ada canvas-nya sama sekali, jadi signPad bakal null).
                const signatureInput = document.getElementById('rlp-create-signature-input');
                if (signatureInput) {
                    if (!this.signPad || this.signPad.isEmpty()) {
                        alert('Please sign as Prepared By first before saving.');
                        event.preventDefault();
                        return;
                    }
                    signatureInput.value = this.signPad.toDataURL('image/png');
                }
            },
        };
    }
</script>
