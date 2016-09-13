<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Datalogger\Actions;

/**
 * Obtém o timestamp atual do relógio interno do datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Datalogger\Actions
 */
final class GetTimeAction extends \GPRS\System\Monitor\ABS\Common\Actions\AbstractGetTimeAction
{

    /**
     * Endereço de leitura das informações.
     *
     * @var int
     */
    const R_TIME_ADDRRES = 15;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractGetTimeAction::getReadAddress()
     */
    protected function getReadAddress(): int
    {
        return self::R_TIME_ADDRRES;
    }
}