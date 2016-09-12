<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS;

use BCL\System\Logger\LogManager;
use GPRS\System\ModemManagerInterface;
use GPRS\System\ConnectionManager;
use GPRS\System\Monitor\AbstractMonitorBase;

/**
 * Monitor dos modem fornecidos pela ABS/ALR.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS
 */
final class ABSModemMonitor extends AbstractMonitorBase
{

    /**
     * Tipo de modems do monitor.
     *
     * @var int
     */
    const MODEM_TYPE_ABS = 0x01;

    /**
     * Reinicia um totalizador do modem.
     *
     * @var int
     */
    const MODEM_GET_SIG_QUALITY = 0x00;

    /**
     * Obtém as informações do hardware do modem.
     *
     * @var int
     */
    const MODEM_GET_DEVICE_INFO = 0x01;

    /**
     * Obtém o timestamp do relógio interno do modem.
     *
     * @var int
     */
    const MODEM_GET_TIME = 0x02;

    /**
     * Define o timestamp do relógio interno do modem.
     *
     * @var int
     */
    const MODEM_SET_TIME = 0x03;

    /**
     * Obtém os canais do modem.
     *
     * @var int
     */
    const MODEM_GET_CHANNELS = 0x04;

    /**
     * Reinicia um totalizador do modem.
     *
     * @var int
     */
    const MODEM_RESET_TOTALIZERS = 0x05;

    /**
     * Obtém as informações de memória do modem.
     *
     * @var int
     */
    const MODEM_GET_MEMORY_INFO = 0x06;

    /**
     * Obtém dados da memória do modem.
     *
     * @var int
     */
    const MODEM_GET_MEMORY_DATA = 0x07;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractMonitorBase::__construct($modems, $log, $connections)
     */
    public function __construct(ModemManagerInterface $modems, LogManager $log, ConnectionManager $connections)
    {
        parent::__construct($modems, $log, $connections, self::MODEM_TYPE_ABS);

        $this->addListener(self::MODEM_GET_SIG_QUALITY)
            ->attach(new Actions\GetSignalQualityAction());

        $this->addListener(self::MODEM_GET_DEVICE_INFO)
            ->attach(new Actions\GetDeviceInfoAction());

        $this->addListener(self::MODEM_GET_TIME)
            ->attach(new Actions\GetTimeAction());

        $this->addListener(self::MODEM_SET_TIME)
            ->attach(new Actions\SetTimeAction());

        $this->addListener(self::MODEM_GET_CHANNELS)
            ->attach(new Actions\GetChannelsAction());

        $this->addListener(self::MODEM_RESET_TOTALIZERS)
            ->attach(new Actions\ResetTotalizersAction());

        $this->addListener(self::MODEM_GET_MEMORY_INFO)
            ->attach(new Actions\GetMemoryInfoAction());

        $this->addListener(self::MODEM_GET_MEMORY_DATA)
            ->attach(new Actions\GetMemoryDataAction());
    }
}