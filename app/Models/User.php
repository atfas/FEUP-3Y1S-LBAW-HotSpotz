<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Import Eloquent relationship classes.
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Disable default created_at and updated_at timestamps for this model.
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * Only these fields may be filled using methods like create() or update().
     * This protects against mass-assignment vulnerabilities.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'date_of_birth',
        'phone',
        'profile_picture_url',
        'is_active',
        'is_blocked',
        'is_admin',
        'application_status',
        'answers',
        'verification_docs',
        'payout_details',
        'submitted_at',
        'verified_at',
        'verifier_id',
    ];

    /**
     * The attributes that should be hidden when serializing the model
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to a specific type.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'is_admin' => 'boolean',
            'date_of_birth' => 'date',
            'registration_date' => 'datetime',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
    
    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin && $this->is_active;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the user's subscription.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Check if the user has an active premium subscription.
     */
    public function isPremium(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    /**
     * Get the user's discount percentage.
     */
    public function getDiscountPercentage(): int
    {
        if ($this->isPremium()) {
            return $this->subscription->discount_percentage;
        }
        return 0;
    }
    

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function hasFavorited($listingId): bool
    {
        return $this->favorites()
            ->where('listing_id', $listingId)
            ->exists();
    }

}