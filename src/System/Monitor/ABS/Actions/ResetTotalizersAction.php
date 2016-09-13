<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS\Actions;

use GPRS\System\Monitor\ABS\AbstractABSMonitorAction;
use GPRS\System\Entities\SensorEntity;

/**
 * Reinicia os totalizadores do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS\Actions
 */
final class ResetTotalizersAction extends AbstractABSMonitorAction
{

    /**
     * Nomes dos sensores.
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
     * Obtém uma lista dos totalizadores para reset.
     *
     * @return array Lista dos totalizadores para reset.
     */
    private function &getResetChannels(): array
    {
        $channels = [
            'PC' => '00000000',
            'TC' => '00000000',
            'TZ' => '00000000'
        ];

        $sensors = [];

        for ($i = 0; ($sensor = $this->modem->getSensor($i)) !== NULL; ++ $i) {

            if (! $sensor->needReset()) {
                continue;
            }

            $type = self::SENSOR_TYPE_NAME[$sensor->getType()];

            if (isset($channels[$type])) {

                $index = (8 - $sensor->getIndex());

                $channels[$type][$index] = '1';
                $sensors[] = $sensor;
            }
        }

        $this->modem->setData('modem.reset.sensors', $sensors);
        return $channels;
    }

    /**
     * Obtém a lista de totalizadores com estados de reset.
     *
     * @param array $channels
     *            Lista dos totalizadores para reset.
     * @return array Lista dos totalizadores com estados de reset.
     */
    private function getResetStates(array &$channels): array
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

        $states = $this->getResetStates($this->getResetChannels());

        // Não existe totalizadores para resetar
        if (empty($states)) {
            return false;
        }

        $pc = ($states['PC'] ?? 0); // Contagem de pulso
        $tc = ($states['TC'] ?? 0); // Contagem de tempo
        $tz = ($states['TZ'] ?? 0); // Totalizador de valor analógico

        $message = $this->modbus->pack16(1, 64502, $pc, $tc, $tz, 0, 0, 0, 0, 0, 3);
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
        if (! $this->readMessage(1, 16, 8, $response)) {
            return false;
        }

        $sensors = $this->modem->getData('modem.reset.sensors');

        if (is_array($sensors)) {

            foreach ($sensors as $sensor) {

                $type = self::SENSOR_TYPE_NAME[$sensor->getType()];
                $index = $sensor->getIndex();

                $this->logger->logInfo('channel: %s reset index %d', $type, $index);
                $sensor->updateResetDate();
            }
        }

        $this->modem->setData('modem.reset.sensors', NULL);
        $this->sleepStage(600); // Próxima execução em 10min

        return true;
    }
}