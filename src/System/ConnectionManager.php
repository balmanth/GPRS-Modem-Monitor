<?php
declare(strict_types = 1);
namespace GPRS\System;

use BCL\System\AbstractComponent;
use BCL\System\AbstractComponentList;
use GPRS\System\Entities\ModemEntity;

/**
 * Gerenciador de conexões.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System
 */
final class ConnectionManager extends AbstractComponentList
{

    /**
     * Instância do gerenciador de registros.
     *
     * @var LogManager
     */
    private $logger;

    /**
     *
     * {@inheritDoc}
     *
     * @see \BCL\System\AbstractComponentList::validateInsert($component)
     */
    protected function validateInsert(AbstractComponent $component): bool
    {
        return (parent::validateInsert($component) && $component instanceof Connection);
    }

    /**
     * Verifica se a conexão esta pronta para uso.
     *
     * @param Connection $connection
     *            Instância da conexão.
     * @param ModemEntity $modem
     *            Instância da entidade com informações do modem.
     * @return bool
     */
    private function isReady(Connection $connection, ModemEntity $modem): bool
    {
        return (! $connection->isHolding() && $connection->isExclusive($modem) && $connection->isReady());
    }

    /**
     * Construtor.
     *
     * @param LogManager $logger
     *            Instância do gerenciador de registros.
     */
    public function __construct(LogManager $logger)
    {
        $this->logger = $logger;
        $this->exclusivity = [];

        parent::__construct();
    }

    /**
     * Obtém uma conexão.
     *
     * Se a conexão não existir, um stream cliente de conexão será criado.
     * Se a conexão já existir, será verificado se o stream cliente possui uma conexão ativa.
     *
     * @param ModemEntity $modem
     *            Instância da entidade com informações do modem.
     * @return Connection|NULL Cliente de conexão ou Null quando o cliente de não puder conectar-se ao endereço.
     */
    public function getConnection(ModemEntity $modem)
    {
        $address = sprintf('%s:%d', $modem->getHost(), $modem->getPort());

        if (! $this->hasComponent($address)) {
            $connection = $this->insertComponent(new Connection($this->logger, $modem));
        } else {
            $connection = $this->fetchComponent($address);
        }

        $this->logger->setConnection($connection);

        if (! $this->isReady($connection, $modem)) {
            $connection = NULL;
        }

        $this->logger->setConnection(NULL);
        return $connection;
    }

    /**
     * Obtém a instância do gerenciador de registros.
     *
     * @return LogManager
     */
    public function getLogger(): LogManager
    {
        return $this->logger;
    }
}