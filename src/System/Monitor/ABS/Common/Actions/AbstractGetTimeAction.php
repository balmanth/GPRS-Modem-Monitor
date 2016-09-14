<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Common\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;

/**
 * Obtém o timestamp atual do relógio interno do modem ou datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Common\Actions
 */
abstract class AbstractGetTimeAction extends AbstractABSMonitorAction
{

    /**
     * Atualiza as informações do timestamp interno.
     *
     * @param array $data
     *            Dados recebidos do gateway.
     * @return void
     */
    private function updateTime(array &$data)
    {
        $time = mktime($data[3], $data[2], $data[1], $data[5], $data[4], (2000 + $data[6]));
        $update = (date('YmdHi') !== date('YmdHi', $time));

        $this->logger->logInfo('internal time: %s, server time: %s, update: %s', date('Y/m/d H:i:s', $time),
            date('Y/m/d H:i:s'), ($update ? 'yes' : 'no'));

        $this->modem->setData('modem.updates.time', $update);
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
        return $this->sendMessage($message);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\AbstractABSMonitorAction::readResponse()
     */
    protected function readResponse(): bool
    {
        $message = '';

        if ($this->receiveMessage($message, 17)) {

            $response = $this->modbus->unpack($message, 1, 4, 'n6');

            $this->updateTime($response['data']);
            $this->sleepStage(3600); // Próxima execução em 1h

            return true;
        }

        return false;
    }

    /**
     * Obtém o endereço de leitura do timestamp interno.
     *
     * @return int
     */
    abstract protected function getReadAddress(): int;
}