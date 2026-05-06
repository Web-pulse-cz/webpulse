<?php

namespace App\Http\Resources\Admin\Site;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $hasFakturoidSecret = false;
        try {
            $hasFakturoidSecret = ! empty($this->fakturoid_client_secret);
        } catch (DecryptException $e) {
            // Secret was encrypted with a different APP_KEY
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'hash' => $this->hash,
            'is_secure' => $this->is_secure,
            'is_active' => $this->is_active,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'settings' => $this->settings,
            'fakturoid_client_id' => $this->fakturoid_client_id,
            'fakturoid_client_secret' => $hasFakturoidSecret ? '••••••••' : null,
            'fakturoid_slug' => $this->fakturoid_slug,
            'billing_name' => $this->billing_name,
            'billing_ico' => $this->billing_ico,
            'billing_dic' => $this->billing_dic,
            'billing_street' => $this->billing_street,
            'billing_city' => $this->billing_city,
            'billing_zip' => $this->billing_zip,
            'billing_bank_account' => $this->billing_bank_account,
            'billing_iban' => $this->billing_iban,
            'billing_swift' => $this->billing_swift,
            'users' => $this->users,
        ];
    }
}
