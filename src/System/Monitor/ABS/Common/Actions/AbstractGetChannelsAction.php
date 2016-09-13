<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Common\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;

/**
 * Obtém as informações sobre os canais do modem ou datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Common\Actions
 */
abstract class AbstractGetChannelsAction extends AbstractABSMonitorAction
{

    /**
     * Atualiza as informações sobre os estados de um tipo de canal.
     *
     * @param string $type
     *            Tipo de canal.
     * @param int $states
     *            Estados do tipo de canal (8 bits onde cada bit representa: 1 = Habilitado, 0 = Desabilitado)
     * @return void
     */
    private function setChannelStates(string $type, int $states)
    {
        $configKey = 'modem.channel.' . $type;
        $newStates = substr('00000000' . decbin($states & 0xFF), - 8);

        // Estados para um tipo de canal não modificado.
        if ($this->modem->getData($configKey) === $newStates) {
            return;
        }

        $this->logger->logInfo('channel: %s, states: %s', $type, $newStates);
        $this->modem->setData($configKey, $newStates);
    }

    /**
     * Atualiza as informações sobre o estado dos canais.
     *
     * @param array $data
     *            Dados recebidos do gateway.
     * @return void
     */
    private function updateChannels(array &$data)
    {
        $this->setChannelStates('A1', $data[1]); // Entrada analógica 1-8
        $this->setChannelStates('A2', $data[2]); // Entrada analógica 9-16
        $this->setChannelStates('PF', $data[3]); // Frequência de pulso
        $this->setChannelStates('PC', $data[4]); // Totalizador de pulso
        $this->setChannelStates('TC', $data[5]); // Totalizador de tempo
        $this->setChannelStates('TZ', $data[6]); // Totalizador de valor analógico

        $this->modem->setData('modem.channels', true);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\AbstractABSMonitorAction::writeCommand()
     */
    protected function writeCommand(): bool
    {
        $message = $this->modbus->pack4(1, $this->getReadAddress(), 6);
        return $this->writeMessage($message);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\AbstractABSMonitorAction::readResponse()
     */
    protected function readResponse(): bool
    {
        if ($this->readMessage(1, 4, 17, $response, 'n6')) {

            $this->updateChannels($response['data']);
            $this->sleepStage(600); // Próxima execução em 10min.

            return true;
        }

        return false;
    }

    /**
     * Obtém o endereço de leitura das informações sobre os canais.
     *
     * @return int
     */
    abstract protected function getReadAddress(): int;
}