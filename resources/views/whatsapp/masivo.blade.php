<div class="container">
    <h2>Enviar masivo WhatsApp</h2>

    @if (session('msg'))
        <div class="alert alert-info">{!! session('msg') !!}</div>
    @endif

    <form action="{{ route('whatsapp.masivo') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label>Archivo CSV (columna: teléfono)</label>
            <input type="file" name="file">
        </div>

        <div>
            <label>Usar lista desde BD</label>
            <input type="checkbox" name="list_from_db" value="1">
        </div>

        <div>
            <label>Título (plantilla)</label>
            <input type="text" name="titulo" required>
        </div>

        <div>
            <label>Cuerpo (plantilla)</label>
            <textarea name="cuerpo" required></textarea>
        </div>

        {{-- <div>
            <label>Imagen (URL pública) - opcional</label>
            <input type="url" name="image">
        </div> --}}

        <div>
            <label>Link dinámico del botón (ej: noticias/slug)</label>
            <input type="text" name="link">
        </div>

        <button type="submit">Enviar masivo</button>
    </form>
</div>
{{-- <div class="container">
    <h2>Enviar Mensaje Masivo</h2>

    @if (session('msg'))
        <div class="alert alert-info">{!! session('msg') !!}</div>
    @endif

    <form action="{{ route('whatsapp.masivo') }}" method="POST">
        @csrf

        <label>Título</label>
        <input class="form-control" name="titulo" required>

        <label>Cuerpo</label>
        <textarea class="form-control" name="cuerpo" required></textarea>

        <label>Link de la noticia</label>
        <input class="form-control" name="link" type="text">

        <br>
        <button class="btn btn-primary">Enviar</button>
    </form>
</div> --}}
