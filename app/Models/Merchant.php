<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Merchant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'merchant_name',
        'api_key',
        'mpesa_shortcode',
        'mpesa_passkey',
        'mpesa_initiator_name',
        'mpesa_initiator_password',
        'mpesa_consumer_key',
        'mpesa_consumer_secret',
        'is_active',
        'environment',
        'last_used_at',
    ];

    protected $hidden = [
        'mpesa_passkey',
        'mpesa_initiator_password',
        'mpesa_consumer_key',
        'mpesa_consumer_secret',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Encrypted attributes
     */
    protected $encryptable = [
        'mpesa_shortcode',
        'mpesa_passkey',
        'mpesa_initiator_name',
        'mpesa_initiator_password',
        'mpesa_consumer_key',
        'mpesa_consumer_secret',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Generate API key on creation
        static::creating(function ($merchant) {
            if (empty($merchant->api_key)) {
                $merchant->api_key = 'mpesa_' . Str::random(64);
            }
        });
    }

    /**
     * Get an attribute with decryption
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable) && !is_null($value)) {
            try {
                return decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Set an attribute with encryption
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable) && !is_null($value)) {
            try {
                $value = encrypt($value);
            } catch (\Exception $e) {
                // If encryption fails, store as is
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Regenerate API key
     */
    public function regenerateApiKey(): string
    {
        $this->api_key = 'mpesa_' . Str::random(64);
        $this->save();
        return $this->api_key;
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Scope for active merchants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get merchant by API key
     */
    public static function findByApiKey(string $apiKey): ?self
    {
        return static::where('api_key', $apiKey)->active()->first();
    }
}
