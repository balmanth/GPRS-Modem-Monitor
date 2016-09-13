<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Datalogger\Actions;

/**
 * Obtém as informações sobre os canais do datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Datalogger\Actions
 */
final class GetChannelsAction extends \GPRS\System\Monitor\ABS\Common\Actions\AbstractGetChannelsAction
{

    /**
     * Endereço de leitura das informações.
     *
     * @var int
     */
    const R_CHANNEL_ADDRRES = 360;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractGetChannelsAction::getReadAddress()
     */
    protected function getReadAddress(): int
    {
        return self::R_CHANNEL_ADDRRES;
    }
}