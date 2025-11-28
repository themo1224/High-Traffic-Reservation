<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;
    // Ticket statuses
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED  = 'reserved';
    public const STATUS_SOLD      = 'sold';

    /** @var string[] Attributes that are mass assignable */
    protected $fillable = [
        'ticket_number',
        'seat_row',
        'seat_number',
        'event_id',
        'user_id',
        'status',
    ];

    /** @var array<string,string> Attribute casting */
    protected $casts = [
        'ticket_number' => 'integer',
        'seat_row' => 'integer',
        'seat_number' => 'integer',
    ];

    /**
     * A ticket belongs to an event.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * A ticket may belong to a user (owner/reserver).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ---------- Scopes ---------- */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeReserved($query)
    {
        return $query->where('status', self::STATUS_RESERVED);
    }

    public function scopeSold($query)
    {
        return $query->where('status', self::STATUS_SOLD);
    }

    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope by seat coordinates (row + number)
     */
    public function scopeSeat($query, int $row, int $number)
    {
        return $query->where('seat_row', $row)->where('seat_number', $number);
    }

    /* ---------- Helpers (non-persistent) ---------- */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE || $this->status === null;
    }

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    public function isSold(): bool
    {
        return $this->status === self::STATUS_SOLD;
    }

    /**
     * Mark ticket as reserved and optionally assign a user id.
     * Note: this modifies the model instance; persist with save() when desired.
     */
    public function reserve(?int $userId = null): self
    {
        $this->status = self::STATUS_RESERVED;
        if (! is_null($userId)) {
            $this->user_id = $userId;
        }

        return $this;
    }

    /**
     * Mark ticket as sold and set the owning user.
     */
    public function buy(int $userId): self
    {
        $this->status = self::STATUS_SOLD;
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Release ticket from reserved state back to available (and unset user_id).
     */
    public function release(): self
    {
        $this->status = self::STATUS_AVAILABLE;
        $this->user_id = null;

        return $this;
    }

    /**
     * Helper to return a human readable seat label: "Row X — Seat Y".
     */
    public function seatLabel(): string
    {
        $row = $this->seat_row ?? '-';
        $num = $this->seat_number ?? '-';

        return sprintf('Row %s — Seat %s', $row, $num);
    }
}
