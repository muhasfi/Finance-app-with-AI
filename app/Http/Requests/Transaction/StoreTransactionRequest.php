<?php

namespace App\Http\Requests\Transaction;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'account_id'  => [
                'required', 'uuid',
                Rule::exists('accounts', 'id')->where('user_id', auth()->id()),
            ],
            'category_id' => [
                'nullable', 'uuid',
                Rule::exists('categories', 'id')->where(function ($q) {
                    $q->whereNull('user_id')->orWhere('user_id', auth()->id());
                }),
            ],
            'type'    => ['required', Rule::enum(TransactionType::class)],
            'amount'  => ['required', 'numeric', 'min:1', 'max:999999999999'],
            'date'    => ['required', 'date', 'before_or_equal:today'],
            'note'    => ['nullable', 'string', 'max:500'],
            'tags'    => ['nullable', 'array', 'max:10'],
            'tags.*'  => ['string', 'max:30'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.exists'    => 'Akun tidak ditemukan atau bukan milik Anda.',
            'category_id.exists'   => 'Kategori tidak valid.',
            'amount.min'           => 'Jumlah minimal adalah Rp 1.',
            'date.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
            'receipt.max'          => 'Ukuran file maksimal 5MB.',
        ];
    }
}
