<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Common\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;

/**
 * Define o timestamp atual do relógio interno do modem ou datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Modem\Actions
 */
abstract class AbstractSetTimeAction extends AbstractABSMonitorAction
{

    /**
     * Monta a mensagem para redefinir o timestamp interno.
     *
     * @return string
     */
    private function packCurrentTimeMessage(): string
    {
        $address = $this->getWriteAddress();

        $day = (int) date('d');
        $month = (int) date('m');
        $year = (int) date('Y');

        $hour = (int) date('H');
        $min = (int) date('i');
        $sec = (int) date('s');

        $this->modem->setData('modem.time', time());

        return $this->modbus->pack16(1, $address, $sec, $min, $hour, $day, $month, ($year - 2000), 0, 0, 1);
    }

    /**
     * Atualiza as informações do timestamp interno do modem ou datalogger.
     *
     * @return void
     */
    private function updatedTime()
    {
        $this->logger->logInfo('internal time updated to %s',
            date('Y/m/d H:i:s', (int) $this->modem->getData('modem.time')));

        $this->modem->setData('modem.updates.time', false);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\AbstractABSMonitorAction::writeCommand()
     */
    protected function writeCommand(): bool
    {
        if ((bool) $this->modem->getData('modem.updates.time')) {
            return $this->sendMessage($this->packCurrentTimeMessage());
        }

        $this->sleepStage(1800); // Próxima execução em 30min
        return true;
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

        if ($this->receiveMessage($message, 8)) {

            $this->modbus->unpack($message, 1, 16);

            $this->updatedTime();
            $this->sleepStage(1800); // Próxima execução em 30min

            return true;
        }

        return false;
    }

    /**
     * Obtém o endereço de escrita do novo timestamp interno.
     *
     * @return int
     */
    abstract protected function getWriteAddress(): int;
}