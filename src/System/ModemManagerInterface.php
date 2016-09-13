<?php
declare(strict_types = 1);
namespace GPRS\System;

use GPRS\System\Entities\ModemEntity;

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

    /**
     * Adiciona um registro de dados relacionado a um modem.
     *
     * @param ModemEntity $modem
     *            Entidade com as informações do modem.
     * @param array $channels
     *            Informação sobre o estado dos canais.
     * @param int $index
     *            Índice de identificação do bloco de memória (do modem físico) com o registro.
     * @param int $status
     *            Status do registro.
     * @param int $time
     *            Timestamp de armazenamento do registro.
     * @param array $values
     *            Valores do registro.
     * @return bool True quando os dados foram adicionados com sucesso.
     *         False quando contrário.
     */
    public function addModemData(ModemEntity $modem, array &$channels, int $index, int $status, int $time,
        array &$values): bool;
}