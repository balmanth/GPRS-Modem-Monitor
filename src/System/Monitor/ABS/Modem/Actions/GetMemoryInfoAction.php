<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Modem\Actions;

/**
 * Obtém as informações de memória do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Modem\Actions
 */
final class GetMemoryInfoAction extends \GPRS\System\Monitor\ABS\Common\Actions\AbstractGetMemoryInfoAction
{

    /**
     * Endereço de leitura das informações.
     *
     * @var int
     */
    const R_MEMINFO_ADDRRES = 64200;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractGetMemoryInfoAction::writeCommand()
     */
    protected function writeCommand(): bool
    {
        if ((bool) $this->modem->getData('modem.signal')) {
            return parent::writeCommand();
        }

        return false;
    }

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