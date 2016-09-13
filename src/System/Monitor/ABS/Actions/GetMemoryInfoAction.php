<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;

/**
 * Obtém as informações de memória do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Actions
 */
final class GetMemoryInfoAction extends AbstractABSMonitorAction
{

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractABSMonitorAction::writeCommand()
     */
    protected function writeCommand(): bool
    {
        if (! $this->modemReady()) {
            return false;
        }

        $message = $this->modbus->pack4(1, 64200, 5);
        return $this->writeMessage($message);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractABSMonitorAction::readResponse()
     */
    protected function readResponse(): bool
    {
        if (! $this->readMessage(1, 4, 15, $response, 'n5')) {
            return false;
        }

        $data = &$response['data'];

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
        $this->sleepStage(600); // Próxima execução em 10min.

        return true;
    }
}