@extends('layouts.app')
@section('title', 'Saved addresses')
@section('content')
<section class="container py-4 py-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Saved addresses</h1>
        <a href="{{ route('profile') }}" class="btn btn-soft btn-sm">← Profile</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="bb-card p-4 rounded-4">
                <h2 class="h6 fw-bold mb-3">Add new address</h2>
                <form method="post" action="{{ route('addresses.store') }}" class="row g-2">
                    @csrf
                    <div class="col-12"><input name="name" class="form-control" placeholder="Full name" value="{{ auth()->user()->name }}" required></div>
                    <div class="col-12"><input name="phone" class="form-control" placeholder="Phone" value="{{ auth()->user()->phone }}" required></div>
                    <div class="col-12"><textarea name="address" class="form-control" rows="2" placeholder="Street address" required></textarea></div>
                    <div class="col-6"><input name="city" class="form-control" placeholder="City"></div>
                    <div class="col-6"><input name="pincode" class="form-control" placeholder="PIN" maxlength="6"></div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="addrDefault">
                            <label class="form-check-label" for="addrDefault">Set as default</label>
                        </div>
                    </div>
                    <div class="col-12"><button class="btn btn-bloom w-100">Save address</button></div>
                </form>
            </div>
        </div>
        <div class="col-lg-7">
            @forelse($addresses as $addr)
                <div class="bb-card p-3 rounded-4 mb-2 d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="fw-bold">{{ $addr->name }} @if($addr->is_default)<span class="badge bg-primary">Default</span>@endif</div>
                        <div class="small text-muted">{{ $addr->address }}@if($addr->city), {{ $addr->city }}@endif @if($addr->pincode)- {{ $addr->pincode }}@endif</div>
                        <div class="small"><i class="bi bi-telephone"></i> {{ $addr->phone }}</div>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        @unless($addr->is_default)
                            <form method="post" action="{{ route('addresses.default', $addr) }}">@csrf<button class="btn btn-soft btn-sm">Default</button></form>
                        @endunless
                        <form method="post" action="{{ route('addresses.destroy', $addr) }}" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></form>
                    </div>
                </div>
            @empty
                <p class="text-muted">No saved addresses yet.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
