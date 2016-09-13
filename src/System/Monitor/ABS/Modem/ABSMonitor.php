<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Modem;

use BCL\System\Logger\LogManager;
use GPRS\System\ModemManagerInterface;
use GPRS\System\ConnectionManager;
use GPRS\System\Monitor\AbstractMonitorBase;

/**
 * Monitor dos modems fornecidos pela ABS/ALR.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Modem
 */
final class ABSMonitor extends AbstractMonitorBase
{

    /**
     * Obtém o nível de qualidade de sinal.
     *
     * @var int
     */
    const MODEM_GET_SIGNAL_QUALITY = 1000;

    /**
     * Obtém as informações do hardware.
     *
     * @var int
     */
    const MODEM_GET_DEVICE_INFO = 1001;

    /**
     * Obtém o timestamp do relógio interno.
     *
     * @var int
     */
    const MODEM_GET_TIME = 1002;

    /**
     * Define o timestamp do relógio interno.
     *
     * @var int
     */
    const MODEM_SET_TIME = 1003;

    /**
     * Obtém as informações sobre os canais.
     *
     * @var int
     */
    const MODEM_GET_CHANNELS = 1004;

    /**
     * Reinicia o valor armazenado nos totalizadores.
     *
     * @var int
     */
    const MODEM_CLEAR_TOTALIZERS = 1005;

    /**
     * Obtém as informações de memória.
     *
     * @var int
     */
    const MODEM_GET_MEMORY_INFO = 1006;

    /**
     * Obtém dados de um bloco de memória.
     *
     * @var int
     */
    const MODEM_GET_MEMORY_DATA = 1007;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractMonitorBase::__construct($modems, $log, $connections)
     */
    public function __construct(ModemManagerInterface $modems, LogManager $log, ConnectionManager $connections)
    {
        parent::__construct($modems, $log, $connections, self::MONITOR_ABS_MODEM);

        $this->addListener(self::MODEM_GET_SIGNAL_QUALITY)
            ->attach(new Actions\GetSignalQualityAction());

        $this->addListener(self::MODEM_GET_DEVICE_INFO)
            ->attach(new Actions\GetDeviceInfoAction());

        $this->addListener(self::MODEM_GET_TIME)
            ->attach(new Actions\GetTimeAction());

        $this->addListener(self::MODEM_SET_TIME)
            ->attach(new Actions\SetTimeAction());

        $this->addListener(self::MODEM_GET_CHANNELS)
            ->attach(new Actions\GetChannelsAction());

        $this->addListener(self::MODEM_CLEAR_TOTALIZERS)
            ->attach(new Actions\ClearTotalizersAction());

        $this->addListener(self::MODEM_GET_MEMORY_INFO)
            ->attach(new Actions\GetMemoryInfoAction());

        $this->addListener(self::MODEM_GET_MEMORY_DATA)
            ->attach(new Actions\GetMemoryDataAction());
    }
}