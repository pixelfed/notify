<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    use HasFactory;

    public $guarded = [];

    public function getAdminUrl()
    {
        return 'https://' . $this->domain . '/i/admin/push-gateway?newKey=' . base64_encode($this->secret);
    }
}
