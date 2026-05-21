@php
    $isTemplate = $isTemplate ?? false;
    $row = $row ?? null;
    $attrs = $row ? (new \App\Models\ProductVariant($row instanceof \App\Models\ProductVariant ? $row->toArray() : (array) $row))->attributeMap() : [];
@endphp
<div class="border rounded-3 p-3 mb-2 bg-light-subtle" data-variant-row data-index="{{ $index }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="small fw-semibold text-muted">Variant #<span data-variant-num>{{ is_numeric($index) ? ((int) $index) + 1 : 1 }}</span></span>
        <button type="button" class="btn btn-sm btn-outline-danger" data-variant-remove-row><i class="bi bi-trash"></i></button>
    </div>
    <div data-variant-attrs class="mb-2">
        @if ($attrs !== [])
            @foreach ($attrs as $attrKey => $attrVal)
                <div class="row g-1 mb-1 align-items-center" data-variant-attr>
                    <div class="col-5">
                        <select name="{{ $prefix }}[{{ $index }}][attribute_keys][]" class="form-select form-select-sm">
                            @foreach ($variantTypes as $type)
                                <option value="{{ $type }}" @selected($attrKey === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <input type="text" name="{{ $prefix }}[{{ $index }}][attribute_values][]" class="form-control form-control-sm" value="{{ $attrVal }}" placeholder="Value">
                    </div>
                    <div class="col-1 text-end">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" data-variant-remove-attr>&times;</button>
                    </div>
                </div>
            @endforeach
        @else
            <div class="row g-1 mb-1 align-items-center" data-variant-attr>
                <div class="col-5">
                    <select name="{{ $prefix }}[{{ $index }}][attribute_keys][]" class="form-select form-select-sm">
                        @foreach ($variantTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <input type="text" name="{{ $prefix }}[{{ $index }}][attribute_values][]" class="form-control form-control-sm" placeholder="e.g. Red, XL, Kids">
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" data-variant-remove-attr>&times;</button>
                </div>
            </div>
        @endif
    </div>
    <button type="button" class="btn btn-sm btn-link px-0 mb-2" data-variant-add-attr>+ Add attribute (color, size, type…)</button>
    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label small mb-0">Sale price (₹)</label>
            <input type="number" step="0.01" min="0" name="{{ $prefix }}[{{ $index }}][price]" class="form-control form-control-sm" placeholder="Override" value="{{ optional($row)->price }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-0">MRP (₹)</label>
            <input type="number" step="0.01" min="0" name="{{ $prefix }}[{{ $index }}][compare_at_price]" class="form-control form-control-sm" placeholder="Cross price" value="{{ optional($row)->compare_at_price }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-0">Stock</label>
            <input type="number" min="0" name="{{ $prefix }}[{{ $index }}][stock]" class="form-control form-control-sm" value="{{ optional($row)->stock ?? 10 }}">
        </div>
    </div>
</div>
