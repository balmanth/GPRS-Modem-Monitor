<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Common\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;
use GPRS\System\Entities\SensorEntity;

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
     * Tipos de canais.
     *
     * @var array
     */
    const SENSOR_TYPE_NAME = [

        SensorEntity::MODEM_SENSOR_MR => 'MR',
        SensorEntity::MODEM_SENSOR_A1 => 'A1',
        SensorEntity::MODEM_SENSOR_A2 => 'A2',
        SensorEntity::MODEM_SENSOR_PF => 'PF',
        SensorEntity::MODEM_SENSOR_PC => 'PC',
        SensorEntity::MODEM_SENSOR_TC => 'CT',
        SensorEntity::MODEM_SENSOR_TZ => 'TZ',
        SensorEntity::MODEM_SENSOR_SQ => 'SQ'
    ];

    /**
     * Monta a mensagem para limpeza dos totalizadores.
     *
     * @param array $states
     *            Totalizadores com canais marcados para limpeza.
     * @return string
     */
    private function packClearMessage(array &$states): string
    {
        $pc = ($states['PC'] ?? 0); // Totalizador de pulso
        $tc = ($states['TC'] ?? 0); // Totalizador de tempo
        $tz = ($states['TZ'] ?? 0); // Totalizador de valor analógico

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
            'PC' => '00000000',
            'TC' => '00000000',
            'TZ' => '00000000'
        ];

        $sensors = [];

        for ($i = 0; ($sensor = $this->modem->getSensor($i)) !== NULL; ++ $i) {

            // Reset não configurado ou desnecessário.
            if (! $sensor->needReset()) {
                continue;
            }

            $type = self::SENSOR_TYPE_NAME[$sensor->getType()];

            if (isset($channels[$type])) {

                $index = (7 - $sensor->getIndex());

                $channels[$type][$index] = '1';
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

                $this->logger->logInfo('reseting channel: %s, reset states: %s', $type, $bits);
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

            $type = self::SENSOR_TYPE_NAME[$sensor->getType()];
            $index = $sensor->getIndex();

            $this->logger->logInfo('channel: %s reset index %d', $type, $index);
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
            return $this->writeMessage($this->packClearMessage($states));
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
        if ($this->readMessage(1, 16, 8, $response)) {

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