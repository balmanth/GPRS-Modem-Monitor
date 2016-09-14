<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Common\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;

/**
 * Obtém as informações de memória do modem ou datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Common\Actions
 */
abstract class AbstractGetMemoryInfoAction extends AbstractABSMonitorAction
{

    /**
     * Atualiza as informações de memória.
     *
     * @param array $data
     *            Dados recebidos do gateway.
     * @return void
     */
    private function updateMemoryInfo(array &$data)
    {
        $blockSize = $data[1];
        $blockCount = $data[2];
        $nextBlock = $data[5];

        $this->modem->setData('modem.memory.blockSize', $blockSize);
        $this->logger->logInfo('block size: %d', $blockSize);

        $this->modem->setData('modem.memory.blockCount', $blockCount);
        $this->logger->logInfo('block count: %d', $blockCount);

        $this->modem->setData('modem.memory.nextBlock', $nextBlock);
        $this->logger->logInfo('next block write index: %d', $nextBlock);

        $this->modem->setData('modem.memory', true);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\AbstractABSMonitorAction::writeCommand()
     */
    protected function writeCommand(): bool
    {
        $message = $this->modbus->pack4(1, $this->getReadAddress(), 5);
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

        if ($this->receiveMessage($message, 15)) {

            $response = $this->modbus->unpack($message, 1, 4, 'n5');

            $this->updateMemoryInfo($response['data']);
            $this->sleepStage(600); // Próxima execução em 10min.

            return true;
        }

        return false;
    }

    /**
     * Obtém o endereço de leitura das informações de memória.
     *
     * @return int
     */
    abstract protected function getReadAddress(): int;
}