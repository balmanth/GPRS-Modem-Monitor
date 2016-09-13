<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Datalogger\Actions;

/**
 * Obtém dados da memória do datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Datalogger\Actions
 */
final class GetMemoryDataAction extends \GPRS\System\Monitor\ABS\Common\Actions\AbstractGetMemoryDataAction
{

    /**
     * Endereço de leitura dos dados.
     *
     * @var int
     */
    const R_MEMDATA_ADDRRES = 205;

    /**
     * Endereço de leitura do índice de memória desejado.
     *
     * @var int
     */
    const W_MEMINDEX_ADDRRES = 511;

    /**
     * Checa se a configuração dos canais foi carregada.
     * Checa se a configuração da memória foi carregada.
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractGetMemoryDataAction::modemReady()
     */
    protected function isReady(): bool
    {
        return (bool) $this->modem->getData('modem.channels') && (bool) $this->modem->getData('modem.memory');
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractGetMemoryDataAction::getReadAddress()
     */
    protected function getReadAddress(): int
    {
        return self::R_MEMDATA_ADDRRES;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\Common\Actions\AbstractGetMemoryDataAction::getWriteAddress()
     */
    protected function getWriteAddress(): int
    {
        return self::W_MEMINDEX_ADDRRES;
    }
}