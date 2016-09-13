<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Datalogger\Actions;

/**
 * Obtém as informações do hardware do datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Datalogger\Actions
 */
final class GetDeviceInfoAction extends \GPRS\System\Monitor\ABS\Common\Actions\AbstractGetDeviceInfoAction
{

    /**
     * Endereço de leitura das informações.
     *
     * @var int
     */
    const R_DEVINFO_ADDRRES = 50;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractGetDeviceInfoAction::getReadAddress()
     */
    protected function getReadAddress(): int
    {
        return self::R_DEVINFO_ADDRRES;
    }
}