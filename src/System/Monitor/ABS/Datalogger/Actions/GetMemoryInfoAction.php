<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Datalogger\Actions;

/**
 * Obtém as informações de memória do datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Datalogger\Actions
 */
final class GetMemoryInfoAction extends \GPRS\System\Monitor\ABS\Common\Actions\AbstractGetMemoryInfoAction
{

    /**
     * Endereço de leitura das informações.
     *
     * @var int
     */
    const R_MEMINFO_ADDRRES = 200;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractGetMemoryInfoAction::getReadAddress()
     */
    protected function getReadAddress(): int
    {
        return self::R_MEMINFO_ADDRRES;
    }
}