<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'global_id'       => $this->global_id,
            'username'        => $this->username,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'profile_picture' => $this->profile_picture,
            'dob'             => $this->dob,
            'id_card'         => $this->id_card,
            // 'role'            => $this->role ? [
            //     'id'          => $this->role->id,
            //     'name'        => $this->role->role, // <-- FIX HERE
            //     'description' => $this->role->description,
            // ] : null,
            'active'     => $this->active,
            'created_at' => $this->created_at,
        ];
    }
    
}
