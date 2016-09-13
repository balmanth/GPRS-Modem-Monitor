<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Datalogger;

use BCL\System\Logger\LogManager;
use GPRS\System\ModemManagerInterface;
use GPRS\System\ConnectionManager;
use GPRS\System\Monitor\AbstractMonitorBase;

/**
 * Monitor para os dataloggers conectados aos modems fornecidos pela ABS/ALR.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Datalogger
 */
final class ABSMonitor extends AbstractMonitorBase
{

    /**
     * Obtém as informações do hardware.
     *
     * @var int
     */
    const DATALOGGER_GET_DEVICE_INFO = 2000;

    /**
     * Obtém o timestamp do relógio interno.
     *
     * @var int
     */
    const DATALOGGER_GET_TIME = 2001;

    /**
     * Define o timestamp do relógio interno.
     *
     * @var int
     */
    const DATALOGGER_SET_TIME = 2002;

    /**
     * Obtém as informações sobre os canais.
     *
     * @var int
     */
    const DATALOGGER_GET_CHANNELS = 2003;

    /**
     * Reinicia o valor armazenado nos totalizadores.
     *
     * @var int
     */
    const DATALOGGER_CLEAR_TOTALIZERS = 2004;

    /**
     * Obtém as informações de memória.
     *
     * @var int
     */
    const DATALOGGER_GET_MEMORY_INFO = 2005;

    /**
     * Obtém dados de um bloco de memória.
     *
     * @var int
     */
    const DATALOGGER_GET_MEMORY_DATA = 2006;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractMonitorBase::__construct($modems, $log, $connections)
     */
    public function __construct(ModemManagerInterface $modems, LogManager $log, ConnectionManager $connections)
    {
        parent::__construct($modems, $log, $connections, self::MONITOR_ABS_DATALOGGER);

        $this->addListener(self::DATALOGGER_GET_DEVICE_INFO)
            ->attach(new Actions\GetDeviceInfoAction());

        $this->addListener(self::DATALOGGER_GET_TIME)
            ->attach(new Actions\GetTimeAction());

        $this->addListener(self::DATALOGGER_SET_TIME)
            ->attach(new Actions\SetTimeAction());

        $this->addListener(self::DATALOGGER_GET_CHANNELS)
            ->attach(new Actions\GetChannelsAction());

        $this->addListener(self::DATALOGGER_CLEAR_TOTALIZERS)
            ->attach(new Actions\ClearTotalizersAction());

        $this->addListener(self::DATALOGGER_GET_MEMORY_INFO)
            ->attach(new Actions\GetMemoryInfoAction());

        $this->addListener(self::DATALOGGER_GET_MEMORY_DATA)
            ->attach(new Actions\GetMemoryDataAction());
    }
}