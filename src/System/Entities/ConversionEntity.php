<?php
declare(strict_types = 1);
namespace GPRS\System\Entities;

use BCL\System\AbstractObject;

/**
 * Manipula as informações de conversão dos dados armazenados pelos sensores dos modems.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Entities
 */
final class ConversionEntity extends AbstractObject
{

    /**
     * Informações de covnersão.
     *
     * @var array
     */
    private $data;

    /**
     * Construtor.
     *
     * @param array $data
     *            Informações de conversão.
     */
    public function __construct(array &$data)
    {
        $this->data = $data;
    }

    /**
     * Atualiza as informações de conversão.
     *
     * @param array $data
     *            Novas informações de conversão.
     */
    public function update(array &$data)
    {
        $this->data['reset_time'] = $data['reset_time'];
    }

    /**
     * Obtém o intervalo (em minutos) para o reset do sensor.
     *
     * @return int
     */
    public function getResetTime(): int
    {
        return (int) $this->data['reset_time'];
    }
}