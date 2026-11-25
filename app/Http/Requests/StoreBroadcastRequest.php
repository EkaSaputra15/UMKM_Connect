<?php

namespace App\Http\Requests;

use App\Models\Broadcast;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBroadcastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:10000'],
            'target_segment' => [
                'required',
                Rule::in([
                    Broadcast::SEGMENT_ALL,
                    Broadcast::SEGMENT_INACTIVE_30_DAYS,
                    Broadcast::SEGMENT_VIP,
                ]),
            ],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Subjek email kampanye wajib diisi.',
            'message.required' => 'Isi pesan promosi wajib diisi.',
            'message.min' => 'Pesan promosi minimal 10 karakter.',
            'target_segment.in' => 'Segmen target pelanggan tidak valid.',
            'scheduled_at.after' => 'Waktu penjadwalan harus di masa mendatang.',
        ];
    }
}
