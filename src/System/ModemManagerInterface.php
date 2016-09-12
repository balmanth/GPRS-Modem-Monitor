<?php
declare(strict_types = 1);
namespace GPRS\System;

/**
 * Define os métodos necessários para manipulação dos modems.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System
 */
interface ModemManagerInterface
{

    /**
     * Carrega a lista de conversão dos dados armazenados pelos sensores dos modems.
     *
     * @return array
     */
    public function loadConversions(): array;

    /**
     * Carrega a lista de modems.
     *
     * @param int $type
     *            Tipo de modem.
     * @return array
     */
    public function loadModems(int $type): array;
}