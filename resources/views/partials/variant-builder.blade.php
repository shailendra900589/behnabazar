@php
    $variantTypes = config('product.variant_attribute_types', ['Color', 'Size', 'Type']);
    $prefix = $prefix ?? 'variants';
    $containerId = $containerId ?? 'variantsContainer';
    $rowIndex = $rowIndex ?? 0;
@endphp
<div class="col-12 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div>
            <label class="form-label mb-0 fw-bold">Product variants (optional)</label>
            <p class="small text-muted mb-0">Color, size, type, age group, height & more — each row is one sellable option.</p>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-variant-add-row data-container="{{ $containerId }}">+ Add variant</button>
    </div>
    <div id="{{ $containerId }}" data-variant-builder data-prefix="{{ $prefix }}">
        @include('partials.variant-builder-row', ['prefix' => $prefix, 'index' => $rowIndex, 'variantTypes' => $variantTypes])
    </div>
</div>

<template id="variantRowTemplate-{{ $containerId }}">
    @include('partials.variant-builder-row', ['prefix' => '__PREFIX__', 'index' => '__INDEX__', 'variantTypes' => $variantTypes, 'isTemplate' => true])
</template>

<script>
(function () {
    if (window.__bbVariantBuilderInit) return;
    window.__bbVariantBuilderInit = true;

    const types = @json($variantTypes);

    function bindRow(row) {
        const addAttr = row.querySelector('[data-variant-add-attr]');
        const attrsBox = row.querySelector('[data-variant-attrs]');
        if (!addAttr || !attrsBox) return;

        addAttr.addEventListener('click', () => {
            const prefix = row.closest('[data-variant-builder]')?.dataset.prefix || 'variants';
            const index = row.dataset.index;
            const attrIndex = attrsBox.querySelectorAll('[data-variant-attr]').length;
            const wrap = document.createElement('div');
            wrap.className = 'row g-1 mb-1 align-items-center';
            wrap.setAttribute('data-variant-attr', '');
            let options = types.map(t => `<option value="${t}">${t}</option>`).join('');
            wrap.innerHTML = `
                <div class="col-5">
                    <select name="${prefix}[${index}][attribute_keys][]" class="form-select form-select-sm">${options}</select>
                </div>
                <div class="col-6">
                    <input type="text" name="${prefix}[${index}][attribute_values][]" class="form-control form-control-sm" placeholder="Value">
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" data-variant-remove-attr>&times;</button>
                </div>`;
            attrsBox.appendChild(wrap);
            wrap.querySelector('[data-variant-remove-attr]')?.addEventListener('click', () => wrap.remove());
        });

        attrsBox.querySelectorAll('[data-variant-remove-attr]').forEach(btn => {
            btn.addEventListener('click', () => btn.closest('[data-variant-attr]')?.remove());
        });
    }

    document.querySelectorAll('[data-variant-builder]').forEach(builder => {
        builder.querySelectorAll('[data-variant-row]').forEach(bindRow);
    });

    document.querySelectorAll('[data-variant-add-row]').forEach(btn => {
        btn.addEventListener('click', () => {
            const containerId = btn.dataset.container || 'variantsContainer';
            const container = document.getElementById(containerId);
            const template = document.getElementById('variantRowTemplate-' + containerId);
            if (!container || !template) return;
            const prefix = container.dataset.prefix || 'variants';
            const index = container.querySelectorAll('[data-variant-row]').length;
            let html = template.innerHTML
                .replace(/__PREFIX__/g, prefix)
                .replace(/__INDEX__/g, String(index));
            const wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            const row = wrap.firstElementChild;
            container.appendChild(row);
            bindRow(row);
        });
    });

    document.querySelectorAll('[data-variant-row] [data-variant-remove-row]').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('[data-variant-row]');
            const container = row?.parentElement;
            if (row && container && container.querySelectorAll('[data-variant-row]').length > 1) {
                row.remove();
            }
        });
    });
})();
</script>
