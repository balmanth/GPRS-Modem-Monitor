<?php
declare(strict_types = 1);
namespace GPRS\System\Data\ABS;

use GPRS\System\Monitor\ABS\ABSTypes;

/**
 * Processa os dados armazenados pelos sensores do modem ABS/ALR.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Data\ABS
 */
trait TraitModemData
{

    /**
     * Obtém um valor inteiro de 16 bits.
     *
     * @param array $values
     *            Valores recebidos do modem.
     * @param int $offset
     *            Posição inicial de leitura.
     * @return int
     */
    private function getWord(array &$values, int &$offset): int
    {
        $buffer = chr($values[$offset ++]) . chr($values[$offset ++]);
        return unpack('n', $buffer)[1];
    }

    /**
     * Obtém um valor inteiro de 32 bits.
     *
     * @param array $values
     *            Valores recebidos do modem.
     * @param int $offset
     *            Posição inicial de leitura.
     * @return int
     */
    private function getDWord(array &$values, int &$offset): int
    {
        $buffer = chr($values[$offset ++]) . chr($values[$offset ++]) . chr($values[$offset ++]) .
             chr($values[$offset ++]);

        $parts = unpack('n*', $buffer);

        return (($parts[1] * 65536) + $parts[2]);
    }

    /**
     * Normaliza os dados.
     *
     * @param array $values
     *            Valores recebidos do modem.
     * @param array $channels
     *            Informação sobre o estado dos canais.
     * @return array Dados normalizados.
     */
    protected function absNormalize(array &$values, array &$channels): array
    {
        $normalized = [];
        $offset = 2; // Ignora bytes 0 e 1 (IO Event)

        foreach ($channels as $type => $status) {

            for ($channel = 0; $channel < 8; ++ $channel) {

                // Canal desabilitado
                if ((int) $status[$channel] !== 1) {
                    continue;
                }

                $value = false;

                switch ($type) {
                    // Entrada analógica 1-8
                    case (ABSTypes::MODEM_SENSOR_A1):
                        $value = $this->getWord($values, $offset);
                        $name = 'A1';
                        break;

                    // Entrada analógica 9-16
                    case (ABSTypes::MODEM_SENSOR_A2):
                        $value = $this->getWord($values, $offset);
                        $name = 'A2';
                        break;

                    // Frequencia de pulso
                    case (ABSTypes::MODEM_SENSOR_PF):
                        $value = $this->getWord($values, $offset);
                        $name = 'FP';
                        break;

                    // Totalizador de pulso
                    case (ABSTypes::MODEM_SENSOR_PT):
                        $value = $this->getDWord($values, $offset);
                        $name = 'CP';
                        break;

                    // Totalizador de tempo
                    case (ABSTypes::MODEM_SENSOR_TT):
                        $value = $this->getDWord($values, $offset);
                        $name = 'CT';
                        break;

                    // Totalizador de valor analógico
                    case (ABSTypes::MODEM_SENSOR_AT):
                        $value = $this->getDWord($values, $offset);
                        $name = 'TZ';
                        break;

                    // Tipo de canal indefinido
                    default:
                        continue 2;
                }

                if ($value !== false) {
                    $normalized["{$name}_{$channel}"] = $value;
                }
            }
        }

        return $normalized;
    }
}