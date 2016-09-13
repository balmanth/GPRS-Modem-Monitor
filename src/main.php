<?php
/**
 * Monitor para modems GPRS.
 *
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 */
declare(strict_types = 1);
namespace GPRS;

require_once '../libraries/BCL/Bootstrap.php';

use BCL\System\Packages\PackageManager;
use BCL\System\Packages\DefaultClassPackage;
use BCL\System\Logger\EchoLogger;
use GPRS\System\LogManager;
use GPRS\System\ConnectionManager;
use GPRS\System\MonitorManager;
use GPRS\Local\ModemManager;
/*
 * Autoload.
 */
$autoload = new PackageManager();
$autoload->addPackage(new DefaultClassPackage('GPRS', __DIR__)); // namespace 'GPRS/'

/*
 * Configurações e inicialização.
 */
$logger = new LogManager();
$logger->addLogger(new EchoLogger());

$modems = new ModemManager();
$connections = new ConnectionManager($logger);

$monitors = new MonitorManager($modems, $logger, $connections);
$monitors->register('ABS\\Modem\\ABSMonitor');
$monitors->register('ABS\\Datalogger\\ABSMonitor');

// loop de monitoramento.
while (true) {

    $monitors->monitore();
    usleep(100000); // 0.1 segundo (Impede sobrecarga)
}