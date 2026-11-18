<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerarClavesPush extends Command
{
    protected $signature = 'push:claves';

    protected $description = 'Genera un par de claves VAPID para las notificaciones push';

    public function handle(): int
    {
        $claves = VAPID::createVapidKeys();

        $this->line('Copia estas líneas en el .env del servidor:');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY='.$claves['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$claves['privateKey']);
        $this->newLine();
        $this->warn('Si cambias las claves, los suscriptores actuales dejarán de recibir avisos.');

        return self::SUCCESS;
    }
}
