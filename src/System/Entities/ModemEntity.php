<?php
declare(strict_types = 1);
namespace GPRS\System\Entities;

use BCL\System\AbstractObject;

/**
 * Manipula as informações do modem.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Entities
 */
final class ModemEntity extends AbstractObject
{

    /**
     * Informações do modem.
     *
     * @var array
     */
    private $data;

    /**
     * Etapa atual do modem.
     *
     * @var int
     */
    private $stage;

    /**
     * Número máximo de etapas do modem.
     *
     * @var int
     */
    private $maxStage;

    /**
     * Lista de manipuladores dos sensores.
     *
     * @var array
     */
    private $sensors;

    /**
     * Lista de dados customizados.
     *
     * @var array
     */
    private $custom;

    /**
     * Atualiza a lista de manipuladores de informações sobre os sensores.
     *
     * @param array $conversions
     *            Lista de manipuladores das informações de conversão.
     * @return void
     */
    private function updateSensors(array &$conversions)
    {
        foreach ($this->data['sensors'] as $index => $data) {

            if (! isset($this->sensors[$index])) {
                $this->sensors[$index] = new SensorEntity($this, $data, $conversions);
            } else {
                $this->sensors[$index]->update($data, $conversions);
            }
        }
    }

    /**
     * Construtor.
     *
     * @param array $data
     *            Informações do modem.
     * @param int $stage
     *            Etapa atual do modem (Atualizado por referência).
     * @param array $conversions
     *            Lista de manipuladores das informações de conversão.
     */
    public function __construct(array &$data, int &$stage, array &$conversions)
    {
        $this->data = $data;
        $this->stage = &$stage;
        $this->maxStage = 0;
        $this->sensors = [];
        $this->custom = [];

        $this->updateSensors($conversions);
    }

    /**
     * Atualiza as informações do modem.
     *
     * @param array $data
     *            Novas informações do modem.
     * @param array $conversions
     *            Lista de manipuladores das informações de conversão.
     */
    public function update(array &$data, array &$conversions)
    {
        $this->data['host'] = $data['host'];
        $this->data['port'] = $data['port'];
        $this->data['next_index'] = $data['next_index'];

        $this->updateSensors($conversions);
    }

    /**
     * Avança a etapa atual do modem.
     *
     * @return void
     */
    public function nextStage()
    {
        $this->stage = (($this->stage + 1) % $this->maxStage);
    }

    /**
     * Obtém uma informação customizada sobre o modem.
     *
     * @param string $key
     *            Nome da chave que identifica a informação.
     * @return mixed|NULL Informação solicitada ou Null quando a chave não existir.
     */
    public function getData(string $key)
    {
        return $this->data[$key] ?? NULL;
    }

    /**
     * Define uma informação customizada sobre o modem.
     *
     * @param string $key
     *            Nome da chave que identifica a informação.
     * @param mixed $value
     *            Informação customizada.
     * @return void
     */
    public function setData(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Obtém uma informação customizada da etapa atual do modem.
     *
     * @param string $key
     *            Nome da chave que identifica a informação.
     * @return mixed|NULL Informação solicitada ou Null quando a chave não existir na etapa atual.
     */
    public function getStageData(string $key)
    {
        return $this->getData($key . '[' . $this->stage . ']');
    }

    /**
     * Define uma informação customizada da etapa atual sobre o modem.
     *
     * @param string $key
     *            Nome da chave que identifica a informação na etapa atual.
     * @param mixed $value
     *            Informação customizada.
     * @return void
     */
    public function setStageData(string $key, $value)
    {
        $this->setData($key . '[' . $this->stage . ']', $value);
    }

    /**
     * Define o número máximo de etapas do modem.
     *
     * @param int $maxStage
     * @return void
     */
    public function setMaxStage(int $maxStage)
    {
        if ($maxStage < 0) {
            $maxStage = 0;
        }
        $this->maxStage = $maxStage;
    }

    /**
     * Obtém o número máximo de etapas do modem.
     *
     * @return int
     */
    public function getMaxStage(): int
    {
        return $this->maxStage;
    }

    /**
     * Obtém a etapa atual do modem.
     *
     * @return int
     */
    public function getStage(): int
    {
        return $this->stage;
    }

    /**
     * Obtém o endereço de conexão.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->data['host'];
    }

    /**
     * Obtém a porta de conexão.
     *
     * @return int
     */
    public function getPort(): int
    {
        return (int) $this->data['port'];
    }

    /**
     * Obtém o próximo índice de leitura.
     *
     * @return int
     */
    public function getNextIndex(): int
    {
        return (int) $this->data['next_index'];
    }

    /**
     * Define o próximo índice de leitura.
     *
     * @param int $index
     */
    public function setNextIndex(int $index)
    {
        $this->data['next_index'] = $index;
    }

    /**
     * Obtém as informações de um sensor a partir do seu índice.
     *
     * @param int $index
     * @return SensorEntity|NULL Instância do sensor ou Null quando o sensor não existir.
     */
    public function getSensor(int $index)
    {
        return $this->sensors[$index] ?? NULL;
    }
}