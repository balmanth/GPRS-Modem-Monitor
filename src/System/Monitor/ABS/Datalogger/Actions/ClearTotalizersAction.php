<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Datalogger\Actions;

/**
 * Limpa os valores armazenados nos totalizadores do datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Datalogger\Actions
 */
final class ClearTotalizersAction extends \GPRS\System\Monitor\ABS\Common\Actions\AbstractClearTotalizersAction
{

    /**
     * Endereço de envio da mensagem para limpeza dos totalizadores.
     *
     * @var int
     */
    const W_CLEARTZ_ADDRRES = 502;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractClearTotalizersAction::getWriteAddress()
     */
    protected function getWriteAddress(): int
    {
        return self::W_CLEARTZ_ADDRRES;
    }
}