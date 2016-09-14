<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Modem\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;

/**
 * Obtém a qualidade do sinal do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Modem\Actions
 */
final class GetSignalQualityAction extends AbstractABSMonitorAction
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
     * Processa uma resposta de sinal indisponível.
     *
     * @return void
     */
    private function processUnavailableSignal()
    {
        $this->logger->logInfo('signal quality: unavailable');

        $this->modem->setData('modem.signal.value', 0);
        $this->modem->setData('modem.signal.percent', 0);
        $this->modem->setData('modem.signal', false);
    }

    /**
     * Processa uma resposta de sinal disponível.
     *
     * @param int $signal
     *            Nível de qualidade do sinal em dBm.
     * @return void
     */
    private function processAvailableSignal(int $signal)
    {
        $lastPercent = (int) $this->modem->getData('modem.signal.percent');
        $newPercent = ((($signal - self::MIN_DBM) / (self::MAX_DBM - self::MIN_DBM)) * 100);

        if ($newPercent > $lastPercent) {
            $this->logger->logInfo('signal quality: increased to %0.2f%% (%d dBm)', $newPercent, $signal);
        } else
            if ($newPercent < $lastPercent) {
                $this->logger->logInfo('signal quality: decreased to %0.2f%% (%d dBm)', $newPercent, $signal);
            } else {
                $this->logger->logInfo('signal quality: stable on %0.2f%% (%d dBm)', $newPercent, $signal);
            }

        $this->modem->setData('modem.signal.value', $signal);
        $this->modem->setData('modem.signal.percent', $newPercent);
        $this->modem->setData('modem.signal', true);
    }

    /**
     * Atualiza as informações de qualidade do sinal.
     *
     * @param array $data
     *            Dados recebidos pelo gateway.
     * @return void
     */
    private function updateSignalQuality(array &$data)
    {
        $signal = $data[2];

        if ($signal === 99) {
            $this->processUnavailableSignal();
        } else {
            $this->processAvailableSignal($signal);
        }
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\AbstractABSMonitorAction::writeCommand()
     */
    protected function writeCommand(): bool
    {
        $message = $this->modbus->pack4(1, 65000, 1);
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

        if ($this->receiveMessage($message, 7)) {

            $response = $this->modbus->unpack($message, 1, 4, 'c2');

            $this->updateSignalQuality($response['data']);
            $this->sleepStage(180); // Próxima execução em 3min.

            return true;
        }

        return false;
    }
}