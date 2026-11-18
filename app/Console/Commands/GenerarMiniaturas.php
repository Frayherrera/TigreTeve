<?php

namespace App\Console\Commands;

use App\Jobs\GenerarMiniaturaJob;
use App\Models\Noticia;
use Illuminate\Console\Command;

class GenerarMiniaturas extends Command
{
    protected $signature = 'noticias:miniaturas {--todas : Regenerar también las que ya tienen miniatura} {--ahora : Procesar aquí mismo en vez de usar la cola}';

    protected $description = 'Genera las miniaturas WebP de las portadas de noticias existentes';

    public function handle(): int
    {
        $consulta = Noticia::whereNotNull('portada_path')
            ->when(! $this->option('todas'), fn ($q) => $q->whereNull('miniatura_path'));

        $total = $consulta->count();
        if ($total === 0) {
            $this->info('No hay portadas pendientes.');

            return self::SUCCESS;
        }

        $this->info("Procesando {$total} portadas…");
        $barra = $this->output->createProgressBar($total);
        $errores = 0;

        $consulta->select('id')->chunkById(100, function ($noticias) use ($barra, &$errores) {
            foreach ($noticias as $noticia) {
                if ($this->option('ahora')) {
                    try {
                        (new GenerarMiniaturaJob($noticia->id))->handle();
                    } catch (\Throwable $e) {
                        $errores++;
                        $this->newLine();
                        $this->warn("Noticia {$noticia->id}: {$e->getMessage()}");
                    }
                } else {
                    GenerarMiniaturaJob::dispatch($noticia->id);
                }
                $barra->advance();
            }
        });

        $barra->finish();
        $this->newLine();
        $this->info($this->option('ahora')
            ? 'Listo.'.($errores ? " {$errores} con error." : '')
            : 'Encoladas. Se procesan con php artisan queue:work.');

        return self::SUCCESS;
    }
}
