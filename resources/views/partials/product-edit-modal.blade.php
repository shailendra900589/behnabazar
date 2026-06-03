<div class="modal fade" id="productEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" method="post" enctype="multipart/form-data" id="productEditForm">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title">Edit product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <div class="alert alert-soft small mb-0" id="productEditQcNote">
                        Approved products return to QC after you save changes.
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input name="title" id="editProductTitle" class="form-control" required maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sale price (₹)</label>
                    <input name="price" id="editProductPrice" type="number" step="0.01" min="1" class="form-control" required>
                    <small class="text-muted d-none" id="editProductResellMin"></small>
                </div>
                <div class="col-md-4" id="editCompareAtWrap">
                    <label class="form-label">MRP / cross price (₹)</label>
                    <input name="compare_at_price" id="editProductMrp" type="number" step="0.01" min="1" class="form-control" placeholder="Higher than sale">
                </div>
                <div class="col-md-4 d-none" id="editResellerDpWrap">
                    <label class="form-label">Reseller DP (₹)</label>
                    <input name="reseller_dp_price" id="editProductDp" type="number" step="0.01" min="0" class="form-control" placeholder="For resellers">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="editProductCategory" class="form-select" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Replace cover image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @include('partials.upload-size-hint', ['type' => 'product_primary'])
                </div>
                <div class="col-12">
                    <label class="form-label">Add gallery images (max {{ config('product.max_gallery_images', 5) }} total)</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    @include('partials.upload-size-hint', ['type' => 'product_gallery'])
                </div>
                <div class="col-12" id="editProductCurrentImages"></div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="editProductDescription" class="form-control" rows="4" required maxlength="3000"></textarea>
                </div>
                <div class="col-12 border-top pt-3 mt-2" id="editVariantsWrap">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="replace_variants" value="1" id="editReplaceVariants">
                        <label class="form-check-label" for="editReplaceVariants">Replace all variants when saving</label>
                    </div>
                    @include('partials.variant-builder', ['prefix' => 'variants', 'containerId' => 'editVariantsContainer', 'rowIndex' => 0])
                </div>
                <div class="col-12 d-none" id="editResellAllowedWrap">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="resell_allowed" value="1" id="editResellAllowed">
                        <label class="form-check-label" for="editResellAllowed">Allow other vendors to resell this product</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-bloom">Save changes</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('productEditModal');
    if (!modal) return;
    const form = document.getElementById('productEditForm');
    const imgBox = document.getElementById('editProductCurrentImages');
    const qcNote = document.getElementById('productEditQcNote');
    const resellMinNote = document.getElementById('editProductResellMin');
    const resellAllowedWrap = document.getElementById('editResellAllowedWrap');
    const resellAllowedInput = document.getElementById('editResellAllowed');
    const compareAtWrap = document.getElementById('editCompareAtWrap');
    const resellerDpWrap = document.getElementById('editResellerDpWrap');
    const editMrp = document.getElementById('editProductMrp');
    const editDp = document.getElementById('editProductDp');
    const updateUrlBase = @json(rtrim(route('manage.products.update', ['product' => 0]), '0'));

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn || !btn.classList.contains('btn-edit-product')) return;

        const id = btn.dataset.id;
        const isResell = btn.dataset.resell === '1';
        const resellMin = btn.dataset.resellMin || '';
        form.action = updateUrlBase + id;
        document.getElementById('editProductTitle').value = btn.dataset.title || '';
        const priceInput = document.getElementById('editProductPrice');
        priceInput.value = btn.dataset.price || '';
        priceInput.min = isResell && resellMin ? resellMin : '1';
        document.getElementById('editProductCategory').value = btn.dataset.categoryId || '';
        document.getElementById('editProductDescription').value = btn.dataset.description || '';

        if (isResell && resellMin) {
            resellMinNote.textContent = 'Resell listing: minimum ₹' + parseFloat(resellMin).toFixed(2) + ' per unit (source vendor share).';
            resellMinNote.classList.remove('d-none');
        } else {
            resellMinNote.classList.add('d-none');
        }
        if (resellAllowedWrap) {
            resellAllowedWrap.classList.toggle('d-none', isResell);
            if (!isResell && resellAllowedInput) {
                resellAllowedInput.checked = btn.dataset.resellAllowed === '1';
            }
        }
        if (compareAtWrap) compareAtWrap.classList.remove('d-none');
        if (resellerDpWrap) resellerDpWrap.classList.toggle('d-none', isResell);
        if (editMrp) editMrp.value = btn.dataset.compareAt || '';
        if (editDp) editDp.value = btn.dataset.resellerDp || '';

        const qc = btn.dataset.qcStatus || '';
        qcNote.classList.toggle('d-none', qc !== 'approved');
        qcNote.textContent = qc === 'approved'
            ? 'This product is live. Saving will send it back to QC for verification.'
            : 'Changes will be saved. Status: ' + qc;

        let images = [];
        try { images = JSON.parse(btn.dataset.images || '[]'); } catch (e) {}
        imgBox.innerHTML = '';
        if (images.length) {
            const wrap = document.createElement('div');
            wrap.className = 'd-flex flex-wrap gap-2';
            images.forEach(function (img) {
                const label = document.createElement('label');
                label.className = 'border rounded p-1 small';
                label.innerHTML = '<img src="' + img.url + '" style="width:64px;height:64px;object-fit:cover" class="rounded d-block mb-1"><input type="checkbox" name="remove_image_ids[]" value="' + img.id + '"> Remove';
                wrap.appendChild(label);
            });
            const heading = document.createElement('p');
            heading.className = 'small fw-semibold mb-2';
            heading.textContent = 'Current photos';
            imgBox.appendChild(heading);
            imgBox.appendChild(wrap);
        }
    });
})();
</script>
