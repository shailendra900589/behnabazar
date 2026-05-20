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
                <div class="col-md-6">
                    <label class="form-label">Price (₹)</label>
                    <input name="price" id="editProductPrice" type="number" step="0.01" min="1" class="form-control" required>
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
                </div>
                <div class="col-12">
                    <label class="form-label">Add more images</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                </div>
                <div class="col-12" id="editProductCurrentImages"></div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="editProductDescription" class="form-control" rows="4" required maxlength="3000"></textarea>
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
    const updateUrlBase = @json(rtrim(route('manage.products.update', ['product' => 0]), '0'));

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn || !btn.classList.contains('btn-edit-product')) return;

        const id = btn.dataset.id;
        form.action = updateUrlBase + id;
        document.getElementById('editProductTitle').value = btn.dataset.title || '';
        document.getElementById('editProductPrice').value = btn.dataset.price || '';
        document.getElementById('editProductCategory').value = btn.dataset.categoryId || '';
        document.getElementById('editProductDescription').value = btn.dataset.description || '';

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
