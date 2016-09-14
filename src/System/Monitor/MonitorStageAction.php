<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor;

use BCL\System\Actions\AbstractAction;
use GPRS\System\Connection;
use GPRS\System\LogManager;
use GPRS\System\ModemManagerInterface;
use GPRS\System\Entities\ModemEntity;

/**
 * Ação disparada pelo gerenciador de ações para notificar uma etapa de monitoramento.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor
 */
final class MonitorStageAction extends AbstractAction
{

    /**
     * Instância da entidade com informações do modem.
     *
     * @var ModemEntity
     */
    private $modemEntity;

    /**
     * Instância da conexão.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Constrtuor.
     *
     * @param AbstractMonitorBase $monitor
     *            Instância do monitor do modem.
     * @param ModemEntity $modemEntity
     *            Instância da entidade com informações do modem.
     * @param Connection $connection
     *            Instância da conexão.
     */
    public function __construct(AbstractMonitorBase $monitor, ModemEntity $modemEntity, Connection $connection)
    {
        parent::__construct($monitor);

        $this->modemEntity = $modemEntity;
        $this->connection = $connection;
    }

    /**
     * Obtém a instância do gerenciador de modems.
     *
     * @return ModemManagerInterface
     */
    public function getModemManager(): ModemManagerInterface
    {
        return $this->actionSender->getModemManager();
    }

    /**
     * Obtém a instância do gerenciador de registros.
     *
     * @return LogManager
     */
    public function getLogger(): LogManager
    {
        return $this->actionSender->getLogger();
    }

    /**
     * Obtém a instância da entidade com informações do modem.
     *
     * @return ModemEntity
     */
    public function getModemEntity(): ModemEntity
    {
        return $this->modemEntity;
    }

    /**
     * Obtém a instância da conexão com o modem.
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }
}