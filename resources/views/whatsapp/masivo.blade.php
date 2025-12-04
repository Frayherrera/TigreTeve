<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Masivo WhatsApp</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            background: linear-gradient(to right, #25D366, #128C7E);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .header h2 i {
            font-size: 32px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .form-container {
            padding: 40px;
        }

        .alert {
            background-color: #e3f2fd;
            border-left: 5px solid #2196f3;
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease-out;
        }

        .alert i {
            color: #2196f3;
            font-size: 20px;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: #25D366;
            width: 20px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }

        .form-control:focus {
            border-color: #25D366;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.2);
            outline: none;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #25D366;
            cursor: pointer;
        }

        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
            font-weight: 500;
        }

        .file-input-container {
            position: relative;
            overflow: hidden;
            margin-top: 5px;
        }

        .file-input-container input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px;
            background-color: #f0f9ff;
            border: 2px dashed #25D366;
            border-radius: 10px;
            color: #128C7E;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
        }

        .file-input-label:hover {
            background-color: #e6f7f0;
        }

        .file-input-label i {
            font-size: 20px;
        }

        .submit-btn {
            background: linear-gradient(to right, #25D366, #128C7E);
            color: white;
            border: none;
            padding: 18px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
            letter-spacing: 0.5px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn i {
            font-size: 22px;
        }

        .info-note {
            background-color: #f8f9fa;
            border-left: 4px solid #25D366;
            padding: 15px;
            margin-top: 25px;
            border-radius: 8px;
            font-size: 14px;
            color: #555;
        }

        .info-note strong {
            color: #128C7E;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .container {
                border-radius: 15px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .header {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fab fa-whatsapp"></i> Envío Masivo WhatsApp</h2>
            <p>Envía mensajes personalizados a múltiples contactos</p>
        </div>

        <div class="form-container">
            @if (session('msg'))
                <div class="alert">
                    <i class="fas fa-info-circle"></i>
                    <div>{!! session('msg') !!}</div>
                </div>
            @endif

            <form action="{{ route('whatsapp.masivo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label for="file"><i class="fas fa-file-csv"></i> Archivo CSV</label>
                    <div class="file-input-container">
                        <input type="file" name="file" id="file" accept=".csv">
                        <div class="file-input-label" id="fileLabel">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Subir archivo CSV (columna: teléfono)</span>
                        </div>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="list_from_db" id="list_from_db" value="1">
                    <label for="list_from_db">Usar lista de contactos desde la base de datos</label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="titulo"><i class="fas fa-heading"></i> Título (plantilla)</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ej: ¡Nueva oferta especial!">
                    </div>

                    <div class="form-group">
                        <label for="link"><i class="fas fa-link"></i> Link del botón</label>
                        <input type="text" class="form-control" id="link" name="link" placeholder="Ej: noticias/slug-oferta-especial">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cuerpo"><i class="fas fa-align-left"></i> Cuerpo del mensaje (plantilla)</label>
                    <textarea class="form-control" id="cuerpo" name="cuerpo" required placeholder="Escribe aquí el contenido principal del mensaje..."></textarea>
                </div>

                <div class="info-note">
                    <p><strong>Nota:</strong> El sistema enviará mensajes de WhatsApp a todos los números proporcionados. Asegúrate de tener permiso para contactar a estos destinatarios.</p>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fab fa-whatsapp"></i> Enviar Mensajes Masivos
                </button>
            </form>
        </div>
    </div>

    <script>
        // Actualizar texto del label del archivo
        document.getElementById('file').addEventListener('change', function(e) {
            const fileName = this.files[0] ? this.files[0].name : 'Subir archivo CSV (columna: teléfono)';
            document.getElementById('fileLabel').innerHTML = `
                <i class="fas fa-file-csv"></i>
                <span>${fileName}</span>
            `;
        });

        // Contador de caracteres para el cuerpo del mensaje
        document.getElementById('cuerpo').addEventListener('input', function(e) {
            const charCount = this.value.length;
            const maxChars = 1000;
            
            // Si no existe el contador, lo creamos
            let counter = document.getElementById('charCounter');
            if (!counter) {
                counter = document.createElement('div');
                counter.id = 'charCounter';
                counter.style.fontSize = '12px';
                counter.style.textAlign = 'right';
                counter.style.marginTop = '5px';
                counter.style.color = '#666';
                this.parentNode.appendChild(counter);
            }
            
            counter.textContent = `${charCount} / ${maxChars} caracteres`;
            
            // Cambiar color si se acerca al límite
            if (charCount > maxChars * 0.9) {
                counter.style.color = '#ff9800';
            } else if (charCount > maxChars) {
                counter.style.color = '#f44336';
            } else {
                counter.style.color = '#666';
            }
        });
    </script>
</body>
</html>