<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateNoticiaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('editar noticias') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

         $id = $this->route('noticia')->id;
         
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('noticias', 'slug')->ignore($id)
            ],
            'resumen' => ['nullable', 'string', 'max:500'],
            'cuerpo' => ['required', 'string'],
            'category_id' => ['nullable', 'exists:categorias,id'],
            'tags' => ['array'],
            'tags.*' => ['exists:tags,id'],
            'estado' => [
                'required',
                Rule::in(['borrador', 'publicada', 'programada'])
            ],
            'publicado_en' => ['nullable', 'date'],
            'portada' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
