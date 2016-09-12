<?php
declare(strict_types = 1);
namespace GPRS\Local;

use BCL\System\AbstractObject;
use GPRS\System\ModemManagerInterface;
use GPRS\System\Entities\SensorEntity;

/**
 * Contêm os métodos para gestão das informações dos modems.
 *
 * Utilize os métodos demonstrados abaixo para carregar as informações de uma base de dados ou API.
 * A informações abaixo são estruturadas de acordo com as características fornecidas pelos modems ABS/ALR.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\Local
 */
final class ModemManager extends AbstractObject implements ModemManagerInterface
{

    /**
     * Cria um sensor fictício para um modem teste.
     *
     * @param int $type
     *            Tipo de sensor.
     * @param int $index
     *            Posição do sensor.
     *            Indica a posição de memória com os dados do sensor e posição física do conector no modem.
     * @param int $resetInterval
     *            Intervalo para resetar os valores (Suportado somente por sensores do tipo totalizador).
     * @return array
     */
    private function createSensor(int $type, int $index, int $resetInterval = 0): array
    {
        static $id = 0; // Id único do sensor.

        return [
            'id' => $id ++,
            'type' => $type,
            'index' => $index,
            'reset_interval' => $resetInterval,
            'last_reset_date' => 0
        ];
    }

    /**
     * Cria um modem fictício para testes.
     *
     * @return array
     */
    private function createModem(string $host, int $port): array
    {
        static $id = 0; // Id único do modem.

        return [
            'id' => $id ++,
            'host' => $host,
            'port' => $port,
            'next_index' => 0,
            'sensors' => [

                // Entradas analógicas 1 (até 8 entradas, não suporta reset)
                $this->createSensor(SensorEntity::MODEM_SENSOR_A1, 0),
                $this->createSensor(SensorEntity::MODEM_SENSOR_A1, 1),

                // Entradas analógicas 2 (até 8 entradas, não suporta reset)
                $this->createSensor(SensorEntity::MODEM_SENSOR_A2, 0),
                $this->createSensor(SensorEntity::MODEM_SENSOR_A2, 1),

                // Totalizador de pulso (Até 8 entradas, suporta reset)
                $this->createSensor(SensorEntity::MODEM_SENSOR_PC, 0), // Sem reset
                $this->createSensor(SensorEntity::MODEM_SENSOR_PC, 1, 3600), // Com reset a cada 1h

                // Frequência de pulso (Até 8 entradas, não suporta reset)
                $this->createSensor(SensorEntity::MODEM_SENSOR_PF, 0),
                $this->createSensor(SensorEntity::MODEM_SENSOR_PF, 1),

                // Totalizador de tempo (Até 8 entradas, suporta reset)
                $this->createSensor(SensorEntity::MODEM_SENSOR_TC, 0), // Sem reset
                $this->createSensor(SensorEntity::MODEM_SENSOR_TC, 0, 7200), // Com reset a cada 2h

                // Totalizador de valor analógico (Até 8 entradas, suporta reset)
                $this->createSensor(SensorEntity::MODEM_SENSOR_TZ, 0), // Sem reset
                $this->createSensor(SensorEntity::MODEM_SENSOR_TZ, 0, 10800), // Com reset a cada 3h

                // Qualidade do sinal
                $this->createSensor(SensorEntity::MODEM_SENSOR_SQ, 0, 0)
            ]
        ];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see GPRS\System\ModemManagerInterface::loadModems($type)
     */
    public function loadModems(int $type): array
    {
        return [
            $this->createModem('127.0.0.1', 5000),
            $this->createModem('127.0.0.1', 5001)
        ];
    }
}