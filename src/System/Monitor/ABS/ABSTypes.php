<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS;

/**
 * Constantes para identificação de dados dos equipamentos ABS.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS
 */
final class ABSTypes
{

    /**
     * Dados seriais customizados (MRI).
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
    const MODEM_SENSOR_PT = 0x05;

    /**
     * Contagem de tempo.
     *
     * @var int
     */
    const MODEM_SENSOR_TT = 0x06;

    /**
     * Totalizador de valor analógico.
     *
     * @var int
     */
    const MODEM_SENSOR_AT = 0x07;

    /**
     * Qualidade do sinal.
     *
     * @var int
     */
    const MODEM_SENSOR_SQ = 0x08;

    /**
     * Nome dos tipos de sensores.
     *
     * @var array
     */
    const SENSOR_TYPE_NAME = [

        self::MODEM_SENSOR_MR => 'Serial MRI',
        self::MODEM_SENSOR_A1 => 'Analog 01-08',
        self::MODEM_SENSOR_A2 => 'Analog 09-16',
        self::MODEM_SENSOR_PF => 'Pulse Frequency',
        self::MODEM_SENSOR_PT => 'Pulse Totalizer',
        self::MODEM_SENSOR_TT => 'Time Totalizer',
        self::MODEM_SENSOR_AT => 'Analog Totalizer',
        self::MODEM_SENSOR_SQ => 'Signal Quality'
    ];
}