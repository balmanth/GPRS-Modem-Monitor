<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorCallable;

/**
 * Obtém a qualidade do sinal do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Actions
 */
final class GetSignalQualityAction extends AbstractABSMonitorCallable
{

    /**
     * Menor de sinal baixo.
     *
     * @var int
     */
    const MIN_DBM = - 102;

    /**
     * Maior nível de sinal.
     *
     * @var int
     */
    const MAX_DBM = - 50;

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\AbstractABSMonitorCallable::writeCommand()
     */
    protected function writeCommand(): bool
    {
        $message = $this->modbus->pack4(1, 65000, 1);
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
        if (! $this->readMessage(1, 4, 7, $response, 'c2')) {
            return false;
        }

        $data = &$response['data'];
        $signal = $data[2];

        if ($signal === 99) {

            $this->logger->logInfo('signal quality: unavailable');

            $this->modem->setData('modem.signal.value', 0);
            $this->modem->setData('modem.signal.percent', 0);
            $this->modem->setData('modem.signal', false);
        } else {

            // Houve alteração de sinal em relação a última verificação.
            if ((int) $this->modem->getData('modem.signal.value') !== $signal) {

                $percent = ((($signal - self::MIN_DBM) / (self::MAX_DBM - self::MIN_DBM)) * 100);

                $this->modem->setData('modem.signal.value', $signal);
                $this->modem->setData('modem.signal.percent', $percent);
                $this->modem->setData('modem.signal', true);

                $this->logger->logInfo('signal quality: %0.2f%% (%ddBm)', $percent, $signal);
            }

            $this->sleepStage(180); // Próxima execução em 3min.
        }

        return true;
    }
}