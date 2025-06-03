<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_orderable_id',
        'work_orderable_type',
        'online_billing_id',
        'process',
        'status',
        'admin_id',
    ];

    /**
     * Get the online billing associated with the status.
     */
    public function onlineBilling()
    {
        return $this->belongsTo(OnlineBilling::class, 'online_billing_id');
    }

    /**
     * Get the admin who created the status.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function workOrderable()
    {
        return $this->morphTo();
    }
}
