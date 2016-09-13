<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;

/**
 * Define o timestamp atual do relógio interno do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Actions
 */
final class SetTimeAction extends AbstractABSMonitorAction
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

        // Não é necessário atualizar o timestamp do relógio interno do modem.
        if (! (bool) $this->modem->getData('modem.updates.time')) {

            $this->sleepStage(1800); // Próxima execução em 30min
            return false;
        }

        $sec = (int) date('s');
        $min = (int) date('i');
        $hour = (int) date('H');
        $day = (int) date('d');
        $month = (int) date('m');
        $year = (int) date('Y');

        $message = $this->modbus->pack16(1, 64502, $sec, $min, $hour, $day, $month, ($year - 2000), 0, 0, 1);

        if (! $this->writeMessage($message)) {
            return false;
        }

        $modemTime = sprintf('%04d/%02d/%02d %02d:%02d:%02d', $year, $month, $day, $hour, $min, $sec);
        $this->modem->setData('modem.time', $modemTime);

        return true;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractABSMonitorAction::readResponse()
     */
    protected function readResponse(): bool
    {
        if (! $this->readMessage(1, 16, 8, $response)) {
            return false;
        }

        $this->logger->logInfo('internal time updated to %s', $this->modem->getData('modem.time'));

        $this->modem->setData('modem.updates.time', false);
        $this->sleepStage(1800); // Próxima execução em 30min

        return true;
    }
}