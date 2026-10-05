<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    protected $fillable = ['user_id', 'form', 'context', 'title', 'payload'];

    public static function forget(string $form, ?string $context = ''): void
    {
        static::where('user_id', auth()->id())
            ->where('form', $form)
            ->where('context', $context ?? '')
            ->delete();
    }

    // Field yang dipakai buat judul draft di list.
    public const TITLE_FIELDS = [
        'material-requests' => ['no_mr'],
        'rlps'              => ['no_rlp'],
        'purchase-requests' => ['no_request', 'title'],
        'purchase-orders'   => ['po_no', 'subject'],
    ];

    public static function titleFrom(string $form, string $payload): ?string
    {
        $fields = json_decode($payload, true)['fields'] ?? [];

        foreach (self::TITLE_FIELDS[$form] ?? [] as $name) {
            if (! empty(trim((string) ($fields[$name] ?? '')))) {
                return mb_substr(trim($fields[$name]), 0, 150);
            }
        }

        return null;
    }

    public function resumeUrl(): string
    {
        return match ($this->form) {
            'material-requests' => route('material-requests.create', ['resume' => 1]),
            'rlps'              => route('rlps.create', ['resume' => 1]),
            'purchase-requests' => route('purchase-requests.create', array_filter(['from_po' => $this->context, 'resume' => 1])),
            'purchase-orders'   => route('purchase-orders.create', array_filter(['from_rlp' => $this->context, 'resume' => 1])),
        };
    }
}
