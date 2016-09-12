<?php
declare(strict_types = 1);
namespace GPRS\System\Entities;

use BCL\System\AbstractObject;

/**
 * Manipula as informações do sensor do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Entities
 */
final class SensorEntity extends AbstractObject
{

    /**
     * Dados seriais (MRI).
     *
     * @var int
     */
    const MODEM_SENSOR_MR = 0x01;

    /**
     * Valor analógico (Parte 1 a 8).
     *
     * @var int
     */
    const MODEM_SENSOR_A1 = 0x02;

    /**
     * Valor analógico (Parte 9 a 16).
     *
     * @var int
     */
    const MODEM_SENSOR_A2 = 0x03;

    /**
     * Frequência de pulso.
     *
     * @var int
     */
    const MODEM_SENSOR_PF = 0x04;

    /**
     * Contagem de pulso.
     *
     * @var int
     */
    const MODEM_SENSOR_PC = 0x05;

    /**
     * Contagem de tempo.
     *
     * @var int
     */
    const MODEM_SENSOR_TC = 0x06;

    /**
     * Totalizador de valor analógico.
     *
     * @var int
     */
    const MODEM_SENSOR_TZ = 0x07;

    /**
     * Qualidade do sinal.
     *
     * @var int
     */
    const MODEM_SENSOR_SQ = 0x08;

    /**
     * Informações do sensor.
     *
     * @var array
     */
    private $data;

    /**
     * Instância do manipulador de informações do modem.
     *
     * @var ModemEntity
     */
    private $modem;

    /**
     * Atualiza as informações de conversão do sensor.
     *
     * @param array $conversions
     *            Lista de manipuladores das informações de conversão.
     * @return void
     */
    private function updateConversion(array &$conversions)
    {
        $conversionId = $this->data['conversion_id'];
        $resetInterval = (isset($conversions[$conversionId]) ? $conversions[$conversionId]->getResetTime() : 0);

        $this->data['reset_time'] = $resetInterval;
    }

    /**
     * Construtor.
     *
     * @param ModemEntity $modem
     *            Instância do manipulador de informações do modem.
     * @param array $data
     *            Informações do sensor.
     * @param array $conversions
     *            Lista de manipuladores das informações de conversão.
     */
    public function __construct(ModemEntity $modem, array &$data, array &$conversions)
    {
        $this->modem = $modem;
        $this->data = $data;

        $this->updateConversion($conversions);
    }

    /**
     * Atualiza as informações do sensor.
     *
     * @param array $data
     *            Novas informações do sensor.
     * @param array $conversions
     *            Lista de manipuladores das informações de conversão.
     */
    public function update(array &$data, array &$conversions)
    {
        $this->data['conversion_id'] = $data['conversion_id'];

        $this->updateConversion($conversions);
    }

    /**
     * Verifica se o sensor precisa ser resetado de acordo com suas definições internas.
     *
     * @return bool True para resetar.
     *         False para não resetar.
     */
    public function needReset(): bool
    {
        // Intervalo de reset não definido.
        if (($resetInterval = (int) $this->data['reset_time']) === 0) {
            return false;
        }

        // Data e Hora do último reset corresponde a Data e Hora atual.
        if (date('YmdH', (int) $this->data['last_reset_date']) === date('YmdH')) {
            return false;
        }

        // O minuto de reset esta no intervalo de zero (minuto exato) a 3 (tolerância)
        return ((int) ((time() % $resetInterval) / 60) < 3);
    }

    /**
     * Atualiza o timestamp do último reset.
     *
     * @return void
     */
    public function updateResetDate()
    {
        $this->data['last_reset_date'] = time();
    }

    /**
     * Obtém o índice de memória do sensor.
     *
     * @return int
     */
    public function getIndex(): int
    {
        return (int) $this->data['index'];
    }

    /**
     * Obtém o tipo do sensor.
     *
     * @return int
     */
    public function getType(): int
    {
        return (int) $this->data['type'];
    }
}