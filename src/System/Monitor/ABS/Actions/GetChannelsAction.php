<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorCallable;

/**
 * Obtém os canais do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Actions
 */
final class GetChannelsAction extends AbstractABSMonitorCallable
{

    /**
     * Atualiza as informações sobre os canais de um único tipo.
     *
     * @param string $type
     *            Tipo de canal.
     * @param int $states
     *            Estados dos canais (8 bits onde cada bit representa: 1 = Habilitado, 0 = Desabilitado)
     * @return void
     */
    private function updateChannelStates(string $type, int $states)
    {
        $configKey = 'modem.channel.' . $type;
        $newStates = substr('00000000' . decbin($states & 0xFF), - 8);

        // Estados do canal modificados.
        if ($this->modem->getData($configKey) !== $newStates) {

            $this->logger->logInfo('channel: %s, states: %s', $type, $newStates);
            $this->modem->setData($configKey, $newStates);
        }
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractABSMonitorCallable::writeCommand()
     */
    protected function writeCommand(): bool
    {
        if (! $this->modemReady()) {
            return false;
        }

        $message = $this->modbus->pack4(1, 64360, 6);
        return $this->writeMessage($message);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractABSMonitorCallable::readResponse()
     */
    protected function readResponse(): bool
    {
        if (! $this->readMessage(1, 4, 17, $response, 'n6')) {
            return false;
        }

        $data = &$response['data'];

        $this->updateChannelStates('A1', $data[1]); // Entrada analógica 1-8
        $this->updateChannelStates('A2', $data[2]); // Entrada analógica 9-16
        $this->updateChannelStates('PF', $data[3]); // Frequência de pulso
        $this->updateChannelStates('PC', $data[4]); // Totalizador de pulso
        $this->updateChannelStates('TC', $data[5]); // Totalizador de tempo
        $this->updateChannelStates('TZ', $data[6]); // Totalizador de valor analógico

        $this->modem->setData('modem.channels', true);
        $this->sleepStage(600); // Próxima execução em 10min.

        return true;
    }
}