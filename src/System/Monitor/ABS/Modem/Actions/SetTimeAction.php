<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Modem\Actions;

/**
 * Define o timestamp atual do relógio interno do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Modem\Actions
 */
final class SetTimeAction extends \GPRS\System\Monitor\ABS\Common\Actions\AbstractSetTimeAction
{

    /**
     * Endereço de escrita das informações.
     *
     * @var int
     */
    const W_TIME_ADDRRES = 502;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractSetTimeAction::writeCommand()
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
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractSetTimeAction::getWriteAddress()
     */
    protected function getWriteAddress(): int
    {
        return self::W_TIME_ADDRRES;
    }
}