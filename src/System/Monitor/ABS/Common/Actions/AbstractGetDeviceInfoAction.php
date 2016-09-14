<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Common\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;

/**
 * Obtém as informações do hardware do modem ou datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Common\Actions
 */
abstract class AbstractGetDeviceInfoAction extends AbstractABSMonitorAction
{

    /**
     * Atualiza as informações do hardware.
     *
     * @param array $data
     *            Dados recebidos do gateway.
     * @return void
     */
    private function updateMemoryInfo(array &$data)
    {
        $this->logger->logInfo('firmware version: %d.%d.%d.%d', $data[1], $data[2], $data[3], $data[4]);
        $this->logger->logInfo('digital inputs: %d', $data[5]);
        $this->logger->logInfo('digital outputs: %d', $data[7]);
        $this->logger->logInfo('analogical inputs: %d', $data[6]);
        $this->logger->logInfo('analogical output: %d', $data[8]);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\AbstractABSMonitorAction::writeCommand()
     */
    protected function writeCommand(): bool
    {
        $message = $this->modbus->pack4(1, $this->getReadAddress(), 8);
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

        if ($this->receiveMessage($message, 21)) {

            $response = $this->modbus->unpack($message, 1, 4, 'n8');

            $this->updateMemoryInfo($response['data']);
            $this->sleepStage(1800); // Próxima execução em 30min

            return true;
        }

        return false;
    }

    /**
     * Obtém o endereço de leitura das informações sobre o hardware.
     *
     * @return int
     */
    abstract protected function getReadAddress(): int;
}