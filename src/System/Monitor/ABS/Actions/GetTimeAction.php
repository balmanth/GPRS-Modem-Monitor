<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorCallable;

/**
 * Obtém o timestamp atual do relógio interno do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Actions
 */
final class GetTimeAction extends AbstractABSMonitorCallable
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

        $message = $this->modbus->pack4(1, 64015, 6);
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
        $time = mktime($data[3], $data[2], $data[1], $data[5], $data[4], (2000 + $data[6]));
        $update = (date('YmdHi') !== date('YmdHi', $time));

        $this->logger->logInfo('internal time: %s, server time: %s, update: %s', date('Y/m/d H:i:s', $time),
            date('Y/m/d H:i:s'), ($update ? 'yes' : 'no'));

        $this->modem->setData('modem.updates.time', $update);
        $this->sleepStage(3600); // Próxima execução em 1h

        return true;
    }
}