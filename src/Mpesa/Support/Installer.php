<?php

namespace Yourdudeken\Mpesa\Support;

use Composer\Script\Event;

class Installer
{
    public static function install(Event $event)
    {
        $config = __DIR__ . '/../../config/mpesa.php';
        $sandboxCert = __DIR__ . '/../../config/SandboxCertificate.cer';
        $productionCert = __DIR__ . '/../../config/ProductionCertificate.cer';
        $configDir = self::getConfigDirectory($event);

        // Copy mpesa config file
        if (! \is_file($configDir . '/mpesa.php')) {
            \copy($config, $configDir . '/mpesa.php');
        }

        // Copy sandbox certificate
        if (! \is_file($configDir . '/SandboxCertificate.cer')) {
            \copy($sandboxCert, $configDir . '/SandboxCertificate.cer');
        }

        // Copy production certificate
        if (! \is_file($configDir . '/ProductionCertificate.cer')) {
            \copy($productionCert, $configDir . '/ProductionCertificate.cer');
        }
    }

    public static function getConfigDirectory(Event $event)
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $configDir = $vendorDir . '/../src/config';

        if (! \is_dir($configDir)) {
            \mkdir($configDir, 0755, true);
        }

        return $configDir;
    }
}
