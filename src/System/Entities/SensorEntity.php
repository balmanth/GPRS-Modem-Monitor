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
     * Informações do sensor.
     *
     * @var array
     */
    private $data;

    /**
     * Intervalo de tempo (em segundos) para o reset do valor atual sensor.
     *
     * @var int
     */
    private $resetInterval;

    /**
     * Timestamp do último reset.
     *
     * @var int
     */
    private $lastResetTime;

    /**
     * Atualiza as informações de conversão do sensor.
     *
     * @param array $conversions
     *            Lista de manipuladores das informações de conversão.
     * @return void
     */
    private function updateConversion(array &$conversions)
    {
        $convId = $this->data['conversion_id'];

        $this->resetInterval = (isset($conversions[$convId]) ? $conversions[$convId]->getResetTime() : 0);
    }

    /**
     * Construtor.
     *
     * @param array $data
     *            Informações do sensor.
     * @param array $conversions
     *            Lista de manipuladores das informações de conversão.
     */
    public function __construct(array &$data, array &$conversions)
    {
        $this->data = $data;

        $this->updateConversion($conversions);
        $this->updateResetDate();
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
        // Intervalo de reset não definido ou inferior ao permitido.
        if ($this->resetInterval < 60) {
            return false;
        }

        // A diferença entre o último reset e o tempo atual esta dentro do intervalo de espera.
        if ((time() - $this->lastResetTime) < $this->resetInterval) {
            return false;
        }

        // Tolerância de 180 segundos na verificação do tempo de reset.
        return ((time() % $this->resetInterval) < 180);
    }

    /**
     * Atualiza o timestamp do último reset.
     *
     * @return void
     */
    public function updateResetDate()
    {
        if ($this->resetInterval > 0) {
            $this->lastResetTime = (time() - (time() % $this->resetInterval));
        }
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