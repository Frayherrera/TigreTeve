<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProgramaPublicacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoticiaRequest extends FormRequest
{
    use ProgramaPublicacion;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('crear noticias') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:noticias,slug'],
            'resumen' => ['nullable', 'string', 'max:500'],
            'cuerpo' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:categorias,id'],
            'tags' => ['array'],
            'tags.*' => ['exists:tags,id'],
            'estado' => [
                'required',
                Rule::in(['borrador', 'publicada', 'programada'])
            ],
            'publicado_en' => $this->reglasPublicadoEn(),
            'portada' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
