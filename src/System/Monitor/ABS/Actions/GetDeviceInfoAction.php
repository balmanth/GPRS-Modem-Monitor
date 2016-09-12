<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorCallable;

/**
 * Obtém as informações do hardware do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Actions
 */
final class GetDeviceInfoAction extends AbstractABSMonitorCallable
{

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

        $message = $this->modbus->pack4(1, 64050, 8);
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
        if (! $this->readMessage(1, 4, 21, $response, 'n8')) {
            return false;
        }

        $data = &$response['data'];

        $this->logger->logInfo('firmware version: %d.%d.%d.%d', $data[1], $data[2], $data[3], $data[4]);
        $this->logger->logInfo('digital inputs: %d', $data[5]);
        $this->logger->logInfo('digital outputs: %d', $data[7]);
        $this->logger->logInfo('analogical inputs: %d', $data[6]);
        $this->logger->logInfo('analogical output: %d', $data[8]);

        $this->sleepStage(1800); // Próxima execução em 30min
        return true;
    }
}