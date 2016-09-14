<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Common\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;
use GPRS\System\Monitor\ABS\ABSTypes;

/**
 * Limpa os valores armazenados nos totalizadores do modem ou datalogger.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Common\Actions
 */
abstract class AbstractClearTotalizersAction extends AbstractABSMonitorAction
{

    /**
     * Monta a mensagem para limpeza dos totalizadores.
     *
     * @param array $states
     *            Totalizadores com canais marcados para limpeza.
     * @return string
     */
    private function packClearMessage(array &$states): string
    {
        $pc = ($states[ABSTypes::MODEM_SENSOR_PT] ?? 0); // Totalizador de pulso
        $tc = ($states[ABSTypes::MODEM_SENSOR_TT] ?? 0); // Totalizador de tempo
        $tz = ($states[ABSTypes::MODEM_SENSOR_AT] ?? 0); // Totalizador de valor analógico

        return $this->modbus->pack16(1, $this->getWriteAddress(), $pc, $tc, $tz, 0, 0, 0, 0, 0, 3);
    }

    /**
     * Obtém os totalizadores que suportam reset e estão configurados para isso.
     *
     * @return array
     */
    private function &getClearList(): array
    {
        $channels = [
            ABSTypes::MODEM_SENSOR_PT => '00000000',
            ABSTypes::MODEM_SENSOR_TT => '00000000',
            ABSTypes::MODEM_SENSOR_AT => '00000000'
        ];

        $sensors = [];

        for ($i = 0; ($sensor = $this->modem->getSensor($i)) !== NULL; ++ $i) {

            if (! $sensor->needReset()) {
                continue;
            }

            $type = $sensor->getType();

            if (isset($channels[$type])) {

                $channels[$type][(7 - $sensor->getIndex())] = '1';
                $sensors[] = $sensor;
            }
        }

        $this->modem->setData('modem.reset.sensors', $sensors);

        return $channels;
    }

    /**
     * Obtém os totalizadores com canais marcados para reinicialização.
     *
     * @param array $channels
     *            Totalizadores que suportam reset.
     * @return array
     */
    private function getClearStates(array &$channels): array
    {
        $states = [];

        foreach ($channels as $type => $bits) {

            if (($value = bindec($bits)) > 0) {

                $this->logger->logInfo('reseting states: %s, channel: %s', $bits, ABSTypes::CHANNEL_TYPE_NAME[$type]);

                $states[$type] = $value;
            }
        }

        return $states;
    }

    /**
     * Atualiza as informações sobre os canais dos totalizadores reiniciados.
     *
     * @return void
     */
    private function updateClearedChannels()
    {
        $sensors = (array) $this->modem->getData('modem.reset.sensors');

        foreach ($sensors as $sensor) {

            $this->logger->logInfo('reset index %d, channel: %s', $sensor->getIndex(),
                self::CHANNEL_TYPE_NAME[$sensor->getType()]);

            $sensor->updateResetDate();
        }

        $this->modem->setData('modem.reset.sensors', NULL);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\Monitor\ABS\AbstractABSMonitorAction::writeCommand()
     */
    protected function writeCommand(): bool
    {
        $states = $this->getClearStates($this->getClearList());

        if (! empty($states)) {
            return $this->sendMessage($this->packClearMessage($states));
        }

        return false;
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

            $this->updateClearedChannels();
            $this->sleepStage(120); // Próxima execução em 2min

            return true;
        }

        return false;
    }

    /**
     * Obtém o endereço de envio da mensagem para limpeza dos totalizadores.
     *
     * @return int
     */
    abstract protected function getWriteAddress(): int;
}