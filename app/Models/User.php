<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'city',
        'pincode',
        'shop_name',
        'product_category',
        'coins',
        'referral_code',
        'referred_by_id',
        'sales_wallet_balance',
        'ad_wallet_balance',
        'account_status',
        'reg_fee_paid',
        'is_email_verified',
        'otp_code',
        'otp_expiry',
        'reset_token',
        'reset_expiry',
        'document_type',
        'document_file',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ad_wallet_balance' => 'decimal:2',
            'sales_wallet_balance' => 'decimal:2',
            'reg_fee_paid' => 'boolean',
            'is_email_verified' => 'boolean',
            'otp_expiry' => 'datetime',
            'reset_expiry' => 'datetime',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    public function referralRewardsEarned()
    {
        return $this->hasMany(ReferralReward::class, 'referrer_id');
    }

    public function vendorWalletTransactions()
    {
        return $this->hasMany(VendorWalletTransaction::class, 'vendor_id');
    }

    public function isRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles, true);
    }
}
